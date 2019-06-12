jQuery(document).ready(($) => {
  const products = JSON.parse(ebBookingParams.products)
  let selectedProducts = [],
      selectedTotal = 0,
      productIndex = 0

  ebAddProduct()
  flatpickr('.eb-datetime-picker', {
    enableTime: true,
    dateFormat: 'Y-m-d H:i:S',
    altInput: true,
    altFormat: 'F j, Y h:i K'
  })

  $(document).on('change', '.eb-product-selected', ebCalculateTotal)
  $(document).on('change', '.eb-product-type', ebCalculateTotal)
  $(document).on('keyup', '.eb-product-quantity', ebCalculateTotal)
  $('#eb-add-product').click(ebAddProduct)

  $(document).on('click', '.eb-icon-container', (event) => {
    let removeIndex = event.currentTarget.getAttribute('index')

    $('#eb-product-row-' + removeIndex).remove()

    ebCalculateTotal()
  })

  $('#eb-booking-form').submit((event) => {
    event.preventDefault()

    let ebBookingObject = {}

    $('#eb-booking-form').serializeArray().forEach((field) => {
      const name = field.name.replace(/-/g, '_')

      ebBookingObject[name] = field.value
    })

    ebBookingObject.products = selectedProducts
    ebBookingObject.total = selectedTotal

    const params = {
      action: 'eb_booking_form_process',
      eb_booking: ebBookingObject
    }

    $.ajax({
      type: 'post',
      url: ebBookingParams.adminAjaxUrl,
      data: params,
      success: (response) => {
        $('#eb-booking-form').hide()
        $('#eb-success h3').text(response)
        $('#eb-success').show()
      }
    })
  })

  function ebAddProduct() {
    const options = products.map((product) => {
      return `<option value='${product.sku}'>${product.name}</option>`
    })

    const template = `
      <div id='eb-product-row-${productIndex}' class='row eb-selected-row'>
        <div class='col-md-5'>
          <select class='eb-product-selected'>
            ${options}
          </select>
        </div>
        <div class='col-md-3'>
          <select class='eb-product-type'>
            <option value='regular'>Regular</option>
            <option value='spicy'>Spicy</option>
          </select>
        </div>
        <div class='col-md-3'>
          <input type='number' class='eb-product-quantity' placeholder='Quantity'>
        </div>
        <div class='col-md-1'>
          <span class='eb-icon-container' index='${productIndex}'>
            <svg viewPort='0 0 12 12' version='1.1' xmlns='http://www.w3.org/2000/svg' class='eb-remove-icon'>
              <line x1='1' y1='20' x2='20' y2='1' stroke='black' stroke-width='2' />
              <line x1='1' y1='1' x2='20' y2='20' stroke='black' stroke-width='2' />
            </svg>
          </span>
        </div>
      </div>
    `

    productIndex++

    $('#eb-products').append(template)
  }

  function ebCalculateTotal() {
    $('#eb-product-price-total').text(ebGetTotal())
  }

  function ebGetTotal() {
    const selectedProductRows = $('#eb-products').children('div').length

    selectedProducts = []
    selectedTotal = 0

    for ( let index of Array(selectedProductRows).keys() ) {
      const typeEl = $('.eb-product-type')[index],
            quantityEl = $('.eb-product-quantity')[index],
            productSelected = $('.eb-product-selected')[index].value,
            quantity = !quantityEl.value ? 0 : parseInt(quantityEl.value),
            price = (products.filter((product) => product.sku === productSelected).shift() || {}).price || 0,
            type = typeEl.value === 'spicy' ? 100 : 0

      let product = {
        sku: productSelected,
        type: typeEl.value,
        quantity: quantity,
        price: price + type
      }

      selectedProducts.push(product)

      selectedTotal += quantity * (price + type)
    }

    selectedTotal = selectedTotal.toFixed(2)

    return selectedTotal
  }
})
