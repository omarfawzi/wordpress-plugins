<?php

/*
  Controller name: CitiesAndDistricts
  Controller description: Cities And Districts Controller
*/

class JSON_API_Cities_And_Districts_Controller
{
	public function list_data(){
		$cities = get_posts( array( 'post_type' => 'cities', 'posts_per_page' => -1, 'suppress_filters' => 0 ) );
		$ret = [];
		foreach ($cities as $city){
			$obj = new stdClass();
			$obj->name = $city->post_title;
			$obj->price =  get_post_meta( $city->ID, '_city_shipping_cost', true );
			$districts = get_posts(['post_type'=>'districts','meta_key'=>'district_meta_box_city','meta_value'=>$city->ID]);
			$temp = [];
			foreach ($districts as $district){
				$obj2 = new stdClass();
				$obj2->name = $district->post_title;
				$obj2->price = get_post_meta($district->ID,'_district_shipping_cost', true );
				$temp[] = $obj2;
			}
			$obj->regions = $temp;
			$ret[] = $obj;
		}
		return ['data'=>$ret];
	}




}
 
 