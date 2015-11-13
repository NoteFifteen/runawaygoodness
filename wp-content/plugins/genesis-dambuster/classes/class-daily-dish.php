<?php
class Genesis_Dambuster_DailyDish extends Genesis_Dambuster_Template {

	function remove_header() {
      remove_action( 'genesis_before', 'daily_dish_before_header' );
      parent::remove_header();
	}

	function remove_footer_widgets() {
		remove_action('genesis_before_footer', 'daily_dish_before_footer_widgets', 5 );		
      parent::remove_footer_widgets();
	}

   function remove_footer() {
      remove_action('genesis_after', 'daily_dish_after_footer' );
      parent::remove_footer();
   }

}