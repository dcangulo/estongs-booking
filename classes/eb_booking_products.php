<?php
class EbBookingProducts {

  private $wpdb;
  private $products;

  public function __construct() {
    global $wpdb;

    $this->wpdb = $wpdb;

    add_filter('set-screen-option', [__CLASS__, 'eb_set_screen'], 10, 3);
    add_action('admin_menu', [$this, 'eb_booking_products_menu']);
  }

  public static function eb_set_screen($status, $option, $value) {
    return $value;
  }

  public function eb_booking_products_menu() {
    $hook = add_submenu_page('eb-bookings', 'All Products', 'Products', 'manage_options', 'eb-products', [$this, 'eb_booking_products_page']);

    add_action("load-$hook", [$this, 'eb_screen_options']);
  }

  public function eb_booking_products_page() {
    if ( isset($_GET['action']) && $_GET['action'] === 'new' ) {
      return $this->eb_booking_products_new();
    }
    if ( isset($_GET['action']) && $_GET['action'] === 'edit' ) {
      return $this->eb_booking_products_edit(esc_sql($_GET['product']));
    }

    return $this->eb_booking_products_index();
  }

  public function eb_booking_products_index() {
  ?>
    <div class='wrap'>
      <h1 class='wp-heading-inline'>Products</h1>
      <?php
        echo sprintf('<a class="page-title-action" href="?page=%s&action=%s">Add New</a>', esc_attr($_REQUEST['page']), 'new');
      ?>
      <div id='post-body' class='metabox-holder columns-2'>
        <div id='post-body-content'>
          <div class='meta-box-sortables ui-sortable'>
            <form method='post'>
              <?php
                $this->products->prepare_items();
                $this->products->display();
              ?>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php
  }

  public function eb_booking_products_new() {
    if ( isset($_POST['eb-submit']) ) {
      $this->eb_booking_products_create($_POST);
    }
  ?>
    <div class='wrap'>
      <h1 class='wp-heading-inline'>Add New Product</h1>
      <?php
        echo sprintf('<a class="page-title-action" href="?page=%s">Cancel</a>', esc_attr($_REQUEST['page']));
      ?>
      <form method='post' action=''>
        <table class='form-table'>
          <tr>
            <th scope='row'>Name</th>
            <td><input type='text' name='eb-product-name' class='regular-text'></td>
          </tr>
          <tr>
            <th scope='row'>SKU</th>
            <td><input type='text' name='eb-product-sku' class='regular-text'></td>
          </tr>
          <tr>
            <th scope='row'>Price (₱)</th>
            <td><input type='number' step='0.01' name='eb-product-price' class='regular-text'></td>
          </tr>
        </table>
        <p class='submit'>
          <button type='submit' name='eb-submit' class='button button-primary'>Save Changes</button>
        </p>
      </form>
    </div>
  <?php
  }

  public function eb_booking_products_create($params) {
    $this->wpdb->insert(EB_PRODUCTS_TABLE, [
      'name' => esc_sql($params['eb-product-name']),
      'sku' => esc_sql($params['eb-product-sku']),
      'price' => esc_sql($params['eb-product-price'])
    ]);
  }

  public function eb_booking_products_edit($product_id) {
    if ( isset($_POST['eb-submit']) ) {
      $this->eb_booking_products_update($_POST, $product_id);
    }

    $product_query = "SELECT * FROM " . EB_PRODUCTS_TABLE . " WHERE id='$product_id'";
    $product = $this->wpdb->get_row($product_query);
  ?>
    <div class='wrap'>
      <h1 class='wp-heading-inline'>Editing Product #<?php echo $product_id; ?></h1>
      <?php
        echo sprintf('<a class="page-title-action" href="?page=%s">Cancel</a>', esc_attr($_REQUEST['page']));
      ?>
      <form method='post' action=''>
        <table class='form-table'>
          <tr>
            <th scope='row'>Name</th>
            <td><input type='text' name='eb-product-name' class='regular-text' value='<?php echo $product->name; ?>'></td>
          </tr>
          <tr>
            <th scope='row'>SKU</th>
            <td><input type='text' name='eb-product-sku' class='regular-text' value='<?php echo $product->sku; ?>'></td>
          </tr>
          <tr>
            <th scope='row'>Price (₱)</th>
            <td><input type='number' step='0.01' name='eb-product-price' class='regular-text' value='<?php echo sprintf("%.2f", $product->price); ?>'></td>
          </tr>
        </table>
        <p class='submit'>
          <button type='submit' name='eb-submit' class='button button-primary'>Save Changes</button>
        </p>
      </form>
    </div>
  <?php
  }

  public function eb_booking_products_update($params, $product_id) {
    $this->wpdb->update(EB_PRODUCTS_TABLE, [
      'name' => esc_sql($params['eb-product-name']),
      'sku' => esc_sql($params['eb-product-sku']),
      'price' => esc_sql($params['eb-product-price'])
    ], ['id' => $product_id]);
  }

  public function eb_screen_options() {
    $option = 'per_page';
    $args = [
      'label' => 'Products',
      'default' => EB_PER_PAGE_DEFAULT,
      'option' => 'products_per_page'
    ];

    add_screen_option($option, $args);

    $this->products = new EbBookingProductsTable();
  }

}
