<?php
/**
 * Add custom field types for WooCommerce settings page.
 *
 * @package WooCommerce Delivery Time Slots
 */

/**
 * Fields class.
 */
class WDTS_Fields {
	/**
	 * Class constructor
	 * Add hooks to Woocommerce
	 */
	public function register() {
		$fields = array( 'checkbox_list', 'images_select', 'custom_checkbox', 'time_slots' );
		foreach ( $fields as $field ) {
			add_action( "woocommerce_admin_field_$field", array( $this, $field ) );
		}
	}

	/**
	 * Callback function to show checkbox list field in settings page
	 *
	 * @param array $value
	 */
	public function checkbox_list( $value ) {
		$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
		if ( ! is_array( $option_value ) ) {
			$option_value = array();
		}
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<?php
				$html = array();
				foreach ( $value['options'] as $k => $v ) {
					$html[] = sprintf(
						'<label><input type="checkbox" name="%s[]" value="%s"%s> %s</label>',
						esc_attr( $value['id'] ),
						$k,
						checked( in_array( $k, $option_value ), 1, false ),
						$v
					);
				}
				echo implode( '<br>', $html );
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Callback function to show checkbox list field in settings page
	 *
	 * @param array $value
	 */
	public function images_select( $value ) {
		$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<?php
				$html   = array();
				$themes = array(
					'black-tie.png'      => 'Black Tie',
					'blitzer.png'        => 'Blitzer',
					'cupertino.png'      => 'Cupertino',
					'dark-hive.png'      => 'Dark Hive',
					'dot-luv.png'        => 'Dot Luv',
					'eggplant.png'       => 'Eggplant',
					'excite-bike.png'    => 'Excite Bike',
					'flick.png'          => 'Flick',
					'hot-sneaks.png'     => 'Hot Sneaks',
					'humanity.png'       => 'Humanity',
					'le-frog.png'        => 'Le Frog',
					'mint-choc.png'      => 'Mint Choc',
					'overcast.png'       => 'Overcast',
					'pepper-grinder.png' => 'Pepper Grinder',
					'smoothness.png'     => 'Smoothness',
					'south-street.png'   => 'South Street',
					'start.png'          => 'Start',
					'sunny.png'          => 'Sunny',
					'swanky-purse.png'   => 'Swanky Purse',
					'trontastic.png'     => 'Trontastic',
					'ui-darkness.png'    => 'UI darkness',
					'ui-lightness.gif'   => 'UI lightness',
				);
				foreach ( $themes as $k => $v ) {
					$theme  = substr( $k, 0, strpos( $k, '.' ) );
					$html[] = sprintf(
						'<label>
								<img src="%s"><br>
								<input type="radio" name="%s" value="%s" %s> %s
							</label>',
						WDTS_URL . 'themes/' . $k,
						esc_attr( $value['id'] ),
						$theme,
						checked( $theme, $option_value, false ),
						$v
					);
				}
				echo implode( '', $html );
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Callback function to show checkbox list field in settings page
	 *
	 * @param array $value
	 */
	public function custom_checkbox( $value ) {
		$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label
					for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<?php
				printf(
					'<label><input type="checkbox" name="%s" id="%s" value="1" %s> %s</label>',
					esc_attr( $value['id'] ),
					esc_attr( $value['id'] ),
					checked( $option_value, 1, false ),
					$value['desc']
				);
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Callback function to show checkbox list field in settings page
	 *
	 * @param array $value
	 */
	public function time_slots( $value ) {
		$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );
		$option_value = empty( $option_value ) ? array( '' ) : $option_value;
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label
					for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="time-slots-container forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<?php
				$count_slot = 0;
				while ( $count_slot < count( $option_value ) ) {
					?>
					<div class="wdts-slot">
						<?php
						if ( $count_slot == 0 ) {
							printf(
								'<input type="text" name="%s[]" value="%s"> <button class="button-primary wdts-add-slot">%s</button> <i class="dashicons dashicons-minus wdts-remove-slot hidden"></i>',
								esc_attr( $value['id'] ),
								esc_attr( $option_value[ $count_slot ] ),
								esc_attr__( '+ Add more', 'woocommerce-delivery-time-slots' )
							);
						} else {
							printf(
								'<input type="text" name="%s[]" value="%s"> <i class="dashicons dashicons-minus wdts-remove-slot"></i>',
								esc_attr( $value['id'] ),
								esc_attr( $option_value[ $count_slot ] )
							);
						}
						$count_slot ++;
						?>
					</div>
					<?php
				}
				?>
				<span class="description"><?php echo $value['desc']; ?></span>
			</td>
		</tr>
		<?php
	}
}