<?php
/**
 * Plugin Name: Digits Mobile Plugin
 * Description: Plugin Developed Specifically to support Digits Plugin in Mobile API
 * Text Domain: MobileValidation
 */

if (!defined('ABSPATH')) {
	exit;
}

class Config_Class
{
	public function __construct()
	{
		define('PLUGIN_FILE', __FILE__);
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		if (is_plugin_active('woocommerce/woocommerce.php') == false) {
			return 0;
		}
	}
}

$config = new Config_Class();

///////////////////////////////////////////////////////////////////////////////////////////////////
// Define for the API User wrapper which is based on json api user plugin
///////////////////////////////////////////////////////////////////////////////////////////////////

if (!is_plugin_active('json-api/json-api.php')) {
	add_action('admin_notices', 'pim_draw_notice_json_api');
	return;
}

add_filter('json_api_controllers', 'registerMobileValidationControllers');
add_filter('json_api_mobile_validation_controller_path', 'setMobileValidationControllerPath');

function registerMobileValidationControllers($aControllers)
{
	$aControllers[] = 'Mobile_Validation';
	return $aControllers;
}

function setMobileValidationControllerPath($sDefaultPath)
{
	return dirname(__FILE__) . '/controllers/MobileValidation.php';
}