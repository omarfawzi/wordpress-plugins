<?php
add_shortcode( 'wdts_shipping_time', 'wdts_shortcode_shipping_time' );
add_shortcode( 'wdts_field', 'wdts_shortcode_field' );

/**
 * Get delivery time for order by order ID
 *
 * @param array $atts
 *
 * @return string
 */
function wdts_shortcode_shipping_time( $atts ) {
	$option = wdts_option();
	if ( $option['show_timepicker'] == 0 ) {
		$format = wdts_get_format_date_time( true );
	} else {
		$format = wdts_get_format_date_time( false );
	}
	$date = get_post_meta( $atts['id'], '_delivery_date', true );
	if ( $date == '' ) {
		return '';
	}
	$time = get_post_meta( $atts['id'], '_delivery_time', true );

	return date( $format, (int) $date ) . ' ' . $time;
}

/**
 * Show delivery time picker field for order by order ID
 *
 * @param array $atts
 *
 * @return string
 */
function wdts_shortcode_field( $atts ) {
	$output = '<input type="text" id="delivery_date" name="delivery_date" class="input-text delivery-date">';

	return $output;
}
