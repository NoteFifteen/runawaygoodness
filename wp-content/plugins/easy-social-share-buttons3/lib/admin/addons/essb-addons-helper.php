<?php

class ESSBAddonsHelper {
	
	private $cache_options_slug = "essb3_addons";
	private $announced_addons_slug = "essb3_addons_announce";
	private $update_addons_server = "http://addons.appscreo.com";
	
	private static $instance = null;
	public static function get_instance() {
	
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
	
		return self::$instance;
	
	} // end get_instance;
	
	function __construct() {
		//add_action('essb3_addons_remove_update', array($this, 'call_remove_addon_list_update'));
	}
	
	public function schedule_update() {
		if ( ! wp_next_scheduled( 'essb3_addon_update' ) ) {
			wp_schedule_event( time(), 'daily',  'essb3_addon_update');
		}
	}	
	
	public function call_remove_addon_list_update() {
		$url = $this->update_addons_server;
		$result = wp_remote_get($url);
		
		$success_connection = true;
		
		if(is_wp_error($result) or (wp_remote_retrieve_response_code($result) != 200)){
			$success_connection = false;
		}
			
		/* Check for incorrect data */
		if ($success_connection) {
			$info = unserialize(wp_remote_retrieve_body($result));
			if (is_array($info)) {	
				update_option($this->cache_options_slug, $info);
			}
		}
	}
	
	public function get_addons() {
		$addons = get_option($this->cache_options_slug);
		
		return $addons;
	}
	
	public function get_new_addons() {
		if (false === get_transient($this->cache_options_slug)) {
			$this->call_remove_addon_list_update();
			delete_transient($this->cache_options_slug);
			set_transient( $this->cache_options_slug, 'updated', 72 * HOUR_IN_SECONDS );
		}
		
		$addons = $this->get_addons();
		
		if (!is_array($addons)) {
			$addons = array();
		}
		
		$current_announced = get_option($this->announced_addons_slug);
		if (!is_array($current_announced)) {
			$current_announced = array();
		}
		
		$list_of_new = array();
		foreach ($addons as $addon_key => $addon_data) {
			if (!isset($current_announced[$addon_key])) {
				$list_of_new[$addon_key] = array("title" => $addon_data['name'], "url" => $addon_data['page']);
			}
		}
		
		return $list_of_new;
	}	
	
	public static function deactivate() {
		wp_clear_scheduled_hook('essb3_addon_update');
	}
	
	public function dismiss_addon_notice($addon) {
		$current_announced = get_option($this->announced_addons_slug);
		if (!is_array($current_announced)) {
			$current_announced = array();
		}
		
		$current_announced[$addon] = "yes";
		
		update_option($this->announced_addons_slug, $current_announced);
	}
}

?>