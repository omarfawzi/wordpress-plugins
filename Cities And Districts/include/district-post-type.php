<?php
/* District Custom Post Type */
if( !function_exists( 'create_district_post_type' ) ){
    function create_district_post_type(){

      $labels = array(
        'name' => __( 'Districts'),
        'singular_name' => __( 'Districts' ),
        'add_new' => __('Add New'),
        'add_new_item' => __('Add New District'),
        'edit_item' => __('Edit District'),
        'new_item' => __('New District'),
        'view_item' => __('View District'),
        'search_items' => __('Search Districts'),
        'not_found' =>  __('No District found'),
        'not_found_in_trash' => __('No District found in Trash'),
        'parent_item_colon' => ''
      );

      $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
		'show_in_menu' => 'edit.php?post_type=cities',
        'query_var' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => 1,
        'exclude_from_search' => true,
        'supports' => array('title','thumbnail','editor'),
        'rewrite' => array( 'slug' => __('districts', 'framework') ),
		'menu_icon' => ''
      );

      register_post_type('districts',$args);
    }
}
add_action('init', 'create_district_post_type');

function district_admin_head(){
//Below css will add the menu icon for Roster Slider admin menu
?>
<style type="text/css">#adminmenu .menu-icon-districts div.wp-menu-image:before { content: "\f123"; }</style>
<?php
}
add_action('admin_head', 'district_admin_head');

add_action( 'add_meta_boxes', 'district_meta_box_add' );
function district_meta_box_add()
{
    add_meta_box( 'district-meta-box-id', 'Provide Related Information', 'district_meta_box_cb', 'districts', 'side', 'high' );
}

function district_meta_box_cb( $post )
{
    // $post is already set, and contains an object: the WordPress post
    global $post;
    $values = get_post_custom( $post->ID );
	//print_r( $values['district_meta_box_city']);exit;
    $selected = isset( $values['district_meta_box_city'] ) ?  $values['district_meta_box_city']: '';
     
    // We'll use this nonce field later on when saving.
    wp_nonce_field( 'district_meta_box_nonce', 'meta_box_nonce' );
	
	/* city */
	$city_array = array( "" => __('Select City','framework') );
	$city_posts = get_posts( array( 'post_type' => 'cities', 'posts_per_page' => -1, 'suppress_filters' => 0 ) );
	
	if(!empty($city_posts)){
		foreach( $city_posts as $city_post ){
			$city_array[$city_post->ID] =$city_post->post_title;
		}
	}
    ?>
    <p>
        <label for="district_meta_box_city"><strong>City: </strong></label></p>
        <select name="district_meta_box_city" id="district_meta_box_city" class="required" required title="Please Select City">
            <?php foreach($city_array as $key=>$val){?>
            <option value="<?php echo $key;?>" <?php selected( $selected[0], $key ); ?>><?php echo $val;?></option>
            <?php }?>
        </select>
    
    <?php   
}

add_action( 'save_post', 'district_meta_box_save' );

function district_meta_box_save( $post_id )
{
    // Bail if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
     
    // if our nonce isn't there, or we can't verify it, bail
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'district_meta_box_nonce' ) ) return;
     
    // if our current user can't edit this post, bail
    //if( !current_user_can( 'edit_post' ) ) return;
	
	// now we can actually save the data
    $allowed = array(
        'a' => array( // on allow a tags
            'href' => array() // and those anchors can only have href attribute
        )
    );
    // Make sure your data is set before trying to save it
     if( isset( $_POST['district_meta_box_city'] ) )
        update_post_meta( $post_id, 'district_meta_box_city',  $_POST['district_meta_box_city'] );
}


/* Add Custom Columns */
if( !function_exists( 'districts_edit_columns' ) ){
    function districts_edit_columns($columns)
    {

        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "title" => __( 'District Title','framework' ),
            "city" => __( 'City','framework' ),
			 "date" => __( 'Publish Time','framework' ),
            "cost" => __( 'Cost','framework' ),

        );

        return $columns;
    }
}
add_filter("manage_edit-districts_columns", "districts_edit_columns");

if( !function_exists( 'districts_custom_columns' ) ){
    function districts_custom_columns($column){
        global $post;
        switch ($column)
        {
            case 'city':
                $ID = get_post_meta($post->ID,'district_meta_box_city',true);
                echo get_the_title( $ID );
				/*if(!empty($address)){
                    echo $address;
                }
                else{
                    _e('No Address Provided!','framework');
                }*/
                break;
        }
    }
}
add_action("manage_posts_custom_column", "districts_custom_columns");

add_action( 'add_meta_boxes', 'districts_add_post_meta_boxes' );
add_action( 'save_post', 'save_districts_cost_meta'  );

function districts_add_post_meta_boxes(){
	add_meta_box(
		'districts_shipping_cost',
		'Add Shipping Cost',
		'districts_cost_meta',
		'districts',
		'normal',
		'default'
	);
}

function save_districts_cost_meta($post_id){
	if ( ! isset( $_POST['post_type'] ) or $_POST['post_type'] !== 'districts' ) {
		return;
	}

	// Validate User
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Verify Nonce
	if ( ! isset( $_POST["_districts_nonce"] ) or ! wp_verify_nonce( $_POST["_districts_nonce"], plugin_basename( __FILE__ ) ) ) {
		return;
	}
	if ( isset( $_POST['district_cost'] ) ) {
		$min_oos = $_POST['district_cost'];
		update_post_meta( $post_id, '_district_shipping_cost', stripslashes( $min_oos ) );
	}
}
function add_districts_column_data($column_name,$id){
	switch ($column_name){
		case 'cost':
			echo get_post_meta( $id, '_district_shipping_cost', true );
			break;
		default:
			break;
	}
}
add_action('manage_districts_posts_custom_column', 'add_districts_column_data', 10, 2);


function districts_cost_meta( $post ) {

	$cost = get_post_meta( $post->ID, '_district_shipping_cost', true );

	// Create Nonce Field
	wp_nonce_field( plugin_basename( __FILE__ ), '_districts_nonce' );

	?>
    <div>
        <label for="_district_shipping_cost">Shipping Cost</label>
        <input type="number" name="district_cost" value="<?php echo $cost ?>" step="any"/>
    </div>

	<?php
}

?>