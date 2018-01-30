<?php

/*
  Controller name: DeliveryTimeSlots
  Controller description: Delivery Time Slots Controller
*/

class JSON_API_Delivery_Time_Slots_Controller
{
	public function count_before_ordering_api( $order ){
		$date   = get_post_meta( $order->id, '_delivery_date', true );
		$date   = date( 'd-m-Y', intval($date) );
		$report = get_shipping_report();
		$num  = isset( $report[ $date ] ) ? absint( $report[ $date ] ) : 0;
		$num  += 1;
		$report[ $date ] = $num;
		$report = delete_past_dates( $report );
		update_shipping_report( $report );
	}

	public function available_days(){
		global $json_api;
		$days = $json_api->query->days;
		if (!isset($days)){
			return ['message'=>'days parameter is required','api_status'=>2];
		}
		$option = wdts_option();
		$restricted_dates = ($option['restricted_dates']) ? array_filter( array_map( 'trim', explode( "\n", $option['restricted_dates'] . "\n" ) ) ) : null;
		$restricted_week_days = ($option['restricted_week_days']) ? array_map( 'intval', $option['restricted_week_days'] ) : null;
		$min_date = $option['restricted_sequence_days'] == '' ? 0 : intval( $option['restricted_sequence_days'] ) + 1 ;
		$max_date =  $option['max_date'] == '' ? 0 : intval( $option['max_date'] ) + 1 ;
		$restrict_to_date = ($option['restricted_sequence_days']) ? date('m-d-Y',strtotime("+".$min_date." day")) : null ;
		$restricted_after_date = ($option['max_date']) ? date('m-d-Y',strtotime("+".$max_date." day")) : null ;
		$shipments_per_day = ($option['shipments_per_day']) ? intval( trim( $option['shipments_per_day'] ) ) : null ;
		$available_dates = get_nearest_available_days($days,$restricted_dates, $restricted_week_days, $restrict_to_date,$restricted_after_date,$shipments_per_day);
		$time_slots = $option['time_slots'];
     $time_slots = array_filter($time_slots);
		foreach ($time_slots as $key => $time_slot){
			 
				$obj2 = new stdClass();
				$obj2->time = $time_slot;
				
			$ret[] = $obj2;
			}
			
		if (count($available_dates) <= 0){
			return ['available_dates'=>[],'time_slots' => [],'api_status'=>1,'message'=>'success'];
		}
		else {
			return ['available_dates'=>$available_dates,'time_slots' => $ret,'api_status'=>1,'message'=>'success'];
		}
	}



}
 
 