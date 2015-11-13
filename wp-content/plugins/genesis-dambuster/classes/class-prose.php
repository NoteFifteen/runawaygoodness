<?php
class Genesis_Dambuster_Prose extends Genesis_Dambuster_Template {

	function remove_after_entry() {
      remove_action( 'genesis_after_post_content', 'prose_after_post' );
      parent::remove_after_entry();
	}

	function remove_footer() {
      remove_action( 'genesis_after', 'genesis_footer_markup_open', 5 );
      remove_action( 'genesis_after', 'genesis_do_footer');
      remove_action ('genesis_after', 'genesis_footer_markup_close', 15 );
      parent::remove_footer();
	}

}