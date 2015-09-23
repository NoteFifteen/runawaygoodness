<?php

class ESSBCacheDetector {
	
	public static function is_w3_plugin_detected() {
		return defined ( 'W3TC' );
	}
	
	public static function is_wp_super_cache_detected() {
		return function_exists ( 'wp_cache_set_home' );
	}
	
	public static function is_wordfence_detected() {
		return defined ( 'WORDFENCE_VERSION' );
	}
	
	public static function is_wp_rocket_detected() {
		return defined ( 'WP_ROCKET_VERSION' );
	}
	
	public static function is_cache_plugin_detected() {
		return self::is_w3_plugin_detected () || self::is_wp_super_cache_detected () || self::is_wordfence_detected () || self::is_wp_rocket_detected ();
	}
	
	public static function cache_plugin_name() {
		if (self::is_w3_plugin_detected ()) {
			return "W3 Total Cache";
		} else if (self::is_wp_super_cache_detected ()) {
			return "WP Super Cache";
		} else if (self::is_wordfence_detected ()) {
			return "WordFence";
		} else if (self::is_wp_rocket_detected ()) {
			return "WP Rocket";
		}
	}
}

?>