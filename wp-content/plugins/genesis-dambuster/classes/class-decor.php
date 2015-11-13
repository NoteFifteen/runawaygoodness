<?php
class Genesis_Dambuster_Decor extends Genesis_Dambuster_Template {

	function remove_primary_navigation() {
	  remove_action( 'genesis_before', 'genesis_do_nav' );         
     parent::remove_primary_navigation();
	}						

	function remove_entry_header() {
      remove_action( 'genesis_before_post_title', 'decor_start_post_wrap' );
      remove_action( 'genesis_before_post_title', 'decor_post_image' );      
      remove_action( 'genesis_after_post_content', 'decor_end_post_wrap' );    
      parent::remove_entry_header();
	}	

}