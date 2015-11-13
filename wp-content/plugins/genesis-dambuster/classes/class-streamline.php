<?php
class Genesis_Dambuster_Streamline extends Genesis_Dambuster_Template {

   function remove_entry_header() {
      remove_action( 'genesis_entry_header', 'streamline_post_image', 1 );
      parent::remove_entry_header();
   }

   function remove_post_info() {
      remove_action( 'genesis_entry_header', 'genesis_post_info', 2 );
      parent::remove_post_info();
   }
  
   function remove_entry_footer() {
      remove_action( 'genesis_entry_footer', 'streamline_after_entry', 10 );
      parent::remove_entry_footer();
   }       
}