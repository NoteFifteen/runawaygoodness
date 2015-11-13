<?php
class Genesis_Dambuster_Ambiance extends Genesis_Dambuster_Template {


   function remove_header() {
      remove_action( 'genesis_before_content_sidebar_wrap', 'ambiance_welcome_message' );
      remove_action( 'genesis_after_header', 'ambiance_entry_background' );
      remove_action( 'genesis_after', 'ambiance_set_background_image' );
      add_action ('wp_enqueue_scripts', array($this, 'dequeue_backstretch'), 20);
      parent::remove_header();
   }

   function dequeue_backstretch() {
      wp_dequeue_script( 'ambiance-backstretch' );
		wp_dequeue_script( 'ambiance-backstretch-set' );
   }


   function remove_entry_header() {
      remove_action( 'genesis_entry_header', 'ambiance_gravatar', 7 );
      parent::remove_entry_header();      
   }

   function remove_post_image() {
      remove_action( 'genesis_entry_header', 'genesis_do_post_image', 3 );
      parent::remove_post_image();      
   }

   function remove_after_entry() {
      remove_action( 'genesis_after_entry',  'genesis_prev_next_post_nav', 9  );
      parent::remove_after_entry();      
   }



}