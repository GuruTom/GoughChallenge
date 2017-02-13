<?php


/* 
Copyright 2014-2015 Scott Paterson
This is not free software.
You do not have permission to distribute this software under any circumstances.
You may modify this software (excluding the license and update manager) for personal use only if you hold a valid license key.
*/

// get form items back and assign to variables
$content = get_post($tagsid);

if (!empty($content)) {
	$array = $content->post_content;
	$tags_back = unserialize(base64_decode($array));
	foreach ($tags_back as $k => $v ) { $tags[$k] = $v; }
}

wp_delete_post($tagsid,true);

if (!isset($tags) || empty($tags)) {
	exit;
}


?>
<!doctype html>
<html>
<head>
<title>Redirecting to Paypal...</title>
</head>
<body>
<form action='https://www.<?php echo $tags['path']; ?>.com/cgi-bin/webscr' method='post' name="cf7pp">
<input type='hidden' name='cmd' value='_xclick' />
<input type='hidden' name='business' value='<?php echo $tags['account']; ?>' />
<input type='hidden' name='item_name' value='<?php echo $tags['name']; ?>' />
<input type='hidden' name='currency_code' value='<?php echo $tags['currency']; ?>' />
<input type='hidden' name='amount' value='<?php echo $tags['price']; ?>' />
<input type='hidden' name='bn' value='WPPlugin_SP'>
<?php if  ($tags['email'] == "1") { ?>
<input type='hidden' name='notify_url' value='<?php echo $tags['notify_url']; ?>'>
<input type='hidden' name='custom' value='<?php echo $postid; echo "|"; echo $input_id; ?>'>
<?php } ?>
<input type='hidden' name='lc' value='<?php echo $tags['language']; ?>'>
<input type='hidden' name='item_number' value='<?php echo $tags['id']; ?>' />
<input type='hidden' name='cancel_return' value='<?php echo $tags['cancelvalue']; ?>' />
<input type='hidden' name='return' value='<?php echo $tags['returnvalue']; ?>' />
<input type='hidden' name='quantity' value='<?php echo $tags['quantity']; ?>'>
<input type='hidden' name='shipping' value='<?php echo $tags['shipping']; ?>' />
<input type='hidden' name='no_note' value='<?php echo $tags['notevalue']; ?>'>
<input type='hidden' name='tax' value='<?php echo $tags['tax']; ?>' />
<input type='hidden' name='tax_rate' value='<?php echo $tags['tax_rate']; ?>' />
<input type='hidden' name='on0' value='<?php echo $tags['text_menu_a_name']; ?>' />
<input type='hidden' name='os0' value='<?php echo $tags['text_menu_a']; ?>' />
<input type='hidden' name='on1' value='<?php echo $tags['text_menu_b_name']; ?>' />
<input type='hidden' name='os1' value='<?php echo $tags['text_menu_b']; ?>' />
<img alt='' border='0' style='border:none;display:none;' src='https://www.paypal.com/$language/i/scr/pixel.gif' width='1' height='1'>
</form>
<script type="text/javascript">
document.cf7pp.submit();
</script>
</body>
</html>