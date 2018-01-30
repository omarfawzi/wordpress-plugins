<?php
/**
 * This class adds time picker for delivery when shipping
 * Delivery time will be save as a part of order and will be included in emails
 */

/**
 * Plugin settings class.
 */
class WDTS_Settings {
	/**
	 * Class constructor
	 * Add hooks to Woocommerce
	 */
	public function init() {
		// Enqueue scripts and styles for admin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Add setting fields to "Shipping" tab of woocommerce settings page
		add_filter( 'woocommerce_shipping_settings', array( $this, 'settings' ) );

		// Update plugin option
		add_action( 'woocommerce_update_options', array( $this, 'update' ) );
	}

	/**
	 * Enqueue scripts and styles on admin pages.
	 */
	public function enqueue() {
		wp_enqueue_style( 'wdts-admin-style', WDTS_URL . 'css/admin-style.css' );
		wp_enqueue_script( 'wdts-admin-script', WDTS_URL . 'js/admin-script.js', array( 'jquery' ), WDTS_VERSION, true );
	}

	/**
	 * Add setting fields to "Shipping" tab of woocommerce settings page
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function settings( $settings ) {
		$docs = sprintf( __( '<a href="%s" target="_blank">See documentation</a>.', 'woocommerce-delivery-time-slots' ), 'http://fitwp.com/docs/woocommerce-delivery-time-slots' );

		// Title
		$settings[] = array(
			'title' => __( 'Delivery Time Options', 'woocommerce-delivery-time-slots' ),
			'desc'  => sprintf( __( 'Options for Woocommerce Delivery Time for Shipping addon. %s', 'woocommerce-delivery-time-slots' ), $docs ),
			'type'  => 'title',
		);

		// Required?
		$settings[] = array(
			'name' => __( 'Required?', 'woocommerce-delivery-time-slots' ),
			'id'   => WDTS_OPTION . '[required]',
			'type' => 'custom_checkbox',
		);

		// Enable date time picker along shipping methods selected
		$options                                              = array();
		$zones                                                = array();
		$default_zone                                         = WC_Shipping_Zones::get_zone( 0 );
		$zones[ $default_zone->get_id() ]                     = $default_zone->get_data();
		$zones[ $default_zone->get_id() ]['shipping_methods'] = $default_zone->get_shipping_methods();
		$zones                                                = array_merge( $zones, WC_Shipping_Zones::get_zones() );
		foreach ( $zones as $id => $zone ) {
			$methods = $zone['shipping_methods'];
			foreach ( $methods as $method ) {
				$options[ $method->get_rate_id() ] = '[' . $zone['zone_name'] . '] ' . esc_html( $method->get_method_title() );
			}
		}
		$settings[] = array(
			'title'   => __( 'Disable for shipping methods', 'woocommerce-delivery-time-slots' ),
			'id'      => WDTS_OPTION . '[disabled_shipping_methods]',
			'type'    => 'checkbox_list',
			'options' => $options,
			'desc'    => '',
		);

		// Field label
		$settings[] = array(
			'title'   => __( 'Field Label', 'woocommerce-delivery-time-slots' ),
			'id'      => WDTS_OPTION . '[label]',
			'type'    => 'text',
			'default' => __( 'Delivery Time', 'woocommerce-delivery-time-slots' ),
		);

		// Date format
		$desc       = array();
		$desc[]     = __( '<code>d</code>: Day of month (no leading zero)', 'woocommerce-delivery-time-slots' );
		$desc[]     = __( '<code>dd</code>: Day of month (two digit)', 'woocommerce-delivery-time-slots' );
		$desc[]     = __( '<code>m</code>: Month of year (no leading zero)', 'woocommerce-delivery-time-slots' );
		$desc[]     = __( '<code>mm</code>: Month of year (two digit)', 'woocommerce-delivery-time-slots' );
		$desc[]     = __( '<code>y</code>: Year (two digit)', 'woocommerce-delivery-time-slots' );
		$desc[]     = __( '<code>yy</code>: Year (four digit)', 'woocommerce-delivery-time-slots' );
		$desc[]     = $docs;
		$settings[] = array(
			'title'   => __( 'Date Format', 'woocommerce-delivery-time-slots' ),
			'id'      => WDTS_OPTION . '[date_format]',
			'type'    => 'text',
			'default' => 'mm/dd/yy',
			'desc'    => '<br>' . implode( '<br>', $desc ),
		);

		// Language
		$settings[] = array(
			'title' => __( 'Custom Close Button Text', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[custom_close_text]',
			'type'  => 'text',
		);
		$settings[] = array(
			'title' => __( 'Custom Prev Button Text', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[custom_prev_text]',
			'type'  => 'text',
		);
		$settings[] = array(
			'title' => __( 'Custom Next Button Text', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[custom_next_text]',
			'type'  => 'text',
		);
		$settings[] = array(
			'title' => __( 'Custom Today Button Text', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[custom_current_text]',
			'type'  => 'text',
		);
		$settings[] = array(
			'title' => __( 'Custom Month Names', 'woocommerce-delivery-time-slots' ),
			'desc'  => __( 'Separated by commas: January,February,March,April,May,June,July,August,September,October,November,December', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[custom_month_names]',
			'type'  => 'text',
		);
		$settings[] = array(
			'title' => __( 'Custom Day Names', 'woocommerce-delivery-time-slots' ),
			'desc'  => __( 'Separated by commas: Su,Mo,Tu,We,Th,Fr,Sa', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[custom_day_names]',
			'type'  => 'text',
		);

		// Restricted dates and date ranges
		$desc       = array();
		$desc[]     = __( 'Enter restricted <b>dates</b> or <b>date ranges</b>, <u>one rule per line</u>.', 'woocommerce-delivery-time-slots' );
		$desc[]     = __( 'Date format: <code>dd/mm/yyyy</code>', 'woocommerce-delivery-time-slots' );
		$desc[]     = __( 'Stars (<code>*</code>) can be used for day, month or year.', 'woocommerce-delivery-time-slots' );
		$desc[]     = __( 'Sample dates: <code>5/6/2013</code>, <code>*/5/2013</code>, <code>6/*/*</code>, <code>*/8/*</code>.', 'woocommerce-delivery-time-slots' );
		$desc[]     = __( 'Sample date ranges: <code>1/1/2018 - 5/1/2018</code>', 'woocommerce-delivery-time-slots' );
		$desc[]     = $docs;
		$settings[] = array(
			'title'             => __( 'Restricted Dates', 'woocommerce-delivery-time-slots' ),
			'desc'              => '<span class="description">' . implode( '<br>', $desc ) . '</span>',
			'id'                => WDTS_OPTION . '[restricted_dates]',
			'type'              => 'textarea',
			'class'             => 'widefat',
			'custom_attributes' => array(
				'rows' => 5,
			),
		);

		// Restricted week days
		$week_days  = array(
			0 => __( 'Sunday', 'woocommerce-delivery-time-slots' ),
			1 => __( 'Monday', 'woocommerce-delivery-time-slots' ),
			2 => __( 'Tuesday', 'woocommerce-delivery-time-slots' ),
			3 => __( 'Wednesday', 'woocommerce-delivery-time-slots' ),
			4 => __( 'Thursday', 'woocommerce-delivery-time-slots' ),
			5 => __( 'Friday', 'woocommerce-delivery-time-slots' ),
			6 => __( 'Saturday', 'woocommerce-delivery-time-slots' ),
		);
		$settings[] = array(
			'title'   => __( 'Restricted Week Days', 'woocommerce-delivery-time-slots' ),
			'id'      => WDTS_OPTION . '[restricted_week_days]',
			'type'    => 'checkbox_list',
			'options' => $week_days,
		);

		// Restricted sequence days
		$settings[] = array(
			'title'   => __( 'Disable shipping in the next', 'woocommerce-delivery-time-slots' ),
			'desc'    => __( 'days (0 = disable today, 1 = disable today and tomorrow, empty = no restriction)', 'woocommerce-delivery-time-slots' ),
			'id'      => WDTS_OPTION . '[restricted_sequence_days]',
			'default' => 0,
			'type'    => 'text',
		);

		// Allow to ship in sequence days
		$settings[] = array(
			'title' => __( 'Allow shipping only in the next', 'woocommerce-delivery-time-slots' ),
			'desc'  => __( 'days (0 = enable shipping for today, 5 = enable shipping for the next five days, empty = no restriction)', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[max_date]',
			'type'  => 'text',
		);

		// Show Timepicker
		$settings[] = array(
			'name' => __( 'Show Time Slots', 'woocommerce-delivery-time-slots' ),
			'id'   => WDTS_OPTION . '[show_timepicker]',
			'type' => 'custom_checkbox',
		);

		// Show Time slots
		$settings[] = array(
			'name' => __( 'Time Slots', 'woocommerce-delivery-time-slots' ),
			'id'   => WDTS_OPTION . '[time_slots]',
			'type' => 'time_slots',
			'desc' => __( 'Define your time slots (can be anything). Example: 8AM - 12AM, After 2PM, In the next day, etc.', 'woocommerce-delivery-time-slots' ),
		);

		// Number of shipments per day
		$settings[] = array(
			'title' => __( 'Max. Number Of Shipments Per Day', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[shipments_per_day]',
			'type'  => 'text',
		);
		// Message displayed when number of shipments exceeds plugin settings
		$settings[] = array(
			'title' => __( 'Notification For Exceeded Shipments', 'woocommerce-delivery-time-slots' ),
			'desc'  => __( 'Message displayed when number of shipments exceeds plugin setting above', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[notification_exceeded_shipments]',
			'type'  => 'text',
		);
		// Themes
		$settings[] = array(
			'title' => __( 'Themes', 'woocommerce-delivery-time-slots' ),
			'id'    => WDTS_OPTION . '[theme]',
			'type'  => 'images_select',
		);
		// Section end
		$settings[] = array(
			'type' => 'sectionend',
		);

		return $settings;
	}

	/**
	 * Update plugin option
	 */
	public function update() {
		if ( isset( $_POST[ WDTS_OPTION ] ) ) {
			update_option( WDTS_OPTION, $_POST[ WDTS_OPTION ] );
		}
	}
}
