<?php
class EbBookingCalendar {

  private $wpdb;

  public function __construct() {
    global $wpdb;

    $this->wpdb = $wpdb;

    add_action('admin_menu', [$this, 'eb_booking_calendar_menu']);
    add_action('admin_enqueue_scripts', [$this, 'eb_admin_scripts']);
    add_action('admin_enqueue_scripts', [$this, 'eb_admin_dependencies']);
  }

  public function eb_booking_calendar_menu() {
    add_submenu_page('eb-bookings', 'Booking Calendar', 'Calendar', 'manage_options', 'eb-booking-calendar', [$this, 'eb_booking_calendar_page']);
  }

  public function eb_booking_calendar_page() {
  ?>
    <div class='wrap'>
      <h1>Booking Calendar</h1>
      <div id='calendar'></div>
    </div>
  <?php
  }

  public function eb_admin_scripts() {
    $bookings = $this->wpdb->get_results('SELECT * FROM ' . EB_BOOKINGS_TABLE);
    $view_url = add_query_arg([
      'page' => 'eb-bookings',
      'action' => 'view',
    ], admin_url('admin.php'));

    wp_register_script('eb-admin-script', EB_PLUGIN_ROOT_URL . '/scripts/eb-admin-script.js');
    wp_enqueue_script('eb-admin-script');
    wp_localize_script('eb-admin-script', 'ebAdminParams', [
      'bookings' => json_encode($bookings),
      'view_url' => $view_url,
      'options' => json_encode(EB_BOOKING_SETTINGS)
    ]);
  }

  public function eb_admin_dependencies() {
    wp_register_style('eb-fullcalendar-core-style', EB_PLUGIN_ROOT_URL . '/dependencies/fullcalendar-core.min.css');
    wp_enqueue_style('eb-fullcalendar-core-style');
    wp_register_style('eb-fullcalendar-daygrid-style', EB_PLUGIN_ROOT_URL . '/dependencies/fullcalendar-daygrid.min.css');
    wp_enqueue_style('eb-fullcalendar-daygrid-style');
    wp_register_style('eb-fullcalendar-timegrid-style', EB_PLUGIN_ROOT_URL . '/dependencies/fullcalendar-timegrid.min.css');
    wp_enqueue_style('eb-fullcalendar-timegrid-style');
    wp_register_script('eb-fullcalendar-core-script', EB_PLUGIN_ROOT_URL . '/dependencies/fullcalendar-core.min.js');
    wp_enqueue_script('eb-fullcalendar-core-script');
    wp_register_script('eb-fullcalendar-daygrid-script', EB_PLUGIN_ROOT_URL . '/dependencies/fullcalendar-daygrid.min.js');
    wp_enqueue_script('eb-fullcalendar-daygrid-script');
    wp_register_script('eb-fullcalendar-timegrid-script', EB_PLUGIN_ROOT_URL . '/dependencies/fullcalendar-timegrid.min.js');
    wp_enqueue_script('eb-fullcalendar-timegrid-script');

  }

}
