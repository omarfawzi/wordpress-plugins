<?php
defined('ABSPATH') or die("No script kiddies please!");
/**
Plugin Name: Multi Vendors
 *
/*-----------------------------------------------------------------------------------*/
/*	Include Custom Post Types
/*-----------------------------------------------------------------------------------*/
require_once ( plugin_dir_path( __FILE__ ) . '/include/special-vendors-post-type.php' );

add_filter('json_api_controllers', 'registerVendorsControllers');
add_filter('json_api_vendors_controller_path', 'setVendorsControllerPath');

function registerVendorsControllers($aControllers)
{

	$aControllers[] = 'Vendors';
	return $aControllers;
}

function setVendorsControllerPath($sDefaultPath)
{
	return dirname(__FILE__) . '/controllers/Vendors.php';
}