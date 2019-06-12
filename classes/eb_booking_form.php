<?php
class EbBookingForm {

  private $wpdb;
  private $table_name;

  public function __construct() {
    global $wpdb;

    $this->wpdb = $wpdb;
    $this->table_name = $wpdb->prefix . 'eb_bookings';

    register_activation_hook(PLUGIN_INDEX_PATH, [$this, 'eb_generate_table']);
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
        `quantity` INT(11) DEFAULT NULL,
        `additional_notes` TEXT DEFAULT '',
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
    <form id='eb-booking-form' class='eb-booking-form-wrapper'>
      <div class='eb-booking-form-name'>
        <label>Name:</label>
        <input type='text' name='name'>
      </div>
      <div class='eb-booking-form-email-address'>
        <label>Email Address:</label>
        <input type='text' name='email-address'>
      </div>
      <div class='eb-booking-form-contact-number'>
        <label>Contact Number:</label>
        <input type='text' name='contact-number'>
      </div>
      <div class='eb-booking-form-delivery-date'>
        <label>Delivery Date:</label>
        <input type='text' name='date' class='eb-datetime-picker'>
      </div>
      <div class='eb-booking-form-quantity'>
        <label>Quantity:</label>
        <input type='number' name='quantity'>
      </div>
      <div class='eb-booking-form-additional-notes'>
        <label>Addtional Notes:</label>
        <textarea name='additional-notes'></textarea>
      </div>
      <div class='eb-booking-form-submit'>
        <button type='submit' name='place-order'>Place Order</button>
      </div>
    </form>
  <?php
  }

  public function eb_booking_form_render() {
    ob_start();

    $this->eb_booking_form_content();

    return ob_get_clean();
  }

  public function eb_booking_form_scripts() {
    wp_register_style('eb-booking-form-style', PLUGIN_ROOT_URL . '/scripts/eb-style.css');
    wp_enqueue_style('eb-booking-form-style');
    wp_register_script('eb-booking-form-script', PLUGIN_ROOT_URL . '/scripts/eb-script.js');
    wp_enqueue_script('eb-booking-form-script');
    wp_localize_script('eb-booking-form-script', 'ebBookingParams', ['adminAjaxPath' => admin_url('admin-ajax.php')]);
  }

  public function eb_booking_form_dependencies() {
    wp_register_style('eb-booking-flatpickr-style', PLUGIN_ROOT_URL . '/dependencies/flatpickr.min.css');
    wp_enqueue_style('eb-booking-flatpickr-style');
    wp_register_script('eb-booking-flatpickr-script', PLUGIN_ROOT_URL . '/dependencies/flatpickr.min.js');
    wp_enqueue_script('eb-booking-flatpickr-script');
  }

  public function eb_booking_form_process() {
    $eb_booking = $_POST['eb_booking'];

    $this->wpdb->insert($this->table_name, [
      'name' => $eb_booking['name'],
      'email_address' => $eb_booking['email_address'],
      'contact_number' => $eb_booking['contact_number'],
      'delivery_date' => $eb_booking['date'],
      'quantity' => $eb_booking['quantity'],
      'additional_notes' => $eb_booking['additional_notes']
    ]);

    $new_booking_query = "SELECT * FROM $this->table_name WHERE id='{$this->wpdb->insert_id}'";
    $new_booking = $this->wpdb->get_row($new_booking_query);

    echo json_encode($new_booking);

    wp_die();
  }

}
