<?php
class Genesis_Dambuster_Community extends Genesis_Dambuster_Template {

	function remove_header() {
      remove_action( 'genesis_before', 'genesis_header_markup_open', 5 );
      remove_action( 'genesis_before', 'genesis_do_header' );
      remove_action( 'genesis_before', 'genesis_header_markup_close', 15 );
      parent::remove_header();
	}

   function remove_entry_header() {
		remove_action( 'genesis_after_header', 'community_pro_entry_header' );
      parent::remove_entry_header();
   }

	function remove_primary_navigation() {
		remove_action( 'genesis_before', 'genesis_do_nav', 1 );
		remove_action( 'genesis_before', 'community_pro_site_overlay', 2 );		
		remove_action( 'genesis_before', 'community_pro_menu_button', 14 );			
      parent::remove_primary_navigation();
	}

	function remove_footer_widgets() {
      remove_action( 'genesis_before_footer', 'community_pro_cta_bar', 5 );
      parent::remove_footer_widgets();
	}	

}