<?php

remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
add_action( 'genesis_entry_content', 'do_genres_entry_content' );

function do_genres_entry_content() {
	echo get_the_post_thumbnail ( get_the_ID(), 'homedeal', array( 'class' => 'alignleft' ) );
	the_title( '<h2>', '</h2>' );
	echo '<p>by ' . get_post_meta( get_the_ID(), 'author', true ) . '</p>';
	echo '<p>' . get_post_meta( get_the_ID(), 'blurb', true ) . '</p>';
	echo '<p>';
	// if has orig price
	echo get_post_meta( get_the_ID(), 'regular_price', true );
	echo get_post_meta( get_the_ID(), 'on_sale_price', true );
	echo '</p>';
	echo '<p>Sale ends '. get_post_meta( get_the_ID(), 'end_date', true ) .'</p>';
	echo '<p><a href="http://amazon.com/'. get_post_meta( get_the_ID(), 'amazon_id', true ) .'">Buy Now</a></p>';

	$args = array(
	'show_option_all'    => '',
	'orderby'            => 'name',
	'order'              => 'ASC',
	'style'              => 'list',
	'show_count'         => 0,
	'hide_empty'         => 1,
	'use_desc_for_title' => 1,
	'child_of'           => 0,
	'feed'               => '',
	'feed_type'          => '',
	'feed_image'         => '',
	'exclude'            => '',
	'exclude_tree'       => '',
	'include'            => '',
	'hierarchical'       => 1,
	'title_li'           => __( 'Genres' ),
	'show_option_none'   => __( '' ),
	'number'             => null,
	'echo'               => 1,
	'depth'              => 0,
	'current_category'   => 0,
	'pad_counts'         => 0,
	'taxonomy'           => 'deal_genres',
	'walker'             => null
    );

	echo '<p>Genres: '. wp_list_categories( $args ) . '</p>';

}






//* Run the Genesis loop
genesis();
