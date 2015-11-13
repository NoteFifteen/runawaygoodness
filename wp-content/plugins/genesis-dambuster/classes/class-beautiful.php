<?php
class Genesis_Dambuster_Beautiful extends Genesis_Dambuster_Template {

	function remove_header() {
      remove_action( 'genesis_before_header', 'beautiful_before_header' );
      remove_action( 'genesis_after_header', 'beautiful_site_header_banner' );
      parent::remove_header();
	}

	function remove_secondary_navigation() {
		remove_action( 'genesis_after_header', 'genesis_do_subnav', 15 );		
      parent::remove_secondary_navigation();
	}


   function remove_entry_header() {
      remove_action( 'genesis_before_loop', 'beautiful_welcome_message' );
      parent::remove_entry_header();
   }

}