<?php

/**
 * Allow for shortcodes in widgets
 * (may or may not need this, but here it is anyway)
 */

add_filter( 'widget_text', 'do_shortcode' );

/**
 * add image sizes
 */

add_image_size( 'homedeal', 210, 315, true );
add_image_size( 'columnthumb', 50, 75, true );

/**
 * Shortcode to display current deals
 */

add_shortcode( 'current_deals', 'current_deals_shortcode' );

function current_deals_shortcode() {
	$args = array (
		'post_type'			=> 'deal',
		'posts_per_page'	=> -5,
		'orderby'			=> 'end_date',
		'order'				=> 'ASC',
		'meta_query'		=> array(
			array(
				'key'		=> 'end_date',
				'value'		=> date( 'Ymd' ),
				'compare'	=> '>=',
			),
			array(
				'key'		=> 'start_date',
				'value'		=> date( 'Ymd' ),
				'compare'	=> '<=',
			)
		)
	);

	$deal_query = new WP_Query( $args );

	if ( $deal_query->have_posts() ) {
		$html = '<div class="homedealsbox">';

		while ( $deal_query->have_posts() ) { 
			$deal_query->the_post();

			$html .= '<div class="singledeal">';
				$html .= get_the_post_thumbnail( get_the_ID(), 'homedeal' ) . '<br />';
				if ( get_post_meta( get_the_ID(), 'on_sale_price', true ) ) {
					$html .= 'Sale Price: $' . get_post_meta( get_the_ID(), 'on_sale_price', true ) . '<br />';
				} else {
					$html .= 'Sale Price: FREE!<br />';
				}

				$html .= 'Final Day: ';
				$html .= date( 'm/d/y', strtotime( get_post_meta( get_the_ID(), 'end_date', true ) ) ) . '<br />';

				$html .= 'Reg. Price: $<span style="text-decoration: line-through;">' . get_post_meta( get_the_ID(), 'regular_price', true ) . '</span><br />';

				if ( get_post_meta( get_the_ID(), 'on_sale_price', true ) ) {
					$html .= '<a class="button" href="http://www.amazon.com/dp/' . get_post_meta( get_the_ID(), 'amazon_id', true ) . '/?tag=runawaygoodness-20">Buy Now</a>';
				} else {
					$html .= '<a class="button" href="http://www.amazon.com/dp/' . get_post_meta( get_the_ID(), 'amazon_id', true ) . '/">Download</a>';
				}

			
			$html .= '</div>';

/*
			if( get_sub_field( 'amazon_asin' ) ) {
				if( get_sub_field( 'book_price' ) == '0' ) {
					$html .= '<a href="http://www.amazon.com/dp/' . get_post_meta( get_the_ID(), 'amazon_id', true ) . '/"><img src="' . $thumb . '"  alt="' . get_sub_field( 'book_title' ) . '" style="max-width:120px;min-width:120px" align="left"></a>';
				} else {
					$html .= '<a href="http://www.amazon.com/dp/' . get_post_meta( get_the_ID(), 'amazon_id', true ) . '/?tag=runawaygoodness-20"><img src="' . $thumb . '"  alt="' . get_sub_field( 'book_title' ) . '" style="max-width:120px;min-width:120px" align="left"></a>';
				}
			} else {
				$html .= '<img src="' . get_sub_field( 'book_cover' ) . '" alt="' . get_sub_field( 'book_title' ) . '" style="max-width:120px;min-width:120px" align="left">';
			}
*/


		}
		wp_reset_postdata();
	}

	return $html;
}


/**
 * 
 * Modifies the Deals CPT to display extra columns
 * 
 */

add_filter( 'manage_edit-deal_columns', 'rg_edit_deal_columns' );

function rg_edit_deal_columns( $columns ) {

	unset( $columns['date'] );

	$mycolumns = array(
		'start_date'	=> __( 'Start Date', 'runawaygoodness' ),
		'end_date'		=> __( 'End Date', 'runawaygoodness' ),
		'thumb'			=> __( 'Thumbnail', 'runawaygoodness' )
	);

	$columns = array_merge( $columns, $mycolumns );

	return $columns;
}

/**
 *
 * Grabs the data for our custom columns
 * 
 */

add_action( 'manage_deal_posts_custom_column', 'rg_deal_columns', 10, 2 );

function rg_deal_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		case 'start_date' :
			echo date( 'm/d/y', strtotime( get_post_meta( get_the_ID(), 'start_date', true ) ) ) ;
		break;

		case 'end_date' :
			echo date( 'm/d/y', strtotime( get_post_meta( get_the_ID(), 'end_date', true ) ) ) ;
		break;

		case 'thumb' :
			echo get_the_post_thumbnail( get_the_ID(), 'columnthumb' );
		break;

		default :
		break;
			
	}
}

add_action( 'genesis_before', 'rg_facebook_code' );

function rg_facebook_code() {
?>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4&appId=341168189960";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
<?php
}

add_action('after_setup_theme', 'remove_admin_bar');


/*
Remove admin bar
 */
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}

add_action( 'init', 'create_genre_taxonomy', 0 );

function create_genre_taxonomy() {

	$labels = array(
	'name' => _x( 'Genres', 'taxonomy general name' ),
	'singular_name' => _x( 'Genre', 'taxonomy singular name' ),
	'search_items' =>  __( 'Search Genres' ),
	'popular_items' => __( 'Popular Genres' ),
	'all_items' => __( 'All Genres' ),
	'parent_item' => null,
	'parent_item_colon' => null,
	'edit_item' => __( 'Edit Topic' ), 
	'update_item' => __( 'Update Topic' ),
	'add_new_item' => __( 'Add New Topic' ),
	'new_item_name' => __( 'New Topic Name' ),
	'separate_items_with_commas' => __( 'Separate Genres with commas' ),
	'add_or_remove_items' => __( 'Add or remove Genres' ),
	'choose_from_most_used' => __( 'Choose from the most used Genres' ),
	'menu_name' => __( 'Genres' ),
	); 

// Now register the non-hierarchical taxonomy like tag

  register_taxonomy( 'deal_genres','deal',array(
'hierarchical' => false,
'labels' => $labels,
'show_ui' => true,
'show_admin_column' => true,
'update_count_callback' => '_update_post_term_count',
'query_var' => true,
'rewrite' => array( 'slug' => 'genres' ),
  ));
}
