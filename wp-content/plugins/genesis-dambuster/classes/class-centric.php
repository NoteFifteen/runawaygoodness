<?php
class Genesis_Dambuster_Centric extends Genesis_Dambuster_Template {

	function remove_post_title() {
	  remove_action( 'genesis_before', 'centric_post_title' );  
	  parent::remove_post_title() ;    
	}	

}