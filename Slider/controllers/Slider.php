<?php

/*
  Controller name: Slider
  Controller description: Slider Controller
*/ 

class JSON_API_Slider_Controller
{
	public function list_data(){
		$slider_posts = get_posts( array( 'post_type' => 'slider', 'posts_per_page' => -1, 'suppress_filters' => 0 ) );
		$sliders = [];
		foreach ($slider_posts as $slider_post){
			$obj = new stdClass();
			$id = get_post_meta($slider_post->ID,'slider_type_link',true);
			$type = get_post_meta($slider_post->ID,'slider_type',true);
			if ($type == 'slider_category'){
				$type = 3 ;
				$obj->name = get_the_category_by_ID($id);
			}
			else if ($type == 'slider_product'){
				$type = 2 ;
				$obj->name = get_post($id)->post_title;
			}
			else{
				$type = 1;
				$obj->name = '';
			}
			$obj->title = $slider_post->post_title;
			$obj->type = $type;
			if ($id)
				$obj->id = intval($id);
			$obj->id = $id;
			$obj->photo = get_the_post_thumbnail_url($slider_post);
			if (!$obj->photo)
				$obj->photo = '';
			if ($type == 2){
				$invoker = new ReflectionMethod('WC_REST_Products_Controller', 'get_product_data');
				$invoker->setAccessible(true);
				$product = $invoker->invoke(new WC_REST_Products_Controller(), new WC_Product($id));
				$keys = array_keys($product);
				foreach ($keys as $key){
					if (!isset($obj->$key)){
						$obj->$key = $product[$key];
					}
				}
			}
			$sliders[] = $obj;
		}

		return ['sliders'=>$sliders];
	}



}
 
 