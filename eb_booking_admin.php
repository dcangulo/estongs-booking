<?php
class EbBookingAdmin {

  private $wpdb;
  private $table_name;
  private $bookings;

  public function __construct() {
    global $wpdb;

    $this->wpdb = $wpdb;
    $this->table_name = $wpdb->prefix . 'eb_bookings';

    add_filter('set-screen-option', [__CLASS__, 'eb_set_screen'], 10, 3);
    add_action('admin_menu', [$this, 'eb_booking_menu']);
  }

  public static function eb_set_screen($status, $option, $value) {
    return $value;
  }

  public function eb_booking_menu() {
    $hook = add_menu_page(
      'Booking List',
      'Bookings',
      'manage_options',
      'eb_booking_list',
      [$this, 'eb_booking_admin_page']
    );

    add_action("load-$hook", [$this, 'eb_screen_options']);
  }

  public function eb_booking_admin_page() {
    if ( isset($_GET['action']) && $_GET['action'] === 'view' ) {
      return $this->eb_booking_admin_show(esc_sql($_GET['booking']));
    }
    if ( isset($_GET['action']) && $_GET['action'] === 'edit' ) {
      return $this->eb_booking_admin_edit(esc_sql($_GET['booking']));
    }

    return $this->eb_booking_admin_index();
  }

  public function eb_booking_admin_index() {
  ?>
    <div class='wrap'>
      <h1>Booking List</h1>
      <div id='post-body' class='metabox-holder columns-2'>
        <div id='post-body-content'>
          <div class='meta-box-sortables ui-sortable'>
            <form method='post'>
              <?php
                $this->bookings->prepare_items();
                $this->bookings->display();
              ?>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php
  }

  public function eb_booking_admin_show($booking_id) {
    $booking_query = "SELECT * FROM $this->table_name WHERE id='$booking_id'";
    $booking = $this->wpdb->get_row($booking_query);
    $delete_nonce = wp_create_nonce('eb_delete_booking');
  ?>
    <div class='wrap'>
      <h1 class='wp-heading-inline'>Booking #<?php echo $booking_id; ?></h1>
      <?php
        echo sprintf('<a class="page-title-action" href="?page=%s">Back to list</a>', esc_attr($_REQUEST['page']));
        echo sprintf('<a class="page-title-action" href="?page=%s&action=%s&booking=%s">Edit</a>', esc_attr($_REQUEST['page']), 'edit', absint($booking_id));
        echo sprintf('<a class="page-title-action" href="?page=%s&action=%s&booking=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($booking_id), $delete_nonce)
      ?>
      <table class='form-table'>
        <tr>
          <th scope='row'>Name</th>
          <td><?php echo $booking->name; ?></td>
        </tr>
        <tr>
          <th scope='row'>Email Address</th>
          <td><?php echo $booking->email_address; ?></td>
        </tr>
        <tr>
          <th scope='row'>Contact Number</th>
          <td><?php echo $booking->contact_number; ?></td>
        </tr>
        <tr>
          <th scope='row'>Delivery Date</th>
          <td><?php echo $booking->delivery_date; ?></td>
        </tr>
        <tr>
          <th scope='row'>Quantity</th>
          <td><?php echo $booking->quantity; ?></td>
        </tr>
        <tr>
          <th scope='row'>Additional Notes</th>
          <td><?php echo $booking->additional_notes; ?></td>
        </tr>
        <tr>
          <th scope='row'>Payment Type</th>
          <td><?php echo $booking->payment_type; ?></td>
        </tr>
        <tr>
          <th scope='row'>Payment Reference</th>
          <td><?php echo $booking->payment_reference; ?></td>
        </tr>
        <tr>
          <th scope='row'>Payment Status</th>
          <td><?php echo $booking->payment_status; ?></td>
        </tr>
        <tr>
          <th scope='row'>Booking Status</th>
          <td><?php echo $booking->booking_status; ?></td>
        </tr>
      </table>
    </div>
  <?php
  }

  public function eb_booking_admin_edit($booking_id) {
    if ( isset($_POST['eb-submit']) ) {
      $this->eb_booking_admin_update($_POST, $booking_id);
    }

    $booking_query = "SELECT * FROM $this->table_name WHERE id='$booking_id'";
    $booking = $this->wpdb->get_row($booking_query);
    $delete_nonce = wp_create_nonce('eb_delete_booking');
  ?>
    <div class='wrap'>
      <h1 class='wp-heading-inline'>Editing Booking #<?php echo $booking_id; ?></h1>
      <?php
        echo sprintf('<a class="page-title-action" href="?page=%s&action=%s&booking=%s">Cancel</a>', esc_attr($_REQUEST['page']), 'view', absint($booking_id));
      ?>
      <form method='post' action=''>
        <table class='form-table'>
          <tr>
            <th scope='row'>Name</th>
            <td><?php echo $booking->name; ?></td>
          </tr>
          <tr>
            <th scope='row'>Email Address</th>
            <td><?php echo $booking->email_address; ?></td>
          </tr>
          <tr>
            <th scope='row'>Contact Number</th>
            <td><?php echo $booking->contact_number; ?></td>
          </tr>
          <tr>
            <th scope='row'>Delivery Date</th>
            <td><?php echo $booking->delivery_date; ?></td>
          </tr>
          <tr>
            <th scope='row'>Quantity</th>
            <td><?php echo $booking->quantity; ?></td>
          </tr>
          <tr>
            <th scope='row'>Additional Notes</th>
            <td><?php echo $booking->additional_notes; ?></td>
          </tr>
          <tr>
            <th scope='row'>Payment Type</th>
            <td><?php echo $booking->payment_type; ?></td>
          </tr>
          <tr>
            <th scope='row'>Payment Reference</th>
            <td><?php echo $booking->payment_reference; ?></td>
          </tr>
          <tr>
            <th scope='row'>Payment Status</th>
            <td>
              <select name='eb-payment-status'>
                <option value='1' selected>Pending</option>
                <option value='2'>Paid</option>
              </select>
            </td>
          </tr>
          <tr>
            <th scope='row'>Booking Status</th>
            <td>
              <select name='eb-booking-status'>
                <option value='1' selected>Processing</option>
                <option value='2'>Completed</option>
              </select>
            </td>
          </tr>
        </table>
        <p class='submit'>
          <button type='submit' name='eb-submit' class='button button-primary'>Save Changes</button>
        </p>
      </form>
    </div>
  <?php
  }

  public function eb_booking_admin_update($params, $booking_id) {
    $this->wpdb->update($this->table_name, [
      'payment_status' => esc_sql($params['eb-payment-status']),
      'booking_status' => esc_sql($params['eb-booking-status'])
    ], ['id' => $booking_id]);
  }

  public function eb_screen_options() {
    $option = 'per_page';
    $args = [
      'label' => 'Bookings',
      'default' => PER_PAGE_DEFAULT,
      'option' => 'bookings_per_page'
    ];

    add_screen_option($option, $args);

    $this->bookings = new EbBookingAdminTable();
  }

}
