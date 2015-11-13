<?php
class Genesis_Dambuster_Wintersong extends Genesis_Dambuster_Template {


	function remove_header() {
		remove_action('genesis_header', 'wintersong_site_gravatar', 5  );	
      remove_action( 'genesis_header', 'genesis_footer_markup_open', 11 );
      remove_action( 'genesis_header', 'genesis_do_footer', 12 );
      remove_action( 'genesis_header', 'genesis_footer_markup_close', 13 );   			
      parent::remove_header();
	}
	
	function remove_secondary_navigation() {
		remove_action( 'genesis_header', 'genesis_do_subnav', 5  );		
      parent::remove_secondary_navigation();
	}

}