jQuery(document).ready(($) => {
  flatpickr('.eb-flatpickr-multiple', {
    mode: 'multiple',
    dateFormat: 'Y-m-d'
  })

  flatpickr('.eb-flatpickr-time', {
    enableTime: true,
    noCalendar: true,
    dateFormat: 'H:i',
    altInput: true,
    altFormat: 'h:i K',
  })

  if ( document.getElementById('calendar') ) {
    const calendarEl = document.getElementById('calendar'),
          bookings = JSON.parse(ebAdminParams.bookings),
          options = JSON.parse(ebAdminParams.options)

    const events = bookings.map((booking) => {
      return {
        id: booking.id,
        title: `Order #${booking.id}`,
        start: booking.delivery_date,
        url: `${ebAdminParams.view_url}&booking=${booking.id}`
      }
    })

    const calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: ['dayGrid', 'timeGrid'],
      header: {
        left: 'title',
        center: null,
        right: 'today,dayGridMonth,timeGridWeek prev,next'
      },
      allDaySlot: false,
      eventLimit: true,
      minTime: options.start_time,
      maxTime: options.end_time,
      events: events
    })

    calendar.render()
  }

})
