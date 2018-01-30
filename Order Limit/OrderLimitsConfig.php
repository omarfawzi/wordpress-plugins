<?php
defined('ABSPATH') or die("No script kiddies please!");
/**
Plugin Name: Order Limits
 *
/*-----------------------------------------------------------------------------------*/
/*	Include Custom Post Types
/*-----------------------------------------------------------------------------------*/


require_once ( plugin_dir_path( __FILE__ ) . '/include/orderlimits-post-type.php' );
//
//function hide_city_add_new_custom_type()
//{
//    global $submenu;
//    // replace my_type with the name of your post type
//    unset($submenu['edit.php?post_type=orderlimits'][10]);
//}
//
//
//register_activation_hook( __FILE__, 'csc_activation_function' );
//function csc_activation_function() {
//	// Do stuff here
//}


add_filter('json_api_controllers', 'registerOrderLimitsControllers');
add_filter('json_api_order_limits_controller_path', 'setOrderLimitsControllerPath');

function registerOrderLimitsControllers($aControllers)
{
	$aControllers[] = 'Order_Limits';
	return $aControllers;
}

function setOrderLimitsControllerPath($sDefaultPath)
{
	return dirname(__FILE__) . '/controllers/OrderLimits.php';
}

add_action( 'woocommerce_after_cart_totals', 'my_function_with_wc_functions' );    
function my_function_with_wc_functions(){
    $orderlimits_ID = get_posts( array( 'post_type' => 'orderlimits', 'posts_per_page' => -1, 'suppress_filters' => 0 ) )[0]->ID;
    $flat_rate_limit = get_post_meta( $orderlimits_ID, 'orderlimits_flat_rate', true );
    $free_shipping_limit = get_post_meta( $orderlimits_ID, 'orderlimits_free_shipping', true );

    ?>
 
    <script>
    jQuery(document).ready(function($){
       var subtotal = $('[data-title="Subtotal"]').find('span.amount')[0].lastChild.data;
       var free_shipping = parseInt('<?php echo $free_shipping_limit  ?>');
       var flat_rate = parseInt('<?php echo $flat_rate_limit  ?>');
       if (subtotal < flat_rate){
           $('#shipping_method_0_flat_rate1').parent().hide();
       }
       if (subtotal < free_shipping){
           $('#shipping_method_0_free_shipping2').parent().hide();
       }
    //   $('#shipping_method_0_free_shipping2').hide();
       $( document.body ).on( 'updated_cart_totals', function() {
          subtotal = $('[data-title="Subtotal"]').find('span.amount')[0].lastChild.data;
          if (subtotal < flat_rate){
              $('#shipping_method_0_flat_rate1').parent().hide();
          }
          else {
              $('#shipping_method_0_flat_rate1').parent().show();
          }
          if (subtotal < free_shipping){
             $('#shipping_method_0_free_shipping2').parent().hide();
          }
          else {
             $('#shipping_method_0_free_shipping2').parent().show();
          }
        //   $('#shipping_method_0_flat_rate1').parent().hide();
         } );
    });
   
    </script>

     <?php
   
}

?>