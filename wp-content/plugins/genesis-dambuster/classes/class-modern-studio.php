<?php
class Genesis_Dambuster_ModernStudio extends Genesis_Dambuster_Template {

	function remove_primary_navigation() {
      remove_action( 'ms_menus', 'genesis_do_nav');
      parent::remove_primary_navigation();	  
	}	

	function remove_secondary_navigation() {
      remove_action( 'ms_menus', 'genesis_do_subnav');
      parent::remove_secondary_navigation();	  
	}	


	function remove_header() {
      remove_action( 'genesis_before', 'ms_sticky_message' );
      parent::remove_header();      
	}

	function remove_footer() {
      remove_action(  'genesis_after', 'genesis_footer_markup_open', 5 );
      remove_action( 'genesis_after', 'genesis_do_footer' );
      remove_action( 'genesis_after', 'genesis_footer_markup_close', 15 );
      parent::remove_header();      
	}
  
}