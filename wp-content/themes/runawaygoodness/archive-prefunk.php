<?php

remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );

add_action( 'genesis_entry_content', 'rg_display_prefunk_books' );
function rg_display_prefunk_books() {
	echo '<div class="singlebook">';
		$amazon_link = 'https://amazon.com/dp/'. get_post_meta( get_the_ID(), 'amazon_asin', TRUE );
		echo '<a href="'. $amazon_link .'" target="_blank">';
			echo get_the_post_thumbnail( get_the_ID(), 'homedeal' );
		echo '</a>';
		echo '<br />';
		echo '<a href="'. $amazon_link .'" target="_blank">';
			echo get_the_title();
		echo '</a>';
	echo '</div>';
}

add_action( 'genesis_before_loop', 'rg_prefunk_head' );
function rg_prefunk_head() {
	echo '<div class="prefunkhead">';
	echo '<h1>Prefunk The Holidays!</h1>';
	echo '<h2>200+ Books, All Free!</h2>';
	echo '</div>';
}


//* Run the Genesis loop
genesis();
