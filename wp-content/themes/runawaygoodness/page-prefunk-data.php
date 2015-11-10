<?php

function pull_prefunk(){
	$args = array(
		'post_type' => 'prefunk',
		'posts_per_page' => -1,
		'tax_query' => array(
			'relation' => 'or',
			array(
				'taxonomy' => 'prefunk_genres',
				'field'    => 'slug',
				'terms'    => 'literary-fiction',
			),
			array(
				'taxonomy' => 'prefunk_genres',
				'field'    => 'slug',
				'terms'    => 'historical-fiction',
			),
			array(
				'taxonomy' => 'prefunk_genres',
				'field'    => 'slug',
				'terms'    => 'x',
			),
			array(
				'taxonomy' => 'prefunk_genres',
				'field'    => 'slug',
				'terms'    => 'x',
			),
		),
	);
	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		echo '<br />';
		while ( $query->have_posts() ) {
			$query->the_post();
			the_title();
			echo '<br />';		
		}
	}
	/* Restore original Post Data */
	wp_reset_postdata();
}

add_action( 'genesis_entry_content', 'pull_prefunk' );

genesis();