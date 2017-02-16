<?php
// activate

if(isset($_POST['cf7pp_license_activate'])) {

	// retrieve the license from the database
	$license = $_POST['key'];
	
	$license = str_replace(' ', '',$license);
	
	// data to send in our API request
	$api_params = array(
		'edd_action'=> 'activate_license',
		'license' 	=> $license,
		'item_name' => urlencode( WPPlUGIN_PRODUCT_NAME ), // the name of our product in EDD
		'url'       => home_url()
	);
	
	// Call the custom API.
	$response = wp_remote_post( WPPlUGIN_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
	
	//echo "<pre>";
	//print_r($response);
	//exit;
	
	
	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
	update_option( 'cf7pp_plicsence_keycf7pp', $_POST['key'] );
	update_option( 'cf7pp_plicsence_key_status', $license_data->license );
	update_option( 'cf7pp_license_expires', $license_data->expires );

}


// deactivate

if(isset($_POST['cf7pp_license_deactivate'])) {

	// retrieve the license from the database
	$license = trim( get_option( 'cf7pp_plicsence_keycf7pp' ) );

	// data to send in our API request
	$api_params = array(
		'edd_action'=> 'deactivate_license',
		'license' 	=> $license,
		'item_name' => urlencode( WPPlUGIN_PRODUCT_NAME ), // the name of our product in EDD
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( WPPlUGIN_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
	
	// make sure the response came back okay
	if ( is_wp_error( $response ) ) {
		return false;
	}

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
	// $license_data->license will be either "deactivated" or "failed"
	if($license_data->license == 'deactivated' || $license_data->license == 'failed') {
		delete_option('cf7pp_plicsence_key_status');
		delete_option('cf7pp_license_expires');
	}

}