<?php
// Setup Newletter Post Type
function bt_cpt_setup() {
	$wptt_options = get_option( 'wptt_settings' );

	/**** Custom Post Type: Newsletters ****/

	$labels = array(
		'name' => _x('Newsletters', 'post type general name'),
		'singular_name' => _x('Newsletter', 'post type singular name'),
		'add_new' => _x('Add Newsletter', 'services'),
		'add_new_item' => __('Add Newsletter'),
		'edit_item' => __('Edit Newsletter'),
		'edit' => _x('Edit Newsletter', 'services'),
		'new_item' => __('New Newsletter'),
		'view_item' => __('View Newsletter'),
		'search_items' => __('Search Newsletters'),
		'not_found' =>  __('No Newsletters found'),
		'not_found_in_trash' => __('No Newsletters found in Trash'),
		'view' =>  __('View Newsletters'),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => false, 
		'rewrite' => array("slug" => "newsletters"),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
// 		'menu_icon' => plugins_url( 'public/assets/images/clock-small.png', dirname(__FILE__) ),
		'supports' => array( 'title' )
	);

	register_post_type( 'newsletters', $args);
}

add_action( 'init', 'bt_cpt_setup', 1 );


function bt_get_newsletter_template( $single_template ) {
global $post;
//	if ($post->post_type == 'my_post_type') {
	if( is_singular( 'newsletters' ) ) {
		$single_template = BTNM_PATH . '/views/newsletter-template.php';
	}
	return $single_template;
}

// add_filter( 'single_template', 'bt_get_newsletter_template' );
