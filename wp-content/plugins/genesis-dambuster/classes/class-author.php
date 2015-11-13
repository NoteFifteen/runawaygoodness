<?php
class Genesis_Dambuster_Author extends Genesis_Dambuster_Template {

	function remove_primary_navigation() {
		remove_action('genesis_header', 'genesis_do_nav', 12  );		
      parent::remove_primary_navigation();
	}
	
	function remove_secondary_navigation() {
		remove_action( 'genesis_before_content_sidebar_wrap', 'genesis_do_subnav' );
      parent::remove_secondary_navigation();
	}


}