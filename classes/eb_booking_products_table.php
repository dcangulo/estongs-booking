<?php
class EbBookingProductsTable extends WP_List_Table {

  private static $wpdb;

  public function __construct() {
    parent::__construct([
      'singular' => __('Product'),
      'plural' => __('Products'),
      'ajax' => false
    ]);

    global $wpdb;

    self::$wpdb = $wpdb;
  }

  public static function get_products($per_page = EB_PER_PAGE_DEFAULT, $page_number = 1) {
    $sql = 'SELECT * FROM ' . EB_PRODUCTS_TABLE;

    if ( !empty($_REQUEST['orderby']) ) {
      $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
      $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' asc';
    }

    $sql .= " LIMIT $per_page";
    $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
    $result = self::$wpdb->get_results($sql, 'ARRAY_A');

    return $result;
  }

  public static function delete_product($id) {
    self::$wpdb->delete(EB_PRODUCTS_TABLE, ['id' => $id]);
  }

  public static function record_count() {
    $sql = 'SELECT COUNT(*) FROM ' . EB_PRODUCTS_TABLE;

    return self::$wpdb->get_var($sql);
  }

  public function no_items() {
    _e('No products avaliable.');
  }

  public function column_default($item, $column_name) {
    return $item[$column_name];
  }

  public function column_cb($item) {
    $cb_elem = "<input type='checkbox' name='bulk-delete[]' value='{$item['id']}'>";

    return $cb_elem;
  }

  public function column_id($item) {
    $delete_nonce = wp_create_nonce('eb_delete_product');
    $title = "<strong>{$item['id']}</strong>";
    $actions = [
      'edit' => sprintf('<a href="?page=%s&action=%s&product=%s">Edit</a>', esc_attr($_REQUEST['page']), 'edit', absint($item['id'])),
      'delete' => sprintf('<a href="?page=%s&action=%s&product=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce)
    ];

    return $title . $this->row_actions($actions);
  }

  public function column_price($item) {
    $title = '₱' . number_format($item['price'], 2);

    return $title;
  }

  public function get_columns() {
    $columns = [
      'cb' => "<input type='checkbox'>",
      'id' => __('Product ID'),
      'name' => __('Name'),
      'sku' => __('SKU'),
      'price' => __('Price')
    ];

    return $columns;
  }

  public function get_sortable_columns() {
    $sortable_columns = [
      'id' => ['id'],
      'name' => ['name'],
      'sku' => ['sku'],
      'price' => ['price']
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

    $per_page = $this->get_items_per_page('products_per_page', EB_PER_PAGE_DEFAULT);
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();

    $this->set_pagination_args([
      'total_items' => $total_items,
      'per_page' => $per_page
    ]);

    $this->items = self::get_products($per_page, $current_page);
  }

  public function process_bulk_action() {
    if ( 'delete' === $this->current_action() ) {
      $nonce = esc_attr($_REQUEST['_wpnonce']);

      if ( !wp_verify_nonce($nonce, 'eb_delete_product') ) exit;

      self::delete_product(absint($_GET['product']));
    }

    $action_1_delete = (isset($_POST['action']) && $_POST['action'] == 'bulk-delete');
    $action_2_delete = (isset( $_POST['action2']) && $_POST['action2'] == 'bulk-delete');

    if ( $action_1_delete || $action_2_delete ) {
      $record_ids = esc_sql($_POST['bulk-delete']);

      foreach($record_ids as $record_id) {
        self::delete_product($record_id);
      }
    }
  }

}
