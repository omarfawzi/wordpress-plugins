<?php
/**
 * Email Addresses
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     1.6.4
 */

?><table cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top;" border="0">

	<tr>

		<td valign="top" width="50%">

			<h3><?php _e('Billing address', 'woocommerce'); ?></h3>

			<p><?php echo $order->get_formatted_billing_address(); ?></p>

		</td>
		<?php
		$shipping = false;
		if ( function_exists( 'wc_ship_to_billing_address_only' ) )
		{
			if ( ! wc_ship_to_billing_address_only() )
			{
				$shipping = true;
			}
		}
		else
		{
			if ( get_option( 'woocommerce_ship_to_billing_address_only' ) == 'no' )
			{
				$shipping = true;
			}
		}
		?>
		<?php if ( $shipping ) : ?>

		<td valign="top" width="50%">

			<h3><?php _e('Shipping address', 'woocommerce'); ?></h3>

			<p><?php echo $order->get_formatted_shipping_address(); ?></p>

			<?php if ( get_post_meta( $order->id, '_delivery_date', true ) ) : ?>
				<?php
				$option = wdts_option();
				$label = $option['label'];
				?>
				<p><strong><?php echo $label; ?></strong> <?php echo do_shortcode( "[wdts_shipping_time id='" . $order->id . "']" ); ?></p>
			<?php endif; ?>
		</td>

		<?php endif; ?>

	</tr>

</table>
