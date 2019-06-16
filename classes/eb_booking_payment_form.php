<?php
class EbBookingPaymentForm {

  private $wpdb;

  public function __construct() {
    global $wpdb;

    $this->wpdb = $wpdb;

    add_shortcode('eb_booking_payment_form', [$this, 'eb_booking_payment_form_render']);
    add_action('wp_ajax_eb_payment_form_process', [$this, 'eb_payment_form_process']);
    add_action('wp_ajax_nopriv_eb_payment_form_process', [$this, 'eb_payment_form_process']);
  }

  public function eb_booking_payment_form_render() {
    ob_start();

    $this->eb_booking_payment_form_content();

    return ob_get_clean();
  }

  public function eb_booking_payment_form_content() {
    $validated_params = $this->eb_validate_params($_GET);
    $invalid_params = $validated_params['invalid_params'];

    if ( $invalid_params ) {
    ?>
      <div>
        <h3 class='eb-success-message'>The requested URL seems to be invalid.<br>Please click the button from your email to get a valid payment URL.</h3>
      </div>
    <?php
    }
    else {
    ?>
      <form id='eb-booking-payment-form'>
        <div class='row'>
          <div class='col-md-6'>
            <label for='order-number'>Order Number:</label>
            <input type='text' id='order-number' name='order-number' class='eb-form-order-number eb-required' value='<?php echo $validated_params['booking']->id; ?>' readonly>
          </div>
          <div class='col-md-6'>
            <label for='amount'>Payment Amount: (₱) <span class='eb-required-indication'>*</span></label>
            <input type='number' step='0.01' id='amount' name='amount' class='eb-form-amount eb-required'>
          </div>
          <div class='col-md-6'>
            <label for='type'>Payment Type:<span class='eb-required-indication'>*</span></label>
            <select id='type' name='type' class='eb-form-type eb-required'>
              <option value='PayMaya'>PayMaya</option>
            </select>
          </div>
          <div class='col-md-6'>
            <label for='reference'>Payment Reference:<span class='eb-required-indication'>*</span></label>
            <input type='text' id='reference' name='reference' class='eb-form-reference eb-required'>
          </div>
          <div class='col-md-12'>
            <button type='submit' class='eb-button' name='place-order'>Submit</button>
          </div>
        </div>
      </form>
      <div id='eb-success'>
        <h3 class='eb-success-message'></h3>
      </div>
    <?php
    }
  }

  public function eb_validate_params($params) {
    $invalid_params = false;

    if ( isset($params['order_number']) && isset($params['token']) ) {
      $order_number = esc_sql($params['order_number']);
      $token = esc_sql($params['token']);

      $booking_query = 'SELECT * FROM ' . EB_BOOKINGS_TABLE . " WHERE id='$order_number' AND token='$token'";
      $booking = $this->wpdb->get_row($booking_query);

      if ( empty($booking) ) {
        $invalid_params = true;
      }
    }
    else {
      $invalid_params = true;
    }

    return [
      'invalid_params' => $invalid_params,
      'booking' => $booking
    ];
  }

  public function eb_payment_form_process() {
    try {
      $eb_booking_payment = $_POST['eb_booking_payment'];

      $this->wpdb->update(EB_BOOKINGS_TABLE, [
        'payment_type' => esc_sql($eb_booking_payment['type']),
        'payment_reference' => esc_sql($eb_booking_payment['reference']),
        'payment_status' => 2
      ], [
        'id' => esc_sql($eb_booking_payment['order_number']),
        'token' => esc_sql($eb_booking_payment['token'])
      ]);

      echo 'Your payment reference has been submitted. Please wait while we validate this payment. We will contact you by email.';
    }
    catch(Exception $e) {
      echo EB_ERROR_MESSAGE;
    }

    wp_die();
  }

}
