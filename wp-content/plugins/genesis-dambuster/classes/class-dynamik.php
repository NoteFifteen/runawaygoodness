<?php
class Genesis_Dambuster_Dynamik extends Genesis_Dambuster_Template {

	function remove_primary_navigation() {
      remove_action( 'genesis_before_header', 'dynamik_dropdown_nav_1' );
      remove_action( 'genesis_before_header', 'dynamik_mobile_nav_1' );
      remove_action( 'genesis_after_header', 'dynamik_dropdown_nav_1' );
      remove_action( 'genesis_after_header', 'dynamik_mobile_nav_1', 9 );
      parent::remove_primary_navigation() ;    
	}	

	function remove_secondary_navigation() {
      remove_action( 'genesis_before_header', 'dynamik_mobile_nav_2' );
      remove_action( 'genesis_before_header', 'dynamik_mobile_nav_2' );
      remove_action( 'genesis_after_header', 'dynamik_dropdown_nav_2' );
      remove_action( 'genesis_after_header', 'dynamik_mobile_nav_2');
      parent::remove_secondary_navigation() ;    
	}	

	function remove_header() {
	   remove_action( 'wp_head', 'ez_feature_top_structure' );
      parent::remove_header() ;    
	}	

	function remove_footer_widgets() {
	   remove_action( 'wp_head', 'ez_fat_footer_structure' );
      parent::remove_footer_widgets() ;    
	}

}


