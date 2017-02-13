<?php

/*
Plugin Name: Contact Form 7 - PayPal Add-on - Pro
Plugin URI: https://wpplugin.org/paypal/
Description: Integrates PayPal with Contact Form 7
Author: Scott Paterson
Author URI: https://wpplugin.org
Version: 1.7.3
*/


/* 
Copyright 2014-2015 Scott Paterson
This is not free software.
You do not have permission to distribute this software under any circumstances.
You may modify this software (excluding the license and update manager) for personal use only if you hold a valid license key.
*/



// plugin variable: cf7pp



//  plugin functions
register_activation_hook( __FILE__, "cf7pp_activate" );
register_deactivation_hook( __FILE__, "cf7pp_deactivate" );
register_uninstall_hook( __FILE__, "cf7pp_uninstall" );

function cf7pp_activate() {
	
	// write initical options
	$cf7pp_options = array(
		'currency'			=> '25',
		'language'			=> '3',
		'liveaccount'		=> '',
		'sandboxaccount'	=> '',
		'mode'				=> '2',
		'cancel'			=> '',
		'return'			=> '',
		'redirect'			=> '1',
		'tax'				=> '',
		'tax_rate'			=> ''
	);

	add_option("cf7pp_options", $cf7pp_options);
	
}


function cf7pp_deactivate() {

	delete_option("cf7pp_my_plugin_notice_shown");

}

function cf7pp_uninstall() {
}




// update to new version

$cf7pp_options_version = get_option('cf7pp_options_version');
if (empty($cf7pp_options_version) || !isset($cf7pp_options_version)) {
	// version 1.7 details have not been loaded yet
	
	
	// mark db as having version number
	$cf7pp_options_version = array(
		'version'			=> '1.7.1',
	);
	add_option("cf7pp_options_version", $cf7pp_options_version);


	// set new variables used in this version
	$options = get_option('cf7pp_options');
	foreach ($options as $k => $v ) { $value[$k] = $v; }
	$options['redirect'] = "1";
	update_option("cf7pp_options", $options);
	
	
	// remove old redirect method code
	function wp_config_delete( $slash = '' ) {
		$config = file_get_contents (ABSPATH . "wp-config.php");
		$config = preg_replace ("/( ?)(define)( ?)(\()( ?)(['\"])WPCF7_LOAD_JS(['\"])( ?)(,)( ?)(0|1|true|false)( ?)(\))( ?);/i", "", $config);
		file_put_contents (ABSPATH . $slash . "wp-config.php", $config);
	} if (file_exists (ABSPATH . "wp-config.php") && is_writable (ABSPATH . "wp-config.php")) {
		wp_config_delete();
	} else if (file_exists (dirname (ABSPATH) . "/wp-config.php") && is_writable (dirname (ABSPATH) . "/wp-config.php")) {
		wp_config_delete('/');
	} else if (file_exists (ABSPATH . "wp-config.php") && !is_writable (ABSPATH . "wp-config.php")) {
		function my_admin_notice() {
			?>
			<div class="updated">
				<p><?php _e( 'Error removing', 'my-text-domain' ); ?></p>
			</div>
			<?php
		}
		add_action( 'admin_notices', 'my_admin_notice' );
	} else if (file_exists (dirname (ABSPATH) . "/wp-config.php") && !is_writable (dirname (ABSPATH) . "/wp-config.php")) {	
		function my_admin_notice() {
			?>
			<div class="updated">
				<p><?php _e( 'Error removing', 'my-text-domain' ); ?></p>
			</div>
			<?php
		}
		add_action( 'admin_notices', 'my_admin_notice' );	
	} else {
		function my_admin_notice() {
			?>
			<div class="updated">
				<p><?php _e( 'Error removing', 'my-text-domain' ); ?></p>
			</div>
			<?php
		}
		add_action( 'admin_notices', 'my_admin_notice' );	
	}
	
}





// display activation notice
add_action('admin_notices', 'cf7pp_my_plugin_admin_notices');

function cf7pp_my_plugin_admin_notices() {
	if (!get_option('cf7pp_my_plugin_notice_shown')) {
		echo "<div class='updated'><p><a href='admin.php?page=cf7pp_admin_table'>Click here to view the plugin settings</a>.</p></div>";
		update_option("cf7pp_my_plugin_notice_shown", "true");
	}
}


// updater
require 'updater/plugin-update-checker.php';
$MyUpdateChecker = PucFactory::buildUpdateChecker(
'https://wpplugin.org/updates-server/?action=get_metadata&slug=contact-form-7-paypal-add-on-pro',
__FILE__,
'contact-form-7-paypal-add-on-pro'
);



// for redirect method 2
$options = get_option('cf7pp_options');
foreach ($options as $k => $v ) { $value[$k] = $v; }
if ($value['redirect'] == "2") {
	define('WPCF7_LOAD_JS', false);
}


// check to make sure contact form 7 is installed and active
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {

	// add paypal menu under contact form 7 menu
	add_action( 'admin_menu', 'cf7pp_admin_menu', 20 );

	function cf7pp_admin_menu() {
		$addnew = add_submenu_page( 'wpcf7',
			__( 'PayPal Settings', 'contact-form-7' ),
			__( 'PayPal Settings', 'contact-form-7' ),
			'wpcf7_edit_contact_forms', 'cf7pp_admin_table',
			'cf7pp_admin_table' );
	}
	
	// PayPal IPN	
	include_once ('includes/public_ipn.php');
	

	// hook into contact form 7 - before send for redirect method 1 and method 2
	add_action('wpcf7_before_send_mail', 'cf7pp_before_send_mail');

	function cf7pp_before_send_mail( $cf7 ) {
		
		global $current_user;
		global $cf7;		
		
		$wpcf7 = WPCF7_ContactForm::get_current();
		
		// need to save submission for later and the variables get lost in the cf7 javascript redirect
		$submission_orig = WPCF7_Submission::get_instance();
		
		if ($submission_orig) {
			// get form post id
			$posted_data = $submission_orig->get_posted_data();
			$postid = $posted_data['_wpcf7'];
			$email = get_post_meta($postid, "_cf7pp_email", true);
			
			// file upload handling
			
			// get files
			$find = "wpcf7_uploads";
			$uploaddir = "/cf7pp_uploads";
			$upload_dir = wp_upload_dir();
			$basedir = $upload_dir['basedir'];
			$array = $submission_orig;
			$test = serialize($array);
			$length = strlen($find);
			$count = substr_count($test,$find);
			//$count = $count - 2;
			$files_arr = (array) null;
			$files_order = (array) null;
			
			function get_string_between($string, $start, $end) {
				$string = " ".$string;
				$ini = strpos($string,$start);
				if ($ini == 0) return "";
				$ini += strlen($start);
				$len = strpos($string,$end,$ini) - $ini;
				return substr($string,$ini,$len);
			}
			
			// finding file item names and order
			for ($i=0; $i<$count; $i++) {
				$result = get_string_between($test,'uploaded_files', ';}');
				$files_order = explode('"',$result);
			}
			
			$files_order = array_filter($files_order);
			function myFilter($string) { return strpos($string, ':') === false;	}
			$files_order = array_filter($files_order, 'myFilter');
			function myFiltera($string) { return strpos($string, '/') === false; }
			$files_order = array_filter($files_order, 'myFiltera');
			$files_order = array_values($files_order);
			
			// finding file paths
			for ($i=0; $i<$count; $i++) {
				$result = $basedir."/".$find.$parsed = get_string_between($test,$find, '"');
				$test = substr($test, strpos($test,$find) + $length);
				$parsed_folder = explode("/",$parsed);
				mkdir($basedir.$uploaddir."/".$parsed_folder['1'], 0777, true);
				copy($result,$basedir.$uploaddir.$parsed);
				array_push($files_arr,$parsed);
			}
			
			
			include_once ('includes/private_mail_tags.php');
			
			
			$options = get_option('cf7pp_options');
			foreach ($options as $k => $v ) { $value[$k] = $v; }
			
			// for redirect method 1
			if ($value['redirect'] == "1") {			
				$site_url = get_site_url();
				$path = $site_url.'/?cf7pp_redirect='.$new_post_id.'&orig='.$postid.'&tags='.$tags_id;
				$wpcf7->set_properties(array('additional_settings' => "on_sent_ok: \"location.replace('".$path."');\"",));
			}
			
			// do not send the email depending on settings
			if  ($email == "1" || $email == "3") {
				$wpcf7->skip_mail = true;
			}
			
		}
		
	}
	
	// hook into contact form 7 - after send
	add_action('template_redirect','cf7pp_redirect');

	function cf7pp_redirect() {
		if (isset($_GET['cf7pp_redirect'])) {
			
			// get the id from the cf7pp_before_send_mail function theme redirect
			$input_id = $_GET['cf7pp_redirect'];
			$postid = $_GET['orig'];
			$tagsid = $_GET['tags'];
			
			$enable = get_post_meta( $postid, "_cf7pp_enable", true);
			if ($enable == "1") {
				include_once ('includes/public_redirect.php');
				exit;
			}
			
		}
	}
	
	
	// hook into contact form 7 - after send
	add_action('wpcf7_mail_sent', 'cf7pp_after_send_mail');

	function cf7pp_after_send_mail( $contact_form ) {
		
		global $postid,$new_post_id,$tags_id;
		$input_id = $new_post_id;
		$submission = WPCF7_Submission::get_instance();
		$wpcf7 = WPCF7_ContactForm::get_current();
		
		if ($submission) {
		
			// for redirect method 2
			$options = get_option('cf7pp_options');
			foreach ($options as $k => $v ) { $value[$k] = $v; }
			if ($value['redirect'] == "2") {
				$posted_data = $submission->get_posted_data();
				$postid = $posted_data['_wpcf7'];
				$enable = get_post_meta( $postid, "_cf7pp_enable", true);
				$tagsid = $tags_id;
				if ($enable == "1") {
					include_once ('includes/public_redirect.php');
					exit;
				}
			}
			
		}
		
	}
	
	
	
	
	
	// clean up files and posts that are over 24 hours old - if the customer went to paypal but never paid then files will be left over

	// delete cf7pp posts older then one day
	$args = array(
		'numberposts' => 50
		,'post_type' =>'cf7pp'
	);
	$posts = get_posts( $args );
	if (is_array($posts)) {
		$yesterday = date("Y-m-d H:i:s",strtotime("-1 day"));
		foreach ($posts as $post) {
			if ($post->post_date < $yesterday) {
				wp_delete_post( $post->ID, true);
			}
		}
	}
	
	// delete uploaded files older then one day
	function rrmdir_oldfiles($dir) {
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
	
	// make uploads directory
	$upload_dir = wp_upload_dir();
	$basedir = $upload_dir['basedir'];
	$uploaddir = "/cf7pp_uploads";
	
	// mkdir if !isset
	$uploaddir = "/cf7pp_uploads";
	if (!file_exists($basedir.$uploaddir)) {
		mkdir($basedir.$uploaddir, 0777, true);
	}
	
	$dirs = scandir($basedir.$uploaddir);
	$dirs = array_diff($dirs, array('.', '..'));
	$dirs = array_values($dirs);
	
	foreach ($dirs as $dir_key) {
		$dir_remove = $basedir.$uploaddir."/".$dir_key;
		$age = filemtime($dir_remove."/.");
		if (time() - 86400 > $age) {
			rrmdir_oldfiles($dir_remove);
		}
	}
	// end files clean up
	
	
	// hook into contact form 7 form
	function cf7pp_editor_panels ( $panels ) {
		
		$new_page = array(
			'PayPal' => array(
				'title' => __( 'PayPal', 'contact-form-7' ),
				'callback' => 'cf7pp_admin_after_additional_settings'
			)
		);
		
		$panels = array_merge($panels, $new_page);
		
		return $panels;
		
	}
	add_filter( 'wpcf7_editor_panels', 'cf7pp_editor_panels' );
	
	function cf7pp_admin_after_additional_settings( $cf7 ) {
		
		$post_id = sanitize_text_field($_GET['post']);
		
		$enable = get_post_meta($post_id, "_cf7pp_enable", true);
		$name = get_post_meta($post_id, "_cf7pp_name", true);
		$desc = get_post_meta($post_id, "_cf7pp_desc", true);
		$price = get_post_meta($post_id, "_cf7pp_price", true);
		$id = get_post_meta($post_id, "_cf7pp_id", true);
		$email = get_post_meta($post_id, "_cf7pp_email", true);
		$quantity = get_post_meta($post_id, "_cf7pp_quantity", true);
		$shipping = get_post_meta($post_id, "_cf7pp_shipping", true);
		$sandbox = get_post_meta($post_id, "_cf7pp_sandbox", true);
		$note = get_post_meta($post_id, "_cf7pp_note", true);
		$form_account = get_post_meta($post_id, "_cf7pp_form_account", true);
		$cancel = get_post_meta($post_id, "_cf7pp_cancel", true);
		$return = get_post_meta($post_id, "_cf7pp_return", true);
		$quantity_menu = get_post_meta($post_id, "_cf7pp_quantity_menu", true);
		$price_menu = get_post_meta($post_id, "_cf7pp_price_menu", true);
		$text_menu_a = get_post_meta($post_id, "_cf7pp_text_menu_a", true);
		$text_menu_a_name = get_post_meta($post_id, "_cf7pp_text_menu_a_name", true);
		$text_menu_b = get_post_meta($post_id, "_cf7pp_text_menu_b", true);
		$text_menu_b_name = get_post_meta($post_id, "_cf7pp_text_menu_b_name", true);
		
		if ($enable == "1") { $checked_enable = "CHECKED"; } else { $checked_enable = ""; }
		if ($sandbox == "1") { $checked_sandbox = "CHECKED"; } else { $checked_sandbox = ""; }
		
		if ($email == "1" || $email == "2" || $email == "3") {
			if ($email == "2") { $before = "SELECTED"; $after = ""; $never = ""; }
			if ($email == "1") { $after = "SELECTED"; $before = ""; $never = ""; } 
			if ($email == "3") { $never = "SELECTED"; $before = ""; $after = ""; }
		} else {
			$before = ""; $after = ""; $never = "";
		}
		
		if ($note == "1") { $checkednote = "CHECKED"; } else { $checkednote = ""; }
		
		$admin_table_output = "";
		$admin_table_output .= "<form>";
		$admin_table_output .= "<div id='additional_settings-sortables' class='meta-box-sortables ui-sortable'><div id='additionalsettingsdiv' class='postbox'>";
		$admin_table_output .= "<div class='handlediv' title='Click to toggle'><br></div><h3 class='hndle ui-sortable-handle'><span>PayPal Settings</span></h3>";
		$admin_table_output .= "<div class='inside'>";
		
		$admin_table_output .= "<table><tr><td>";
		$admin_table_output .= "<label>Enable PayPal on this form</label>: ";
		$admin_table_output .= "<input name='enable' value='1' type='checkbox' $checked_enable></td></tr><tr><td>";
		
		$admin_table_output .= "<label>Sandbox Mode<label>: ";
		$admin_table_output .= "<input name='sandbox' value='1' type='checkbox' $checked_sandbox> </td><td>(Optional, will override settings page value. Check to enable sandbox mode.</td></tr><tr><td>";
		
		$admin_table_output .= "Send Contact Form 7 Email: </td></tr><tr><td>";
		$admin_table_output .= "<select name='email'><option value='2' $before>Before redirecting to PayPal</option><option value='1' $after>After payment is successful</option><option value='3' $never>Never send email</option></select></td></tr><tr><td>";
		
		$admin_table_output .= "<br /><br />";
		$admin_table_output .= "Item Description: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='name' value='$name'> </td><td> (Optional, if left blank customer will be able to enter their own description at checkout.)</td></tr><tr><td>";
		
		$admin_table_output .= "Item Description Code: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='desc' value='$desc'> </td><td> (Optional, will override Item Description above. Link a form item to the description by entering item code. Example: text-530)</td></tr><tr><td>";
		
		$admin_table_output .= "Item Price: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='price' value='$price'> </td><td> (Optional, if left blank customer will be able to enter their own price at checkout. Format: enter 2.99 for $2.99.)</td></tr><tr><td>";
		
		$admin_table_output .= "Item ID / SKU: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='id' value='$id'> </td><td> (Optional - Example: enter 123.22 for an SKU/ID of 123.22.)</td></tr><tr><td>";
		
		$admin_table_output .= "Quantity: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='quantity' value='$quantity'> </td><td> (Optional - Example: enter 2 for a quantity of 2.)</td></tr><tr><td>";
		
		$admin_table_output .= "Fixed Shipping: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='shipping' value='$shipping'> </td><td> (Optional - Example: enter 2.25 for $2.25 shipping. Setup more advanced shipping profiles <a target='_blank' href='https://www.paypal.com/cgi-bin/customerprofileweb?cmd=_profile-shipping'>here</a>.)</td></tr><tr><td>";
        
		$admin_table_output .= "Hide Note: </td></tr><tr><td>";
		$admin_table_output .= "<input type='checkbox' name='note' value='1' $checkednote> </td><td>  (Optional - if checked, the field on PayPal where customers can enter a custom note will be hidden.)</td></tr><tr><td>";
		
		$admin_table_output .= "PayPal Account: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='form_account' value='$form_account'> </td><td>  (Optional - will override PayPal account on settings page, but only for this form.)</td></tr><tr><td>";
		
		$admin_table_output .= "Cancel URL: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='cancel' value='$cancel'> </td><td> (Optional - Overrides settings page value. If the customer goes to PayPal and clicks the cancel button, where do they go.)</td></tr><tr><td>";
		
		$admin_table_output .= "Return URL: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='return' value='$return'> </td><td> (Optional - Overrides settings page value. If the customer goes to PayPal and successfully pays, where are they redirected to.)</td></tr><tr><td>";
		
		$admin_table_output .= "Quantity Code: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='quantity_menu' value='$quantity_menu'> </td><td> (Optional - Link number form item to quantity by entering item code. Example: menu-292  Documentation: <a target='_blank' href='https://wpplugin.org/documentation/?document=2848'>here</a>)</td></tr><tr><td>";
		
		$admin_table_output .= "Price Code: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='price_menu' value='$price_menu'> </td><td> (Optional - Link number form item to price by entering item code. Example: menu-244 Documentation: <a target='_blank' href='https://wpplugin.org/documentation/?document=2823'>here</a>)</td></tr><tr><td>";
		
		$admin_table_output .= "Options - Text 1 Name / Code: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='text_menu_a_name' value='$text_menu_a_name'><input type='text' name='text_menu_a' value='$text_menu_a'> </td><td> (Optional - Link text or number form item to text field 1 by entering item code. Example: Color / text-530 Documentation: <a target='_blank' href='https://wpplugin.org/documentation/?document=2860'>here</a>)</td></tr><tr><td>";
		
		$admin_table_output .= "Options - Test 2 Name / Code: </td></tr><tr><td>";
		$admin_table_output .= "<input type='text' name='text_menu_b_name' value='$text_menu_b_name'><input type='text' name='text_menu_b' value='$text_menu_b'> </td><td valign='top'> (Optional - Link text or number form item to text field 2 by entering item code. Example: Email / email-100 Documentation: <a target='_blank' href='https://wpplugin.org/documentation/?document=2860'>here</a>)</td></tr><tr><td>";
		
		$admin_table_output .= "<input type='hidden' name='post' value='$post_id'>";
		
		$admin_table_output .= "</td></tr></table></form>";
		$admin_table_output .= "</div>";
		$admin_table_output .= "</div>";
		$admin_table_output .= "</div>";
		
		echo $admin_table_output;
	}
	add_action('wpcf7_admin_after_additional_settings', 'cf7pp_admin_after_additional_settings');
	
	
	// hook into contact form 7 admin form save
	add_action('wpcf7_save_contact_form', 'cf7pp_save_contact_form');

	function cf7pp_save_contact_form( $cf7 ) {
		
			$post_id = sanitize_text_field($_POST['post']);
			
			if (!empty($_POST['enable'])) {
				$enable = sanitize_text_field($_POST['enable']);
				update_post_meta($post_id, "_cf7pp_enable", $enable);
			} else {
				update_post_meta($post_id, "_cf7pp_enable", 0);
			}
			
			if (!empty($_POST['sandbox'])) {
				$sandbox = sanitize_text_field($_POST['sandbox']);
				update_post_meta($post_id, "_cf7pp_sandbox", $sandbox);
			} else {
				update_post_meta($post_id, "_cf7pp_sandbox", 0);
			}
			
			$name = sanitize_text_field($_POST['name']);
			update_post_meta($post_id, "_cf7pp_name", $name);
			
			$desc = sanitize_text_field($_POST['desc']);
			update_post_meta($post_id, "_cf7pp_desc", $desc);
			
			$price = sanitize_text_field($_POST['price']);
			update_post_meta($post_id, "_cf7pp_price", $price);
			
			$id = sanitize_text_field($_POST['id']);
			update_post_meta($post_id, "_cf7pp_id", $id);
			
			$email = sanitize_text_field($_POST['email']);
			update_post_meta($post_id, "_cf7pp_email", $email);
			
			$quantity = sanitize_text_field($_POST['quantity']);
			update_post_meta($post_id, "_cf7pp_quantity", $quantity);
			
			$shipping = sanitize_text_field($_POST['shipping']);
			update_post_meta($post_id, "_cf7pp_shipping", $shipping);
			
			if (!empty($_POST['note'])) {
				$note = sanitize_text_field($_POST['note']);
				update_post_meta($post_id, "_cf7pp_note", $note);
			} else {
				update_post_meta($post_id, "_cf7pp_note", 0);
			}
			
            $form_account = sanitize_text_field($_POST['form_account']);
			update_post_meta($post_id, "_cf7pp_form_account", $form_account);
        
			$cancel = sanitize_text_field($_POST['cancel']);
			update_post_meta($post_id, "_cf7pp_cancel", $cancel);
			
			$return = sanitize_text_field($_POST['return']);
			update_post_meta($post_id, "_cf7pp_return", $return);
			
			$quantity_menu = sanitize_text_field($_POST['quantity_menu']);
			update_post_meta($post_id, "_cf7pp_quantity_menu", $quantity_menu);
			
			$price_menu = sanitize_text_field($_POST['price_menu']);
			update_post_meta($post_id, "_cf7pp_price_menu", $price_menu);
			
			$text_menu_a = sanitize_text_field($_POST['text_menu_a']);
			update_post_meta($post_id, "_cf7pp_text_menu_a", $text_menu_a);
			
			$text_menu_a_name = sanitize_text_field($_POST['text_menu_a_name']);
			update_post_meta($post_id, "_cf7pp_text_menu_a_name", $text_menu_a_name);
			
			$text_menu_b = sanitize_text_field($_POST['text_menu_b']);
			update_post_meta($post_id, "_cf7pp_text_menu_b", $text_menu_b);
			
			$text_menu_b_name = sanitize_text_field($_POST['text_menu_b_name']);
			update_post_meta($post_id, "_cf7pp_text_menu_b_name", $text_menu_b_name);
	}

	
	
	
	// admin table
	function cf7pp_admin_table() {
	
	// manager
	include_once ('manager/manager.php');
	$kstatus = get_option('cf7pp_plicsence_key_status');
	if (empty($kstatus)) { $kstatus = "false"; }
	
	if ($kstatus == "true") {

		?>
		
		<form method='post' action='<?php $_SERVER["REQUEST_URI"]; ?>'>
		
		
		<?php
		// save and update options
		if (isset($_POST['update'])) {
		
		
			$options['currency'] = sanitize_text_field($_POST['currency']);
			$options['language'] = sanitize_text_field($_POST['language']);
			$options['liveaccount'] = sanitize_text_field($_POST['liveaccount']);
			$options['sandboxaccount'] = sanitize_text_field($_POST['sandboxaccount']);
			$options['mode'] = sanitize_text_field($_POST['mode']);
			$options['cancel'] = sanitize_text_field($_POST['cancel']);
			$options['return'] = sanitize_text_field($_POST['return']);
			$options['tax'] = sanitize_text_field($_POST['tax']);
			$options['tax_rate'] = sanitize_text_field($_POST['tax_rate']);
			$options['redirect'] = sanitize_text_field($_POST['redirect']);
			
			update_option("cf7pp_options", $options);
			
			echo "<br /><div class='updated'><p><strong>"; _e("Settings Updated."); echo "</strong></p></div>";
			
		}
		
		
		$options = get_option('cf7pp_options');
		foreach ($options as $k => $v ) { $value[$k] = $v; }
		
		$siteurl = get_site_url();
		
		?>
		
		
		<div style='width: 80%;'>
			
		<table width='100%'><tr><td>
		<div class='wrap'><h2>Contact Form 7 - PayPal Settings</h2></div><br /></td><td><br />
		<input type='submit' name='btn2' class='button-primary' style='font-size: 17px;line-height: 28px;height: 32px;float: right;' value='Save Settings'>
		</td></tr></table>
			
			
			<div style="background-color:#333333;padding:8px;color:#eee;font-size:12pt;font-weight:bold;">
			&nbsp; Usage
			</div><div style="background-color:#fff;border: 1px solid #E5E5E5;padding:5px;"><br />
			
				On this page, you can setup your general PayPal and other settings which will be used for all contact forms.
				
				<br /><br />
				
				If you go to your list of contact forms and choose one, you will see will see a tab labeled 'PayPal'. Here you can 
				setup settings for that specific contact form.
				
				<br /><br />
				
				The documentation for this plugin can be found <a target='_blank' href='https://wpplugin.org/documentation'>here</a>.
				
				<br /><br />
			
			</div><br /><br />
			
			
			<div style="background-color:#333333;padding:8px;color:#eee;font-size:12pt;font-weight:bold;">
			&nbsp; Language & Currency
			</div><div style="background-color:#fff;border: 1px solid #E5E5E5;padding:5px;"><br />
			
				<b>Language:</b>
				<select name="language">
				<option <?php if ($value['language'] == "1") { echo "SELECTED"; } ?> value="1">Danish</option>
				<option <?php if ($value['language'] == "2") { echo "SELECTED"; } ?> value="2">Dutch</option>
				<option <?php if ($value['language'] == "3") { echo "SELECTED"; } ?> value="3">English</option>
				<option <?php if ($value['language'] == "4") { echo "SELECTED"; } ?> value="4">French</option>
				<option <?php if ($value['language'] == "5") { echo "SELECTED"; } ?> value="5">German</option>
				<option <?php if ($value['language'] == "6") { echo "SELECTED"; } ?> value="6">Hebrew</option>
				<option <?php if ($value['language'] == "7") { echo "SELECTED"; } ?> value="7">Italian</option>
				<option <?php if ($value['language'] == "8") { echo "SELECTED"; } ?> value="8">Japanese</option>
				<option <?php if ($value['language'] == "9") { echo "SELECTED"; } ?> value="9">Norwgian</option>
				<option <?php if ($value['language'] == "10") { echo "SELECTED"; } ?> value="10">Polish</option>
				<option <?php if ($value['language'] == "11") { echo "SELECTED"; } ?> value="11">Portuguese</option>
				<option <?php if ($value['language'] == "12") { echo "SELECTED"; } ?> value="12">Russian</option>
				<option <?php if ($value['language'] == "13") { echo "SELECTED"; } ?> value="13">Spanish</option>
				<option <?php if ($value['language'] == "14") { echo "SELECTED"; } ?> value="14">Swedish</option>
				<option <?php if ($value['language'] == "15") { echo "SELECTED"; } ?> value="15">Simplified Chinese -China only</option>
				<option <?php if ($value['language'] == "16") { echo "SELECTED"; } ?> value="16">Traditional Chinese - Hong Kong only</option>
				<option <?php if ($value['language'] == "17") { echo "SELECTED"; } ?> value="17">Traditional Chinese - Taiwan only</option>
				<option <?php if ($value['language'] == "18") { echo "SELECTED"; } ?> value="18">Turkish</option>
				<option <?php if ($value['language'] == "19") { echo "SELECTED"; } ?> value="19">Thai</option>
				</select>
				
				PayPal currently supports 18 languages.
				<br /><br />
				
				<b>Currency:</b> 
				<select name="currency">
				<option <?php if ($value['currency'] == "1") { echo "SELECTED"; } ?> value="1">Australian Dollar - AUD</option>
				<option <?php if ($value['currency'] == "2") { echo "SELECTED"; } ?> value="2">Brazilian Real - BRL</option> 
				<option <?php if ($value['currency'] == "3") { echo "SELECTED"; } ?> value="3">Canadian Dollar - CAD</option>
				<option <?php if ($value['currency'] == "4") { echo "SELECTED"; } ?> value="4">Czech Koruna - CZK</option>
				<option <?php if ($value['currency'] == "5") { echo "SELECTED"; } ?> value="5">Danish Krone - DKK</option>
				<option <?php if ($value['currency'] == "6") { echo "SELECTED"; } ?> value="6">Euro - EUR</option>
				<option <?php if ($value['currency'] == "7") { echo "SELECTED"; } ?> value="7">Hong Kong Dollar - HKD</option> 	 
				<option <?php if ($value['currency'] == "8") { echo "SELECTED"; } ?> value="8">Hungarian Forint - HUF</option>
				<option <?php if ($value['currency'] == "9") { echo "SELECTED"; } ?> value="9">Israeli New Sheqel - ILS</option>
				<option <?php if ($value['currency'] == "10") { echo "SELECTED"; } ?> value="10">Japanese Yen - JPY</option>
				<option <?php if ($value['currency'] == "11") { echo "SELECTED"; } ?> value="11">Malaysian Ringgit - MYR</option>
				<option <?php if ($value['currency'] == "12") { echo "SELECTED"; } ?> value="12">Mexican Peso - MXN</option>
				<option <?php if ($value['currency'] == "13") { echo "SELECTED"; } ?> value="13">Norwegian Krone - NOK</option>
				<option <?php if ($value['currency'] == "14") { echo "SELECTED"; } ?> value="14">New Zealand Dollar - NZD</option>
				<option <?php if ($value['currency'] == "15") { echo "SELECTED"; } ?> value="15">Philippine Peso - PHP</option>
				<option <?php if ($value['currency'] == "16") { echo "SELECTED"; } ?> value="16">Polish Zloty - PLN</option>
				<option <?php if ($value['currency'] == "17") { echo "SELECTED"; } ?> value="17">Pound Sterling - GBP</option>
				<option <?php if ($value['currency'] == "18") { echo "SELECTED"; } ?> value="18">Russian Ruble - RUB</option>
				<option <?php if ($value['currency'] == "19") { echo "SELECTED"; } ?> value="19">Singapore Dollar - SGD</option>
				<option <?php if ($value['currency'] == "20") { echo "SELECTED"; } ?> value="20">Swedish Krona - SEK</option>
				<option <?php if ($value['currency'] == "21") { echo "SELECTED"; } ?> value="21">Swiss Franc - CHF</option>
				<option <?php if ($value['currency'] == "22") { echo "SELECTED"; } ?> value="22">Taiwan New Dollar - TWD</option>
				<option <?php if ($value['currency'] == "23") { echo "SELECTED"; } ?> value="23">Thai Baht - THB</option>
				<option <?php if ($value['currency'] == "24") { echo "SELECTED"; } ?> value="24">Turkish Lira - TRY</option>
				<option <?php if ($value['currency'] == "25") { echo "SELECTED"; } ?> value="25">U.S. Dollar - USD</option>
				</select>
				PayPal currently supports 25 currencies.
				<br /><br /></div>
				
				
				<br /><br /><div style="background-color:#333333;padding:8px;color:#eee;font-size:12pt;font-weight:bold;">
				&nbsp; PayPal Account </div><div style="background-color:#fff;border: 1px solid #E5E5E5;padding:5px;"><br />
				
				
				<b>Live Account: </b><input type='text' name='liveaccount' value='<?php echo $value['liveaccount']; ?>'> Required
				<br />Enter a valid Merchant account ID (strongly recommend) or PayPal account email address. All payments will go to this account.
				<br /><br />You can find your Merchant account ID in your PayPal account under Profile -> My business info -> Merchant account ID
				
				<br /><br />If you don't have a PayPal account, you can sign up for free at <a target='_blank' href='https://paypal.com'>PayPal</a>. <br /><br />
				
				<b>Sandbox Account: </b><input type='text' name='sandboxaccount' value='<?php echo $value['sandboxaccount']; ?>'> Optional
				<br />Enter a valid sandbox PayPal account email address. A Sandbox account is a PayPal accont with fake money used for testing. This is useful to make sure your PayPal account and settings are working properly being going live.
				<br /><br />To create a Sandbox account, you first need a Developer Account. You can sign up for free at the <a target='_blank' href='https://www.paypal.com/webapps/merchantboarding/webflow/unifiedflow?execution=e1s2'>PayPal Developer</a> site. <br /><br />
				
				Once you have made an account, create a Sandbox Business and Personal Account <a target='_blank' href='https://developer.paypal.com/webapps/developer/applications/accounts'>here</a>. Enter the Business acount email on this page and use the Personal account username and password to buy something on your site as a customer. <br /><br /><br />
				
				<b>Sandbox Mode:</b>
				&nbsp; &nbsp; <input <?php if ($value['mode'] == "1") { echo "checked='checked'"; } ?> type='radio' name='mode' value='1'>On (Sandbox mode)
				&nbsp; &nbsp; <input <?php if ($value['mode'] == "2") { echo "checked='checked'"; } ?> type='radio' name='mode' value='2'>Off (Live mode)
				
			<br /><br /></div><br /><br />
			
			
			<div style="background-color:#333333;padding:8px;color:#eee;font-size:12pt;font-weight:bold;">
			&nbsp; Tax Settings </div><div style="background-color:#fff;border: 1px solid #E5E5E5;padding:5px;"><br />
				
				<b>Tax Fixed Amount: </b>
				<input type='text' name='tax' value='<?php echo $value['tax']; ?>'> Optional <br />
				Set a fixed amount, for example for $0.70 tax, enter .70 <br /><br />
				
				<b>Tax Rate (Percentage) Amount: </b>
				<input type='text' name='tax_rate' value='<?php echo $value['tax_rate']; ?>'> Optional <br />
				Set a tax rate, for example for 2.5% tax, enter 2.5
				
				<br /><br /> Enter tax amount or tax rate, but not both.<br />
				You can setup advanced tax profiles <a target='_blank' href='https://www.paypal.com/cgi-bin/customerprofileweb?cmd=_profile-sales-tax'>here</a>.
				
			<br /><br /></div><br /><br />
			
			
			<div style="background-color:#333333;padding:8px;color:#eee;font-size:12pt;font-weight:bold;">
			&nbsp; Other Settings
			</div><div style="background-color:#fff;border: 1px solid #E5E5E5;padding:5px;"><br />
				
				<b>Cancel URL: </b>
				<input type='text' name='cancel' value='<?php echo $value['cancel']; ?>'> Optional <br />
				If the customer goes to PayPal and clicks the cancel button, where do they go. Example: <?php echo $siteurl; ?>/cancel. Max length: 1,024. <br /><br />
				
				<b>Return URL: </b>
				<input type='text' name='return' value='<?php echo $value['return']; ?>'> Optional <br />
				If the customer goes to PayPal and successfully pays, where are they redirected to. Example: <?php echo $siteurl; ?>/thankyou. Max length: 1,024. <br /><br />
				
				<b>Redirect Method: </b>
				
				<select name="redirect">
				<option <?php if ($value['redirect'] == "1") { echo "SELECTED"; } ?> value="1">Method 1</option>
				<option <?php if ($value['redirect'] == "2") { echo "SELECTED"; } ?> value="2">Method 2</option>
				</select> <br />
				
				Method 1 uses a Contact Form 7 to redirect (Prefered method). Method 2 disables Contact Form 7's Ajax to redirect to PayPal. <br /><br />
			
			
			</div>
			
			<input type='hidden' name='update'>
			</form>
			
			
			
				
			<br /><br /><div style="background-color:#333333;padding:8px;color:#eee;font-size:12pt;font-weight:bold;">
			&nbsp; License Key </div><div style="background-color:#fff;border: 1px solid #E5E5E5;padding:5px;">
			
				<form method="post" action="">
				<table style="width:100%;">
				<tr><th colspan="2" align="left">
				<?php 
				
				if ($kstatus == "true") {
				echo 'Your license key is:';
				$buttonvalue = "Change";
				} else {
				echo '<b>Please enter your license key here:</b>';
				$buttonvalue = "Activate";
				}
				
				?>
				</th></tr><tr><td width="300px">
				<input class="textfield" name="cf7pp_plicsence_key" size="30" type="text" id="cf7pp_plicsence_key" value="<?php echo get_option('cf7pp_plicsence_key'.PLICENSE_PRODUCT_NAME); ?>" />
				</td><td>
				<input type="submit" value="<?php echo $buttonvalue; ?>" class="button-primary" id="plicense_activate" name="plicense_activate">&nbsp;&nbsp;&nbsp;
				<br />
				<?php
				switch (get_option('cf7pp_plicsence_key_status')) {
				case 'false': echo '<span style="color:red;float:left;">License key is not valid.</span>'; break;
				case 'pending': echo '<span style="color:red;">License key is pending.</span>'; break;
				case 'expired': echo '<span style="color:red;">License key is expired.</span>'; break;
				}
				?>
				</td>
				</tr>
				</table>
				</form>
				
			</div>
			
			<br />
			WP Plugin is an offical PayPal Partner. Various trademarks held by their respective owners.
			
			
			
			
		
		</div>
		
		
		<?php
		
		} else {
		
		?>
		
		<div style='width:400px;'>
			
			<br /><br /><div style="background-color:#333333;padding:8px;color:#eee;font-size:12pt;font-weight:bold;">
			&nbsp; License Key </div><div style="background-color:#fff;border: 1px solid #E5E5E5;padding:5px;">
				
				
				<form method="post" action="">
				<table style="width:100%;">
				<tr><th colspan="2" align="left">
				<?php 
				
				if ($kstatus == "true") {
				echo 'Your license key is:';
				$buttonvalue = "Change";
				} else {
				echo '<b>Please enter your license key here:</b>';
				$buttonvalue = "Activate";
				}
				
				?>
				</th></tr><tr><td>
				<input class="textfield" name="cf7pp_plicsence_key" size="27" type="text" id="cf7pp_plicsence_key" value="<?php echo get_option('cf7pp_plicsence_key'.PLICENSE_PRODUCT_NAME); ?>" />
				&nbsp; <input type="submit" value="<?php echo $buttonvalue; ?>" class="button-primary" id="plicense_activate" name="plicense_activate">&nbsp;&nbsp;&nbsp;
				<br />
				<?php
				switch (get_option('cf7pp_plicsence_key_status')) {
				case 'false': echo '<span style="color:red;float:left;">License key is not valid.</span>'; break;
				case 'pending': echo '<span style="color:red;">License key is pending.</span>'; break;
				case 'expired': echo '<span style="color:red;">License key is expired.</span>'; break;
				}
				?>
				</td>
				</tr>
				</table>
				</form>
			
			</div>
		
		</div>
		
		<?php
		
		}
	}


} else {

	// give warning if contact form 7 is not active
	function cf7pp_my_admin_notice() {
		?>
		<div class="error">
			<p><?php _e( '<b>Contact Form 7 PayPal Add-on:</b> Contact Form 7 is not active! Please <a target=_blank href=https://wordpress.org/plugins/contact-form-7/>install</a> and/or activate it to use this plugin.', 'my-text-domain' ); ?></p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'cf7pp_my_admin_notice' );

}

?>