<?php
/*
  Plugin Name: Estong's Booking
  Plugin URI: https://github.com/dcangulo/estongs-booking
  Description: A WordPress plugin for Estong's Bellychon booking
  Version: 1.0.0
  Author: David Angulo
  Author URI: https://www.davidangulo.xyz/
*/

define('PER_PAGE_DEFAULT', 5);

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
require_once('eb_booking_form.php');
require_once('eb_booking_admin_table.php');
require_once('eb_booking_admin.php');

new EbBookingForm(__FILE__);
new EbBookingAdmin();
