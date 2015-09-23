<?php

class ESSBCoreHelper {
	public static function generate_network_list() {
		global $essb_networks;
		
		$network_order = array();
		
		foreach ($essb_networks as $key => $data) {
			$network_order[] = $key;
		}
		
		return $network_order;
	}
	
	public static function template_folder ($template_id) {
		$folder = 'default';
	
		if ($template_id == 1) {
			$folder = "default";
		}
		if ($template_id == 2) {
			$folder = "metro";
		}
		if ($template_id == 3) {
			$folder = "modern";
		}
		if ($template_id == 4) {
			$folder = "round";
		}
		if ($template_id == 5) {
			$folder = "big";
		}
		if ($template_id == 6) {
			$folder = "metro-retina";
		}
		if ($template_id == 7) {
			$folder = "big-retina";
		}
		if ($template_id == 8) {
			$folder = "light-retina";
		}
		if ($template_id == 9) {
			$folder = "flat-retina";
		}
		if ($template_id == 10) {
			$folder = "tiny-retina";
		}
		if ($template_id == 11) {
			$folder = "round-retina";
		}
		if ($template_id == 12) {
			$folder = "modern-retina";
		}
		if ($template_id == 13) {
			$folder = "circles-retina";
		}
		if ($template_id == 14) {
			$folder = "blocks-retina";
		}
		if ($template_id == 15) {
			$folder = "dark-retina";
		}
		if ($template_id == 16) {
			$folder = "grey-circles-retina";
		}
		if ($template_id == 17) {
			$folder = "grey-blocks-retina";
		}
		if ($template_id == 18) {
			$folder = "clear-retina";
		}
		if ($template_id == 19) {
			$folder = "copy-retina";
		}
		if ($template_id == 20) {
			$folder = "dimmed-retina";
		}
		if ($template_id == 21) {
			$folder = "grey-retina";
		}
		if ($template_id == 22) {
			$folder = "default-retina";
		}
		
		// fix when using template_slug instead of template_id
		if (intval($template_id) == 0 && $template_id != '') {
			$folder = $template_id;
		}
	
		return $folder;
	}
	
	public static function urlencode($str) {
		$str = str_replace(" ", "%20", $str);
		$str = str_replace("'", "%27", $str);
		$str = str_replace("\"", "%22", $str);
		$str = str_replace("#", "%23", $str);
		$str = str_replace("+", "%2B", $str);
		$str = str_replace("$", "%24", $str);
		$str = str_replace("&", "%26", $str);
		$str = str_replace(",", "%2C", $str);
		$str = str_replace("/", "%2F", $str);
		$str = str_replace(":", "%3A", $str);
		$str = str_replace(";", "%3B", $str);
		$str = str_replace("=", "%3D", $str);
		$str = str_replace("?", "%3F", $str);
		$str = str_replace("@", "%40", $str);
	
		return $str;
	}
	
	public static function generate_list_networks($all_networks = false) {
		global $essb_networks, $essb_options;
		$networks = array();
		
		$listOfNetworks = ($all_networks) ? self::generate_network_list() : ESSBOptionValuesHelper::options_value($essb_options, 'networks');
		
		foreach ($listOfNetworks as $single) {
			if ($single != 'more') {
				$networks[] = $single;
			}
		}
		
		return $networks;
	}
	
	public static function generate_fullwidth_key($style) {
		
	}
	
	public static function is_plugin_deactivated_on() {
		global $essb_options;
		
	
		if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'reset_postdata')) {
			wp_reset_postdata();
		}
	
		//display_deactivate_on
		$is_deactivated = false;
		$exclude_from = ESSBOptionValuesHelper::options_value($essb_options, 'display_deactivate_on');
		if (!empty($exclude_from)) {
			$excule_from = explode(',', $exclude_from);
				
			$excule_from = array_map('trim', $excule_from);
			if (in_array(get_the_ID(), $excule_from, false)) {
				$is_deactivated = true;
			}
		}
		return $is_deactivated;
	}
	
	public static function is_module_deactivate_on($module = 'share') {
		global $essb_options;
		
		
		if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'reset_postdata')) {
			wp_reset_postdata();
		}
		
		//display_deactivate_on
		$is_deactivated = false;
		$exclude_from = ESSBOptionValuesHelper::options_value($essb_options, 'deactivate_on_'.$module);
		if (!empty($exclude_from)) {
			$excule_from = explode(',', $exclude_from);
		
			$excule_from = array_map('trim', $excule_from);
			if (in_array(get_the_ID(), $excule_from, false)) {
				$is_deactivated = true;
			}
		}
		return $is_deactivated;
	}
}

?>