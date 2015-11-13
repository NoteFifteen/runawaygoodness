<?php
class Genesis_Dambuster_Expose extends Genesis_Dambuster_Template {

	function remove_primary_navigation() {
      remove_action( 'genesis_before', 'genesis_do_nav');
      parent::remove_primary_navigation();
	}

	function remove_header() {
      remove_action( 'genesis_header', 'expose_site_gravatar', 5 );
      parent::remove_header();
	}

	function remove_post_title() {
      add_action('genesis_before_entry', array($this, 'late_remove_post_title'), 20) ; //remove post title later
	}

	function remove_post_meta() {
      add_action('genesis_before_entry', array($this, 'late_remove_post_meta'), 20) ; //remove post meta later
	}

	function remove_entry_footer() {
      add_action('genesis_before_entry', array($this, 'late_remove_entry_footer'), 20) ; //remove entry footer later
	}

	function late_remove_post_title() {
      parent::remove_post_title() ;
	}

	function late_remove_post_meta() {
      parent::remove_post_meta() ;
	}

	function late_remove_entry_footer() {
      remove_action( 'genesis_entry_footer', 'genesis_prev_next_post_nav' );
      parent::remove_entry_footer() ;
	}

}