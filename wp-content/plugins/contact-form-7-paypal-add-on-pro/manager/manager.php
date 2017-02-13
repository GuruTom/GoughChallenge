<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('PLICENSE_PRODUCT_NAME', 'cf7pp');

if (!class_exists('cf7pp_PLicense_Activation')) {
    class cf7pp_PLicense_Activation {
        
        function is_plicense_valid() {
            $plicense_key = get_option('plicsence_key'.PLICENSE_PRODUCT_NAME);
            if (!$plicense_key) return false;

            if (false === ($value = get_transient('plicense_state'))) {
                if ($this->cf7pp_plicense_valid_check($plicense_key)) {
                    set_transient('plicense_state', 1, 30 * MINUTE_IN_SECONDS);
                    return true;
                }
            }
            else return true;

            return false;
        }

    }
}

if(isset($_POST['cf7pp_plicsence_key'])) {
global $cf7pp_PLicense_Activation;
$license = $_POST['cf7pp_plicsence_key'];


$license = trim($license);


$product = "Contact Form 7 - PayPal Add-on";

$serverurl = "http://104.236.184.140/wpplugin/wp-content/plugins/product-licensing-system/verify.php";

$response = wp_remote_post($serverurl, array(
		'method' => 'POST',
		'timeout' => 20,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'sslverify' => false,
		'body' => array( 'domain' => $_SERVER['SERVER_NAME'], 'licensekey' => $license, 'product' => $product ),
		'cookies' => array()
	)
);

if (is_wp_error($response))
   $response = '';

$result = json_decode($response['body'], true);

//avaiable 'valid' value is true, false, pending, expired
update_option('cf7pp_plicsence_key_status', $result['valid']);
update_option('cf7pp_plicsence_key'.PLICENSE_PRODUCT_NAME, $license);

if($result['valid'] == "true")
	return "1";
else {
	delete_transient('plicense_state');
}
return "2";
}

?>