<?php
/* city Custom Post Type */
if( !function_exists( 'create_city_post_type' ) ){
    function create_city_post_type(){

      $labels = array(
        'name' => __( 'Cities'),
        'singular_name' => __( 'Cities' ),
		'menu_name'           => __( 'Cities And Districts'),
		'all_items'           => __( 'Cities'),
        'add_new' => __('Add New'),
        'add_new_item' => __('Add New City'),
        'edit_item' => __('Edit City'),
        'new_item' => __('New City'),
        'view_item' => __('View City'),
        'search_items' => __('Search Cities'),
        'not_found' =>  __('No City found'),
        'not_found_in_trash' => __('No City found in Trash'),
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
        'supports' => array('title','thumbnail','editor'),
        'rewrite' => array( 'slug' => __('cities', 'framework') ),
		'menu_icon' => ''
      );
      register_post_type('cities',$args);
    }
}
add_action('init', 'create_city_post_type');
add_action( 'add_meta_boxes', 'cities_add_post_meta_boxes' );
add_action( 'save_post', 'save_cities_cost_meta'  );

function cities_add_post_meta_boxes(){
	add_meta_box(
		'cities_shipping_cost',
		'Add Shipping Cost',
		'cities_cost_meta',
		'cities',
		'normal',
		'default'
	);
}

function save_cities_cost_meta($post_id){
	if ( ! isset( $_POST['post_type'] ) or $_POST['post_type'] !== 'cities' ) {
		return;
	}

	// Validate User
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Verify Nonce
	if ( ! isset( $_POST["_cities_nonce"] ) or ! wp_verify_nonce( $_POST["_cities_nonce"], plugin_basename( __FILE__ ) ) ) {
		return;
	}
	if ( isset( $_POST['city_cost'] ) ) {
		$min_oos = $_POST['city_cost'];
		update_post_meta( $post_id, '_city_shipping_cost', stripslashes( $min_oos ) );
	}
}

function cities_cost_meta( $post ) {

	$cost = get_post_meta( $post->ID, '_city_shipping_cost', true );

	// Create Nonce Field
	wp_nonce_field( plugin_basename( __FILE__ ), '_cities_nonce' );

	?>
    <div>
        <label for="_city_shipping_cost">Shipping Cost</label>
        <input type="number" name="city_cost" value="<?php echo $cost ?>" step="any"/>
    </div>

	<?php
}
function city_admin_head(){
//Below css will add the menu icon for Roster Slider admin menu
?>
<style type="text/css">#adminmenu .menu-icon-cities div.wp-menu-image:before { content: "\f123"; }</style>
<?php
}
add_action('admin_head', 'city_admin_head');

/* Add Custom Columns */
if( !function_exists( 'cities_edit_columns' ) ){
    function cities_edit_columns($columns)
    {

        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "title" => __( 'City Title','framework' ),
			"date" => __( 'Publish Time','framework' ),
            "cost" => __( 'Cost','framework' ),
        );

        return $columns;
    }
}
function add_cities_column_data($column_name,$id){
    switch ($column_name){
        case 'cost':
	        echo get_post_meta( $id, '_city_shipping_cost', true );
	        break;
	    default:
		    break;
    }
}
add_action('manage_cities_posts_custom_column', 'add_cities_column_data', 10, 2);

add_filter("manage_edit-cities_columns", "cities_edit_columns");
?>