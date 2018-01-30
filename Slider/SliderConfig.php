<?php
defined('ABSPATH') or die("No script kiddies please!");
/**
Plugin Name: Slider
 *
/*-----------------------------------------------------------------------------------*/
/*	Include Custom Post Types
/*-----------------------------------------------------------------------------------*/
require_once ( plugin_dir_path( __FILE__ ) . '/include/slider-post-type.php' );



add_filter('json_api_controllers', 'registerSliderControllers');
add_filter('json_api_slider_controller_path', 'setSliderControllerPath');

function registerSliderControllers($aControllers)
{

	$aControllers[] = 'Slider';
	return $aControllers;
}

function setSliderControllerPath($sDefaultPath)
{

	return dirname(__FILE__) . '/controllers/Slider.php';
}
