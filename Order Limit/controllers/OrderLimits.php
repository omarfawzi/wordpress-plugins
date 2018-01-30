<?php

/*
  Controller name: OrderLimits
  Controller description: Order Limits Controller
*/ 

class JSON_API_Order_Limits_Controller
{
	public function list_data(){
		$orderlimits_ID = get_posts( array( 'post_type' => 'orderlimits', 'posts_per_page' => -1, 'suppress_filters' => 0 ) )[0]->ID;
		$obj = new stdClass();
		$obj->limit = get_post_meta($orderlimits_ID,'orderlimits_free_shipping',true);
		$obj->delivery_date = get_post_meta($orderlimits_ID,'orderlimits_free_shipping_end_date',true);
		$orderlimits['free_shipping'] = $obj;
		$obj = new stdClass();
		$obj->limit = get_post_meta($orderlimits_ID,'orderlimits_flat_rate',true);
		$obj->delivery_date = get_post_meta($orderlimits_ID,'orderlimits_flat_rate_end_date',true);
		$orderlimits['flat_rate'] = $obj;
        return ['data'=>$orderlimits];
        }




}
 
 