<?php

/*
  Controller name: Vendors
  Controller description: Vendors Controller
*/

class JSON_API_Vendors_Controller {

	public function get_vendors() {
		$vendors      = [];
		$wcmp_vendors = get_wcmp_vendors();
		foreach ( $wcmp_vendors as $user ) {
			$wcmp_vendor = new WCMp_Vendor( $user->id );
			$vendor      = $this->assign_wcmp_vendor_to_api_vendor( $wcmp_vendor );
			$vendors[]   = $vendor;
		}
		return [ 'vendors' => $vendors ];
	}

	public function get_vendor_products() {
		global $json_api;
		$id = $json_api->query->id;
		if ( ! isset( $id ) ) {
			return [ 'message' => 'id parameter is required' ];
		}
		$wcmp_vendor          = new WCMp_Vendor( $id );
		$wcmp_vendor_products = $wcmp_vendor->get_products();
		$products             = $this->get_products_from_wcmp_vendor_products( $wcmp_vendor_products );
		add_filter( 'json_api_encode', 'products_array_only' );
		function products_array_only( $data ) {
			return $data['products'];
		}
		return [ 'products' => $products ];
	}

	public function assign_wcmp_vendor_to_api_vendor( $wcmp_vendor ) {
		$vendor              = new stdClass();
		$vendor->id          = $wcmp_vendor->id;
		$vendor->name        = $wcmp_vendor->user_data->data->user_nicename;
		$vendor->email       = $wcmp_vendor->user_data->data->user_email;
		$vendor->photo       = $wcmp_vendor->image;
		$vendor->cover       = $wcmp_vendor->banner;
		$vendor->description = $wcmp_vendor->description;
		return $vendor;
	}

	public function get_products_from_wcmp_vendor_products( $wcmp_vendor_products ) {
		$products = [];
		$invoker  = new ReflectionMethod( 'WC_REST_Products_Controller', 'get_product_data' );
		$invoker->setAccessible( true );
		foreach ( $wcmp_vendor_products as $wcmp_vendor_product ) {
			$products[] = $invoker->invoke( new WC_REST_Products_Controller(), new WC_Product( $wcmp_vendor_product->ID ) );
		}
		return $products;
	}

	public function special_vendors() {
		$special_vendors_post_id = get_posts( array( 'post_type' => 'special_vendors', 'posts_per_page' => -1, 'suppress_filters' => 0 ) )[0]->ID;
		$special_vendors_ids = get_post_meta( $special_vendors_post_id, 'special_vendors' );
		$vendors = [];
		foreach ($special_vendors_ids as $id){
			$wcmp_vendor = new WCMp_Vendor( $id );
			$vendor      = $this->assign_wcmp_vendor_to_api_vendor( $wcmp_vendor );
			$vendors[]   = $vendor;
		}
		return ['vendors'=>$vendors];
	}


}

 