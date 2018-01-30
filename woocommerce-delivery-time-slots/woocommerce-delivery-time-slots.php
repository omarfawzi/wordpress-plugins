<?php
/**
 * Plugin Name: WooCommerce Delivery Time Slots
 * Plugin URI: http://codecanyon.net/item/woocommerce-delivery-time-slots/14208513?ref=rilwis
 * Description: Allow customers pick time for delivery when shipping
 * Version: 1.1
 * Author: FitWP
 * Author URI: http://fitWP.com
 */

define( 'WDTS_DIR', plugin_dir_path( __FILE__ ) );
define( 'WDTS_URL', plugin_dir_url( __FILE__ ) );
define( 'WDTS_OPTION', 'wdts' );
define( 'WDTS_VERSION', '1.0.3' );
require WDTS_DIR .'controllers/DeliveryTimeSlots.php';
require WDTS_DIR . 'inc/functions.php';
require WDTS_DIR . 'inc/shortcodes.php';
if ( is_admin() ) {
	require WDTS_DIR . 'inc/fields.php';
	$fields = new WDTS_Fields;
	$fields->register();
	require WDTS_DIR . 'inc/settings.php';
	$settings = new WDTS_Settings;
	$settings->init();

	require WDTS_DIR . 'inc/sortable-column.php';
	if ( defined( 'DOING_AJAX' ) ) {
		require WDTS_DIR . 'inc/ajax.php';
	}
}
require WDTS_DIR . 'inc/frontend.php';
$frontend = new WDTS_Frontend;
$frontend->init();

add_filter( 'woocommerce_rest_insert_shop_order_object',array(JSON_API_Delivery_Time_Slots_Controller::class,'count_before_ordering_api'));
add_filter('json_api_controllers', 'registerDeliveryTimeSlotsControllers');
add_filter('json_api_delivery_time_slots_controller_path', 'setDeliveryTimeSlotsControllerPath');

function registerDeliveryTimeSlotsControllers($aControllers)
{
	$aControllers[] = 'Delivery_Time_Slots';
	return $aControllers;
}

function setDeliveryTimeSlotsControllerPath($sDefaultPath)
{
	return dirname(__FILE__) . '/controllers/DeliveryTimeSlots.php';
}
