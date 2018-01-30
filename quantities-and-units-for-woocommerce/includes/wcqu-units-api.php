<?php

/**
 *
 * Get The latest post from a category !
 * @param array $params Options for the function.
 * @return string|null Post title for the latest,? * or null if none
 *
 */


function get_units ( $params ){
	return get_terms(['taxonomy'=>'product_unit']);
}

// Register the rest route here.

add_action( 'rest_api_init', function () {
	register_rest_route( 'wc/v1', 'products/units',array(
		'methods'  => 'GET',
		'callback' => 'get_units'
	) );
} );