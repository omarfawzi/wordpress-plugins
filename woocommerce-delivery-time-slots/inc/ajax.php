<?php
add_action( 'wp_ajax_wdts_check_number_of_shipments', 'wdts_check_number_of_shipments' );
add_action( 'wp_ajax_nopriv_wdts_check_number_of_shipments', 'wdts_check_number_of_shipments' );

/**
 * Check number of shipments for a date
 */
function wdts_check_number_of_shipments() {
	$option = wdts_option();
	$max   = intval( trim( $option['shipments_per_day'] ) );

	if ( $max < 1 ) {
		wp_send_json_success();
	}

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'check-shipping' ) || empty( $_POST['date'] ) ) {
		wp_send_json_error( array(
			'message' => __( 'Invalid request', 'woocommerce' ),
			'type'    => 'invalid',
		) );
	}

	$report = wdts_get_shipping_report();
	$date   = date( 'd-m-Y', strtotime( $_POST['date'] ) );

	if ( isset( $report[ $date ] ) && $max <= $report[ $date ] ) {
		$message = $option['notification_exceeded_shipments'] ? $option['notification_exceeded_shipments'] : __( 'You cannot request shipping on this day!', 'woocommerce' );
		wp_send_json_error( array(
			'message' => $message,
			'type'    => 'exceed',
		) );
		
	}
	else {
		$num             = isset( $report[ $date ] ) ? absint( $report[ $date ] ) : 0;
		$num             += 1;
		$report[ $date ] = $num;
		// Delete shipping days less than today
		$report = wdts_delete_past_dates( $report );
		// Save report
		wdts_update_shipping_report( $report );
		wp_send_json_success();
	}
}

/**
 * Update shipping days in file JSon
 *
 * @return void
 */
function wdts_update_shipping_report( $report ) {
	$upload_dir  = wp_upload_dir();
	$upload_path = $upload_dir['basedir'];

	$file_json = $upload_path . '/data_json.txt';
	file_put_contents( $file_json, json_encode( $report ) );
}

/**
 * Get shipping report
 *
 * @return array
 */
function wdts_get_shipping_report() {
	$upload_dir  = wp_upload_dir();
	$upload_path = $upload_dir['basedir'];

	$file_json = $upload_path . '/data_json.txt';
	if ( ! file_exists( $file_json ) ) {
		return array();
	}
	$report = file_get_contents( $file_json );
	$report = json_decode( $report, true );

	return $report;
}

/**
 * Delete log of shipping for past dates
 *
 * @return array
 */
function wdts_delete_past_dates( $report ) {
	$today = date('d-m-Y');
	foreach ( $report as $key => $value ) {
		if ( strtotime( $key ) < strtotime($today) ) {
			unset( $report[ $key ] );
		}
	}
	return $report;
}
