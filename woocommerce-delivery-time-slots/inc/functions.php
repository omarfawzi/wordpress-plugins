<?php
/**
 * Convert a date to timestamp
 *
 * @param string $date
 *
 * @return int
 */
function wdts_to_timestamp( $date, $show_timepicker ) {
	$format = wdts_get_format_date_time( true );

	if ( ! $date ) {
		return 0;
	}

	if ( version_compare( phpversion(), '5.3.0', '>=' ) ) {
		$date_time = DateTime::createFromFormat( $format, $date );

		return $date_time->getTimestamp();
	}

	return strtotime( $date ); // Not 100% safe
}

/**
 * Get format date time saved
 *
 * @return string
 */
function wdts_get_format_date_time( $get_date_format_only ) {
	$option = wdts_option();
	$date   = $option['date_format'] ? wdts_convert_date_format( $option['date_format'] ) : 'm/d/Y';
	$format = $date;

	return $format;
}

/**
 * Returns a date() compatible format string from the JavaScript format
 *
 * @see      http://www.php.net/manual/en/function.date.php
 *
 * @param string $format
 *
 * @return string
 */
function wdts_convert_date_format( $format ) {
	// Missing:  'o' => '', '!' => '', 'oo' => '', '@' => '', "''" => "'"
	$translation = array(
		'd'  => 'j',
		'dd' => 'd',
		'oo' => 'z',
		'D'  => 'D',
		'DD' => 'l',
		'm'  => 'n',
		'mm' => 'm',
		'M'  => 'M',
		'MM' => 'F',
		'y'  => 'y',
		'yy' => 'Y'
	);

	return strtr( $format, $translation );
}

/**
 * Get plugin option
 * Use default values if missed
 *
 * @return array
 */
function wdts_option() {
	$option = get_option( WDTS_OPTION );
	$option = wp_parse_args( $option, array(
		'required'                  => 0,
		'disabled_shipping_methods' => array(),
		'label'                     => __( 'Delivery Time', 'woocommerce' ),
		'date_format'               => 'mm/dd/yy',

		'language'            => '',
		'custom_close_text'   => '',
		'custom_prev_text'    => '',
		'custom_next_text'    => '',
		'custom_current_text' => '',
		'custom_month_names'  => '',
		'custom_day_names'    => '',

		'max_date'                 => '',
		'restricted_dates'         => '',
		'restricted_week_days'     => array(),
		'restricted_sequence_days' => '',
		'show_timepicker'          => 0,

		'restricted_minhour' => 0,
		'restricted_maxhour' => 23,

		'shipments_per_day'               => '',
		'notification_exceeded_shipments' => '',

		'theme' => 'smoothness',
	) );

	return $option;
}

function wdt_sub_days( $day1, $day2 ) {
	$date2 = strtotime( $day2 );
	$date1 = strtotime( $day1 );
	$days  = ( $date2 - $date1 ) / 86400;

	return $days;
}

/**
 * Check if a date is restricted by "Restricted Dates" rules.
 *
 * @param string $date Date need to check (m/d/Y)
 * @param string $restricted Restricted date (m/d/Y with/without stars)
 *
 * @return bool
 */
function wdt_is_restricted( $date, $restricted ) {
	$restricted = str_replace( '/', '-', $restricted );
	// If no stars
	if ( false === strpos( $restricted, '*' ) ) {
		$restricted = date( 'm-d-Y', strtotime( $restricted ) );

		return $date == $restricted;
	}

	$restricted_parts = explode( '-', $restricted );
	list( $restricted_parts[0], $restricted_parts[1] ) = array( $restricted_parts[1], $restricted_parts[0] );
	$date_parts = explode( '-', $date );

	return ( $restricted_parts[2] == '*' || $restricted_parts[2] == $date_parts[2] )
	       && ( $restricted_parts[0] == '*' || $restricted_parts[0] == $date_parts[0] )
	       && ( $restricted_parts[1] == '*' || $restricted_parts[1] == $date_parts[1] );
}

function is_restricted( $date, $restricted_dates, $restricted_week_days, $restrict_to_date, $shippments_per_day ) {
	if ( isset( $restrict_to_date ) ) {
		$one_min = DateTime::createFromFormat( 'm-d-Y', $date )->getTimestamp();
		$two_min = DateTime::createFromFormat( 'm-d-Y', $restrict_to_date )->getTimestamp();
		if ( $one_min < $two_min ) {
			return true;
		}
	}
	if ( isset( $shippments_per_day ) ) {
		if ( ! shippment_available( $date, $shippments_per_day ) ) {
			return true;
		}
	}

	if ( isset( $restricted_week_days ) ) {
		$day = get_day_of_a_week( $date );
		for ( $i = 0; $i < count( $restricted_week_days ); $i ++ ) {
			if ( $day == $restricted_week_days[ $i ] ) {
				return true;
			}
		}
	}

	if ( isset( $restricted_dates ) ) {

		foreach ( $restricted_dates as $restricted_date ) {
			if (strpos('-',$restricted_date) != false) {
				if ( wdt_is_restricted( $date, $restricted_date ) ) {
					return true;
				}
			}
			else {
				$range = explode('-',$restricted_date);
				$from = convert_stars( $date,trim($range[0]));
				$to = convert_stars( $date,trim($range[1]));
				$date = DateTime::createFromFormat('m-d-Y',$date);
				$date = strtotime($date->format('d-m-Y'));
				if ( $from <= $to ) {
					if ( $from <= $date && $date <= $to ) {
						return true;
					}
				}
				// Compare only if at least one of from date and to date contains stars ( * )
				else if ( strpos($range[0],'*') != false || strpos($range[1],'*') != false ) {
					if ( $from <= $date || $date <= $to ) {
						return true;
					}
				}
			}

		}
	}

	return false;
}

function convert_stars($date,$restricted){
	if (strpos($restricted,'*') === false){
		$restricted   = DateTime::createFromFormat( 'd/m/Y', $restricted );
		$restricted   = $restricted->format( 'm/d/Y' );
	}
	$parts = explode('/',$restricted);
	if ( '*' == $parts[0] ) {
		$parts[0] = date('m',strtotime($date));
	} else {
//		$parts[0] = intval( $parts[0] ) - 1;
	}
	if ( '*' == $parts[1] ) {
		$parts[1] = date('d',strtotime($date));
	}
	if ( '*' == $parts[2] ) {
		$parts[2] = date('y',strtotime($date));
	}
	if (strpos($restricted,'*') !== false){
		list( $parts[0], $parts[1] ) = array($parts[1],$parts[0]);
	}

	return mktime(null, null, 0, $parts[0], $parts[1], $parts[2]);
}
function get_day_of_a_week( $date ) {
	$date = DateTime::createFromFormat( 'm-d-Y', $date );
	$date = $date->format( 'd-m-Y' );

	return intval( date( 'w', strtotime( $date ) ) );
}

function shippment_available( $date, $shippments_per_day ) {
	$date   = DateTime::createFromFormat( 'm-d-Y', $date );
	$date   = $date->format( 'd-m-Y' );
	$report = get_shipping_report();
	if ( isset( $report[ $date ] ) && $shippments_per_day <= $report[ $date ] ) {
		return false;
	}
	return true;
}

function get_nearest_available_days( $days, $restricted_dates, $restricted_week_days, $restrict_to_date, $restricted_after_date, $shippments_per_day ) {
	$available_dates = [];
	for ( $i = 0; $i < $days; $i ++ ) {
		$date = date( 'm-d-Y', strtotime( "+" . $i . " day" ) );
		if ( isset( $restricted_after_date ) ) {
			$one_max = DateTime::createFromFormat( 'm-d-Y', $date )->getTimestamp();
			$two_max = DateTime::createFromFormat( 'm-d-Y', $restricted_after_date )->getTimestamp();
			if ( $one_max >= $two_max ) {
				continue;
			}
		}
		if ( ! is_restricted( $date, $restricted_dates, $restricted_week_days, $restrict_to_date, $shippments_per_day ) ) {
		    $obj = new stdClass();
		    $obj->date = date( 'd-m-Y', strtotime( "+" . $i . " day" ) );
		    $obj->timestamp = strtotime( "+" . $i . " day" );
			$available_dates[] = $obj;
		} else {
			$days ++;
		}
	}

	return $available_dates;
}

function update_shipping_report( $report ) {
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
function get_shipping_report() {
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
function delete_past_dates( $report ) {
	$today = date('d-m-Y');
	foreach ( $report as $key => $value ) {
		if ( strtotime( $key ) < strtotime($today) ) {
			unset( $report[ $key ] );
		}
	}
	return $report;
}
