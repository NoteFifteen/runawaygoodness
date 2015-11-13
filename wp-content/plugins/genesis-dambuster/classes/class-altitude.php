<?php
class Genesis_Dambuster_Altitude extends Genesis_Dambuster_Template {


	function remove_primary_navigation() {
		remove_action('genesis_header', 'genesis_do_nav', 12  );		
      parent::remove_primary_navigation();
	}
	
	function remove_secondary_navigation() {
		remove_action( 'genesis_header', 'genesis_do_subnav', 5  );		
      parent::remove_secondary_navigation();
	}

}