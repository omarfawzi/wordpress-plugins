<?php
/**
 * Main plugin class
 * @package    WooCommerce
 * @subpackage WooCommerce Delivery Time Slots
 */

/**
 * This class adds date time picker for delivery when shipping
 * Delivery time will be save as a part of order and will be included in emails
 */
class WDTS_Frontend {
	/**
	 * Add hooks to WooCommerce
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Show and save delivery time field before order notes, e.g. after shipping address
		add_action( 'woocommerce_before_order_notes', array( $this, 'show_field' ), 20 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_field' ) );

		// Check if delivery time field is required
		add_filter( 'woocommerce_checkout_fields', array( $this, 'checkout_fields' ), 20, 1 );

		// Save delivery time in order detail
		add_action( 'save_post_shop_order', array( $this, 'save_field_in_order_detail' ) );

		// Show time delivery on order detail
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array(
			$this,
			'admin_show_field_in_order_detail'
		) );

		// Change email template to include Delivery Time
		add_filter( 'woocommerce_locate_template', array( $this, 'email_template' ), 10, 3 );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function enqueue() {
		$option = wdts_option();

		wp_enqueue_style( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/' . $option['theme'] . '/jquery-ui.min.css' );
		wp_enqueue_style( 'wdts', WDTS_URL . 'css/style.css' );
		wp_register_script( 'jquery-ui-datepicker-i18n', WDTS_URL . 'js/jquery-ui-i18n.min.js', array( 'jquery-ui-datepicker' ), '1.11.4', true );
		wp_enqueue_script( 'wdts', WDTS_URL . 'js/script.js', array( 'jquery-ui-datepicker-i18n' ), '1.0.2', true );

		// Get restricted dates
		$restricted_dates = array_filter( array_map( 'trim', explode( "\n", $option['restricted_dates'] . "\n" ) ) );

		// Convert week days from string to int
		$week_days = array_map( 'intval', $option['restricted_week_days'] );
		$report = get_shipping_report();
		$max   = intval( trim( $option['shipments_per_day'] ) );
		$restricted_shipping_dates = [];
		if ($max >= 1 && isset($report) ) {
			foreach ( $report as $date => $amount ) {
				if ( $amount >= $max ) {
					$restricted_shipping_dates[] = $date;
				}
			}
		}
		// Get object last date what cannot access and min time available
		$format_date = 'F j, Y';

		// Get restricted sequence days, value is empty enable current day, 0 = disable today, 1 = disable today and tomorrow ...
		$next = $option['restricted_sequence_days'] == '' ? 0 : intval( $option['restricted_sequence_days'] ) + 1;

		// Add time restricted sequence days
		$min_datetime = date( $format_date, strtotime( date( $format_date ) . ' + ' . $next . ' days' ) );

		$custom_date = array(
			'dayNamesMin' => empty( $option['custom_day_names'] ) ? '' : explode( ',', $option['custom_day_names'] ),
			'nextText'    => $option['custom_next_text'],
			'prevText'    => $option['custom_prev_text'],
			'closeText'   => $option['custom_close_text'],
			'currentText' => $option['custom_current_text'],
			'monthNames'  => empty( $option['custom_month_names'] ) ? '' : explode( ',', $option['custom_month_names'] )
		);
		$custom_date = array_filter( $custom_date );

		// Check is admin page so set flag for variable to doesn't get data from cookie
		if ( is_admin() ) {
			$is_admin_page = '0';
		} else {
			$is_admin_page = '1';
		}

		$locale       = str_replace( '_', '-', get_locale() );
		$locale_short = substr( $locale, 0, 2 );
		wp_localize_script( 'wdts', 'WDTS', array(
			'dateFormat'              => $option['date_format'] == '' ? 'mm/dd/yy' : $option['date_format'],
			'restrictedWeekDays'      => $week_days,
			'restrictedDates'         => $restricted_dates,
			'minDate'                 => $min_datetime,
			'maxDate'                 => $option['max_date'] == '' ? '+9999d' : "+{$option['max_date']}d",
			'ajaxUrl'                 => admin_url( 'admin-ajax.php' ),
			'showTimepicker'          => empty( $option['show_timepicker'] ) ? 0 : 1,
			'nonceCheckShipping'      => wp_create_nonce( 'check-shipping' ),
			'customDate'              => $custom_date,
			'isAdminPage'             => $is_admin_page,
			'disabledShippingMethods' => $option['disabled_shipping_methods'],
			'locale'                  => $locale,
			'localeShort'             => $locale_short,
            'restrictedShippingDates'  => $restricted_shipping_dates,
		) );
	}

	/**
	 * Show delivery time field before order notes, e.g. after shipping address
	 */
	public function show_field() {
		// Don't show delivery time field when shipping is disabled
		if ( ! WC()->cart->needs_shipping() ) {
			return;
		}

		$option = wdts_option();
		woocommerce_form_field(
			'delivery_date',
			array(
				'label'       => $option['label'],
				'placeholder' => $option['label'],
				'clear'       => true,
				'required'    => ! empty( $option['required'] ),
			),
			''
		);
		if ( $option['show_timepicker'] == '1' && ! empty( $option['time_slots'] ) ) {
			$slots = array();
			foreach ( $option['time_slots'] as $slot ) {
				$slots[ $slot ] = $slot;
			}
			woocommerce_form_field(
				'delivery_time',
				array(
					'label'       => '',
					'placeholder' => '',
					'type'        => 'select',
					'options'     => $slots,
				),
				''
			);
		}
	}

	/**
	 * Add fields to registered checkout fields to easily check if it's required
	 *
	 * @param  array $checkout_fields
	 *
	 * @return array
	 */
	public function checkout_fields( $checkout_fields ) {
		global $woocommerce;

		// Don't show delivery time field when shipping is disabled
		if ( ! $woocommerce->cart->needs_shipping() ) {
			return $checkout_fields;
		}

		$option = wdts_option();
		if ( empty( $option['required'] ) ) {
			return $checkout_fields;
		}

		// Custom field
		$checkout_fields['wdts'] = array(
			'delivery_date' => array(
				'label'       => $option['label'],
				'placeholder' => $option['label'],
				'required'    => ! empty( $option['required'] ),
			)
		);

		return $checkout_fields;
	}

	/**
	 * Save delivery time into order ( post ) meta
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public function save_field( $order_id ) {
		$option = wdts_option();
		$delivery_date = trim( filter_input( INPUT_POST, 'delivery_date', FILTER_SANITIZE_STRING ) );
		$delivery_time = trim( filter_input( INPUT_POST, 'delivery_time', FILTER_SANITIZE_STRING ) );
		if ( $delivery_date ) {
			update_post_meta( $order_id, '_delivery_date', wdts_to_timestamp( $_POST['delivery_date'], $option['show_timepicker'] ) );
		}
		if ( $delivery_time ) {
			update_post_meta( $order_id, '_delivery_time', sanitize_text_field( $_POST['delivery_time'] ) );
		}
	}

	/**
	 * Save delivery time in order detail ( post ) meta
	 *
	 * @param int $post_id
	 */
	public function save_field_in_order_detail( $post_id ) {
		$this->save_field( $post_id );
	}

	/**
	 * Show time delivery on order detail page in admin area
	 *
	 * @param object $order
	 */
	public function admin_show_field_in_order_detail( $order ) {
		if ( get_post_meta( $order->id, '_delivery_date', true ) ) {
			$option = wdts_option();
			$label  = $option['label'];
			$format = wdts_get_format_date_time( true );
			$date   = get_post_meta( $order->id, '_delivery_date', true );
			$date   = $date ? date( $format, (int) $date ) : '';
			$time   = get_post_meta( $order->id, '_delivery_time', true );
			?>
			<p>
				<strong><?php echo $label; ?></strong><br>
				<input type="text" id="delivery_date" name="delivery_date" value="<?php echo esc_attr( $date ); ?>">
				<br>
				@ <?php echo $time; ?>
			</p>
			<?php
		}
	}

	/**
	 * Change email template to include Delivery Time
	 *
	 * @param string $template Template path
	 * @param string $template_name Template name
	 * @param string $template_path Path to parent theme
	 *
	 * @return string
	 */
	public function email_template( $template, $template_name, $template_path ) {
		// We change only email-addresses.php template
		if ( 'emails/email-addresses.php' !== $template_name ) {
			return $template;
		}

		global $woocommerce;

		if ( ! $template_path ) {
			$template_path = $woocommerce->template_url;
		}

		// Look within passed path within the theme - this is priority
		$theme_template = locate_template( array(
			$template_path . $template_name,
			$template_name
		) );
		if ( $theme_template ) {
			return $theme_template;
		}

		// Get plugin template
		return WDTS_DIR . 'tpl/' . $template_name;
	}
}
