<?php
class Genesis_Dambuster_Epik extends Genesis_Dambuster_Template {

	function remove_header() {
      remove_action( 'genesis_before_header', 'before_header_wrap' );
      remove_action( 'genesis_after_header', 'after_header_wrap' );      
      parent::remove_header();
	}

}