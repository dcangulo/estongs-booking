<?php
class EbBookingForm {

  private $wpdb;
  private $table_name;

  public function __construct() {
    global $wpdb;

    $this->wpdb = $wpdb;
    $this->table_name = $wpdb->prefix . 'eb_bookings';

    register_activation_hook(EB_PLUGIN_INDEX_PATH, [$this, 'eb_generate_table']);
    add_shortcode('eb_booking_form', [$this, 'eb_booking_form_render']);
    add_action('wp_enqueue_scripts', [$this, 'eb_booking_form_scripts']);
    add_action('wp_enqueue_scripts', [$this, 'eb_booking_form_dependencies']);
    add_action('wp_ajax_eb_booking_form_process', [$this, 'eb_booking_form_process']);
    add_action('wp_ajax_nopriv_eb_booking_form_process', [$this, 'eb_booking_form_process']);
  }

  public function eb_generate_table() {
    $sql = "
      CREATE TABLE IF NOT EXISTS `$this->table_name` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(220) DEFAULT '',
        `email_address` VARCHAR(220) DEFAULT '',
        `contact_number` VARCHAR(220) DEFAULT '',
        `delivery_date` TIMESTAMP NULL DEFAULT NULL,
        `address` TEXT DEFAULT '',
        `products` TEXT DEFAULT '',
        `additional_notes` TEXT DEFAULT '',
        `total` INT(11) DEFAULT 0,
        `payment_type` VARCHAR(220) DEFAULT '',
        `payment_reference` VARCHAR(220) DEFAULT '',
        `payment_status` INT DEFAULT 1,
        `booking_status` INT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB AUTO_INCREMENT=1000000 DEFAULT CHARSET=latin1;
    ";

    dbDelta($sql);
  }

  public function eb_booking_form_content() {
  ?>
    <form id='eb-booking-form'>
      <div class='row'>
        <div class='col-md-6'>
          <label for='name'>Name:</label>
          <input type='text' id='name' name='name'>
        </div>
        <div class='col-md-6'>
          <label for='email-address'>Email Address:</label>
          <input type='text' id='email-address' name='email-address'>
        </div>
        <div class='col-md-6'>
          <label for='contact-number'>Contact Number:</label>
          <input type='text' id='contact-number' name='contact-number'>
        </div>
        <div class='col-md-6'>
          <label for='delivery-date'>Delivery Date:</label>
          <input type='text' id='delivery-date' name='delivery-date' class='eb-datetime-picker'>
        </div>
        <div class='col-md-12'>
          <label for='address'>Address:</label>
          <textarea id='address' name='address'></textarea>
        </div>
        <div class='col-md-12'>
          <label>Products:</label>
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
          <label for='additional-notes'>Addtional Notes:</label>
          <textarea id='additional-notes' name='additional-notes'></textarea>
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
    wp_register_style('eb-booking-form-style', EB_PLUGIN_ROOT_URL . '/scripts/eb-style.css');
    wp_enqueue_style('eb-booking-form-style');
    wp_register_script('eb-booking-form-script', EB_PLUGIN_ROOT_URL . '/scripts/eb-script.js');
    wp_enqueue_script('eb-booking-form-script');
    wp_localize_script('eb-booking-form-script', 'ebBookingParams', [
      'adminAjaxUrl' => admin_url('admin-ajax.php'),
      'products' => json_encode(EB_PRODUCTS)
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

      $this->wpdb->insert($this->table_name, [
        'name' => $eb_booking['name'],
        'email_address' => $eb_booking['email_address'],
        'contact_number' => $eb_booking['contact_number'],
        'delivery_date' => $eb_booking['delivery_date'],
        'address' => $eb_booking['address'],
        'products' => json_encode($eb_booking['products']),
        'additional_notes' => $eb_booking['additional_notes'],
        'total' => $eb_booking['total']
      ]);

      // $new_booking_query = "SELECT * FROM $this->table_name WHERE id='{$this->wpdb->insert_id}'";
      // $new_booking = $this->wpdb->get_row($new_booking_query);

      // echo json_encode($new_booking);
      echo 'Your order has been sent. We will reply to you via email in 24 hours.';
    }
    catch(Exception $e) {
      echo EB_ERROR_MESSAGE;
    }

    wp_die();
  }

}
