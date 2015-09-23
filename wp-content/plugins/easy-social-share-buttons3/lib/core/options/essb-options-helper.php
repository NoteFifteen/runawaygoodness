<?php

class ESSBOptionValuesHelper {
	public static function options_value($optionsContainer, $param, $default = '') {
		return isset ( $optionsContainer [$param] ) ? $optionsContainer [$param]  : $default;
	}
	
	public static function options_bool_value($optionsContainer, $param) {
		$value = isset ( $optionsContainer [$param] ) ? $optionsContainer [$param]  : 'false';
	
		if ($value == "true") {
			return true;
		}
		else {
			return false;
		}
	
	}
	
	public static function is_active_module($module = '') {
		global $essb_options;
		
		$is_active = false;
		
		switch ($module) {
			case "sso":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'opengraph_tags') ||
				ESSBOptionValuesHelper::options_bool_value($essb_options, 'twitter_card') ||
				ESSBOptionValuesHelper::options_bool_value($essb_options, 'sso_google_author') ||
				ESSBOptionValuesHelper::options_bool_value($essb_options, 'sso_google_markup')) {
					$is_active = true;
				}
				break;
			case "ssanalytics":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'stats_active')) {
					$is_active = true;
				}
				break;
			case "mycred":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'mycred_activate')) {
					$is_active = true;
				}
				break;
			case "aftershare":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'afterclose_active')) {
					$is_active = true;
				}
				break;
			case "imageshare":
				$positions = ESSBOptionValuesHelper::options_value($essb_options, 'button_position');
				
				if (is_array($positions)) {
					if (in_array('onmedia', $positions)) {
						$is_active = true;
					}
				}
				break;
			case "loveyou":
				$is_active = true;
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'module_off_lv')) {
					$is_active = false;
				}
				break;
			case "socialprofiles":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'profiles_display')) {
					$is_active = true;
				}					
				break;
			case "socialfans":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'fanscounter_active')) {
					$is_active = true;
				}
				break;
			case "native":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'native_active')) {
					$is_active = true;
				}
				break;
			case "cachedynamic":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'essb_cache')) {
					$is_active = true;
				}
				break;
			case "cachestatic":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'essb_cache_static') || ESSBOptionValuesHelper::options_bool_value($essb_options, 'essb_cache_static_js')) {
					$is_active = true;
				}	
				break;
			case "metricslite":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'esml_active')) {
					$is_active = true;
				}
				break;
			case "ctt":
				$is_active = true;
				
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'deactivate_ctt')) {
					$is_active = false;
				}
				break;
			case "topsocialposts":
				if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'esml_top_posts_widget')) {
					$is_active = true;
				}
				break;
		}
		
		return $is_active;
	}
	
	
	public static function is_active_position_settings ($position = '') {
		global $essb_options;
		
		$result = false;

		$key = $position.'_activate';
		if (ESSBOptionValuesHelper::options_bool_value($essb_options, $key)) {
			$result = true;
		}
		
		return $result;
	
	}
	
	public static function apply_position_style_settings($postion, $basic_style) {
		global $essb_options;
		
		if (ESSBOptionValuesHelper::options_value($essb_options, $postion.'_template') != "") {
			$basic_style['template'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_template');
		}
		$basic_style['button_style'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_button_style');
		$basic_style['button_align'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_button_align');
		$basic_style['button_width'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_button_width');
		$basic_style['nospace'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_nospace');
		
		$basic_style['button_width_fixed_value'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_fixed_width_value');
		$basic_style['button_width_fixed_align'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_fixed_width_align');
		$basic_style['button_width_full_container'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_fullwidth_share_buttons_container');
		$basic_style['button_width_full_button'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_fullwidth_share_buttons_correction');
		$basic_style['button_width_full_button_mobile'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_fullwidth_share_buttons_correction_mobile');
		$basic_style['button_width_columns'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_fullwidth_share_buttons_columns');
		$basic_style['show_counter'] = ESSBOptionValuesHelper::options_bool_value($essb_options, $postion.'_show_counter');
		$basic_style['counter_pos'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_counter_pos');
		$basic_style['total_counter_pos'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_total_counter_pos');
		
		$basic_style['fullwidth_align'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_fullwidth_align');
		$basic_style['fullwidth_share_buttons_columns_align'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_fullwidth_share_buttons_columns_align');

		// @since 3.0.3
		$more_button_icon = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_more_button_icon');
		if ($more_button_icon != '') {
			$basic_style['more_button_icon'] = $more_button_icon;
		}
		
		
		return $basic_style;
	}
	
	public static function apply_mobile_position_style_settings($postion, $basic_style) {
		global $essb_options;
	
		if (ESSBOptionValuesHelper::options_value($essb_options, $postion.'_template') != "") {
			$basic_style['template'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_template');
		}
		
		if ($position != 'sharebottom') {
			$basic_style['nospace'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_nospace');
			$basic_style['show_counter'] = ESSBOptionValuesHelper::options_bool_value($essb_options, $postion.'_show_counter');
			$basic_style['counter_pos'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_counter_pos');
			$basic_style['total_counter_pos'] = ESSBOptionValuesHelper::options_value($essb_options, $postion.'_total_counter_pos');
		}
		return $basic_style;
	}
	
	public static function get_active_social_networks_by_position($position) {
		global $essb_options;
		
		$result = array();
		
		$result = ESSBOptionValuesHelper::options_value($essb_options, $position.'_networks');
		if (!is_array($result)) { $result = array(); }
		
		return $result;
	}
	
	public static function get_order_of_social_networks_by_position($position) {
		global $essb_options;
		
		$ordered_list = array();
		
		$result = ESSBOptionValuesHelper::options_value($essb_options, $position.'_networks_order');
		if (!is_array($result)) {
			$result = array();
		}
		
		foreach ($result as $text_values) {
			$key_array = explode('|', $text_values);
			$network_key = $key_array[0];
			
			$ordered_list[] = $network_key;
		}
		
		return $ordered_list;
		
	}
	
	public static function apply_position_network_names($position, $network_names) {
		global $essb_options, $essb_networks;

		foreach ($essb_networks as $key => $object) {
			$search_for = $position."_".$key."_name";
			$user_network_name = ESSBOptionValuesHelper::options_value($essb_options, $search_for);
			if ($user_network_name != '') {
				$network_names[$key] = $user_network_name;
			}
		}
		
		return $network_names;
	}
	
	public static function advanced_array_to_simple_array($values) {
		$new = array();
		
		foreach ($values as $key => $text) {
			$new[] = $key;
		}
		
		return $new;
	}
	
	public static function unified_true($value) {
		$result = '';
		
		if ($value == 'true' || $value == 'yes') {
			$result = true;
		}
		else {
			$result = false;		
		}
		
		return $result;
	}
}

?>