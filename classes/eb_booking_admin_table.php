<?php
class EbBookingAdminTable extends WP_List_Table {

  private static $wpdb;
  private static $table_name;

  public function __construct() {
    parent::__construct([
      'singular' => __('Booking'),
      'plural' => __('Bookings'),
      'ajax' => false
    ]);

    global $wpdb;

    self::$wpdb = $wpdb;
    self::$table_name = self::$wpdb->prefix . 'eb_bookings';
  }

  public static function get_bookings($per_page = PER_PAGE_DEFAULT, $page_number = 1) {
    $sql = 'SELECT * FROM ' . self::$table_name;

    if ( !empty($_REQUEST['orderby']) ) {
      $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
      $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' asc';
    }

    $sql .= " LIMIT $per_page";
    $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
    $result = self::$wpdb->get_results($sql, 'ARRAY_A');

    return $result;
  }

  public static function delete_booking($id) {
    self::$wpdb->delete(self::$table_name, ['id' => $id]);
  }

  public static function record_count() {
    $sql = 'SELECT COUNT(*) FROM ' . self::$table_name;

    return self::$wpdb->get_var($sql);
  }

  public function no_items() {
    _e('No bookings avaliable.');
  }

  public function column_default($item, $column_name) {
    return $item[$column_name];
  }

  public function column_cb($item) {
    $cb_elem = "<input type='checkbox' name='bulk-delete[]' value='{$item['id']}'>";

    return $cb_elem;
  }

  public function column_id($item) {
    $delete_nonce = wp_create_nonce('eb_delete_booking');
    $title = "<strong>{$item['id']}</strong>";
    $actions = [
      'view' => sprintf('<a href="?page=%s&action=%s&booking=%s">View</a>', esc_attr($_REQUEST['page']), 'view', absint($item['id'])),
      'edit' => sprintf('<a href="?page=%s&action=%s&booking=%s">Edit</a>', esc_attr($_REQUEST['page']), 'edit', absint($item['id'])),
      'delete' => sprintf('<a href="?page=%s&action=%s&booking=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce)
    ];

    return $title . $this->row_actions($actions);
  }

  public function get_columns() {
    $columns = [
      'cb' => "<input type='checkbox'>",
      'id' => __('Booking ID'),
      'name' => __('Name'),
      'email_address' => __('Email Address'),
      'contact_number' => __('Contact Number'),
      'delivery_date' => __('Date'),
      'quantity' => __('Quantity'),
      'additional_notes' => __('Addtional Notes')
    ];

    return $columns;
  }

  public function get_sortable_columns() {
    $sortable_columns = [
      'id' => ['id'],
      'name' => ['name'],
      'email_address' => ['email_address'],
      'delivery_date' => ['delivery_date'],
      'quantity' => ['quantity']
    ];

    return $sortable_columns;
  }

  public function get_bulk_actions() {
    $actions = ['bulk-delete' => 'Delete'];

    return $actions;
  }

  public function prepare_items() {
    $this->_column_headers = $this->get_column_info();

    $this->process_bulk_action();

    $per_page = $this->get_items_per_page('bookings_per_page', PER_PAGE_DEFAULT);
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();

    $this->set_pagination_args([
      'total_items' => $total_items,
      'per_page' => $per_page
    ]);

    $this->items = self::get_bookings($per_page, $current_page);
  }

  public function process_bulk_action() {
    if ( 'delete' === $this->current_action() ) {
      $nonce = esc_attr($_REQUEST['_wpnonce']);

      if ( !wp_verify_nonce($nonce, 'eb_delete_booking') ) exit;

      self::delete_booking(absint($_GET['booking']));

      wp_redirect(esc_url_raw(add_query_arg()));

      exit;
    }

    $action_1_delete = (isset($_POST['action']) && $_POST['action'] == 'bulk-delete');
    $action_2_delete = (isset( $_POST['action2']) && $_POST['action2'] == 'bulk-delete');

    if ( $action_1_delete || $action_2_delete ) {
      $record_ids = esc_sql($_POST['bulk-delete']);

      foreach($record_ids as $record_id) {
        self::delete_booking($record_id);
      }

      wp_redirect(esc_url_raw(add_query_arg()));

      exit;
    }
  }

}
