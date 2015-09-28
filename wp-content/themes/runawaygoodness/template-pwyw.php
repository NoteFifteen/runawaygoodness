<?php

/*
Template Name: PWYW Single
*/

/** Remove default sidebar */
remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );

add_action( 'genesis_sidebar', 'rg_pwyw_sidebar' );

/** Display PWYW Sidebar */
function rg_pwyw_sidebar() {
	if ( !function_exists( 'dynamic_sidebar' ) || !dynamic_sidebar( 'PWYW Sidebar' ) ) {
}}


//* Run the Genesis loop
genesis();