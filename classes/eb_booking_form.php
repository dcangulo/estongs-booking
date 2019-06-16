<?php
class EbBookingForm {

  private $wpdb;

  public function __construct() {
    global $wpdb;

    $this->wpdb = $wpdb;

    register_activation_hook(EB_PLUGIN_INDEX_PATH, [$this, 'eb_generate_table']);
    add_shortcode('eb_booking_form', [$this, 'eb_booking_form_render']);
    add_action('wp_enqueue_scripts', [$this, 'eb_booking_form_scripts']);
    add_action('wp_enqueue_scripts', [$this, 'eb_booking_form_dependencies']);
    add_action('wp_ajax_eb_booking_form_process', [$this, 'eb_booking_form_process']);
    add_action('wp_ajax_nopriv_eb_booking_form_process', [$this, 'eb_booking_form_process']);
  }

  public function eb_generate_table() {
    $create_tables_sql[] = "
      CREATE TABLE IF NOT EXISTS `" . EB_BOOKINGS_TABLE . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(220) DEFAULT '',
        `email_address` VARCHAR(220) DEFAULT '',
        `contact_number` VARCHAR(220) DEFAULT '',
        `delivery_date` TIMESTAMP NULL DEFAULT NULL,
        `address` TEXT,
        `additional_notes` TEXT,
        `total` DECIMAL(13, 4) DEFAULT 0.0000,
        `token` VARCHAR(220) DEFAULT '',
        `payment_type` VARCHAR(220) DEFAULT '',
        `payment_reference` VARCHAR(220) DEFAULT '',
        `payment_status` INT DEFAULT 1,
        `booking_status` INT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB AUTO_INCREMENT=1000000 DEFAULT CHARSET={$this->wpdb->charset} COLLATE={$this->wpdb->collate};
    ";

    $create_tables_sql[] = "
      CREATE TABLE IF NOT EXISTS `" . EB_PRODUCTS_TABLE . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(220) DEFAULT '',
        `sku` VARCHAR(220) DEFAULT '',
        `price` DECIMAL(13, 4) DEFAULT 0.0000,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$this->wpdb->charset} COLLATE={$this->wpdb->collate};
    ";

    $create_tables_sql[] = "
      CREATE TABLE IF NOT EXISTS `" . EB_RESERVATIONS_TABLE . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(220) DEFAULT '',
        `email_address` VARCHAR(220) DEFAULT '',
        `contact_number` VARCHAR(220) DEFAULT '',
        `event_name` VARCHAR(220) DEFAULT '',
        `event_location` TEXT,
        `event_date` TIMESTAMP NULL DEFAULT NULL,
        `additional_notes` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$this->wpdb->charset} COLLATE={$this->wpdb->collate};
    ";

    $create_tables_sql[] = "
      CREATE TABLE IF NOT EXISTS `" . EB_BOOKING_PRODUCTS . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `booking_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `quantity` INT(11) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$this->wpdb->charset} COLLATE={$this->wpdb->collate};
    ";

    $create_associations_sql = "
      ALTER TABLE `" . EB_BOOKING_PRODUCTS . "`
        ADD KEY `" . EB_BOOKING_PRODUCTS . "_booking_id_foreign` (`booking_id`),
        ADD KEY `" . EB_BOOKING_PRODUCTS . "_product_id_foreign` (`product_id`),
        ADD CONSTRAINT `" . EB_BOOKING_PRODUCTS . "_booking_id_foreign`
          FOREIGN KEY (`booking_id`) REFERENCES `" . EB_BOOKINGS_TABLE . "` (`id`)
          ON DELETE CASCADE
          ON UPDATE CASCADE,
        ADD CONSTRAINT `" . EB_BOOKING_PRODUCTS . "_product_id_foreign`
          FOREIGN KEY (`product_id`) REFERENCES `" . EB_PRODUCTS_TABLE . "` (`id`)
          ON DELETE CASCADE
          ON UPDATE CASCADE;
    ";

    foreach($create_tables_sql as $create_table_sql) {
      dbDelta($create_table_sql);
    }

    $this->wpdb->query($create_associations_sql);
  }

  public function eb_booking_form_content() {
  ?>
    <form id='eb-booking-form'>
      <div class='row'>
        <div class='col-md-6'>
          <label for='name'>Name:<span class='eb-required-indication'>*</span></label>
          <input type='text' id='name' name='name' class='eb-form-name eb-required'>
        </div>
        <div class='col-md-6'>
          <label for='email-address'>Email Address:<span class='eb-required-indication'>*</span></label>
          <input type='text' id='email-address' name='email-address' class='eb-form-email-address eb-required'>
        </div>
        <div class='col-md-6'>
          <label for='contact-number'>Contact Number:<span class='eb-required-indication'>*</span></label>
          <input type='text' id='contact-number' name='contact-number' class='eb-form-contact-number eb-required'>
        </div>
        <div class='col-md-6'>
          <label for='delivery-date'>Delivery Date:<span class='eb-required-indication'>*</span></label>
          <input type='text' id='delivery-date' name='delivery-date' class='eb-datetime-picker eb-form-delivery-date eb-required'>
        </div>
        <div class='col-md-12'>
          <label for='address'>Address:<span class='eb-required-indication'>*</span></label>
          <textarea id='address' name='address' class='eb-form-address eb-required'></textarea>
        </div>
        <div class='col-md-12'>
          <label>Products:<span class='eb-required-indication'>*</span></label>
          <div id='eb-products'>
          </div>
          <div class='row'>
            <div class='col-md-8'></div>
            <div class='col-md-4'>
              Total: ₱<span id='eb-product-price-total'>0.00</span>
            </div>
            <div class='col-md-8'></div>
            <div class='col-md-4'>
              <button id='eb-add-product' class='eb-button' type='button'>Add Product</button>
            </div>
          </div>
        </div>
        <div class='col-md-12'>
          <label for='additional-notes'>Additional Notes:</label>
          <textarea id='additional-notes' name='additional-notes'></textarea>
        </div>
        <div class='col-md-12'>
          <label for='captcha'>Please prove that you are human by solving the equation (<span id='captcha-equation'></span>)<span class='eb-required-indication'>*</span></label>
          <input type='number' id='captcha' name='captcha' class='eb-form-captcha eb-required'>
        </div>
        <div class='col-md-12'>
          <button type='submit' class='eb-button' name='place-order'>Place Order</button>
        </div>
      </div>
    </form>
    <div id='eb-success'>
      <h3 class='eb-success-message'></h3>
    </div>
  <?php
  }

  public function eb_booking_form_render() {
    ob_start();

    $this->eb_booking_form_content();

    return ob_get_clean();
  }

  public function eb_booking_form_scripts() {
    $products = $this->wpdb->get_results('SELECT * FROM ' . EB_PRODUCTS_TABLE);

    wp_register_style('eb-booking-form-style', EB_PLUGIN_ROOT_URL . '/scripts/eb-style.css');
    wp_enqueue_style('eb-booking-form-style');
    wp_register_script('eb-booking-form-script', EB_PLUGIN_ROOT_URL . '/scripts/eb-script.js');
    wp_enqueue_script('eb-booking-form-script');
    wp_localize_script('eb-booking-form-script', 'ebBookingParams', [
      'adminAjaxUrl' => admin_url('admin-ajax.php'),
      'products' => json_encode($products),
      'options' => json_encode(EB_BOOKING_SETTINGS),
      'errorMsg' => EB_ERROR_MESSAGE
    ]);
  }

  public function eb_booking_form_dependencies() {
    wp_register_style('eb-booking-flatpickr-style', EB_PLUGIN_ROOT_URL . '/dependencies/flatpickr.min.css');
    wp_enqueue_style('eb-booking-flatpickr-style');
    wp_register_style('eb-booking-bootstrap-grid-style', EB_PLUGIN_ROOT_URL . '/dependencies/bootstrap-grid.min.css');
    wp_enqueue_style('eb-booking-bootstrap-grid-style');
    wp_register_script('eb-booking-flatpickr-script', EB_PLUGIN_ROOT_URL . '/dependencies/flatpickr.min.js');
    wp_enqueue_script('eb-booking-flatpickr-script');
  }

  public function eb_booking_form_process() {
    try {
      $eb_booking = $_POST['eb_booking'];
      $total = $this->eb_order_total($eb_booking['products']);

      $this->wpdb->insert(EB_BOOKINGS_TABLE, [
        'name' => $eb_booking['name'],
        'email_address' => $eb_booking['email_address'],
        'contact_number' => $eb_booking['contact_number'],
        'delivery_date' => $eb_booking['delivery_date'],
        'address' => $eb_booking['address'],
        'additional_notes' => $eb_booking['additional_notes'],
        'total' => $total,
        'token' => wp_generate_uuid4() . '-' . rand(1000, 9999)
      ]);

      $booking_id = $this->wpdb->insert_id;

      foreach($eb_booking['products'] as $eb_booking_product) {
        $this->wpdb->insert(EB_BOOKING_PRODUCTS, [
          'booking_id' => $booking_id,
          'product_id' => $eb_booking_product['id'],
          'quantity' => $eb_booking_product['quantity']
        ]);
      }

      $booking_query = "SELECT * FROM " . EB_BOOKINGS_TABLE . " WHERE id='$booking_id'";
      $booking_products_query = "
        SELECT name, quantity, price FROM " . EB_BOOKING_PRODUCTS . "
          INNER JOIN " . EB_PRODUCTS_TABLE . "
          ON " . EB_PRODUCTS_TABLE . ".id = " . EB_BOOKING_PRODUCTS . ".product_id
          WHERE booking_id='$booking_id'
      ";
      $booking = $this->wpdb->get_row($booking_query);

      $booking->products = $this->wpdb->get_results($booking_products_query);

      $this->eb_send_email($booking);

      echo json_encode($booking);
    }
    catch(Exception $e) {
      echo EB_ERROR_MESSAGE;
    }

    wp_die();
  }

  public function eb_send_email($booking) {
    $payment_url = add_query_arg([
      'order_number' => $booking->id,
      'token' => $booking->token
    ], home_url('payment'));
    $to = $booking->email_address;
    $subject = "Your order {$booking->id} has been received";
    $message = "
      <html>
        <head>
        </head>
        <body>
          <div bgcolor='#f8f8f8' marginwidth='0' marginheight='0' style='margin:0;padding:0;background:#f8f8f8'>
            <table cellpadding='0' cellspacing='0' border='0' height='100%' width='100%' bgcolor='#f8f8f8' style='background:#f8f8f8'>
              <tbody>
                <tr>
                  <td>
                    <table border='0' width='600' cellpadding='0' cellspacing='0' align='center'>
                      <tbody>
                        <tr>
                          <td valign='middle' style='text-align:left'>
                            <a href='#' target='_blank'><img src='https://www.davidangulo.xyz/estongs-staging/wp-content/uploads/2019/05/logo.png' height='119' width='236' border='0'></a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <table border='0' width='600' cellpadding='0' cellspacing='0' align='center' style='background:#ffffff;border:1px solid #e5e5e5'>
                      <tbody>
                        <tr>
                          <td style='border-bottom:1px solid #e5e5e5'>
                            <table border='0' width='100%' cellpadding='0' cellspacing='0' align='center'>
                              <tbody>
                                <tr>
                                  <td style='padding:30px 30px 00px 30px;font-family:sans-serif;font-size:16px;line-height:25px;color:#666666'>
                                    <table cellpadding='10' style='width:100%'>
                                      <tbody>
                                        <tr>
                                          <td colspan='2'>
                                            <h2 style='margin-bottom:20px'>Your order has been received</h2>
                                            <div style='font-size:20px; margin-bottom: 10px;'>
                                              Dear {$booking->name},
                                            </div>
                                            <div style='font-size: 16px'>
                                              Thanks for shopping with us! We are glad to inform you that your order #{$booking->id} has been received successfully, please see below details for more information regarding your order.
                                            </div>
                                          </td>
                                        </tr>
                                        <tr>
                                          <td colspan='2'>
                                            <h2 style='margin:0'>Summary of your order</h2>
                                          </td>
                                        </tr>
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Order Number</td>
                                          <td style='float:right'>{$booking->id}</td>
                                        </tr>
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Name</td>
                                          <td style='float:right'>{$booking->name}</td>
                                        </tr>
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Email Address</td>
                                          <td style='float:right'>{$booking->email_address}</td>
                                        </tr>
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Contact Number</td>
                                          <td style='float:right'>{$booking->contact_number}</td>
                                        </tr>
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Address</td>
                                          <td style='float:right'>{$booking->address}</td>
                                        </tr>
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Delivery Date</td>
                                          <td style='float:right'>" . date('F j, Y g:i A', strtotime($booking->delivery_date)) . "</td>
                                        </tr>
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Total</td>
                                          <td style='float:right'>₱" . number_format($booking->total, 2) . "</td>
                                        </tr>
                                        <tr>
                                          <td colspan='2'>
                                            <h2 style='margin:0'>Summary of products ordered</h2>
                                          </td>
                                        </tr>
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Product</td>
                                          <td style='float:right;text-transform: uppercase;'>Total</td>
                                        </tr>
    ";

    foreach($booking->products as $product) {
      $total = (float)$product->price * (float)$product->quantity;

      $message .= "
        <tr style='border-bottom:1px solid #eee'>
          <td width='70%'>{$product->name} x {$product->quantity}</td>
          <td style='float:right' widht='30%'>₱" . number_format($total, 2) . "</td>
        </tr>
      ";
    }

    $message .= "
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Total</td>
                                          <td style='float:right'>₱" . number_format($booking->total, 2) . "</td>
                                        </tr>
                                        <tr style='border-bottom:1px solid #eee'>
                                          <td style='text-transform: uppercase;'>Additional Notes</td>
                                          <td style='float:right'>{$booking->additional_notes}</td>
                                        </tr>
                                       <tr style='border-bottom:1px solid #eee'>
                                         <td colspan='2' style='text-align: center;'><a href='$payment_url' style='background: #ff0028; color: #fff; text-decoration: none; padding: 10px 20px 10px 20px; border-radius: 5px;'>PAY NOW</a></td>
                                       </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td style='padding-left:37px;padding-bottom:20px;margin-top:0px;font-family:sans-serif;color:#666666'>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <table border='0' width='100%' cellpadding='0' cellspacing='0' align='center'>
                      <tbody>
                        <tr>
                          <td style='text-align:center;padding:30px;font-family:Helvetica,Arial,sans-serif;font-size:13px;line-height:28px;color:#888888'>
                            © 2019 Estong's Bellychon
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </body>
      </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    wp_mail($to, $subject, $message, $headers);
  }

  public function eb_order_total($products_ordered) {
    $total = 0;
    $product_ordered_ids = [];

    foreach($products_ordered as $product_ordered) {
      array_push($product_ordered_ids, $product_ordered['id']);
    }

    $product_ordered_ids = join(',', $product_ordered_ids);
    $products_query = "SELECT id, price FROM " . EB_PRODUCTS_TABLE . " WHERE id IN ($product_ordered_ids)";
    $products = $this->wpdb->get_results($products_query);

    foreach($products as $product) {
      foreach($products_ordered as $product_ordered) {
        if ( $product_ordered['id'] === $product->id ) {
          $total += $product_ordered['quantity'] * $product->price;
        }
      }
    }

    return $total;
  }

}
