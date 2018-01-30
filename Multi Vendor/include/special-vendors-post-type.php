<?php
/* Special Vendors Post Type */
if( !function_exists( 'create_special_vendors_post_type' ) ){
    function create_special_vendors_post_type(){

      $labels = array(
        'name' => __( 'Special Vendors'),
        'singular_name' => __( 'Special Vendor' ),
		'menu_name'           => __( 'Special Vendors'),
		'all_items'           => __( 'Special Vendors'),
        'add_new' => __('Add New'),
        'add_new_item' => __('Special Vendor'),
        'edit_item' => __('Edit Special Vendor'),
        'new_item' => __('New Special Vendor'),
        'view_item' => __('View Special Vendor'),
        'search_items' => __('Search Special Vendor'),
        'not_found' =>  __('No Special Vendor found'),
        'not_found_in_trash' => __('No Special Vendor found in Trash'),
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
        'supports' => array('title','thumbnail'),
        'rewrite' => array( 'slug' => __('special_vendors', 'framework') ),
		'menu_icon' => ''
      );
      register_post_type('special_vendors',$args);
    }
}
add_action('init', 'create_special_vendors_post_type');
add_action( 'add_meta_boxes', 'special_vendors_select_add_post_meta_boxes' );
add_action( 'save_post', 'save_special_vendors_select_meta'  );
add_action( 'save_post', 'delete_previous_special_vendors'  );


function delete_previous_special_vendors($post_id){

	if ( ! isset( $_POST['post_type'] ) or $_POST['post_type'] !== 'special_vendors' ) {
		return;
	}

	// Validate User
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Verify Nonce
	if ( ! isset( $_POST["_special_vendors_nonce"] ) or ! wp_verify_nonce( $_POST["_special_vendors_nonce"], plugin_basename( __FILE__ ) ) ) {
		return;
	}

	$posts = get_posts( [ 'post_type' => 'special_vendors' ] );
	foreach ( $posts as $post ) {
		if ( $post->ID != $post_id ) {
			wp_delete_post( $post->ID );
		}
	}
}

function special_vendors_select_add_post_meta_boxes(){
	add_meta_box(
		'special_vendors_select',
		'Choose',
		'special_vendors_select',
		'special_vendors',
		'normal',
		'default'
	);
}

function special_vendors_select( $post ) {
	wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
	wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );
    $selected = get_post_meta( $post->ID, 'special_vendors' );
	$vendors = get_wcmp_vendors();
	// Create Nonce Field
	wp_nonce_field( plugin_basename( __FILE__ ), '_special_vendors_nonce' );

	?>
    <style type="text/css">
        .select2-container {margin: 0 2px 0 2px;}
        .tablenav.top #doaction, #doaction2, #post-query-submit {margin: 0px 4px 0 4px;}
    </style>
    <select name="vendors[]" id="vendors" style="width:300px" multiple>
		<?php
		foreach ($vendors as $vendor) {
			?>
            <option value="<?php echo $vendor->id ?>" <?php
			if (in_array($vendor->id,$selected) )
				echo 'selected';
			?>
            >
				<?php echo $vendor->user_data->data->user_nicename ?>
            </option>
			<?php
		}
		?>
    </select>

    <script>
        jQuery(document).ready(function ($) {
            if( $( 'select' ).length > 0 ) {
                $( 'select' ).select2();
            }
        });

    </script>

	<?php

}

function save_special_vendors_select_meta($post_id){

	if ( ! isset( $_POST['post_type'] ) or $_POST['post_type'] !== 'special_vendors' ) {
		return;
	}

	// Validate User
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Verify Nonce
	if ( ! isset( $_POST["_special_vendors_nonce"] ) or ! wp_verify_nonce( $_POST["_special_vendors_nonce"], plugin_basename( __FILE__ ) ) ) {
		return;
	}

	if ( isset( $_POST['vendors'] ) ) {
	    $special_vendors = $_POST['vendors'];
	    delete_post_meta($post_id,'special_vendors');
	    foreach ($special_vendors as $special_vendor){
		    add_post_meta( $post_id, 'special_vendors', stripslashes( $special_vendor ) );
	    }
	}

}



function special_vendors_admin_head(){
//Below css will add the menu icon for Roster special_vendors admin menu
?>
<style type="text/css">#adminmenu .menu-icon-special_vendors div.wp-menu-image:before { content: "\f123"; }</style>
<?php
}
add_action('admin_head', 'special_vendors_admin_head');

/* Add Custom Columns */
if( !function_exists( 'special_vendors_edit_columns' ) ){
    function special_vendors_edit_columns($columns)
    {

        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "title" => __( 'Title','framework' ),
//            'type' => __('Type','framework'),
//            'linked_to' => __('Linked With','framework')
		
        );

        return $columns;
    }
}
function add_special_vendors_column_data($column_name,$id){
	$type = get_post_meta($id, 'special_vendors_type', true );
	$linked = get_post_meta( $id, 'special_vendors_type_link', true );
	switch ($column_name){
        case 'type':
	        if ($type=='special_vendors_image'){
	            echo 'Image';
            }
            else if ($type == 'special_vendors_product'){
	            echo 'Product';
            }
            else {
                echo 'Category';
            }
            break;
        case 'linked_to':
	        if ($type == 'special_vendors_category'){
	            echo get_term($linked)->name;
            }
            else if ($type == 'special_vendors_product'){
	            echo get_post($linked)->post_title;
            }
	        break;
        default:
            break;
    }
}
//add_action('manage_special_vendors_posts_custom_column', 'add_special_vendors_column_data', 10, 2);

add_filter("manage_edit-special_vendors_columns", "special_vendors_edit_columns");
?>