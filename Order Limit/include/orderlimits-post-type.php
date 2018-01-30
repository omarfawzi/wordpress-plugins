<?php
/* Order Limits Post Type */
if( !function_exists( 'create_orderlimits_post_type' ) ){
    function create_orderlimits_post_type(){

      $labels = array(
        'name' => __( 'Order Limits'),
        'singular_name' => __( 'Order Limit' ),
		'menu_name'           => __( 'Order Limits'),
		'all_items'           => __( 'Order Limits'),
        'add_new' => __('Add New'),
        'add_new_item' => __('Order Limit'),
        'edit_item' => __('Edit Order Limit'),
        'new_item' => __('New Order Limit'),
        'view_item' => __('View Order Limit'),
        'search_items' => __('Search Order Limits'),
        'not_found' =>  __('No Order Limit found'),
        'not_found_in_trash' => __('No Order Limit found in Trash'),
        'parent_item_colon' => ''
      );

      $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
		//'show_in_menu' => 'edit.php',
        'query_var' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        //'menu_position' => 1,
        'exclude_from_search' => true,
        'supports' => array('none'),
        'rewrite' => array( 'slug' => __('orderlimits', 'framework') ),
		'menu_icon' => ''
      );
      register_post_type('orderlimits',$args);
    }
    
}
add_action('init', 'create_orderlimits_post_type');
add_action( 'add_meta_boxes', 'orderlimits_free_shipping_add_post_meta_boxes' );
add_action( 'save_post', 'delete_before_save'  );

add_action( 'save_post', 'save_orderlimits_free_shipping_meta'  );


function delete_before_save($post_id){
    if ( ! isset( $_POST['post_type'] ) or $_POST['post_type'] !== 'orderlimits' ) {
        return;
    }
    
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

   $posts = get_posts( [ 'post_type' => 'orderlimits' ] );
   foreach ( $posts as $post ) {
        if ( $post->ID != $post_id ) {
            wp_delete_post( $post->ID );
        }
   }
}

function orderlimits_free_shipping_add_post_meta_boxes(){
	add_meta_box(
		'orderlimits_free_shipping',
		'Free Shipping',
		'orderlimits_free_shipping_meta',
		'orderlimits',
		'normal',
		'default'
	);
}

function save_orderlimits_free_shipping_meta($post_id){
	if ( ! isset( $_POST['post_type'] ) or $_POST['post_type'] !== 'orderlimits' ) {
		return;
	}

	// Validate User
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Verify Nonce
	if ( ! isset( $_POST["_orderlimits_nonce"] ) or ! wp_verify_nonce( $_POST["_orderlimits_nonce"], plugin_basename( __FILE__ ) ) ) {
		return;
	}
	if ( isset( $_POST['orderlimits_free_shipping'] ) ) {
		$free_shipping_limit = $_POST['orderlimits_free_shipping'];
		update_post_meta( $post_id, 'orderlimits_free_shipping', stripslashes( $free_shipping_limit ) );
	}
	
	if ( isset( $_POST['orderlimits_free_shipping_end_date'] ) ) {
		$orderlimits_free_shipping_end_date = $_POST['orderlimits_free_shipping_end_date'];
		update_post_meta( $post_id, 'orderlimits_free_shipping_end_date', stripslashes( $orderlimits_free_shipping_end_date ) );
	}
}

function orderlimits_free_shipping_meta( $post ) {

	$free_shipping_limit = get_post_meta( $post->ID, 'orderlimits_free_shipping', true );
	$orderlimits_free_shipping_end_date = get_post_meta( $post->ID, 'orderlimits_free_shipping_end_date', true );

	// Create Nonce Field
	wp_nonce_field( plugin_basename( __FILE__ ), '_orderlimits_nonce' );

	?>
    <div>
        <label for="orderlimits_free_shipping">Limit</label>
        <br>
        <input type="number" name="orderlimits_free_shipping" value="<?php echo $free_shipping_limit ?>" min="0" step="any"/>
    </div>
	
	<div>
        <label for="orderlimits_free_shipping_end_date">Delivery Date</label>
        <br>
        <input	name="orderlimits_free_shipping_end_date" value="<?php echo $orderlimits_free_shipping_end_date ?>"/>
    </div>
	
	
	<?php
}

add_action( 'add_meta_boxes', 'orderlimits_flat_rate_add_post_meta_boxes' );
add_action( 'save_post', 'save_orderlimits_flat_rate_meta'  );
function orderlimits_flat_rate_add_post_meta_boxes(){
	add_meta_box(
		'orderlimits_flat_rate',
		'Flat Rate',
		'orderlimits_flat_rate_meta',
		'orderlimits',
		'normal',
		'default'
	);
}

function save_orderlimits_flat_rate_meta($post_id){
	if ( ! isset( $_POST['post_type'] ) or $_POST['post_type'] !== 'orderlimits' ) {
		return;
	}

	// Validate User
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Verify Nonce
	if ( ! isset( $_POST["_orderlimits_nonce"] ) or ! wp_verify_nonce( $_POST["_orderlimits_nonce"], plugin_basename( __FILE__ ) ) ) {
		return;
	}

	if ( isset( $_POST['orderlimits_flat_rate'] ) ) {
		$flat_rate_limit = $_POST['orderlimits_flat_rate'];

		update_post_meta( $post_id, 'orderlimits_flat_rate', stripslashes( $flat_rate_limit ) );
	}

	if ( isset( $_POST['orderlimits_flat_rate_end_date'] ) ) {
		$orderlimits_flat_rate_end_date = $_POST['orderlimits_flat_rate_end_date'];
		update_post_meta( $post_id, 'orderlimits_flat_rate_end_date', stripslashes( $orderlimits_flat_rate_end_date ) );
	}
}

function orderlimits_flat_rate_meta( $post ) {

	$flat_rate_limit = get_post_meta( $post->ID, 'orderlimits_flat_rate', true );
	$orderlimits_flat_rate_end_date = get_post_meta( $post->ID, 'orderlimits_flat_rate_end_date', true );

	// Create Nonce Field
	wp_nonce_field( plugin_basename( __FILE__ ), '_orderlimits_nonce' );

	?>
    <div>
        <label for="orderlimits_flat_rate">Limit</label>
        <br>
        <input type="number" name="orderlimits_flat_rate" value="<?php echo $flat_rate_limit ?>" min="0" step="any"/>
    </div>

    <div>
        <label for="orderlimits_flat_rate_end_date">Delivery Date</label>
        <br>
        <input	name="orderlimits_flat_rate_end_date" value="<?php echo $orderlimits_flat_rate_end_date ?>"/>
    </div>


	<?php
}

function orderlimits_admin_head(){
//Below css will add the menu icon for Roster Slider admin menu
?>
<style type="text/css">#adminmenu .menu-icon-orderlimits div.wp-menu-image:before { content: "\f123"; }</style>
<?php
}
add_action('admin_head', 'orderlimits_admin_head');

/* Add Custom Columns */
if( !function_exists( 'orderlimits_edit_columns' ) ){
    function orderlimits_edit_columns($columns)
    {

        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "title" => __( 'LIMIT','framework' ),
			"free_shipping" => __( 'Free Shipping Limit','framework' ),
            "free_shipping_end_date" => __( 'Free Shipping Delivery Date','framework' ),
            "flat_rate" => __( 'Flat Rate Limit','framework' ),
            "flat_rate_end_date" => __( 'Flat Rate Delivery Date','framework' )
        );

        return $columns;
    }
}
function add_orderlimits_column_data($column_name,$id){
    switch ($column_name){
        case 'title':
            echo 'a';
            break;
        case 'flat_rate':
	        echo get_post_meta( $id, 'orderlimits_flat_rate', true );
	        break;
        case 'free_shipping':
	        echo get_post_meta( $id, 'orderlimits_free_shipping', true );
	        break;
        case 'free_shipping_end_date':
	        echo get_post_meta( $id, 'orderlimits_free_shipping_end_date', true );
	        break;
	    case 'flat_rate_end_date':
		    echo get_post_meta( $id, 'orderlimits_flat_rate_end_date', true );
		    break;
	    default:
		    break;
    }
}
add_action('manage_orderlimits_posts_custom_column', 'add_orderlimits_column_data', 10, 2);

add_filter("manage_edit-orderlimits_columns", "orderlimits_edit_columns");


?>