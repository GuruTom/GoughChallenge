<?php
// Register Custom Navigation Walker
require_once('wp_bootstrap_navwalker.php');

/***************************************
WordPress Script Hooks
***************************************/

// Function to add all stylesheets and JavaScript files
function add_theme_scripts() {
    wp_enqueue_style('Raleway', 'https://fonts.googleapis.com/css?family=Raleway:100,300,400,600,900');
	wp_enqueue_style('font-awesome', 'http://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css');
    wp_enqueue_style('bootstrap', get_template_directory_uri().'/css/bootstrap.min.css');
    wp_enqueue_style('animation', get_template_directory_uri().'/css/animate.css');
	wp_enqueue_style('main', get_template_directory_uri().'/css/screen.css');

    wp_enqueue_script('bootstrapjs', get_template_directory_uri().'/js/bootstrap.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('appjs', get_template_directory_uri().'/js/app.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'add_theme_scripts');

// Adding JQuery
if (!is_admin()) add_action("wp_enqueue_scripts", "my_jquery_enqueue", 11);
function my_jquery_enqueue() {
   wp_deregister_script('jquery');
   wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js", false, null);
   wp_enqueue_script('jquery');
}


/***************************************
WordPress Navigation Hooks
***************************************/

// Register Menu Support
function register_my_menu() {
  register_nav_menu('main-menu',__( 'Main Menu' ));
}
add_action( 'init', 'register_my_menu' );

// Getting Childs of Navigation Bar Item
function get_nav_menu_item_children( $parent_id, $nav_menu_items, $depth = true )
{
	$nav_menu_item_list = array();
	foreach ( (array) $nav_menu_items as $nav_menu_item ) {

		if ( $nav_menu_item->menu_item_parent == $parent_id ) {
			$nav_menu_item_list[] = $nav_menu_item;
			if ( $depth ) {
				if ( $children = get_nav_menu_item_children( $nav_menu_item->ID, $nav_menu_items ) )
				$nav_menu_item_list = array_merge( $nav_menu_item_list, $children );
			}
		}
	}
	return $nav_menu_item_list;
}

// Register Widget Support
if ( function_exists('register_sidebar') ) {
	register_sidebar();
}


/***************************************
WordPress Admin Login Hooks
***************************************/

// WordPress Login Stylesheet
function my_login_stylesheet() {
    wp_enqueue_style( 'custom-login', get_template_directory_uri() . '/css/screen.css' );
}
add_action( 'login_enqueue_scripts', 'my_login_stylesheet' );

// Login Logo URL
function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );


/***************************************
WordPress Search Hook
***************************************/

// Sort search by post type
add_filter('posts_orderby','my_sort_custom',10,2);
function my_sort_custom( $orderby, $query ){
    global $wpdb;

    if(!is_admin() && is_search())
        $orderby =  $wpdb->prefix."posts.post_type DESC, {$wpdb->prefix}posts.post_date DESC";

    return  $orderby;
}


/***************************************
WooCommerce Methods
***************************************/

/* Remove WooCommerce Lightbox styles and scripts. */
function woo_remove_lightboxes() {
        // Styles
        wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
        // Scripts
        wp_dequeue_script( 'prettyPhoto' );
        wp_dequeue_script( 'prettyPhoto-init' );
        wp_dequeue_script( 'fancybox' );
        wp_dequeue_script( 'enable-lightbox' );
}
add_action( 'wp_enqueue_scripts', 'woo_remove_lightboxes', 99 );

// Making Responsive Lightbox by dFactory work with WooCommerce
function df_woocommerce_single_product_image_html($html) {
    $html = str_replace('data-rel="prettyPhoto', 'rel="lightbox', $html);
    return $html;
}
add_filter('woocommerce_single_product_image_html', 'df_woocommerce_single_product_image_html', 99, 1); // single image
add_filter('woocommerce_single_product_image_thumbnail_html', 'df_woocommerce_single_product_image_html', 99, 1); // thumbnails
