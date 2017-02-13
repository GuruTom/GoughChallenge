<?php

// setup for emailing, convert tags, and save to db to be retrieved after ipn is sucessful
if  ($email == "1") {
	
	$array = (array) $submission_orig;
	$valuesa = array_values ($array);
	$array = (array) $valuesa[0];
	$values = array_values ($array);
	$post_unit_tag = $values[0];
	$uploaddir = "/cf7pp_uploads";
	

	// mail 1
	$everything = $values[3]['mail'];
	$body = $values[3]['mail']['body'];
	$subject = $values[3]['mail']['subject'];
	$sender = $values[3]['mail']['sender'];
	$additional_headers = $values[3]['mail']['additional_headers'];
	$recipient = $values[3]['mail']['recipient'];
	$attachments = $values[3]['mail']['attachments'];
	

	// mail 2
	$everything2 = $values[3]['mail_2'];
	$active2 = $values[3]['mail_2']['active'];
	$body2 = $values[3]['mail_2']['body'];
	$subject2 = $values[3]['mail_2']['subject'];
	$sender2 = $values[3]['mail_2']['sender'];
	$additional_headers2 = $values[3]['mail_2']['additional_headers'];
	$recipient2 = $values[3]['mail_2']['recipient'];
	$attachments2 = $values[3]['mail_2']['attachments'];

	include_once ('private_tags_replace.php');
	include_once ('private_tags_replace_special.php');

	// mail 1
	$body_new = replace_tags($body);
	$body_new = replace_tags_special($body_new);
	$subject_new = replace_tags($subject);
	$subject_new = replace_tags_special($subject_new);
	$sender_new = replace_tags($sender);
	$additional_headers_new = replace_tags($additional_headers);
	$recipient_new = replace_tags($recipient);

	// mail 2
	if ($active2 == "1") {
		$body_new2 = replace_tags($body2);
		$body_new2 = replace_tags_special($body_new2);
		$subject_new2 = replace_tags($subject2);
		$subject_new2 = replace_tags_special($subject_new2);
		$sender_new2 = replace_tags($sender2);
		$additional_headers_new2 = replace_tags($additional_headers2);
		$recipient_new2 = replace_tags($recipient2);
	}

	
	// files - mail 1
	$attachment = explode("][",$attachments);
	
	foreach($attachment as $key=>$value_att){ $attachment[$key]=str_replace("[","",$value_att); }
	foreach($attachment as $key=>$value_att){ $attachment[$key]=str_replace("]","",$value_att); }
	
	$attachments = "";
	
	
	$attachment = array_filter($attachment);
	
	$num_items_in_array = count($files_arr);
	
	if (!empty($files_arr)) {
		foreach ($attachment as $key) {
			
			$count = "0";
			while ($count < $num_items_in_array) {
				if ($key == $files_order[$count]) {
					$attachments .= "uploads".$uploaddir.$files_arr[$count];
					$attachments .= "\r\n";	
				}
				$count++;
			}
			
			
		}
		
		// files - mail 2
		$attachment2 = explode("][",$attachments2);
		
		foreach($attachment2 as $key2=>$value_att2){ $attachment2[$key2]=str_replace("[","",$value_att2); }
		foreach($attachment2 as $key2=>$value_att2){ $attachment2[$key2]=str_replace("]","",$value_att2); }
		
		$attachments2 = "";
		
		$attachment2 = array_filter($attachment2);
		
		foreach ($attachment2 as $key2) {
			
			$count2 = "0";
			while ($count2 < $num_items_in_array) {
				if ($key2 == $files_order[$count2]) {
					$attachments2 .= "uploads".$uploaddir.$files_arr[$count2];
					$attachments2 .= "\r\n";	
				}
				$count2++;
			}
			
		}		
		
	}	
	


	// mail 1
	unset($everything['body']);
	unset($everything['subject']);
	unset($everything['sender']);
	unset($everything['additional_headers']);
	unset($everything['recipient']);
	unset($everything['attachments']);

	// mail 2
	if ($active2 == "1") {
		unset($everything2['body2']);
		unset($everything2['subject2']);
		unset($everything2['sender2']);
		unset($everything2['additional_headers2']);
		unset($everything2['recipient2']);
		unset($everything2['attachments2']);
	}

	// mail 1
	$everything['body'] = $body_new;
	$everything['subject'] = $subject_new;
	$everything['sender'] = $sender_new;
	$everything['additional_headers'] = $additional_headers_new;
	$everything['recipient'] = $recipient_new;
	$everything['attachments'] = $attachments;

	// mail 2
	if ($active2 == "1") {
		$everything2['body'] = $body_new2;
		$everything2['subject'] = $subject_new2;
		$everything2['sender'] = $sender_new2;
		$everything2['additional_headers'] = $additional_headers_new2;
		$everything2['recipient'] = $recipient_new2;
		$everything2['attachments'] = $attachments2;
	}
	
	
	$main['mail'] = $everything;
	if ($active2 == "1") {
		$main['mail_2'] = $everything2;
	}

	$string = base64_encode(serialize($main));





	// create new post
	$my_post = array(
		'post_title'    => 'cf7pp_tmp_email',
		'post_status'   => 'publish',
		'post_author'   => $current_user->ID,
		'post_type'     => 'cf7pp',
		'post_content'  => $string
	);

	// insert the post into the database
	global $new_post_id;
	$new_post_id = wp_insert_post($my_post);
	
}


















// process tags for public_redirect.php


// get variables

$tags = (array) null;

$post_id = $postid;

$enable = get_post_meta($post_id, "_cf7pp_enable", true);
$tags['name'] = get_post_meta($post_id, "_cf7pp_name", true);
$desc = get_post_meta($post_id, "_cf7pp_desc", true);
$tags['price'] = get_post_meta($post_id, "_cf7pp_price", true);
$tags['id'] = get_post_meta($post_id, "_cf7pp_id", true);
$tags['email'] = get_post_meta($post_id, "_cf7pp_email", true);
$tags['quantity'] = get_post_meta($post_id, "_cf7pp_quantity", true);
$tags['shipping'] = get_post_meta($post_id, "_cf7pp_shipping", true);
$sandbox = get_post_meta($post_id, "_cf7pp_sandbox", true);
$note = get_post_meta($post_id, "_cf7pp_note", true);
$form_account = get_post_meta($post_id, "_cf7pp_form_account", true);
$cancel = get_post_meta($post_id, "_cf7pp_cancel", true);
$return = get_post_meta($post_id, "_cf7pp_return", true);
$price_menu = get_post_meta($post_id, "_cf7pp_price_menu", true);
$quantity_menu = get_post_meta($post_id, "_cf7pp_quantity_menu", true);
$text_menu_a = get_post_meta($post_id, "_cf7pp_text_menu_a", true);
$tags['text_menu_a_name'] = get_post_meta($post_id, "_cf7pp_text_menu_a_name", true);
$text_menu_b = get_post_meta($post_id, "_cf7pp_text_menu_b", true);
$tags['text_menu_b_name'] = get_post_meta($post_id, "_cf7pp_text_menu_b_name", true);

// notify url
$tags['notify_url'] = get_site_url();


$options = get_option('cf7pp_options');
foreach ($options as $k => $v ) { $value[$k] = $v; }


// live or test mode
if ($value['mode'] == "1") {
	$tags['account'] = $value['sandboxaccount'];
	$tags['path'] = "sandbox.paypal";
} elseif ($value['mode'] == "2")  {
	$tags['account'] = $value['liveaccount'];
	$tags['path'] = "paypal";
}

if ($sandbox == "1") {
	$tags['account'] = $value['sandboxaccount'];
	$tags['path'] = "sandbox.paypal";
}

// form account
if (!empty($form_account)) {
    $tags['account'] = $form_account;
}

// tax
if (!empty($value['tax'])) { $tags['tax'] = $value['tax']; } else { $tags['tax'] = ""; }
if (!empty($value['tax_rate'])) { $tags['tax_rate'] = $value['tax_rate']; } else { $tags['tax_rate'] = ""; }


// currency
if ($value['currency'] == "1") { $tags['currency'] = "AUD"; }
if ($value['currency'] == "2") { $tags['currency'] = "BRL"; }
if ($value['currency'] == "3") { $tags['currency'] = "CAD"; }
if ($value['currency'] == "4") { $tags['currency'] = "CZK"; }
if ($value['currency'] == "5") { $tags['currency'] = "DKK"; }
if ($value['currency'] == "6") { $tags['currency'] = "EUR"; }
if ($value['currency'] == "7") { $tags['currency'] = "HKD"; }
if ($value['currency'] == "8") { $tags['currency'] = "HUF"; }
if ($value['currency'] == "9") { $tags['currency'] = "ILS"; }
if ($value['currency'] == "10") { $tags['currency'] = "JPY"; }
if ($value['currency'] == "11") { $tags['currency'] = "MYR"; }
if ($value['currency'] == "12") { $tags['currency'] = "MXN"; }
if ($value['currency'] == "13") { $tags['currency'] = "NOK"; }
if ($value['currency'] == "14") { $tags['currency'] = "NZD"; }
if ($value['currency'] == "15") { $tags['currency'] = "PHP"; }
if ($value['currency'] == "16") { $tags['currency'] = "PLN"; }
if ($value['currency'] == "17") { $tags['currency'] = "GBP"; }
if ($value['currency'] == "18") { $tags['currency'] = "RUB"; }
if ($value['currency'] == "19") { $tags['currency'] = "SGD"; }
if ($value['currency'] == "20") { $tags['currency'] = "SEK"; }
if ($value['currency'] == "21") { $tags['currency'] = "CHF"; }
if ($value['currency'] == "22") { $tags['currency'] = "TWD"; }
if ($value['currency'] == "23") { $tags['currency'] = "THB"; }
if ($value['currency'] == "24") { $tags['currency'] = "TRY"; }
if ($value['currency'] == "25") { $tags['currency'] = "USD"; }

// language
if ($value['language'] == "1") {
	$tags['language'] = "da_DK";
} //Danish

if ($value['language'] == "2") {
	$tags['language'] = "nl_BE";
} //Dutch

if ($value['language'] == "3") {
	$tags['language'] = "EN_US";
} //English

if ($value['language'] == "4") {
	$tags['language'] = "fr_CA";
} //French

if ($value['language'] == "5") {
	$tags['language'] = "de_DE";
} //German

if ($value['language'] == "6") {
	$tags['language'] = "he_IL";
} //Hebrew

if ($value['language'] == "7") {
	$tags['language'] = "it_IT";
} //Italian

if ($value['language'] == "8") {
	$tags['language'] = "ja_JP";
} //Japanese

if ($value['language'] == "9") {
	$tags['language'] = "no_NO";
} //Norwgian

if ($value['language'] == "10") {
	$tags['language'] = "pl_PL";
} //Polish

if ($value['language'] == "11") {
	$tags['language'] = "pt_BR";
} //Portuguese

if ($value['language'] == "12") {
	$tags['language'] = "ru_RU";
} //Russian

if ($value['language'] == "13") {
	$tags['language'] = "es_ES";
} //Spanish

if ($value['language'] == "14") {
	$tags['language'] = "sv_SE";
} //Swedish

if ($value['language'] == "15") {
	$tags['language'] = "zh_CN";
} //Simplified Chinese - China

if ($value['language'] == "16") {
	$tags['language'] = "zh_HK";
} //Traditional Chinese - Hong Kong

if ($value['language'] == "17") {
	$tags['language'] = "zh_TW";
} //Traditional Chinese - Taiwan

if ($value['language'] == "18") {
	$tags['language'] = "tr_TR";
} //Turkish

if ($value['language'] == "19") {
	$tags['language'] = "th_TH";
} //Thai

// note action
if ($note == "1") { $tags['notevalue'] = "1"; } else { $tags['notevalue'] = ""; }
if (!isset($note)) { $tags['notevalue'] = ""; }

// return url
if (!empty($return)) { $tags['returnvalue'] = $return; } else { $tags['returnvalue'] = $value['return']; }
if (!isset($return)) { $tags['returnvalue'] = ""; }

// cancel url
if (!empty($cancel)) { $tags['cancelvalue'] = $cancel; } else { $tags['cancelvalue'] = $value['cancel']; }
if (!isset($cancel)) { $tags['cancelvalue'] = ""; }

// quantity menu
if (isset($_POST[$quantity_menu])) { $tags['quantity'] = $_POST[$quantity_menu]; }

// price menu
if (isset($_POST[$price_menu])) { $tags['price'] = $posted_data[$price_menu]; }

// text menu 1
if (isset($_POST[$text_menu_a])) { $tags['text_menu_a'] = $_POST[$text_menu_a]; } else { $tags['text_menu_a'] = ""; }

// text menu 2
if (isset($_POST[$text_menu_b])) { $tags['text_menu_b'] = $_POST[$text_menu_b]; } else { $tags['text_menu_b'] = ""; }

// description
if (isset($_POST[$desc])) { $tags['name'] = $_POST[$desc]; }



// for certain values that are arrays

// price

if (isset($tags['price'])) {
	$price_array = is_array($tags['price']) ? '1' : '2';

	if ($price_array == "1") {

		$price_total = "";
		foreach ($tags['price'] as $val) {
			
			$val = preg_replace('/[^0-9.]*/','',$val);
			
			$price_total = $val + $price_total;
			
		}
		
		$tags['price'] = $price_total;
	}
}


// text field 1

if (isset($tags['text_menu_a'])) {
	$desc_a_array = is_array($tags['text_menu_a']) ? '1' : '2';

	if ($desc_a_array == "1") {
		
		$counter_a = "0";
		$text_menu_a_c = "";
		foreach($tags['text_menu_a'] as $val) {
			
			if ($counter_a >= "1") {
			$text_menu_a_c .= ", ";
			}
			
			$text_menu_a_c .= $val;
			
		$counter_a++;
			
		}
		
		$tags['text_menu_a'] = $text_menu_a_c;
		
	}
}

// text field 2

if (isset($tags['text_menu_b'])) {
	$desc_b_array = is_array($tags['text_menu_b']) ? '1' : '2';

	if ($desc_b_array == "1") {
		
		$counter_b = "0";
		$text_menu_b_c = "";
		foreach($tags['text_menu_b'] as $val) {
			
			if ($counter_b >= "1") {
			$text_menu_b_c .= ", ";
			}
			
			$text_menu_b_c .= $val;
			
		$counter_b++;
			
		}
		
		$tags['text_menu_b'] = $text_menu_b_c;
		
	}
}

// save tags
$tags_string = base64_encode(serialize($tags));


// create new post
$my_post_tags = array(
	'post_title'    => 'cf7pp_tmp_tags',
	'post_status'   => 'publish',
	'post_author'   => $current_user->ID,
	'post_type'     => 'cf7pp',
	'post_content'  => $tags_string
);

// insert the post into the database
global $tags_id;
$tags_id = wp_insert_post($my_post_tags);