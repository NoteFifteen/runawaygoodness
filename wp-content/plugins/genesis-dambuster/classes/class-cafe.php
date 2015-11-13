<?php
class Genesis_Dambuster_Cafe extends Genesis_Dambuster_Template {

	function remove_header() {
      remove_action( 'genesis_before_header', 'cafe_before_header' );
      parent::remove_header();
	}

	function remove_secondary_navigation() {
		remove_action('genesis_before_header', 'genesis_do_subnav', 11 );		
      parent::remove_secondary_navigation();
	}


   function remove_footer() {
      remove_action('genesis_footer', 'rainmaker_footer_menu', 7 );
      parent::remove_footer();
   }

}