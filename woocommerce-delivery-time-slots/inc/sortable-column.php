<?php

class RW_Woocommerce_Delivery_Time_Slots_Sort_Column {
	/**
	 * Class constructor
	 * Add hooks to Woocommerce
	 */
	function __construct() {
		// Add column Delivery time to manage Orders
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'expand_columns_delivery' ), 15 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'values_on_delivery' ), 15 );
		// Sort Delivery time column
		add_filter( "manage_edit-shop_order_sortable_columns", array( $this, 'sortable_columns' ) );
		add_filter( 'parse_query', array( $this, 'filter' ) );
	}

	/**
	 * Define columns shipping date for the orders page.
	 *
	 * @param mixed $columns
	 *
	 * @return array
	 */
	function expand_columns_delivery( $columns ) {
		$columns['delivery_date'] = __( 'Delivery', 'woocommerce-delivery-time-slots' );

		return $columns;
	}

	/**
	 * Values for the custom columns on the orders page.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	function values_on_delivery( $name ) {
		if ( 'delivery_date' == $name ) {
			echo do_shortcode( "[wdts_shipping_time id='" . get_the_ID() . "']" );
		}
	}

	/**
	 * Make columns sortable
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	function sortable_columns( $columns ) {
		$columns = array_merge( $columns, array(
			'delivery_date' => 'delivery_date',
		) );

		return $columns;
	}

	/**
	 * Add taxonomy filter when request posts (in screen)
	 *
	 * @param WP_Query $query
	 *
	 * @return mixed
	 */
	function filter( $query ) {
		$vars = &$query->query_vars;

		// Sort by delivery date
		if ( ! empty( $_GET['orderby'] ) && 'delivery_date' == $_GET['orderby'] ) {
			$vars['orderby']  = 'meta_value_num';
			$vars['meta_key'] = '_delivery_date';
		}
	}
}

new RW_Woocommerce_Delivery_Time_Slots_Sort_Column;
