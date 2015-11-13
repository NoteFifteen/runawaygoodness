<?php
class Genesis_Dambuster_Outreach extends Genesis_Dambuster_Template {

	function remove_footer_widgets() {
      remove_action( 'genesis_before_footer', 'outreach_sub_footer', 5);
      parent::remove_footer_widgets();      
	}
       
}