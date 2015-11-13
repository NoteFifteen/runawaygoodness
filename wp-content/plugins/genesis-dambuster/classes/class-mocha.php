<?php
class Genesis_Dambuster_Mocha extends Genesis_Dambuster_Template {

	function remove_after_entry() {
      remove_action( 'genesis_after_post_content', 'mocha_after_post' );
      parent::remove_after_entry();
	}

}