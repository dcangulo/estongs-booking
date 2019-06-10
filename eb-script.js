jQuery(document).ready(($) => {
  flatpickr('.eb-datetime-picker', {
    enableTime: true,
    dateFormat: 'Y-m-d h:i K',
    altInput: true,
    altFormat: 'F j, Y h:i K'
  })

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
