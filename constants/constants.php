<?php
define('EB_PER_PAGE_DEFAULT', 5);
define('EB_PLUGIN_ROOT_URL', plugins_url('estongs-booking'));
define('EB_PLUGIN_INDEX_PATH', join('/', [WP_PLUGIN_DIR, 'estongs-booking', 'index.php']));
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
    'name' => 'Small Size (5kg / 20-25pax)',
    'sku' => 'LS5KG',
    'price' => 3500,
    'tax' => 0
  ]
]);
