<?php

remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );

function rg_prefunk_single() {
	echo '<a href="http://amazon.com/dp/'. get_field('amazon_asin') .'">';
	echo get_the_post_thumbnail( get_the_ID(), 'homedeal', array( 'class' => 'alignleft' ) );
	echo '</a>';
	echo '<h1>' . get_the_title() . '</h1>';

	$terms = get_the_terms( $post->ID, 'prefunk_genres' );

	if( $terms && ! is_wp_error( $terms ) ) {

		$term_data = array();

		foreach ( $terms as $term ) {
			$term_data[] = $term->name;
		}
							
		$term_list = join( ", ", $term_data );

		echo '<div>Genre(s): ' . $term_list .'</div>';
	}

	echo '<div><a href="http://amazon.com/dp/'. get_field('amazon_asin').'/?tag=prefunk-20" class="button">Download Now</a></div>';
}



add_action( 'genesis_entry_content', 'rg_prefunk_single' );



genesis();