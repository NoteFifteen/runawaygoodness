<?php
class Genesis_Dambuster_The411 extends Genesis_Dambuster_Template {

    function remove_header() {
        remove_action( 'genesis_after_header', 'the_411_extras' );
        parent::remove_header();
    } 

   function remove_post_title() {
      add_action('genesis_before_entry', array($this, 'late_remove_post_title'), 20) ; //remove post title later
	}

   function remove_post_info() {
      add_action('genesis_before_entry', array($this, 'late_remove_post_info'), 20) ; //remove post info later
	}

   function remove_post_meta() {
      add_action('genesis_before_entry', array($this, 'late_remove_post_meta'), 20) ; //remove post meta later
	}
	
   function remove_entry_header() {
      add_action('genesis_before_entry', array($this, 'late_remove_entry_header'), 20) ; //remove entry header later
	}

   function remove_entry_footer() {
      add_action('genesis_before_entry', array($this, 'late_remove_entry_footer'), 20) ; //remove entry footer later
	}

    function enqueue_full_width_styles() {
        wp_dequeue_script( 'the-411-backstretch-set' );
        parent::enqueue_full_width_styles();
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

	function late_remove_entry_header() {
      parent::remove_entry_header() ;
	}

	function late_remove_entry_footer() {
      parent::remove_entry_footer() ;
	}

}
