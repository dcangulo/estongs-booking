jQuery(document).ready(($) => {
  $('#eb-booking-form').submit((event) => {
    event.preventDefault()

    let ebBookingObject = {}

    $('#eb-booking-form').serializeArray().forEach((field) => {
      const name = field.name.replace(/-/g, '_')

      ebBookingObject[name] = field.value
    })

    const params = {
      action: 'eb_booking_form_process',
      eb_booking: ebBookingObject
    }

    $.ajax({
      type: 'post',
      url: ebBookingParams.adminAjaxPath,
      data: params,
      success: (response) => {
        console.log(response)
      }
    })
  })
})
