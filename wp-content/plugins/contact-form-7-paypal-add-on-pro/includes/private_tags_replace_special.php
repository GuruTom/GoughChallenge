<?php

// get post details

global $page_post_id, $page_post_user, $page_post_submission, $page_post_timestamp;


$page_post_id = $post_unit_tag;
$page_post_id = get_post($page_post_id);
$page_post_user = new WP_User($page_post_id->post_author);
$page_post_submission = $submission_orig;
$page_post_timestamp = current_time('timestamp');


function replace_tags_special($input) {

	global $page_post_id, $page_post_user, $page_post_submission, $page_post_timestamp;

	// conversions and replace tags

	// _date
	$page_post_date = date_i18n( get_option('date_format'), $page_post_timestamp);
	$output = str_replace("[_date]", $page_post_date, $input);

	// _time
	$page_post_time = date_i18n( get_option('time_format'), $page_post_timestamp);
	$output = str_replace("[_time]", $page_post_time, $output);

	// _post_author
	$page_post_author = $page_post_user->display_name;
	$output = str_replace("[_post_author]", $page_post_author, $output);

	// _post_author_email
	$page_post_author_email = $page_post_user->user_email;
	$output = str_replace("[_post_author_email]", $page_post_author_email, $output);

	// _post_url
	$page_post_url = get_permalink( $page_post_id->ID );
	$output = str_replace("[_post_url]", $page_post_url, $output);

	// _post_id
	$page_post_page_id = (string) $page_post_id->ID;
	$output = str_replace("[_post_id]", $page_post_page_id, $output);

	// _post_name
	$page_page_post_name = $page_post_id->post_name;
	$output = str_replace("[_post_name]", $page_page_post_name, $output);

	// _post_title
	$page_post_tite = $page_post_id->post_title;
	$output = str_replace("[_post_title]", $page_post_tite, $output);

	// _url	
	$url = $page_post_submission->get_meta('url');
	$page_post_url = esc_url( $url );
	$output = str_replace("[_url]", $page_post_url, $output);

	// _remote_ip
	$page_port_ip = $page_post_submission->get_meta('remote_ip');
	$output = str_replace("[_remote_ip]", $page_port_ip, $output);

	// _user_agent
	$page_post_user_agent = $page_post_submission->get_meta('user_agent');
	$output = str_replace("[_user_agent]", $page_post_user_agent, $output);

	return $output;

}

?>