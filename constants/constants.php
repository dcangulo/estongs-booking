<?php
define('EB_PER_PAGE_DEFAULT', 10);
define('EB_PLUGIN_ROOT_URL', plugins_url('estongs-booking'));
define('EB_PLUGIN_INDEX_PATH', join('/', [WP_PLUGIN_DIR, 'estongs-booking', 'index.php']));
define('EB_ERROR_MESSAGE', 'An error has occured.');
define('EB_BOOKINGS_TABLE', $wpdb->prefix . 'eb_bookings');
define('EB_PRODUCTS_TABLE', $wpdb->prefix . 'eb_products');
define('EB_RESERVATIONS_TABLE', $wpdb->prefix . 'eb_reservations');
define('EB_BOOKING_PRODUCTS', $wpdb->prefix . 'eb_booking_products');
define('EB_BOOKING_SETTINGS', [
  'preparation_days' => empty(get_option('eb-preparation-days')) ? 0 : get_option('eb-preparation-days'),
  'disabled_dates' => explode(', ', get_option('eb-disabled-dates')),
  'start_time' => empty(get_option('eb-start-time')) ? '0:00' : get_option('eb-start-time'),
  'end_time' => empty(get_option('eb-end-time')) ? '23:00' : get_option('eb-end-time')
]);
define('EB_PAYMENT_STATUSES', [
  '1' => 'AWAITING PAYMENT',
  '2' => 'VALIDATING PAYMENT',
  '3' => 'VERIFIED PAYMENT',
  '4' => 'CASH ON DELIVERY'
]);
define('EB_BOOKING_STATUSES', [
  '1' => 'PROCESSING',
  '2' => 'DELIVERED'
]);
