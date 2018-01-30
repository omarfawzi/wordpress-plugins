<?php

/*
  Controller name: MobileValidation
  Controller description: Mobile Validation Controller
*/

class JSON_API_Mobile_Validation_Controller
{

    public function send_otp(){
	    global $json_api;
	    $mobile_number = $json_api->query->mobile_number;
	    $country_code = $json_api->query->country_code;
	    if (!isValidMobile($mobile_number))
	    	$message = 'mobile number is invalid';
	    else
	    	$message = self::send_sms_to_phone($country_code,$mobile_number);
	    if ($message == 'success'){
	    	$status = 1 ;
	    }
	    else {
	    	$status = 2 ;
	    }
    	return ['message'=>$message,'api_status'=>$status];
    }

	public function send_sms_to_phone($countrycode,$mobileno){
    	$error_msg = 'success';
		$digit_tapp = get_option('digit_tapp',1);
		if($digit_tapp==1) return 'error';
		else if(!checkwhitelistcode($countrycode)) {
			$error_msg = 'country is not in whitelist';
		}
		else {
			$user = getUserFromPhone( $countrycode . $mobileno );
			if ( $user != null ) {
				$error_msg = 'user already exists';
			} else {
				$dig_otp_size = get_option( "dig_otp_size", 4 );
				$code = "";
				for ( $i = 0; $i < $dig_otp_size; $i ++ ) {
					$code .= rand( 0, 9 );
				}
				if ( ! digit_send_otp( $countrycode, $mobileno, $code ) ) {
					$error_msg = 'error occured while sending';
				}
				else {
					$mobileVerificationCode = md5( $code );
					global $wpdb;
					$table_name = $wpdb->prefix . "digits_mobile_otp";

					$wpdb->replace( $table_name, array(
						'countrycode' => $countrycode,
						'mobileno'    => $mobileno,
						'otp'         => $mobileVerificationCode,
						'time'        => current_time( 'mysql' )
					), array( '%d', '%d', '%s', '%s' )
					);
				}
			}
		}
		return $error_msg;
	}

	public function verify_otp(){
		global $json_api;
		$mobile_number = $json_api->query->mobile_number;
		$country_code = $json_api->query->country_code;
		$otp = $json_api->query->otp;
		$ret = verifyOTP($country_code,$mobile_number,$otp,true);
		if ($ret){
			$status = 1 ;
			$ret = 'success';
		}
		else {
			$status = 2 ;
			$ret = 'failure';
		}
		return ['message'=>$ret,'api_status'=>$status];
	}

}
 
 