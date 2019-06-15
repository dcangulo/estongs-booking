<?php
class EbBookingSettings {

  public function __construct() {
    add_action('admin_menu', [$this, 'eb_settings_menu']);
    add_action('admin_init', [$this, 'eb_register_settings']);
    add_action('admin_enqueue_scripts', [$this, 'eb_admin_dependencies']);
  }

  public function eb_settings_menu() {
    add_options_page('Bookings', 'Bookings', 'manage_options', 'eb-settings', [$this, 'eb_settings_page']);
  }

  public function eb_register_settings() {
    register_setting('eb-settings', 'eb-disabled-dates');
    register_setting('eb-settings', 'eb-start-time');
    register_setting('eb-settings', 'eb-end-time');
    register_setting('eb-settings', 'eb-preparation-days');
  }

  public function eb_settings_page() {
  ?>
    <div class='wrap'>
      <h1>Booking Settings</h1>
      <form method='post' action='options.php'>
        <?php settings_fields('eb-settings'); ?>
        <table class='form-table'>
          <tbody>
            <tr>
              <th>Preparation Days</th>
              <td>
                <input type='number' class='regular-text' name='eb-preparation-days' id='eb-preparation-days' value="<?php echo get_option('eb-preparation-days'); ?>"><br><span class='description'>The number of days to accept a booking from today.</span>
              </td>
            </tr>
            <tr>
              <th>Disabled Dates</th>
              <td>
                <input type='text' class='regular-text eb-flatpickr-multiple' name='eb-disabled-dates' id='eb-disabled-dates' value="<?php echo get_option('eb-disabled-dates'); ?>"><br><span class='description'>Select the dates you want to be disabled.</span>
              </td>
            </tr>
            <tr>
              <th>Start Time</th>
              <td>
                <input type='text' class='regular-text eb-flatpickr-time' name='eb-start-time' id='eb-start-time' value="<?php echo get_option('eb-start-time'); ?>"><br><span class='description'>The start time of the day where delivery will be accepted.</span>
              </td>
            </tr>
            <tr>
              <th>End Time</th>
              <td>
                <input type='text' class='regular-text eb-flatpickr-time' name='eb-end-time' id='eb-end-time' value="<?php echo get_option('eb-end-time'); ?>"><br><span class='description'>The end time of the day where delivery will be accepted.</span>
              </td>
            </tr>
          </tbody>
        </table>
        <?php submit_button(); ?>
      </form>
    </div>
  <?php
  }

  public function eb_admin_dependencies() {
    wp_register_style('eb-admin-flatpickr-style', EB_PLUGIN_ROOT_URL . '/dependencies/flatpickr.min.css');
    wp_enqueue_style('eb-admin-flatpickr-style');
    wp_register_script('eb-admin-flatpickr-script', EB_PLUGIN_ROOT_URL . '/dependencies/flatpickr.min.js');
    wp_enqueue_script('eb-admin-flatpickr-script');
  }

}
