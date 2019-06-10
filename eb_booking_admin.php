<?php
class EbBookingAdmin {

  public $bookings;

  public function __construct() {
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
  ?>
    <div class='wrap'>
      <h2>Booking List</h2>
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

  public function eb_screen_options() {
    $option = 'per_page';
    $args   = [
      'label' => 'Bookings',
      'default' => PER_PAGE_DEFAULT,
      'option' => 'bookings_per_page'
    ];

    add_screen_option($option, $args);

    $this->bookings = new EbBookingAdminTable();
  }

}
