<?php
class Genesis_Dambuster_Agency extends Genesis_Dambuster_Template {

	function remove_header() {
      remove_action( 'genesis_before', 'genesis_header_markup_open', 5 );
      remove_action( 'genesis_before', 'genesis_do_header' );
      remove_action( 'genesis_before', 'genesis_header_markup_close', 15 );
      parent::remove_header();
	}

	function remove_secondary_navigation() {
	  remove_action( 'genesis_footer', 'genesis_do_subnav', 7 );
     parent::remove_header();
	}	


	function enqueue_full_width_styles() {
      wp_dequeue_script( 'agency-pro-backstretch-set' );
      parent::enqueue_full_width_styles();
	} 

	
}