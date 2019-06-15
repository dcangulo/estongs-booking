<?php
/*
  Plugin Name: Estong's Booking
  Plugin URI: https://github.com/dcangulo/estongs-booking
  Description: A WordPress plugin for Estong's Bellychon booking
  Version: 1.0.0
  Author: David Angulo
  Author URI: https://www.davidangulo.xyz/
*/

global $wpdb;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
require_once('constants/constants.php');
require_once('classes/eb_booking_form.php');
require_once('classes/eb_booking_admin_table.php');
require_once('classes/eb_booking_admin.php');
require_once('classes/eb_booking_calendar.php');
require_once('classes/eb_booking_products_table.php');
require_once('classes/eb_booking_products.php');
require_once('classes/eb_booking_settings.php');

new EbBookingForm();
new EbBookingAdmin();
new EbBookingCalendar();
new EbBookingProducts();
new EbBookingSettings();
