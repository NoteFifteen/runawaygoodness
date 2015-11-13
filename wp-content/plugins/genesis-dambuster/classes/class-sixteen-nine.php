<?php
class Genesis_Dambuster_SixteenNine extends Genesis_Dambuster_Template {

   function remove_header() {
      remove_theme_support ('custom-header');
     	remove_action( 'genesis_header', 'sixteen_nine_site_gravatar', 5 );
      parent::remove_header();
   }

   function remove_footer() {
      remove_action( 'genesis_header', 'genesis_footer_markup_open', 11 );
      remove_action( 'genesis_header', 'genesis_do_footer', 12 );
      remove_action( 'genesis_header', 'genesis_footer_markup_close', 13 );        
      parent::remove_footer();
	}

	function remove_background() {
	   add_filter( 'pre_option_sixteen-nine-backstretch-image', '__return_empty_string' );
      parent::remove_background();
	} 

}
