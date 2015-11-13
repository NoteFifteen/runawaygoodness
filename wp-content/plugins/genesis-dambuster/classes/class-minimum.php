<?php
class Genesis_Dambuster_Minimum extends Genesis_Dambuster_Template {

	function remove_primary_navigation() {
      remove_action( 'genesis_after_header', 'genesis_do_nav', 15 );
      parent::remove_primary_navigation();	  
	}	

	function remove_header() {
      remove_action( 'genesis_after_header', 'minimum_site_tagline' );
      parent::remove_header();      
	}
       
}