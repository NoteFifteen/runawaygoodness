<?php
class Genesis_Dambuster_Mindstream extends Genesis_Dambuster_Template {


	function remove_post_title() {
      add_action('genesis_before_post', array($this, 'late_remove_post_title'), 20) ; 
	}

	function remove_post_info() {
      add_action('genesis_before_post', array($this, 'late_remove_post_info'), 20) ; 
	}

	function remove_post_meta() {
      add_action('genesis_before_post', array($this, 'late_remove_post_meta'), 20) ; 
	}

	function late_remove_post_title() {
      parent::remove_post_title() ;
	}

	function late_remove_post_info() {
      parent::remove_post_info() ;
	}



	function late_remove_post_meta() {
      parent::remove_post_meta() ;
	}


}