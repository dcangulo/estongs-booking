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
})
