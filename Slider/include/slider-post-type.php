<?php
/* Slider Post Type */
if( !function_exists( 'create_slider_post_type' ) ){
    function create_slider_post_type(){

      $labels = array(
        'name' => __( 'Slider'),
        'singular_name' => __( 'Slider' ),
		'menu_name'           => __( 'Sliders'),
		'all_items'           => __( 'Sliders'),
        'add_new' => __('Add New'),
        'add_new_item' => __('Slider'),
        'edit_item' => __('Edit Slider'),
        'new_item' => __('New Slider'),
        'view_item' => __('View Slider'),
        'search_items' => __('Search Slider'),
        'not_found' =>  __('No Slider found'),
        'not_found_in_trash' => __('No Slider found in Trash'),
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
        'rewrite' => array( 'slug' => __('slider', 'framework') ),
		'menu_icon' => ''
      );
      register_post_type('slider',$args);
    }
}
add_action('init', 'create_slider_post_type');
add_action( 'add_meta_boxes', 'slider_type_add_post_meta_boxes' );
add_action( 'save_post', 'save_slider_type_meta'  );
add_action( 'add_meta_boxes', 'slider_type_link_add_post_meta_boxes' );
add_action( 'save_post', 'save_slider_type_link_meta'  );

function slider_type_add_post_meta_boxes(){
	add_meta_box(
		'slider_type',
		'Slider Type',
		'slider_type_meta',
		'slider',
		'normal',
		'default'
	);
}

function save_slider_type_meta($post_id){
	if ( ! isset( $_POST['post_type'] ) or $_POST['post_type'] !== 'slider' ) {
		return;
	}

	// Validate User
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Verify Nonce
	if ( ! isset( $_POST["_slider_nonce"] ) or ! wp_verify_nonce( $_POST["_slider_nonce"], plugin_basename( __FILE__ ) ) ) {
		return;
	}
	if ( isset( $_POST['slider_type'] ) ) {
		$slider_type = $_POST['slider_type'];
		update_post_meta( $post_id, 'slider_type', stripslashes( $slider_type ) );
	}
	
	
}
function slider_type_meta( $post ) {
	$type = get_post_meta($post->ID, 'slider_type', true );
	// Create Nonce Field
	wp_nonce_field( plugin_basename( __FILE__ ), '_slider_nonce' );
    ?>
    <div>
        <input type="radio" name="slider_type" value="slider_category" <?php
        if ($type == 'slider_category')
	        echo 'checked';
        ?>> Category
        <br>
        <br>
        <input type="radio" name="slider_type" value="slider_product" <?php
        if ($type == 'slider_product')
	        echo 'checked';
        ?>> Product
        <br>
        <br>
        <input type="radio" name="slider_type" value="slider_image" <?php
        if ($type == 'slider_image')
	        echo 'checked';
        ?>> Image
    </div>
	
	<?php
}


function slider_type_link_add_post_meta_boxes(){
	add_meta_box(
		'slider_type_link',
		'Attach To',
		'slider_type_link_meta',
		'slider',
		'normal',
		'default'
	);
}

function save_slider_type_link_meta($post_id){
	if ( ! isset( $_POST['post_type'] ) or $_POST['post_type'] !== 'slider' ) {
		return;
	}

	// Validate User
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Verify Nonce
	if ( ! isset( $_POST["_slider_nonce"] ) or ! wp_verify_nonce( $_POST["_slider_nonce"], plugin_basename( __FILE__ ) ) ) {
		return;
	}
	if ( isset( $_POST['slider_type'] ) && $_POST['slider_type'] == 'slider_category' && isset( $_POST['link_category'] ) ) {
		$slider_type_link = $_POST['link_category'];
		update_post_meta( $post_id, 'slider_type_link', stripslashes( $slider_type_link ) );
	}
	else if ( isset( $_POST['slider_type'] ) && $_POST['slider_type'] == 'slider_product' && isset( $_POST['link_product'] ) ) {
		$slider_type_link = $_POST['link_product'];
		update_post_meta( $post_id, 'slider_type_link', stripslashes( $slider_type_link ) );
	}

}

function slider_type_link_meta( $post ) {
	wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
	wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );

	$type = get_post_meta($post->ID, 'slider_type', true );
	$selected = get_post_meta( $post->ID, 'slider_type_link', true );
	$categories = get_categories(['taxonomy'=>'product_cat','hide_empty' => 0 ]);
	$products = get_posts(['post_type'=>'product','numberposts' => -1,'offset'=> -1]);
	// Create Nonce Field
	wp_nonce_field( plugin_basename( __FILE__ ), '_slider_nonce' );

	?>
    <style type="text/css">
        .select2-container {margin: 0 2px 0 2px;}
        .tablenav.top #doaction, #doaction2, #post-query-submit {margin: 0px 4px 0 4px;}
    </style>
    <select name="link_category" id="categories" style="width:300px">
        <?php
          foreach ($categories as $category) {
	          ?>
              <option value="<?php echo $category->term_id ?>" <?php
                 if ($type == 'slider_category' && $category->term_id == $selected )
                     echo 'selected';
              ?>
              >
		          <?php echo $category->name ?>
              </option>
	          <?php
          }
        ?>
    </select>

    <select name="link_product" id="products" style="width:300px">
		<?php
		foreach ($products as $product) {
			?>
            <option value="<?php echo $product->ID ?>"<?php
	            if ($type == 'slider_product' && $product->ID == $selected )
		            echo 'selected';
	            ?>
            >
				<?php echo $product->post_title ?>
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
            act($('input[name=slider_type]:checked').val());
            $('[name="slider_type"]').change(function () {
                var radio_val = $(this).val();
                act(radio_val);
            });

            function act(radio_val) {
                if (radio_val === 'slider_category'){
                    $('#products').next(".select2-container").hide();
                    $('#categories').next(".select2-container").show();
                    $('#slider_type_link').show();
                    var children = $('#slider_type_link').find('span');
                    children.eq(2).html('Select Category');

                }
                else if (radio_val === 'slider_product'){
                    $('#categories').next(".select2-container").hide();
                    $('#products').next(".select2-container").show();
                    $('#slider_type_link').show();
                    var children = $('#slider_type_link').find('span');
                    children.eq(2).html('Select Product');

                }
                else {
                    $('#products').hide();
                    $('#categories').hide();
                    $('#slider_type_link').hide();
                }
            }
        });


//        var type = $('input[name=slider_type]:checked').val();
    </script>

    <?php

}


function slider_admin_head(){
//Below css will add the menu icon for Roster Slider admin menu
?>
<style type="text/css">#adminmenu .menu-icon-slider div.wp-menu-image:before { content: "\f123"; }</style>
<?php
}
add_action('admin_head', 'slider_admin_head');

/* Add Custom Columns */
if( !function_exists( 'slider_edit_columns' ) ){
    function slider_edit_columns($columns)
    {

        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "title" => __( 'Title','framework' ),
            'type' => __('Type','framework'),
            'linked_to' => __('Linked With','framework')
		
        );

        return $columns;
    }
}
function add_slider_column_data($column_name,$id){
	$type = get_post_meta($id, 'slider_type', true );
	$linked = get_post_meta( $id, 'slider_type_link', true );
	switch ($column_name){
        case 'type':
	        if ($type=='slider_image'){
	            echo 'Image';
            }
            else if ($type == 'slider_product'){
	            echo 'Product';
            }
            else {
                echo 'Category';
            }
            break;
        case 'linked_to':
	        if ($type == 'slider_category'){
	            echo get_term($linked)->name;
            }
            else if ($type == 'slider_product'){
	            echo get_post($linked)->post_title;
            }
	        break;
        default:
            break;
    }
}
add_action('manage_slider_posts_custom_column', 'add_slider_column_data', 10, 2);

add_filter("manage_edit-slider_columns", "slider_edit_columns");
?>