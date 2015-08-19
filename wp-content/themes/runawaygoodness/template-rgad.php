<?php

/*
Template Name: RGAD Single
*/

//* Add custom body class to the head
add_filter( 'body_class', 'centric_add_body_class' );
function centric_add_body_class( $classes ) {

   $classes[] = 'rgad';
   return $classes;
   
}

//* Run the Genesis loop
genesis();
