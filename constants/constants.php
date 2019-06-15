<?php
define('EB_PER_PAGE_DEFAULT', 5);
define('EB_PLUGIN_ROOT_URL', plugins_url('estongs-booking'));
define('EB_PLUGIN_INDEX_PATH', join('/', [WP_PLUGIN_DIR, 'estongs-booking', 'index.php']));
define('EB_ERROR_MESSAGE', 'An error has occured.');
define('EB_PRODUCTS', [
  [
    'name' => 'Small Size (3kg / 10-12pax)',
    'sku' => 'SS3KG',
    'price' => 2200,
    'tax' => 0
  ],
  [
    'name' => 'Medium Size (4kg / 15-20pax)',
    'sku' => 'MS4KG',
    'price' => 2900,
    'tax' => 0
  ],
  [
    'name' => 'Large Size (5kg / 20-25pax)',
    'sku' => 'LS5KG',
    'price' => 3500,
    'tax' => 0
  ]
]);
define('EB_PAYMENT_STATUSES', [
  '1' => 'AWAITING PAYMENT',
  '2' => 'VALIDATING PAYMENT',
  '3' => 'PAID'
]);
define('EB_BOOKING_STATUSES', [
  '1' => 'PROCESSING',
  '2' => 'DELIVERED'
]);
define('EB_BOOKINGS_TABLE', $wpdb->prefix . 'eb_bookings');
define('EB_PRODUCTS_TABLE', $wpdb->prefix . 'eb_products');
define('EB_RESERVATIONS_TABLE', $wpdb->prefix . 'eb_reservations');
