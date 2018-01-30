<?php
defined('ABSPATH') or die("No script kiddies please!");
/**
Plugin Name: Cities And Districts
 *
/*-----------------------------------------------------------------------------------*/
/*	Include Custom Post Types
/*-----------------------------------------------------------------------------------*/
require_once ( plugin_dir_path( __FILE__ ) . '/include/city-post-type.php' );
require_once ( plugin_dir_path( __FILE__ ) . '/include/district-post-type.php' );


/*-----------------------------------------------------------------------------------*/
/*	Load Required JS Scripts
/*-----------------------------------------------------------------------------------*/
if(!function_exists('load_csc_scripts')){
	function load_csc_scripts(){
		if (is_admin()) {

			// Defining scripts directory url
			$java_script_url = plugin_dir_url( __FILE__ ).'js/';

			// Custom Script
			wp_register_script('jquery.validate.min',$java_script_url.'jquery.validate.min.js', array('jquery'));
			wp_register_script('custom',$java_script_url.'custom.js', array('jquery'), '1.0', true);

			// Enqueue Scripts that are needed on all the pages
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery.validate.min');
			wp_enqueue_script('custom');
		}
	}
}
add_action('admin_enqueue_scripts', 'load_csc_scripts');

add_action( 'wp_ajax_get_districts_of_city', 'get_districts_of_city' );
add_action( 'wp_ajax_nopriv_get_districts_of_city', 'get_districts_of_city' );

function get_districts_of_city()
{
	global $wpdb;
	$cid = intval( $_POST['CID'] );
	$district_posts = get_posts( array( 'post_type' => 'districts', 'posts_per_page' => -1, 'suppress_filters' => 0, 'meta_query' => array(
		array(
			'key' => 'district_meta_box_city',
			'value' => $cid,
		)
	) ) );
	$district_ops = '<option value="">'.__('Select District')."</option>";
	if(!empty($district_posts)){
		foreach( $district_posts as $district_post ){
			$district_ops .= '<option value="'.$district_post->ID.'">'.$district_post->post_title."</option>";
		}
	}
	echo $district_ops;
	die(); // this is required to terminate immediately and return a proper response
}

function hide_city_add_new_custom_type()
{
	global $submenu;
	// replace my_type with the name of your post type
	unset($submenu['edit.php?post_type=cities'][10]);
}
add_action('admin_menu', 'hide_city_add_new_custom_type');


register_activation_hook( __FILE__, 'csc_activation_function' );
function csc_activation_function() {
	// Do stuff here
}


add_filter('json_api_controllers', 'registerCitiesAndDistrictsControllers');
add_filter('json_api_cities_and_districts_controller_path', 'setCitiesAndDistrictsControllerPath');

function registerCitiesAndDistrictsControllers($aControllers)
{
	$aControllers[] = 'Cities_And_Districts';
	return $aControllers;
}

function setCitiesAndDistrictsControllerPath($sDefaultPath)
{
	return dirname(__FILE__) . '/controllers/CitiesAndDistricts.php';
}

add_filter("woocommerce_checkout_fields", "custom_override_checkout_fields", 1);
function custom_override_checkout_fields($fields) {
$fields['billing']['billing_first_name']['priority'] = 1;
	$fields['billing']['billing_country']['priority'] = 2;
	$fields['billing']['billing_state']['priority'] = 3;
	$fields['billing']['billing_city']['priority'] = 4;
	$fields['billing']['billing_address_1']['priority'] = 5;
// 	$fields['billing']['billing_address_2']['priority'] = 6;
	$fields['billing']['billing_email']['priority'] = 7;
// 	$fields['billing']['billing_phone']['priority'] = 8;
	$fields['shipping']['shipping_first_name']['priority'] = 1;
	$fields['shipping']['shipping_country']['priority'] = 2;
	$fields['shipping']['shipping_state']['priority'] = 3;
	$fields['shipping']['shipping_city']['priority'] = 4;
	$fields['shipping']['shipping_address_1']['priority'] = 5;
	$fields['shipping']['shipping_address_2']['priority'] = 6;
	$fields['shipping']['shipping_email']['priority'] = 7;
	$fields['shipping']['shipping_phone']['priority'] = 8;
	list($fields['shipping']['shipping_city']['label'],$fields['shipping']['shipping_state']['label']) = array($fields['shipping']['shipping_state']['label'],$fields['shipping']['shipping_city']['label']);
	list($fields['billing']['billing_city']['label'],$fields['billing']['billing_state']['label']) = array($fields['billing']['billing_state']['label'],$fields['billing']['billing_city']['label']);
	return $fields;
}

add_filter( 'woocommerce_default_address_fields', 'custom_override_default_locale_fields' );
function custom_override_default_locale_fields( $fields ) {
	$fields['state']['priority'] = 3;
	$fields['city']['priority'] = 4;
	$fields['address_1']['priority'] = 5;
	$fields['address_2']['priority'] = 6;
	return $fields;
}

function get_cities_with_districts() {
	$cities_db = get_posts( [ 'post_type' => 'cities' ] );
	$cities    = [];
	foreach ( $cities_db as $city ) {
		$obj          = new stdClass();
		$obj->name    = $city->post_name;
		$obj->title   = $city->post_title;
		$obj->cost    = get_post_meta( $city->ID, '_city_shipping_cost', true );
		$districts_db = get_posts( [ 'post_type'  => 'districts',
		                             'meta_key'   => 'district_meta_box_city',
		                             'meta_value' => $city->ID
		] );
		$regions      = [];
		foreach ( $districts_db as $district ) {
			$obj2        = new stdClass();
			$obj2->name  = $district->post_name;
			$obj2->title = $district->post_title;
			$obj2->cost  = get_post_meta( $district->ID, '_district_shipping_cost', true );
			$regions[]   = $obj2;
		}
		$obj->regions = $regions;
		$cities[]     = $obj;
	}

	return $cities;
}

function extract_for_options( $to_extract ) {
	$extracted = [];
	foreach ( $to_extract as $item ) {
		$extracted[ $item->title ] = $item->title;
	}

	return $extracted;
}


add_filter( 'woocommerce_states', 'custom_woocommerce_states' );

function custom_woocommerce_states( $states ) {
	$keys = array_keys(WC()->countries->get_allowed_countries());
	$cities = extract_for_options(get_cities_with_districts());
	foreach ($keys as $key){;
		$states[$key] = $cities;
	}
	return $states;
}
add_filter( 'wc_city_select_cities', 'my_cities' );
function my_cities( $cities ) {
	$keys = array_keys(WC()->countries->get_allowed_countries());
	$cities_db = get_cities_with_districts();
	$cities_with_districts = [];
	foreach ($cities_db as $item){
		$temp = [];
		foreach ($item->regions as $region){
			$temp[] = $region->title;
		}
		$cities_with_districts[$item->title] = $temp;
	}


	foreach ($keys as $key){;
		$cities[$key] = $cities_with_districts;
	}
	return $cities;
}

add_action( 'woocommerce_checkout_update_order_review', 'woocommerce_checkout_update_order_review' );
function woocommerce_checkout_update_order_review( $post_data ) {
	$data = array();
	$vars = explode( '&', $post_data );
	foreach ( $vars as $k => $value ) {
		$v = explode( '=', urldecode( $value ) );
		$data[ $v[0] ] = $v[1];
	}
	$shipping_cost = get_shipping_cost_by_city( $data['billing_state'], $data['billing_city'] );
	WC()->session->set( 'shipping_city_cost', $shipping_cost );
	foreach ( WC()->cart->get_shipping_packages() as $package_key => $package ) {
		WC()->session->set( 'shipping_for_package_' . $package_key, false );
	}

}

add_filter( 'woocommerce_package_rates', 'adjust_shipping_rate', 50 );
function adjust_shipping_rate( $rates ) {
	foreach ( $rates as $rate ) {
		if ($rate->method_id == 'free_shipping')
			continue;
		$rate->cost = WC()->session->get( 'shipping_city_cost' );
	}
	
	return $rates;
}

function get_shipping_cost_by_city( $city, $region ) {
	$cities = get_cities_with_districts();
	foreach ( $cities as $item ) {
		if ( $item->title == $city ) {
			foreach ( $item->regions as $elem ) {
				if ( $elem->title == $region ) {
					if ( $elem->cost && $elem->cost != 0 ) {
						return $elem->cost;
					}
				}
			}
			if ( $item->cost ) {
				return $item->cost;
			} else {
				return 0;
			}
		}
	}

	return 0;
}


?>