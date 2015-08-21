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


