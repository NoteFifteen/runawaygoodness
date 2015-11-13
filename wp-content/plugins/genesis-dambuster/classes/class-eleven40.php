<?php
class Genesis_Dambuster_Eleven40 extends Genesis_Dambuster_Template {

	function remove_primary_navigation() {
	  remove_action( 'genesis_before_content_sidebar_wrap', 'genesis_do_nav' );        
	  remove_action( 'genesis_header', 'genesis_do_nav', 12);    
     parent::remove_primary_navigation();
	}						

	function remove_secondary_navigation() {
	  remove_action( 'genesis_before_content_sidebar_wrap', 'genesis_do_subnav' );    
     remove_action( 'genesis_footer', 'genesis_do_subnav', 12 );	      
     parent::remove_secondary_navigation();
	}	
	
	function remove_header() {
      remove_action( 'genesis_before_content_sidebar_wrap', 'genesis_seo_site_description' );
      parent::remove_header();
   }
}