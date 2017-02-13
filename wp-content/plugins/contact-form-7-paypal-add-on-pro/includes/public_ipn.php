<?php

// paypal post

add_action('template_redirect','cf7pp_ipn');
function cf7pp_ipn() {

    if (isset($_POST['mc_gross'])) {
		
		
		define("DEBUG", 0);
		define("LOG_FILE", "../logs/ipn.log");
		
		$options = get_option('cf7pp_options');
		foreach ($options as $k => $v ) { $value[$k] = $v; }
		
		if ($value['mode'] == "1") {
			$sandbox = "1";
		} else {
			$sandbox = "0";
		}
		
		$custom_back = explode("|", $_POST['custom']);
		
		$pid = $custom_back[0];
		$post_id = $custom_back[1];
		
		
		$sb_back = get_post_meta($pid, "_cf7pp_sandbox", true);
		
		if ($sb_back == "1") {
			$sandbox = "1";
		}
		
		
		if ($sandbox == "1") {
			define("USE_SANDBOX", 1);
		} else {
			define("USE_SANDBOX", 0);
		}
		
		
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
		foreach ($raw_post_array as $keyval) {
			$keyval = explode ('=', $keyval);
			if (count($keyval) == 2)
				$myPost[$keyval[0]] = urldecode($keyval[1]);
		}

		$req = 'cmd=_notify-validate';
		if(function_exists('get_magic_quotes_gpc')) {
			$get_magic_quotes_exists = true;
		}
		foreach ($myPost as $key => $value) {
			if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
				$value = urlencode(stripslashes($value));
			} else {
				$value = urlencode($value);
			}
			$req .= "&$key=$value";
		}

		if(USE_SANDBOX == true) {
			$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
		}

		$ch = curl_init($paypal_url);
		if ($ch == FALSE) {
			return FALSE;
		}

		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

		if(DEBUG == true) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		}

		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		
		$res = curl_exec($ch);
		if (curl_errno($ch) != 0) // cURL error
			{
			if(DEBUG == true) {	
				error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
			}
			curl_close($ch);
			exit;

		} else {
			if(DEBUG == true) {
				error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
				error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
			}
			curl_close($ch);
		}

		$tokens = explode("\r\n\r\n", trim($res));
		$res = trim(end($tokens));



		if (strcmp ($res, "VERIFIED") == 0) {
			
			$content = get_post($post_id);
			
			if (!empty($content)) {
				
				// content
				$array = $content->post_content;
				$string_back = unserialize(base64_decode($array));
				$values = array_values ($string_back);
				
				// mail 1
				$mail1 = $values[0];
				$result = WPCF7_Mail::send($mail1,'mail');
				
				// mail 2
				if (isset($values[1])) {
					$mail2 = $values[1];
					$result = WPCF7_Mail::send($mail2,'mail');
				}
				
				
				wp_delete_post($post_id,true);
				
				
				// delete uploaded files
				function rrmdir($dir) {
					if (is_dir($dir)) {
						$objects = scandir($dir); 
						foreach ($objects as $object) {
							if ($object != "." && $object != "..") {
								if (filetype($dir."/".$object) == "dir") rmdir($dir."/".$object); else unlink($dir."/".$object); 
							} 
						}
						reset($objects); 
						rmdir($dir); 
					} 
				}

				$upload_dir = wp_upload_dir();
				$basedir = $upload_dir['basedir'];
				$uploaddir = "/cf7pp_uploads";

				// mail 1 attachments
				$mail1_attachments = $values[0]['attachments'];
				$mail1_attachments = explode("\r\n",$mail1_attachments);
				$mail1_attachments = array_filter($mail1_attachments);

				foreach ($mail1_attachments as $mail1_key) {
					$mail1_key = explode("/",$mail1_key);
					$mail1_dir = $basedir.$uploaddir."/".$mail1_key[2];
					rrmdir($mail1_dir);
				}

				// mail 2 attachments
				$mail2_attachments = $values[0]['attachments2'];
				$mail2_attachments = explode("\r\n",$mail2_attachments);
				$mail2_attachments = array_filter($mail2_attachments);

				foreach ($mail2_attachments as $mail2_key) {
					$mail2_key = explode("/",$mail2_key);
					$mail2_dir = $basedir.$uploaddir."/".$mail2_key[2];
					rrmdir($mail2_dir);
				}

				do_action('cf7pp_payment_successful', $values[1]);
				
			}
		
		
	} else if (strcmp ($res, "INVALID") == 0) {
			//echo "IPN failed.";
		}
		
	}

}