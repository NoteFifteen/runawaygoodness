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
add_image_size( 'sidebarthumb', 105, 157, true );

/**
 * Shortcode to display current deals
 */

add_shortcode( 'current_deals', 'current_deals_shortcode' );

function current_deals_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'loc' => 'main',
		'qty' => 5
		), $atts, 'current_deals'
	);

	$args = array (
		'post_type'			=> 'deal',
		'posts_per_page'	=> $atts['qty'],
		'orderby'			=> 'rand',
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
		$free_amounts = array( '0', '0.00', '0.0' );

		if( $atts['loc'] == 'sidebar' ) {

			$html = '<div class="sidebardeals">';

			while ( $deal_query->have_posts() ) { 
				$deal_query->the_post();

				if ( get_post_meta( get_the_ID(), 'on_sale_price', true ) && !in_array( get_post_meta( get_the_ID(), 'on_sale_price', true ), $free_amounts ) ) {
					$amazon_code = "/?tag=runawaygoodness-20";
				} else {
					$amazon_code = "/";
				}

				$html .= '<a class="sidebardealcover" href="http://www.amazon.com/dp/' . get_post_meta( get_the_ID(), 'amazon_id', true ) . $amazon_code . '">'. get_the_post_thumbnail( get_the_ID(), 'sidebarthumb' ) .'</a>';
			}

			$html .= '</div>';

		} else {

			$html = '<div class="homedealsbox">';
			
			while ( $deal_query->have_posts() ) { 
				$deal_query->the_post();

				$html .= '<div class="singledeal">';
					$html .= get_the_post_thumbnail( get_the_ID(), 'homedeal' ) . '<br />';

					if ( get_post_meta( get_the_ID(), 'on_sale_price', true ) && !in_array( get_post_meta( get_the_ID(), 'on_sale_price', true ), $free_amounts ) ) {
						$html .= 'Sale Price: $' . get_post_meta( get_the_ID(), 'on_sale_price', true ) . '<br />';
					} else {
						$html .= 'Sale Price: FREE!<br />';
					}

					$html .= 'Final Day: ';
					$html .= date( 'm/d/y', strtotime( get_post_meta( get_the_ID(), 'end_date', true ) ) ) . '<br />';

					$html .= 'Reg. Price: $<span style="text-decoration: line-through;">' . get_post_meta( get_the_ID(), 'regular_price', true ) . '</span><br />';

					if ( get_post_meta( get_the_ID(), 'on_sale_price', true ) && !in_array( get_post_meta( get_the_ID(), 'on_sale_price', true ), $free_amounts ) ) {
						$html .= '<a class="button" href="http://www.amazon.com/dp/' . get_post_meta( get_the_ID(), 'amazon_id', true ) . '/?tag=runawaygoodness-20">Buy Now</a>';
					} else {
						$html .= '<a class="button" href="http://www.amazon.com/dp/' . get_post_meta( get_the_ID(), 'amazon_id', true ) . '/">Download</a>';
					}
				$html .= '</div>';
			}

			$html .= '</div>';
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
	unset( $columns['wpseo-score'] );

	$mycolumns = array(
		'start_date'	=> __( 'Start Date', 'runawaygoodness' ),
		'end_date'		=> __( 'End Date', 'runawaygoodness' ),
		'amazon'		=> __( 'Amazon ID', 'runawaygoodness'),
		'thumb'			=> __( 'Thumbnail', 'runawaygoodness' ),
		'used'			=> __( 'In Newsletter', 'runawaygoodness' )
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

	// figure out if deal is current
	$today = strtotime( date( 'm/d/y' ) );
	$s = strtotime( get_post_meta( get_the_ID(), 'start_date', true ) );
	$e = strtotime( get_post_meta( get_the_ID(), 'end_date', true ) );

// 	wp_die( $today . ' _ ' . $s . ' _ ' . $e );
	if( ( $today >= $s ) && ( $today <= $e ) ) {
		$start_color = "#0ad222";
		$end_color = "#0ad222";
	} else {
		$start_color = "#555";
	}

	if( $today > $e ) {
		$end_color = '#D20A29';
	}


	switch( $column ) {

		case 'start_date' :
			echo '<span style="color:'. $start_color .'">' . date( 'm/d/y', strtotime( get_post_meta( get_the_ID(), 'start_date', true ) ) ) . '</span>';
		break;

		case 'end_date' :
			echo '<span style="color:'. $end_color .'">' . date( 'm/d/y', strtotime( get_post_meta( get_the_ID(), 'end_date', true ) ) ) . '</span>';
		break;

		case 'thumb' :
			echo get_the_post_thumbnail( get_the_ID(), 'columnthumb' );
		break;

		case 'amazon' :
			echo '<a href="http://amazon.com/dp/'. get_post_meta( get_the_ID(), 'amazon_id', true ) .'" target="_blank">'. get_post_meta( get_the_ID(), 'amazon_id', true ) .'</a>';
		break;

		case 'used' :
			if( get_post_meta( get_the_ID(), 'used_in_newsletter', true )[0] == 'Yes' ) {
				echo '<img src="/wp-content/plugins/core-functionality/images/checkmark.png">';
			}
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

  register_taxonomy( 'deal_genres', 'deal' ,array(
'hierarchical' => true,
'labels' => $labels,
'show_ui' => true,
'show_admin_column' => true,
'update_count_callback' => '_update_post_term_count',
'query_var' => true,
'rewrite' => array( 'slug' => 'genres' ),
  ));
}

add_action( 'init', 'create_dealsource_taxonomy', 0 );

function create_dealsource_taxonomy() {

	$labels = array(
	'name' => _x( 'Deal Source', 'taxonomy general name' ),
	'singular_name' => _x( 'Deal Source', 'taxonomy singular name' ),
	'search_items' =>  __( 'Search Deal Source' ),
	'popular_items' => __( 'Popular Deal Source' ),
	'all_items' => __( 'All Deal Source' ),
	'parent_item' => null,
	'parent_item_colon' => null,
	'edit_item' => __( 'Edit Deal Source' ), 
	'update_item' => __( 'Update Deal Source' ),
	'add_new_item' => __( 'Add New Deal Source' ),
	'new_item_name' => __( 'New Deal Source Name' ),
	'separate_items_with_commas' => __( 'Separate Deal Sources with commas' ),
	'add_or_remove_items' => __( 'Add or remove Deal Sources' ),
	'choose_from_most_used' => __( 'Choose from the most used Deal Sources' ),
	'menu_name' => __( 'Deal Sources' ),
	); 

// Now register the non-hierarchical taxonomy like tag

  register_taxonomy( 'deal_sources', 'deal', array(
'hierarchical' => true,
'labels' => $labels,
'show_ui' => true,
'show_admin_column' => true,
'update_count_callback' => '_update_post_term_count',
'query_var' => true,
'rewrite' => array( 'slug' => 'dealsources' ),
  ));
}

add_action( 'init', 'create_prefunkgenre_taxonomy', 0 );

function create_prefunkgenre_taxonomy() {

	$labels = array(
	'name' => _x( 'Prefunk Genres', 'taxonomy general name' ),
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
	'menu_name' => __( 'Prefunk Genres' ),
	); 

// Now register the non-hierarchical taxonomy like tag

register_taxonomy( 'prefunk_genres', 'prefunk' ,array(
	'hierarchical' => true,
	'labels' => $labels,
	'show_ui' => true,
	'show_admin_column' => true,
	'update_count_callback' => '_update_post_term_count',
	'query_var' => true,
	'rewrite' => array( 'slug' => 'prefunk-genres' ),
  ));
}

function rg_mime_types($mime_types){
	$mime_types['epub'] = 'application/epub+zip'; //Adding epub extension
	$mime_types['mobi'] = 'application/x-mobipocket-ebook'; //Adding mobi files
	$mime_types['prc'] = 'application/x-mobipocket-ebook'; // Adding prc files

	
	return $mime_types;
}

add_filter( 'upload_mimes', 'rg_mime_types', 1, 1);

// show prefunk sidebar
add_action('get_header','rg_prefunk_switchsidebar');

function rg_prefunk_switchsidebar() {
	if ( is_post_type_archive( 'prefunk' ) ) {
		remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );
		add_action( 'genesis_sidebar', 'rg_prefunksidebar' );
	}
}

function rg_prefunksidebar() {
	// genesis_widget_area( 'sidebar-prefunk' );
	dynamic_sidebar( 'sidebar-prefunk' );
}

function custom_facetwp_class( $atts ) {
    $atts['class'] .= ' facetwp-template';
    return $atts;
}
add_filter( 'genesis_attr_content', 'custom_facetwp_class' );


// add_action( 'pre_get_posts', 'rg_change_prefunk_pageperpost' );

function rg_change_prefunk_pageperpost( $query ) {
	
	if( $query->is_main_query() && !is_admin() && is_post_type_archive( 'prefunk' ) ) {
		$query->set( 'posts_per_page', '2' );
	}
}

add_action( 'pre_get_posts', 'rg_change_prefunk_postsperpage' );

function rg_change_prefunk_postsperpage( $query ) {
	
	if( $query->is_main_query() && !is_admin() && is_post_type_archive( 'prefunk' ) ) {
		$query->set( 'posts_per_page', '40' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'asc' );
		$query->set( 'meta_key', 'pf_order' );
	}
}

// Prefunk order builder

function rg_prefunk_order() {
	$args = array(
		'post_type' => 'prefunk',
		'orderby'	=> 'rand',
		'posts_per_page' => -1
	);

	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) {

		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$order_num++;
			update_post_meta( $the_query->post->ID, 'pf_order', $order_num );
		}
	}
}

add_action( 'rg_prefunk_hook', 'rg_prefunk_order' );

//prepping prefunk scheduler
add_action( 'wp_login', 'rg_set_cron' );

function rg_set_cron() {
	if ( ! wp_next_scheduled( 'rg_prefunk_hook' ) ) {
		wp_schedule_event( time(), 'hourly', 'rg_prefunk_hook' );
	}
}


add_filter('wpseo_opengraph_image', 'rg_prefunk_seo_image', 10, 1);

function rg_prefunk_seo_image($og_image) {
    if( is_post_type_archive('prefunk') )
        $og_image = 'https://runawaygoodness.com/wp-content/uploads/2015/11/FB_wk3.jpg';

   return $og_image;
};


add_action( 'wp_head', 'rg_prefunk_description' );

function rg_prefunk_description() {
	if( is_post_type_archive('prefunk') ) {
		echo '<meta property="og:description" content="Prefunk the Holidays with 200+ books, each just $2.99!" />';
	}
}

// display shareasale tracking pixel
add_filter( 'the_content', 'rg_shareasale_pixel' );

// https://runawaygoodness.com/?ref=sas
function rg_shareasale_pixel( $content ) {
	global $_POST;
	if( is_page( '2991' ) ) {
		if( isset( $_POST['lp-source']) && $_POST['lp-source'] == 'sas' ) {
			$autovoid = '';
		} else {
			$autovoid = '&autovoid=1';
		}

		$html .= '<img src="https://shareasale.com/sale.cfm?amount=0.00&tracking='. md5( $_POST['lp-email'] ) .'&transtype=lead&merchantID=62532'. $autovoid .'" width="1" height="1">';
	} else {
		$html = '';
	}

	

	return $content . $html;
}



// Latest Newsletter shortcode
add_shortcode( 'latest_newsletters', 'latest_newsletters_shortcode' );

function latest_newsletters_shortcode() {
	$args = array(
		'post_type' => 'newsletters',
		'posts_per_page' => 5
	);

	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) {

		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			
			// deal with title
			$title = get_the_title();

			$newsletter_type = strstr( $title, ' Newsletter ', true );

			$row_count = 1;
			while ( have_rows('book') ) : the_row();
				if( $row_count == 1 ) {
					// grab some image information first
					$image = get_sub_field( 'book_cover' );

					if( !empty($image) ) {

						// vars
						$url = $image['url'];
						$title = $image['title'];
						$alt = $image['alt'];
						$caption = $image['caption'];

						// thumbnail
						$size = 'homedeal';
						$thumb = $image['sizes'][ $size ];
						$width = $image['sizes'][ $size . '-width' ];
						$height = $image['sizes'][ $size . '-height' ];
					}
				}
				$row_count++;
			endwhile;



			$html .= '<div class="newsletter_box">';
				$html .= '<strong><a href="' . get_the_permalink() . '">' . $newsletter_type . '</a></strong><br />';
				$html .= '<a href="' . get_the_permalink() . '"><img src="'. $thumb .'"></a><br />';
				$html .= get_field( 'newsletter_send_date' ) . '<br />';
				$html .= '<a href="' . get_the_permalink() . '" class="button">Click To View</a>';
			$html .= '</div>';


		}
	}

	return $html;

}