jQuery(document).ready(($) => {
  const products = JSON.parse(ebBookingParams.products),
        options = JSON.parse(ebBookingParams.options),
        validDate = new Date().fp_incr(options.preparation_days || 0),
        biggerNum = Math.floor((Math.random() * 10) + 5),
        smallerNum = Math.floor((Math.random() * 5) + 1),
        captchaAnswer = biggerNum + smallerNum

  let selectedProducts = [],
      selectedTotal = 0,
      productIndex = 0

  $('#captcha-equation').text(`${biggerNum} + ${smallerNum} = ?`)

  ebAddProduct()
  flatpickr('.eb-datetime-picker', {
    enableTime: true,
    dateFormat: 'Y-m-d H:i:S',
    altInput: true,
    altFormat: 'F j, Y h:i K',
    disableMobile: true,
    minDate: validDate,
    disable: options.disabled_dates,
    minTime: options.start_time,
    maxTime: options.end_time
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

    let ebBookingObject = {},
        combinedProducts = []

    $('#eb-booking-form').serializeArray().forEach((field) => {
      const name = field.name.replace(/-/g, '_')

      ebBookingObject[name] = field.value
    })

    selectedProducts.forEach((selectedProduct) => {
      if ( combinedProducts.some((combinedProduct) => combinedProduct.sku === selectedProduct.sku) ) {
        let matchedProduct = combinedProducts.filter((combinedProduct) =>
          combinedProduct.sku === selectedProduct.sku
        ).shift()

        matchedProduct.quantity += selectedProduct.quantity
        matchedProduct.price += selectedProduct.price

        combinedProducts = combinedProducts.filter((combinedProduct) =>
          combinedProduct.sku !== selectedProduct.sku
        ).concat(matchedProduct)
      }
      else {
        combinedProducts.push(selectedProduct)
      }
    })

    ebBookingObject.products = combinedProducts
    ebBookingObject.total = parseFloat(selectedTotal)

    if ( !ebIsFormValid(ebBookingObject) ) return

    const params = {
      action: 'eb_booking_form_process',
      eb_booking: ebBookingObject
    }

    ebToggleOverlayLoader()

    $.ajax({
      type: 'post',
      url: ebBookingParams.adminAjaxUrl,
      data: params,
      success: (response) => {
        if ( response === ebBookingParams.errorMsg ) {
          $('#eb-booking-form').hide()
          $('#eb-success h3').text(response)
          $('#eb-success').show()
          ebToggleOverlayLoader(false)

          return
        }

        const order = JSON.parse(response)
        const products = order.products.map((product) => {
          const totalProductPrice = parseFloat(product.price) * parseFloat(product.quantity)

          return `
            <tr>
              <td>${product.name} x ${product.quantity}</td>
              <td>₱${totalProductPrice.toFixed(2)}</td>
            </tr>`
        })

        const template = `
          <h2>Order received</h2>
          <h3>Thank you. Your order has been received.</h3>
          <table>
            <tr>
              <td>
                <div class='eb-summary-title'>Order Number:</div>
                <div class='eb-summary-value'>${order.id}</div>
              </td>
            </tr>
            <tr>
              <td>
                <div class='eb-summary-title'>Name:</div>
                <div class='eb-summary-value'>${order.name}</div>
              </td>
            </tr>
            <tr>
              <td>
                <div class='eb-summary-title'>Email Address:</div>
                <div class='eb-summary-value'>${order.email_address}</div>
              </td>
            </tr>
            <tr>
              <td>
                <div class='eb-summary-title'>Contact Number:</div>
                <div class='eb-summary-value'>${order.contact_number}</div>
              </td>
            </tr>
            <tr>
              <td>
                <div class='eb-summary-title'>Address:</div>
                <div class='eb-summary-value'>${order.address}</div>
              </td>
            </tr>
            <tr>
              <td>
                <div class='eb-summary-title'>Delivery Date:</div>
                <div class='eb-summary-value'>${order.delivery_date}</div>
              </td>
            </tr>
            <tr>
              <td>
                <div class='eb-summary-title'>Total:</div>
                <div class='eb-summary-value'>₱${parseFloat(order.total).toFixed(2)}</div>
              </td>
            </tr>
          </table>
          <h2>Order details</h2>
          <table>
            <tr>
              <th>Product</th>
              <th>Total</th>
            </tr>
            ${products}
            <tr>
              <th>Total:</th>
              <td>₱${parseFloat(order.total).toFixed(2)}</td>
            </tr>
            <tr>
              <th>Additional Note:</th>
              <td>${order.additional_notes}</td>
            </tr>
          </table>
        `
        $('#eb-booking-form').hide()
        $('#eb-success').html(template)
        $('#eb-success').show()
        ebToggleOverlayLoader(false)
      }
    })
  })

  function ebAddProduct() {
    const options = products.map((product) => {
      return `<option data-id='${product.id}' value='${product.sku}'>${product.name}</option>`
    })

    const template = `
      <div id='eb-product-row-${productIndex}' class='row eb-selected-row'>
        <div class='col-md-5'>
          <select class='eb-product-selected eb-required'>
            ${options}
          </select>
        </div>
        <div class='col-md-3'>
          <select class='eb-product-type eb-required'>
            <option value='regular'>Regular</option>
            <option value='spicy'>Spicy</option>
          </select>
        </div>
        <div class='col-md-3'>
          <input type='number' class='eb-product-quantity eb-required' placeholder='Quantity'>
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
      const quantityEl = $('.eb-product-quantity')[index],
            productSelected = $('.eb-product-selected')[index].value,
            productSelectedId = $('.eb-product-selected option:selected')[index].getAttribute('data-id'),
            quantity = !quantityEl.value ? 0 : parseInt(quantityEl.value),
            price = (products.filter((product) => product.sku === productSelected).shift() || {}).price || 0

      const productTotal = quantity * price

      let product = {
        id: productSelectedId,
        sku: productSelected,
        quantity: quantity,
        price: productTotal
      }

      selectedProducts.push(product)

      selectedTotal += productTotal
    }

    selectedTotal = selectedTotal.toFixed(2)

    return selectedTotal
  }

  function ebIsFormValid(formObject) {
    let isValid = true,
        emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

    $('.eb-form-name').removeClass('eb-required-field')
    $('.eb-form-email-address').removeClass('eb-required-field')
    $('.eb-form-contact-number').removeClass('eb-required-field')
    $('.eb-form-delivery-date').removeClass('eb-required-field')
    $('.eb-form-address').removeClass('eb-required-field')
    $('.eb-product-selected').removeClass('eb-required-field')
    $('.eb-product-type').removeClass('eb-required-field')
    $('.eb-product-quantity').removeClass('eb-required-field')
    $('.eb-form-captcha').removeClass('eb-required-field')

    if ( !formObject.name ) {
      $('.eb-form-name').addClass('eb-required-field')
      isValid = false
    }
    if ( !emailRegex.test(formObject.email_address) ) {
      $('.eb-form-email-address').addClass('eb-required-field')
      isValid = false
    }
    if ( !formObject.contact_number ) {
      $('.eb-form-contact-number').addClass('eb-required-field')
      isValid = false
    }
    if ( !formObject.delivery_date || !(validDate < new Date(formObject.delivery_date)) ) {
      $('.eb-form-delivery-date').addClass('eb-required-field')
      isValid = false
    }
    if ( !formObject.address ) {
      $('.eb-form-address').addClass('eb-required-field')
      isValid = false
    }
    if ( !formObject.products.length ) {
      $('.eb-product-selected').addClass('eb-required-field')
      $('.eb-product-type').addClass('eb-required-field')
      $('.eb-product-quantity').addClass('eb-required-field')
      isValid = false
    }
    if ( !formObject.total ) {
      alert('Please select atleast one (1) product')
      isValid = false
    }
    if ( formObject.captcha != captchaAnswer ) {
      $('.eb-form-captcha').addClass('eb-required-field')
      isValid = false
    }

    return isValid
  }

  function ebToggleOverlayLoader(visible = true) {
    if ( visible ) {
      $('body').prepend(`
        <div class='eb-overlay'>
          <div class='eb-overlay-loader'>
            <img src='https://davidangulo.xyz/estongs-staging/wp-content/uploads/2019/06/loader.gif'>
          </div>
        </div>
      `)
    }
    else $('.eb-overlay').hide()
  }
})
