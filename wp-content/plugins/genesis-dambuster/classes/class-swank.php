<?php
class Genesis_Dambuster_Swank extends Genesis_Dambuster_Template {

	function remove_header() {
      remove_action( 'genesis_before_header', 'swank_top_bar' );
      parent::remove_header();      
	}
	
	function remove_post_info() {
      remove_action( 'genesis_post_info', 'swank_post_info_filter' );
      parent::remove_post_info();
   }

	function remove_secondary_navigation() {
      remove_action( 'genesis_before_footer', 'genesis_do_subnav' );
      parent::remove_secondary_navigation();
   }
       
}