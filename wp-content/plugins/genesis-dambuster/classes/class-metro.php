<?php
class Genesis_Dambuster_Metro extends Genesis_Dambuster_Template {

	function remove_secondary_navigation() {
      remove_action( 'genesis_before', 'genesis_do_subnav' );
      parent::remove_secondary_navigation();	  
	}	

	function remove_footer_widgets() {
      remove_action( 'genesis_after', 'genesis_footer_widget_areas' );
      parent::remove_footer_widgets();      
	}
	
	function remove_footer() {
      remove_action( 'genesis_after', 'genesis_footer_markup_open', 11 );
      remove_action( 'genesis_after', 'genesis_do_footer', 12 );
      remove_action( 'genesis_after', 'genesis_footer_markup_close', 13 );
      parent::remove_footer();
   }
       
}