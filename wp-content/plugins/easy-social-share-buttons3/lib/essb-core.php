<?php

class ESSBCore {
	private $resource_builder;
	private $mobile_detect;

	// options container
	private $options;
	private $design_options = array();
	private $network_options = array();
	private $button_style = array();
	private $general_options = array();
	
	private $list_of_activated_locations = array();
	private $temporary_decativated_locations = array();
	
	private $activated_resources = array();
	private $use_minified_js = false;
	private $use_minified_css = false;
	
	private static $instance = null;
	
	public static function get_instance() {
	
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
	
		return self::$instance;
	
	} // end get_instance;
	
	function __construct() {
		global $essb_options;
		$this->resource_builder = ESSBResourceBuilder::get_instance();
		$this->options = $essb_options;
		
		// load settings and defaults
		$this->load();
				
		//$this->register_assets();		
		
		$this->register_locations();
		
		add_action ( 'wp_ajax_nopriv_essb_self_postcount', array ($this, 'actions_update_post_count' ) );
		add_action ( 'wp_ajax_essb_self_postcount', array ($this, 'actions_update_post_count' ) );
		
		add_action ( 'wp_ajax_nopriv_essb_counts', array ($this, 'actions_get_share_counts' ) );
		add_action ( 'wp_ajax_essb_counts', array ($this, 'actions_get_share_counts' ) );
		
		add_action ( 'template_redirect', array ($this, 'essb_proccess_light_ajax' ), 1 );
		
		//add_action ( 'wp_enqueue_scripts', array ($this, 'check_after_postload_settings' ), 1 );
		add_action ( 'wp_enqueue_scripts', array ($this, 'register_assets' ), 1 );
		
		// shortcodes
		add_shortcode('easy-profiles', array($this, 'essb_shortcode_profiles'));
		add_shortcode('easy-social-like', array($this, 'essb_shortcode_native'));
		
		add_shortcode ( 'essb', array ($this, 'essb_shortcode_share' ) );
		add_shortcode ( 'easy-share', array ($this, 'essb_shortcode_share' ) );
		add_shortcode ( 'easy-social-share-buttons', array ($this, 'essb_shortcode_share' ) );
		
		add_shortcode ( 'easy-social-share', array ($this, 'essb_shortcode_share_vk' ) );
		add_shortcode ( 'easy-total-shares', array($this, 'essb_shortcode_total_shares'));
		add_shortcode ( 'easy-social-share-popup', array($this, 'essb_shortcode_share_popup'));
		add_shortcode ( 'easy-social-share-flyin', array($this, 'essb_shortcode_share_flyin'));
	}
		
	public function check_after_postload_settings() {
		global $post, $essb_available_button_positions;

		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}
		
		if ($this->is_plugin_deactivated_on() || ESSBCoreHelper::is_module_deactivate_on('share')) {
			$this->deactivate_stored_filter_and_actions();
		}
		
		if (!isset($post)) { return; }

		// loading custom post settings		
		$essb_post_button_style = get_post_meta($post->ID, 'essb_post_button_style', true);
		$essb_post_template = get_post_meta($post->ID, 'essb_post_template', true);
		$essb_post_counters = get_post_meta($post->ID, 'essb_post_counters', true);
		$essb_post_counter_pos = get_post_meta($post->ID, 'essb_post_counter_pos', true);
		$essb_post_total_counter_pos = get_post_meta($post->ID, 'essb_post_total_counter_pos', true);
		$essb_post_customizer = get_post_meta($post->ID, 'essb_post_customizer', true);
		$essb_post_animations = get_post_meta($post->ID, 'essb_post_animations', true);
		$essb_post_optionsbp = get_post_meta($post->ID, 'essb_post_optionsbp', true);
		$essb_post_content_position = get_post_meta($post->ID, 'essb_post_content_position', true);
		
		$essb_post_button_position = array();
		foreach ( $essb_available_button_positions as $position => $name ) {
			$position_value =  get_post_meta($post->ID, 'essb_post_button_position_'.$position, true);
			if ($position_value != '') {
				$essb_post_button_position[$position] = $position_value;
			}
		}

		$essb_post_native = get_post_meta($post->ID, 'essb_post_native', true);
		$essb_post_native_skin = get_post_meta($post->ID, 'essb_post_native_skin', true);
		
		if (!empty($essb_post_button_style)) {
			$this->design_options['button_style'] = $essb_post_button_style;
		}
		if (!empty($essb_post_template)) {
			if ($essb_post_template != $this->design_options['template_slug']) {
				$this->design_options['template_slug'] = $template_slug;
				
				$template_url = ESSB3_PLUGIN_URL.'/assets/css/'.$template_slug.'/easy-social-share-buttons'.$this->use_minified_css.'.css';
				$this->resource_builder->add_static_resource($template_url, 'easy-social-share-buttons', 'css');
			}
		}
		
		if (!empty($essb_post_counters)) {
			$this->button_style['show_counter'] = ESSBOptionValuesHelper::unified_true($essb_post_counters);
		}
		if (!empty($essb_post_counter_pos)) {
			$this->button_style['counter_pos'] = $essb_post_counter_pos;
		}
		if (!empty($essb_post_total_counter_pos)) {
			$this->button_style['total_counter_pos'] = $essb_post_total_counter_pos;
		}
		if (!empty($essb_post_animations)) {
			if ($essb_post_animations == "no") {
				$this->resource_builder->remove_css('essb-css-animations');
			}
			else {
				$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_animation_code($css_animations), 'essb-css-animations');
			}
		}
	}
	
	public function register_assets() {
		global $post, $essb_options, $essb_available_button_positions;				
		
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}

		if ($this->is_plugin_deactivated_on() || ESSBCoreHelper::is_module_deactivate_on('share')) {
			$this->deactivate_stored_filter_and_actions();
			return;
		}
		
		$essb_post_button_style = "";
		$essb_post_template = "";
		$essb_post_counters = "";
		$essb_post_counter_pos = "";
		$essb_post_total_counter_pos = "";
		$essb_post_customizer = "";
		$essb_post_animations = "";
		$essb_post_content_position = "";
			
		$essb_post_button_position = array();
			
		$essb_post_native = "";
		$essb_post_native_skin = "";
		
		if (isset($post)) {
			$essb_post_button_style = get_post_meta($post->ID, 'essb_post_button_style', true);
			$essb_post_template = get_post_meta($post->ID, 'essb_post_template', true);
			$essb_post_counters = get_post_meta($post->ID, 'essb_post_counters', true);
			$essb_post_counter_pos = get_post_meta($post->ID, 'essb_post_counter_pos', true);
			$essb_post_total_counter_pos = get_post_meta($post->ID, 'essb_post_total_counter_pos', true);
			$essb_post_animations = get_post_meta($post->ID, 'essb_post_animations', true);
			$essb_post_optionsbp = get_post_meta($post->ID, 'essb_post_optionsbp', true);
			$essb_post_content_position = get_post_meta($post->ID, 'essb_post_content_position', true);
			
			$essb_post_button_position = array();
			foreach ( $essb_available_button_positions as $position => $name ) {
				$position_value =  get_post_meta($post->ID, 'essb_post_button_position_'.$position, true);
				if ($position_value != '') {
					$essb_post_button_position[$position] = $position_value;
				}
			}
			
			$essb_post_native = get_post_meta($post->ID, 'essb_post_native', true);
			$essb_post_native_skin = get_post_meta($post->ID, 'essb_post_native_skin', true);
			
			// apply to general settings
			if (!empty($essb_post_button_style)) {
				$this->design_options['button_style'] = $essb_post_button_style;
			}
			if (!empty($essb_post_counters)) {
				$this->button_style['show_counter'] = ESSBOptionValuesHelper::unified_true($essb_post_counters);
			}
			if (!empty($essb_post_counter_pos)) {
				$this->button_style['counter_pos'] = $essb_post_counter_pos;
			}
			if (!empty($essb_post_total_counter_pos)) {
				$this->button_style['total_counter_pos'] = $essb_post_total_counter_pos;
			}
			
			if (!empty($essb_post_optionsbp)) {
				define ('ESSB3_POST_OPTIONSBP', $essb_post_optionsbp);
			}
			if (!empty($essb_post_native)) {
				$essb_options['native_active'] = ESSBOptionValuesHelper::unified_true($essb_post_native);
				
				if ($essb_options['native_active']) {
					// manually activate and deactivate native buttons
					if (!defined('ESSB3_NATIVE_ACTIVE')) {
						include_once (ESSB3_PLUGIN_ROOT . 'lib/core/native-buttons/essb-skinned-native-button.php');
						include_once (ESSB3_PLUGIN_ROOT . 'lib/core/native-buttons/essb-social-privacy.php');
						include_once (ESSB3_PLUGIN_ROOT . 'lib/core/native-buttons/essb-native-buttons-helper.php');
						define('ESSB3_NATIVE_ACTIVE', true);
						
						$essb_spb = ESSBSocialPrivacyNativeButtons::get_instance();
						ESSBNativeButtonsHelper::$essb_spb = $essb_spb;
						foreach ($essb_spb->resource_files as $key => $object) {
							$this->resource_builder->add_static_resource($object["file"], $object["key"], $object["type"]);
						}
						foreach (ESSBSkinnedNativeButtons::get_assets() as $key => $object) {
							$this->resource_builder->add_static_resource($object["file"], $object["key"], $object["type"]);
						}
						$this->resource_builder->add_css(ESSBSkinnedNativeButtons::generate_skinned_custom_css(), 'essb-skinned-native-buttons');
							
						// asign instance of native buttons privacy class to helper
							
						// register active social network apis
						foreach (ESSBNativeButtonsHelper::get_list_of_social_apis() as $key => $code) {
							$this->resource_builder->add_social_api($key);
						}
					}
				}
				else {
					define('ESSB3_NATIVE_DEACTIVE', true);
				}
			}
			if (!empty($essb_post_native_skin)) {
				$essb_options['skin_native'] = ESSBOptionValuesHelper::unified_true($essb_post_native_skin);
			}
			
			// change active button positions
			$modified_global_button_position = false;
			$new_button_positions_set = array();
			foreach ($essb_post_button_position as $position => $active) {
				if (ESSBOptionValuesHelper::unified_true($active)) {
					$new_button_positions_set[] = $position;
					$modified_global_button_position = true;
				}
			}
			foreach ($this->general_options['button_position'] as $settings_position) {
				if (!isset($essb_post_button_position[$settings_position])) {
					$new_button_positions_set[] = $settings_position;
				}
			}
			
			$this->general_options['button_position'] = $new_button_positions_set;
			/// TODO: register proper display locations after post settings (or unregister not used);
			if ($modified_global_button_position) {
				$this->deactivate_stored_filters_and_actions_by_group('button_position');
				$this->reactivate_button_position_filters();
			}			
		}
		
		// non cachable - required for generation of proper ajax requests
		$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_admin_ajax_access_code(), false, 'essb-head-ajax', 'head');
		$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_window_open_code(), true, 'essb-window-code');
		
		if (ESSBOptionValuesHelper::options_bool_value($this->options, 'activate_ga_tracking')) {
			$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_ga_tracking_code(), true, 'essb-ga-tracking-code');
		}
		
		if (in_array('print', $this->network_options['networks']) && !ESSBOptionValuesHelper::options_bool_value($this->options, 'print_use_printfriendly')) {
			$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_window_print_code(), true, 'essb-printing-code');
			$this->activated_resources['print'] = 'true';
		}
		
		// loading aminations
		$css_animations = ESSBOptionValuesHelper::options_value($this->options, 'css_animations');
		if (!empty($essb_post_animations)) {
			$css_animations = $essb_post_animations;
		}
		
		if ($css_animations != '' && $css_animations != "no") {
			$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_animation_code($css_animations), 'essb-css-animations');
		}
		$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_counter_style(), 'essb-counter-style');
		$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_generate_column_width(), 'essb-column-width-style');
		$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_sidebar_options(), 'essb-sidebar-style');
		$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_compile_display_locations_code(), 'essb-locations-css');
		
		// activate the fixer for mobiles
		$mobile_sharebuttonsbar_fix = ESSBOptionValuesHelper::options_bool_value($this->options, 'mobile_sharebuttonsbar_fix');
		if ($mobile_sharebuttonsbar_fix) {
			$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_mobilesharebar_fix_code(), 'essb-mobilesharebar-fix-css');				
		}
		
		$use_minifed_css = ($this->general_options['use_minified_css']) ? ".min" : "";
		$use_minifed_js = ($this->general_options['use_minified_js']) ? ".min" : "";

		$this->use_minified_css = $use_minifed_css;
		$this->use_minified_js = $use_minifed_js;
		
		// main theme CSS
		$template_id = $this->design_options['template'];
		$template_slug = ESSBCoreHelper::template_folder($template_id);
		if (!empty($essb_post_template)) {
			$template_slug = $essb_post_template;
			$this->design_options['template'] = $template_slug;
		}
		$this->design_options['template_slug'] = $template_slug;
		
		$template_url = ESSB3_PLUGIN_URL.'/assets/css/'.$template_slug.'/easy-social-share-buttons'.$use_minifed_css.'.css';
		$this->resource_builder->add_static_resource($template_url, 'easy-social-share-buttons', 'css');
		
		// counter script
		if ($this->button_style['show_counter']) {
			if (!defined('ESSB3_COUNTER_LOADED')) {
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/easy-social-share-buttons'.$use_minifed_js.'.js';
				$this->resource_builder->add_static_resource($script_url, 'easy-social-share-buttons', 'js');
				$this->activated_resources['counters'] = 'true';
				define('ESSB3_COUNTER_LOADED', true);
			}
		}
		
		// float from content top
		$content_postion = $this->general_options['content_position'];
		if (!empty($essb_post_content_position)) {
			$content_postion = $essb_post_content_position;
			$this->general_options['content_position'] = $content_postion;
		}
		if ($content_postion == "content_float" || $content_postion == "content_floatboth") {
			$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-float'.$use_minifed_js.'.js';
			$this->resource_builder->add_static_resource($script_url, 'essb-float', 'js');
			$this->activated_resources['float'] = 'true';
		}
		
		
		// mobile CSS load
		if ($this->is_mobile_safecss() && (in_array('sharebar', $this->general_options['button_position']) || 
				in_array('sharepoint', $this->general_options['button_position']) ||
				in_array('sharebottom', $this->general_options['button_position']))) {
			
			
			$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-mobile'.$use_minifed_css.'.css';
			$this->resource_builder->add_static_resource($style_url, 'easy-social-share-buttons-mobile', 'css');

			$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-mobile'.$use_minifed_js.'.js';
			$this->resource_builder->add_static_resource($script_url, 'essb-mobile', 'js');
			
			$this->activated_resources['mobile'] = 'true';
		}
		
		// post vertical float or sidebar
		if (in_array('sidebar', $this->general_options['button_position']) || in_array('postfloat', $this->general_options['button_position'])) {
			$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-sidebar'.$use_minifed_css.'.css';
			$this->resource_builder->add_static_resource($style_url, 'easy-social-share-buttons-sidebar', 'css');

			$this->activated_resources['sidebar'] = 'true';
			
			if (in_array('postfloat', $this->general_options['button_position'])) {
				
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-postfloat'.$use_minifed_js.'.js';
				$this->resource_builder->add_static_resource($script_url, 'essb-postfloat', 'js');
				$this->activated_resources['postfloat'] = 'true';
			}
			/*if ($display_where == "postfloat") {
				$postfloat_limitfooter = ESSBOptionsHelper::optionsBoolValue($this->options, 'postfloat_limitfooter');
					
				if ($postfloat_limitfooter) {
					$this->essb_js_builder->include_postfloat_stick_script();
				}
			}*/
		}

		if (in_array('topbar', $this->general_options['button_position']) || in_array('bottombar', $this->general_options['button_position'])) {
			$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-topbottom-bar'.$use_minifed_css.'.css';
			$this->resource_builder->add_static_resource($style_url, 'easy-social-share-buttons-topbottom-bar', 'css');
			$this->activated_resources['topbottombar'] = 'true';
			//$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-topbottom-bar'.$use_minifed_js.'.js';
			//$this->resource_builder->add_static_resource($script_url, 'essb-topbottom-bar', 'js');				
		}
		
		if (in_array('popup', $this->general_options['button_position'])) {
			$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-popup'.$use_minifed_css.'.css';
			$this->resource_builder->add_static_resource($style_url, 'easy-social-share-buttons-popup', 'css');
			
			$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-popup'.$use_minifed_js.'.js';
			$this->resource_builder->add_static_resource($script_url, 'essb-popup', 'js', true);		
			$this->activated_resources['popup'] = 'true';		
		}
		
		if (in_array('flyin', $this->general_options['button_position'])) {
			$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-flyin'.$use_minifed_css.'.css';
			$this->resource_builder->add_static_resource($style_url, 'easy-social-share-buttons-flyin', 'css');
			
			$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-flyin'.$use_minifed_js.'.js';
			$this->resource_builder->add_static_resource($script_url, 'essb-flyin', 'js');	
			$this->activated_resources['flyin'] = 'true';			
		}
		
		$this->general_options['included_mail'] = false;
		if (in_array('mail', $this->network_options['networks'])) {
			if ($this->network_options['mail_function'] == "form") {
				$this->general_options['included_mail'] = true;
				
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-mailform'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource($style_url, 'easy-social-share-buttons-mailform', 'css');
					
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-mailform.js';
				$this->resource_builder->add_static_resource($script_url, 'essb-mailform', 'js', true);		

				$this->activated_resources['mail'] = 'true';
			}
		}
	}
	
	public function load() {
		global $essb_networks;
		$this->general_options['mobile_exclude_tablet'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'mobile_exclude_tablet');
		$this->general_options['mobile_css_activate'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'mobile_css_activate');
		
		// loading static resources based on current options
		$this->design_options['template'] = ESSBOptionValuesHelper::options_value($this->options, 'style', '0');
		$this->design_options['button_style'] = ESSBOptionValuesHelper::options_value($this->options, 'button_style', 'button');
		$this->design_options['button_align'] = ESSBOptionValuesHelper::options_value($this->options, 'button_pos');
		$this->design_options['button_width'] = ESSBOptionValuesHelper::options_value($this->options, 'button_width');
		$this->design_options['button_width_fixed_value'] = ESSBOptionValuesHelper::options_value($this->options, 'fixed_width_value');
		$this->design_options['button_width_fixed_align'] = ESSBOptionValuesHelper::options_value($this->options, 'fixed_width_align');
		$this->design_options['button_width_full_container'] = ESSBOptionValuesHelper::options_value($this->options, 'fullwidth_share_buttons_container');
		$this->design_options['button_width_full_button'] = ESSBOptionValuesHelper::options_value($this->options, 'fullwidth_share_buttons_correction');
		$this->design_options['button_width_Full_button_mobile'] = ESSBOptionValuesHelper::options_value($this->options, 'fullwidth_share_buttons_correction');
		$this->design_options['button_width_columns'] = ESSBOptionValuesHelper::options_value($this->options, 'fullwidth_share_buttons_columns');
		$this->design_options['nospace'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'nospace');

		$this->design_options['fullwidth_align'] = ESSBOptionValuesHelper::options_value($this->options, 'fullwidth_align');
		$this->design_options['fullwidth_share_buttons_columns_align'] = ESSBOptionValuesHelper::options_value($this->options, 'fullwidth_share_buttons_columns_align');		
		
		$this->design_options['sidebar_leftright_close'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'sidebar_leftright_close');
		
		// social network options
		$this->network_options['networks'] = ESSBOptionValuesHelper::options_value($this->options, 'networks');
		if (!is_array($this->network_options['networks'])) {
			$this->network_options['networks'] = array();
		}
		$this->network_options['networks_order'] = ESSBOptionValuesHelper::options_value($this->options, 'networks_order');
		$this->network_options['more_button_func'] = ESSBOptionValuesHelper::options_value($this->options, 'more_button_func');
		
		$this->network_options['default_names'] = array();
		foreach ($essb_networks as $key => $object) {
			$search_for = "user_network_name_".$key;
			$user_network_name = ESSBOptionValuesHelper::options_value($this->options, $search_for, $object['name']);
			$this->network_options['default_names'][$key] = $user_network_name;
		}
		
		$this->network_options['twitter_shareshort'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'twitter_shareshort');
		$this->network_options['twitter_shareshort_service'] = ESSBOptionValuesHelper::options_value($this->options, 'twitter_shareshort_service');
		$this->network_options['twitter_always_count_full'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'twitter_always_count_full');
		$this->network_options['twitter_user'] = ESSBOptionValuesHelper::options_value($this->options, 'twitteruser');
		$this->network_options['twitter_hashtags'] = ESSBOptionValuesHelper::options_value($this->options, 'twitterhashtags');
		$this->network_options['facebook_advanced'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'facebookadvanced');
		$this->network_options['facebook_advancedappid'] = ESSBOptionValuesHelper::options_value($this->options, 'facebookadvancedappid');
		$this->network_options['pinterest_sniff_disable'] = ESSBOptionValuesHelper::options_value($this->options, 'pinterest_sniff_disable');
		$this->network_options['mail_disable_editmessage'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'mail_disable_editmessage');
		$this->network_options['mail_function'] = ESSBOptionValuesHelper::options_value($this->options, 'mail_function');
		// mobile mail setting
		if ($this->is_mobile()) {
			$this->network_options['mail_function'] = "link";
		}
		 
		$this->network_options['mail_function_mobile'] = ESSBOptionValuesHelper::options_value($this->options, 'mail_function_mobile');
		$this->network_options['use_wpmandrill'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'use_wpmandrill');
		$this->network_options['mail_copyaddress'] = ESSBOptionValuesHelper::options_value($this->options, 'mail_copyaddress');
		$this->network_options['mail_captcha'] = ESSBOptionValuesHelper::options_value($this->options, 'mail_captcha');
		$this->network_options['mail_captcha_answer'] = ESSBOptionValuesHelper::options_value($this->options, 'mail_captcha_answer');
		$this->network_options['mail_subject'] = ESSBOptionValuesHelper::options_value($this->options, 'mail_subject', '');
		$this->network_options['mail_body'] = ESSBOptionValuesHelper::options_value($this->options, 'mail_body', '');
		$this->network_options['print_use_printfriendly'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'print_use_printfriendly');
		$this->network_options['stumble_noshortlink'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'stumble_noshortlink');
		$this->network_options['buffer_twitter_user'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'buffer_twitter_user');
		$this->network_options['flattr_username'] = ESSBOptionValuesHelper::options_value($this->options, 'flattr_username');
		$this->network_options['flattr_tags'] = ESSBOptionValuesHelper::options_value($this->options, 'flattr_tags');
		$this->network_options['flattr_cat'] = ESSBOptionValuesHelper::options_value($this->options, 'flattr_cat');
		$this->network_options['flattr_lang'] = ESSBOptionValuesHelper::options_value($this->options, 'flattr_lang');
		$this->network_options['whatsapp_shareshort'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'whatsapp_shareshort');
		$this->network_options['whatsapp_shareshort_service'] = ESSBOptionValuesHelper::options_value($this->options, 'whatsapp_shareshort_service');
		
		// button style options
		$this->button_style['show_counter'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'show_counter');
		$this->button_style['counter_pos'] = ESSBOptionValuesHelper::options_value($this->options, 'counter_pos');
		$this->button_style['active_internal_counters'] = ESSBOptionValuesHelper::options_value($this->options, 'active_internal_counters');
		$this->button_style['total_counter_pos'] = ESSBOptionValuesHelper::options_value($this->options, 'total_counter_pos');
		
		$this->button_style['message_share_buttons'] = ESSBOptionValuesHelper::options_value($this->options, 'message_above_share_buttons');
		$this->button_style['message_share_before_buttons'] = ESSBOptionValuesHelper::options_value($this->options, 'message_share_before_buttons');
		$this->button_style['message_like_buttons'] = ESSBOptionValuesHelper::options_value($this->options, 'message_like_buttons');
		
		$this->general_options['facebooktotal'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'facebooktotal');
		$this->general_options['force_counters_admin'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'force_counters_admin');
		$this->general_options['admin_ajax_cache'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'admin_ajax_cache');
		$this->general_options['admin_ajax_cache_time'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'admin_ajax_cache_time');
		$this->general_options['activate_total_counter_text'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'activate_total_counter_text');
		$this->general_options['total_counter_afterbefore_text'] = ESSBOptionValuesHelper::options_value($this->options, 'total_counter_afterbefore_text');
		
		$this->general_options['stats_active'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'stats_active');
		$this->general_options['activate_ga_tracking'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'activate_ga_tracking');
		$this->general_options['ga_tracking_mode'] = ESSBOptionValuesHelper::options_value($this->options, 'ga_tracking_mode');
		$this->general_options['activate_ga_campaign_tracking'] = ESSBOptionValuesHelper::options_value($this->options, 'activate_ga_campaign_tracking');
		
		$this->general_options['customshare'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'customshare');
		$this->general_options['customshare_text'] = ESSBOptionValuesHelper::options_value($this->options, 'customshare_text');
		$this->general_options['customshare_url'] = ESSBOptionValuesHelper::options_value($this->options, 'customshare_url');
		$this->general_options['customshare_image'] = ESSBOptionValuesHelper::options_value($this->options, 'customshare_image');
		$this->general_options['customshare_description'] = ESSBOptionValuesHelper::options_value($this->options, 'customshare_description');
		
		$this->general_options['mycred_activate'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'mycred_activate');
		$this->general_options['mycred_points'] = ESSBOptionValuesHelper::options_value($this->options, 'mycred_points', '1');
		$this->general_options['mycred_group'] = ESSBOptionValuesHelper::options_value($this->options, 'mycred_group', 'mycred_default');
		
		$this->general_options['shorturl_activate'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'shorturl_activate');
		$this->general_options['shorturl_type'] = ESSBOptionValuesHelper::options_value($this->options, 'shorturl_type');
		$this->general_options['shorturl_bitlyuser'] = ESSBOptionValuesHelper::options_value($this->options, 'shorturl_bitlyuser');
		$this->general_options['shorturl_bitlyapi'] = ESSBOptionValuesHelper::options_value($this->options, 'shorturl_bitlyapi');
		
		// post types where buttons are active
		$this->general_options['display_in_types'] = ESSBOptionValuesHelper::options_value($this->options, 'display_in_types');
		$this->general_options['display_excerpt'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'display_excerpt');
		$this->general_options['display_excerpt_pos'] = ESSBOptionValuesHelper::options_value($this->options, 'display_excerpt_pos');
		$this->general_options['display_exclude_from'] = ESSBOptionValuesHelper::options_value($this->options, 'display_exclude_from');
		$this->general_options['display_include_on'] = ESSBOptionValuesHelper::options_value($this->options, 'display_include_on');
		$this->general_options['display_deactivate_on'] = ESSBOptionValuesHelper::options_value($this->options, 'display_deactivate_on');
		$this->general_options['deactivate_homepage'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'deactivate_homepage');
		
		
		// content and button positions
		$this->general_options['content_position'] = ESSBOptionValuesHelper::options_value($this->options, 'content_position');
		$this->general_options['button_position'] = ESSBOptionValuesHelper::options_value($this->options, 'button_position');
		if (!is_array($this->general_options['button_position'])) {
			$this->general_options['button_position'] = array();
		}
		
		if (!is_array($this->general_options['display_in_types'])) {
			$this->general_options['display_in_types'] = array();
		}
		
		// administrative options
		
		$this->general_options['total_counter_hidden_till'] = ESSBOptionValuesHelper::options_value($this->options, 'total_counter_hidden_till');
		$this->general_options['button_counter_hidden_till'] = ESSBOptionValuesHelper::options_value($this->options, 'button_counter_hidden_till');
		
		// that settings need to be added to plugin settings
		$this->general_options['reset_postdata'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'reset_postdata');
		$this->general_options['reset_posttype'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'reset_posttype');
		$this->general_options['metabox_visual'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'metabox_visual');
		$this->general_options['using_yoast_ga'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'using_yoast_ga');
		$this->general_options['use_minified_css'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'use_minified_css');
		$this->general_options['use_minified_js'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'use_minified_js');
		$this->general_options['scripts_in_head'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'scripts_in_head');
		
		// cleaner
		$this->general_options['apply_clean_buttons'] = ESSBOptionValuesHelper::options_bool_value($this->options, 'apply_clean_buttons');
		$this->general_options['apply_clean_buttons_method'] = ESSBOptionValuesHelper::options_value($this->options, 'apply_clean_buttons_method');
		
		// custom buttons priority
		$this->general_options['priority_of_buttons'] = ESSBOptionValuesHelper::options_value($this->options, 'priority_of_buttons', '10');
		$this->general_options['priority_of_buttons'] = intval($this->general_options['priority_of_buttons']);
		if ($this->general_options['priority_of_buttons'] == 0) {
			$this->general_options['priority_of_buttons'] = 10;
		}
 		
		
		// apply mobile options for content positions
		if ($this->general_options['mobile_css_activate']) {
			if ($this->is_mobile_safecss()) {
				$user_set_mobile = ESSBOptionValuesHelper::options_value ( $this->options, 'button_position_mobile' );
				
				if (!is_array($user_set_mobile)) {
					$user_set_mobile = array();
				}
				
				if (in_array('sharebottom', $user_set_mobile)) {
					$this->general_options ['button_position'][] = 'sharebottom';
				}
				if (in_array('sharebar', $user_set_mobile)) {
					$this->general_options ['button_position'][] = 'sharebar';
				}
				if (in_array('sharepoint', $user_set_mobile)) {
					$this->general_options ['button_position'][] = 'sharepoint';
				}
				
			}
		}
		else {
			if ($this->is_mobile ()) {
				if (ESSBOptionValuesHelper::options_bool_value ( $this->options, 'mobile_positions' )) {
					$this->general_options ['content_position'] = ESSBOptionValuesHelper::options_value ( $this->options, 'content_position_mobile' );
					$this->general_options ['button_position'] = ESSBOptionValuesHelper::options_value ( $this->options, 'button_position_mobile' );
					if (! is_array ( $this->general_options ['button_position'] )) {
						$this->general_options ['button_position'] = array ();
					}
				}
				
				if (ESSB3_DEMO_MODE) {
					$demo_mode_mobile = isset ( $_REQUEST ['mobile'] ) ? $_REQUEST ['mobile'] : '';
					if (! empty ( $demo_mode_mobile )) {
						$this->general_options ['button_position'] = array ();
						$this->general_options ['button_position'] [] = $demo_mode_mobile;
					}
				}
			}
		}
	}
	
	public function register_locations() {
		if (is_admin()) {
			return;
		}
		
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}
		
		// @since version 3.1 CSS hide of mobile buttons
		$mobile_css_activate = ESSBOptionValuesHelper::options_bool_value($this->options, 'mobile_css_activate');
		if ($mobile_css_activate) {
			$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_mobile_compatibility(), 'essb-mobile-compatibility');
		}
		
		$this->list_of_activated_locations = array();
		
		$current_post_content_locations = $this->general_options['content_position'];
		$current_post_button_position = $this->general_options['button_position'];
		
		if ($current_post_content_locations != '' && $current_post_content_locations != 'content_manual') {
			add_filter('the_content', array($this, 'display_inline'), $this->general_options['priority_of_buttons']);
			$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_content", "function" => 'display_inline', "priority" => $this->general_options['priority_of_buttons'], 'position' => 'content_position');
		}
	
		if (is_array($current_post_button_position)) {
			foreach ($current_post_button_position as $position) {
				if (method_exists($this, 'display_'.$position)) {
					if ($position == "postfloat") {
						add_filter('the_content', array($this, 'display_postfloat'));
						add_filter( 'the_content', array( $this, 'trigger_bottom_mark' ), 9999 );
						$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_content", "function" => 'display_postfloat', "priority" => "", 'position' => 'button_position');
						$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_content", "function" => 'trigger_bottom_mark', "priority" => "9999", 'position' => 'button_position');
					}
					else if ($position == "onmedia") {
						add_filter( 'the_content', array( $this, 'display_onmedia' ), 9999 );
						$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_content", "function" => 'display_onmedia', "priority" => "9999", 'position' => 'button_position');
					}
					else {
						
						if ($position == "popup" && ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_display_comment')) {
							add_filter( 'comment_post_redirect', array( $this, 'after_comment_trigger' ) );								
							$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "comment_post_redirect", "function" => 'after_comment_trigger', "priority" => "", 'position' => 'button_position');
						}

						if ($position == "flyin" && ESSBOptionValuesHelper::options_bool_value($this->options, 'flyin_display_comment')) {
							add_filter( 'comment_post_redirect', array( $this, 'after_comment_trigger' ) );
							$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "comment_post_redirect", "function" => 'after_comment_trigger', "priority" => "", 'position' => 'button_position');
						}
						
						add_filter( 'the_content', array( $this, 'trigger_bottom_mark' ), 9999 );
						add_action( 'wp_footer', array( $this, "display_{$position}" ) );
						$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_content", "function" => 'trigger_bottom_mark', "priority" => "9999", 'position' => 'button_position');
						$this->list_of_activated_locations[] = array("type" => "action", "hook" => "wp_footer", "function" => "display_{$position}", "priority" => "", 'position' => 'button_position');						
					}
				}
			}	
		}
		
		// excerpt display
		if ($this->general_options['display_excerpt']) {
			
			// @since verion 3.0.4 - build in Avada theme bridge
			if (class_exists('FusionCore_Plugin')) {
				// detected Avada theme
				if ($this->general_options['display_excerpt_pos'] == "top") {
					add_action('fusion_blog_shortcode_loop_content', array($this, 'display_excerpt_avada'), $this->general_options['priority_of_buttons']);
					$this->list_of_activated_locations[] = array("type" => "action", "hook" => "fusion_blog_shortcode_loop_content", "function" => 'display_excerpt_avada', "priority" => $this->general_options['priority_of_buttons']);						
				}
				else {
					add_action('fusion_blog_shortcode_loop_footer', array($this, 'display_excerpt_avada'), $this->general_options['priority_of_buttons']);
					$this->list_of_activated_locations[] = array("type" => "action", "hook" => "fusion_blog_shortcode_loop_content", "function" => 'display_excerpt_avada', "priority" => $this->general_options['priority_of_buttons']);
				}
			}
			else {
				add_filter('the_excerpt', array($this, 'display_excerpt'), $this->general_options['priority_of_buttons']);
				$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_excerpt", "function" => 'display_excerpt', "priority" => $this->general_options['priority_of_buttons']);
			}
		}
		
		// clean buttons 
		if ($this->general_options['apply_clean_buttons']) {

			if ($this->general_options['apply_clean_buttons_method'] == "actionremove") {
				add_filter( 'get_the_excerpt', array( $this, 'remove_buttons_excerpts_method2'), -999);
			}
			else {
				add_filter( 'get_the_excerpt', array( $this, 'remove_buttons_excerpts'));
			}
		}
		
		// additional module integraton hooks
		//-- WooCommerce
		$woocommece_share = ESSBOptionValuesHelper::options_bool_value($this->options, 'woocommece_share');
		$woocommece_beforeprod = ESSBOptionValuesHelper::options_bool_value($this->options, 'woocommece_beforeprod');
		$woocommece_afterprod = ESSBOptionValuesHelper::options_bool_value($this->options, 'woocommece_afterprod');
		
		if ($woocommece_share) {
			add_action ( 'woocommerce_share', array ($this, 'handle_woocommerce_integration' ) );
		}
		if ($woocommece_beforeprod) {
			add_action ( 'woocommerce_before_single_product', array ($this, 'handle_woocommerce_integration' ) );
		}
		if ($woocommece_afterprod) {
			add_action ( 'woocommerce_after_single_product', array ($this, 'handle_woocommerce_integration' ) );
		}
		
		//-- WP eCommerce
		$wpec_before_desc = ESSBOptionValuesHelper::options_bool_value($this->options, 'wpec_before_desc');
		$wpec_after_desc = ESSBOptionValuesHelper::options_bool_value($this->options, 'wpec_after_desc');
		$wpec_theme_footer = ESSBOptionValuesHelper::options_bool_value($this->options, 'wpec_theme_footer');
		if ($wpec_before_desc) {
			add_action ( 'wpsc_product_before_description', array ($this, 'handle_wpecommerce_integration' ) );
		}
		if ($wpec_after_desc) {
			add_action ( 'wpsc_product_addons', array ($this, 'handle_wpecommerce_integration' ) );
		}
		if ($wpec_theme_footer) {
			add_action ( 'wpsc_theme_footer', array ($this, 'handle_wpecommerce_integration' ) );
		}

		// JigoShop
		$jigoshop_top = ESSBOptionValuesHelper::options_bool_value($this->options, 'jigoshop_top');
		$jigoshop_bottom = ESSBOptionValuesHelper::options_bool_value($this->options, 'jigoshop_bottom');
		if ($jigoshop_top) {
			add_action ( 'jigoshop_before_single_product_summary', array ($this, 'handle_jigoshop_integration' ) );
		}
		if ($jigoshop_bottom) {
			add_action ( 'jigoshop_after_main_content', array ($this, 'handle_jigoshop_integration' ) );
		}

		// BBPress
		$bbpress_forum = ESSBOptionValuesHelper::options_bool_value($this->options, 'bbpress_forum');
		$bbpress_topic = ESSBOptionValuesHelper::options_bool_value($this->options, 'bbpress_topic');
		
		if ($bbpress_topic) {
			add_action ( 'bbp_template_before_topics_loop', array ($this, 'handle_bbpress_integration' ) );
		}
		if ($bbpress_forum) {
			add_action ( 'bbp_template_before_replies_loop', array ($this, 'handle_bbpress_integration' ) );
		}
		
		// iThemes Exchange
		$ithemes_after_title = ESSBOptionValuesHelper::options_bool_value($this->options, 'ithemes_after_title');
		$ithemes_before_desc = ESSBOptionValuesHelper::options_bool_value($this->options, 'ithemes_before_desc');
		$ithemes_after_desc = ESSBOptionValuesHelper::options_bool_value($this->options, 'ithemes_after_desc');
		$ithemes_after_product = ESSBOptionValuesHelper::options_bool_value($this->options, 'ithemes_after_product');
		$ithemes_after_purchase = ESSBOptionValuesHelper::options_bool_value($this->options, 'ithemes_after_purchase');
		
		if ($ithemes_after_title) {
			add_action ( 'it_exchange_content_product_before_wrap', array ($this, 'handle_ithemes_integration' ) );
		}
		if ($ithemes_before_desc) {
			add_action ( 'it_exchange_content_product_before_description_element', array ($this, 'handle_ithemes_integration' ) );
		}
		if ($ithemes_after_desc) {
			add_action ( 'it_exchange_content_product_after_description_element', array ($this, 'handle_ithemes_integration' ) );
		}
		if ($ithemes_after_product) {
			add_action ( 'it_exchange_content_product_end_wrap', array ($this, 'handle_ithemes_integration' ) );
		}
		if ($ithemes_after_purchase) {
			add_action ( 'it_exchange_content_confirmation_after_product_title', array ($this, 'handle_ithemes_purchase_integration' ) );
		}
		
		// BuddyPress
		$buddypress_group = ESSBOptionValuesHelper::options_bool_value($this->options, 'buddypress_group');
		$buddypress_activity = ESSBOptionValuesHelper::options_bool_value($this->options, 'buddypress_activity');
		
		if ($buddypress_group) {
			add_action ( 'bp_before_group_home_content', array ($this, 'handle_buddypress_group_integration' ) );
		}
		if ($buddypress_activity) {
			add_action ( 'bp_activity_entry_meta', array ($this, 'handle_buddypress_activity_integration' ) );
		}
	}
	
	function remove_buttons_excerpts_method2($text) {		
		remove_filter( 'the_content', array( $this, 'display_inline' ), $this->general_options['priority_of_buttons']);
		remove_filter( 'the_content', array( $this, 'display_postfloat' ));
		remove_filter( 'the_content', array( $this, 'trigger_bottom_mark' ), 9999 );
		remove_filter( 'the_content', array( $this, 'display_onmedia' ), 9999 );
		
		return $text;
	}
	
	function remove_buttons_excerpts($text) {
		global $essb_networks;
		
		foreach ($essb_networks as $k => $data) {
			$network_name = $data['name'];
			
			if ($network_name != '-') {
				$text = str_replace($network_name, '', $text);
			}
			$text = str_replace($k, '', $text);
			
			$position_top_name = ESSBOptionValuesHelper::options_value($this->options, 'top_'.$k.'_name');
			$position_float_name = ESSBOptionValuesHelper::options_value($this->options, 'float_'.$k.'_name');
			$position_postfloat_name = ESSBOptionValuesHelper::options_value($this->options, 'postfloat_'.$k.'_name');
			
			$default_name = $this->network_options['default_names'][$k];
			
			if (!empty($position_top_name)) {
				if ($position_top_name != '-') {
					$text = str_replace($position_top_name, '', $text);
				}
			}
			if (!empty($position_float_name)) {
				if ($position_float_name != '-') {
					$text = str_replace($position_float_name, '', $text);
				}
			}
			if (!empty($position_postfloat_name)) {
				if ($position_postfloat_name != '-') {
					$text = str_replace($position_postfloat_name, '', $text);
				}
			}
			if (!empty($default_name)) {
				if ($default_name != '-') {
					$text = str_replace($default_name, '', $text);
				}
			}
		}
		
		if (defined('ESSB3_NATIVE_ACTIVE')) {
			$skin_native = ESSBOptionValuesHelper::options_bool_value($this->options, 'skin_native');
			if (ESSB3_NATIVE_ACTIVE && $skin_native) {
				$native_buttons = ESSBNativeButtonsHelper::active_native_buttons();

				foreach ($native_buttons as $network) {
					$skinned_text = ESSBOptionValuesHelper::options_value($this->options, $network.'_text');
					if (!empty($skinned_text)) {
						$text = str_replace($skinned_text, '', $text);
					}
				}
			}
		}
		
		return $text;
	}
	
	function is_plugin_deactivated_on() {		
		if (is_admin()) {
			return;
		}
		
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}
		
		//display_deactivate_on
		$is_deactivated = false;
		if ($this->general_options['display_deactivate_on'] != "") {
			$excule_from = explode(',', $this->general_options['display_deactivate_on']);
					
			$excule_from = array_map('trim', $excule_from);
			if (in_array(get_the_ID(), $excule_from, false)) {
				$is_deactivated = true;
			}
		}
		return $is_deactivated;
	}
	
	function is_plugin_activated_on() {
		if (is_admin()) {
			return;
		}
		
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}
		
		//display_deactivate_on
		$is_activated = false;
		if ($this->general_options['display_include_on'] != "") {
			$excule_from = explode(',', $this->general_options['display_include_on']);
				
			$excule_from = array_map('trim', $excule_from);
			if (in_array(get_the_ID(), $excule_from, false)) {
				$is_activated = true;
			}
		}
		return $is_activated;
	}
	
	
	function reactivate_content_filters_after_temporary_deactivate() {
		if (is_admin()) {
			return;
		}
		
		foreach ($this->temporary_decativated_locations as $hook_data) {
			$type = isset($hook_data["type"]) ? $hook_data["type"] : "filter";
			$hook = isset($hook_data["hook"]) ? $hook_data["hook"] : "the_content";
			$action = isset($hook_data["function"]) ? $hook_data["function"] : "";
			$priority = isset($hook_data["priority"]) ? $hook_data["priority"] : "";
			$position = isset($hook_data['position']) ? $hook_data['position'] : '';
		
			if ($hook != "the_content" && $hook != "the_excerpt") {
				continue;
			}
		
			if ($hook != "" && $action != "") {
				if ($type == "filter") {
					if (!empty($priority)) {
						add_filter($hook, array($this, $action), $priority);
					}
					else {
						add_filter($hook, array($this, $action));
					}
		
				}
				if ($type == "action") {
					if (!empty($priority)) {
						add_action($hook, array($this, $action), $priority);
					}
					else {
						add_action($hook, array($this, $action));
					}
				}
			}
		}
		
		$this->temporary_decativated_locations = array();
	}
	
	function temporary_deactivate_content_filters() {
		$this->temporary_decativated_locations = array();
		
		if (is_admin()) {
			return;
		}
		
		foreach ($this->list_of_activated_locations as $hook_data) {
			$type = isset($hook_data["type"]) ? $hook_data["type"] : "filter";
			$hook = isset($hook_data["hook"]) ? $hook_data["hook"] : "the_content";
			$action = isset($hook_data["function"]) ? $hook_data["function"] : "";
			$priority = isset($hook_data["priority"]) ? $hook_data["priority"] : "";
			$position = isset($hook_data['position']) ? $hook_data['position'] : '';
				
			if ($hook != "the_content" && $hook != "the_excerpt") {
				continue;
			}
		
			if ($hook != "" && $action != "") {
				if ($type == "filter") {
					if (!empty($priority)) {
						remove_filter($hook, array($this, $action), $priority);
					}
					else {
						remove_filter($hook, array($this, $action));
					}
		
				}
				if ($type == "action") {
					if (!empty($priority)) {
						remove_action($hook, array($this, $action), $priority);
					}
					else {
						remove_action($hook, array($this, $action));
					}
				}
			}
			
			$this->temporary_decativated_locations[] = $hook_data;
		}
	}
	
	function reactivate_button_position_filters() {
		$current_post_button_position = $this->general_options['button_position'];
		if (is_array($current_post_button_position)) {
			foreach ($current_post_button_position as $position) {
				if (method_exists($this, 'display_'.$position)) {
					if ($position == "postfloat") {
						add_filter('the_content', array($this, 'display_postfloat'));
						add_filter( 'the_content', array( $this, 'trigger_bottom_mark' ), 9999 );
						$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_content", "function" => 'display_postfloat', "priority" => "", 'position' => 'button_position');
						$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_content", "function" => 'trigger_bottom_mark', "priority" => "9999", 'position' => 'button_position');
					}
					else if ($position == "onmedia") {
						add_filter( 'the_content', array( $this, 'display_onmedia' ), 9999 );
						$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_content", "function" => 'display_onmedia', "priority" => "9999", 'position' => 'button_position');
					}
					else {
		
						if ($position == "popup" && ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_display_comment')) {
							add_filter( 'comment_post_redirect', array( $this, 'after_comment_trigger' ) );
							$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "comment_post_redirect", "function" => 'after_comment_trigger', "priority" => "", 'position' => 'button_position');
						}
		
						if ($position == "flyin" && ESSBOptionValuesHelper::options_bool_value($this->options, 'flyin_display_comment')) {
							add_filter( 'comment_post_redirect', array( $this, 'after_comment_trigger' ) );
							$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "comment_post_redirect", "function" => 'after_comment_trigger', "priority" => "", 'position' => 'button_position');
						}
		
						if ($position == "popup" && ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_display_purchase')) {
							//woocommerce_thankyou
							add_action( 'woocommerce_thankyou',  array( $this, 'display_popup' ) );
							$this->list_of_activated_locations[] = array("type" => "action", "hook" => "woocommerce_thankyou", "function" => 'display_popup', "priority" => "", 'position' => 'button_position');
						}
						
						add_filter( 'the_content', array( $this, 'trigger_bottom_mark' ), 9999 );
						add_action( 'wp_footer', array( $this, "display_{$position}" ) );
						$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_content", "function" => 'trigger_bottom_mark', "priority" => "9999", 'position' => 'button_position');
						$this->list_of_activated_locations[] = array("type" => "action", "hook" => "wp_footer", "function" => "display_{$position}", "priority" => "", 'position' => 'button_position');
					}
				}
			}
		}
	}
	
	function deactivate_stored_filters_and_actions_by_group($group = '') {
		if (empty($group)) { 
			return;
		}
		if (is_admin()) {
			return;
		}
		
		foreach ($this->list_of_activated_locations as $hook_data) {
			$type = isset($hook_data["type"]) ? $hook_data["type"] : "filter";
			$hook = isset($hook_data["hook"]) ? $hook_data["hook"] : "the_content";
			$action = isset($hook_data["function"]) ? $hook_data["function"] : "";
			$priority = isset($hook_data["priority"]) ? $hook_data["priority"] : "";
			$position = isset($hook_data['position']) ? $hook_data['position'] : '';
			
			if (empty($position) || $position != $group) {
				continue;
			}
				
			if ($hook != "" && $action != "") {
				if ($type == "filter") {
					if (!empty($priority)) {
						remove_filter($hook, array($this, $action), $priority);
					}
					else {
						remove_filter($hook, array($this, $action));
					}
		
				}
				if ($type == "action") {
					if (!empty($priority)) {
						remove_action($hook, array($this, $action), $priority);
					}
					else {
						remove_action($hook, array($this, $action));
					}
				}
			}
		}
	}
	
	function deactivate_stored_filter_and_actions() {
		if (is_admin()) {
			return;
		}
		
		//$this->list_of_activated_locations[] = array("type" => "filter", "hook" => "the_excerpt", "function" => array($this, 'display_excerpt'), "priority" => $this->general_options['priority_of_buttons']);
		foreach ($this->list_of_activated_locations as $hook_data) {
			$type = isset($hook_data["type"]) ? $hook_data["type"] : "filter";
			$hook = isset($hook_data["hook"]) ? $hook_data["hook"] : "the_content";
			$action = isset($hook_data["function"]) ? $hook_data["function"] : "";
			$priority = isset($hook_data["priority"]) ? $hook_data["priority"] : "";
			
			if ($hook != "" && $action != "") {
				if ($type == "filter") {
					if (!empty($priority)) {
						remove_filter($hook, array($this, $action), $priority);
					}
					else {
						remove_filter($hook, array($this, $action));
					}
						
				}
				if ($type == "action") {
					if (!empty($priority)) {
						remove_action($hook, array($this, $action), $priority);
					}
					else {
						remove_action($hook, array($this, $action));
					}
				}
			}
		}
	}
	
	function deactivate_content_filters () {
		if (is_admin()) {
			return;
		}
		
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}
		
		$current_post_content_locations = $this->general_options['content_position'];
		$current_post_button_position = $this->general_options['button_position'];
		
		// user is activated the visual metabox editor
		if ($this->general_options['metabox_visual']) {
			// integrate post meta change options
		}
		
		if ($current_post_content_locations != '' && $current_post_content_locations != 'content_manual') {
			remove_filter('the_content', array($this, 'display_inline'), $this->general_options['priority_of_buttons']);
		}
		
		if (is_array($current_post_button_position)) {
			foreach ($current_post_button_position as $position) {
				if (method_exists($this, 'display_'.$position)) {
					if ($position == "postfloat") {
						remove_filter('the_content', array($this, 'display_postfloat'));
						remove_filter( 'the_content', array( $this, 'trigger_bottom_mark' ), 9999 );
					}
					else if ($position == "onmedia") {
						remove_filter( 'the_content', array( $this, 'display_onmedia' ), 9999 );
					}
					else {		
						remove_filter( 'the_content', array( $this, 'trigger_bottom_mark' ), 9999 );
					}
				}
			}
		}
		
		if ($this->general_options['display_excerpt']) {
			remove_filter('the_excerpt', array($this, 'display_excerpt'), $this->general_options['priority_of_buttons']);
		}
	}		
	
	function check_applicability_module ($location = '') {
		$is_singular = true;
		$is_lists_authorized = true;
		
		if ($this->general_options['display_exclude_from'] != "") {
			$excule_from = explode(',', $this->general_options['display_exclude_from']);
				
			$excule_from = array_map('trim', $excule_from);
				
			if (in_array(get_the_ID(), $excule_from, false)) {
				$is_singular = false;
				$is_lists_authorized = false;
			}
		}
		
		if (ESSBCoreHelper::is_module_deactivate_on('share')) {
			$is_singular = false;
			$is_lists_authorized = false;
		}
		
		// additional plugin hacks
		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		if ($request_uri != '') {
			$exist_ai1ec_export = strpos($request_uri, 'ai1ec_exporter_controller');
			if ($exist_ai1ec_export !== false) {
				$is_singular = false; $is_lists_authorized = false;
			}
		
			$exist_tribe_cal = strpos($request_uri, 'ical=');
			if ($exist_tribe_cal !== false) {
				$is_singular = false; $is_lists_authorized = false;
			}
		}
		
		// check post meta for turned off
		$essb_off = get_post_meta(get_the_ID(),'essb_off',true);
		
		if ($essb_off == "true") {
			$is_lists_authorized = false;
			$is_singular = false;
		}
		
		// deactivate on mobile devices if selected
		if ($this->is_mobile()) {
			if (ESSBOptionValuesHelper::options_value($this->options, $location.'_mobile_deactivate')) {
				$is_singular = false;
				$is_lists_authorized = false;
			}
		}
		
		// check current location settings
		if ($is_singular || $is_lists_authorized) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function check_applicability($post_types = array(), $location = '') {
		global $post;
		
		$current_active_post_type = "";
		if ($this->general_options['reset_posttype'] && isset($post)) {
			$current_active_post_type = isset($post->post_type) ? $post->post_type : "";
 		}
		
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}	
		
		// @since 3.0
		// another check to avoid buttons appear on unwanted post types
		
		$is_exclusive_active = false;
		if (isset($post)) {
			$is_exclusive_active = $this->is_plugin_activated_on();
		}
		
		if ($this->general_options['reset_posttype'] && !empty($current_active_post_type)) {
			if (!in_array($current_active_post_type, $post_types)) {
				if (!$is_exclusive_active) {
					return false;
				}
			}
		}
		
		
		//if (isset($post)) {
		//	print " parsing post type=".$post->post_type;
		//}
		
		$is_all_lists = in_array('all_lists', $post_types);
		$is_set_list = count($post_types) > 0 ?  true: false;
		
		unset($post_types['all_lists']);
		$is_lists_authorized = (is_archive() || is_front_page() || is_search() || is_tag() || is_post_type_archive() || is_home()) && $is_all_lists ? true : false;
		$is_singular = is_singular($post_types);
		if ($is_singular && !$is_set_list) {
			$is_singular = false;
		}
		
		if ($this->general_options['deactivate_homepage']) {
			if (is_home() || is_front_page()) {
				$is_lists_authorized = false;
				$is_singular = false;
			}
		}
		
		if ($this->general_options['display_exclude_from'] != "") {
			$excule_from = explode(',', $this->general_options['display_exclude_from']);
			
			$excule_from = array_map('trim', $excule_from);
			
			if (in_array(get_the_ID(), $excule_from, false)) {
				$is_singular = false;
				$is_lists_authorized = false;
			}
		}
		
		if (ESSBCoreHelper::is_module_deactivate_on('share')) {
			$is_singular = false;
			$is_lists_authorized = false;
		}
		
		// additional plugin hacks
		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		if ($request_uri != '') {
			$exist_ai1ec_export = strpos($request_uri, 'ai1ec_exporter_controller');
			if ($exist_ai1ec_export !== false) {
				$is_singular = false; $is_lists_authorized = false;
			}
				
			$exist_tribe_cal = strpos($request_uri, 'ical=');
			if ($exist_tribe_cal !== false) {
				$is_singular = false; $is_lists_authorized = false;
			}
		}
		
		// check post meta for turned off
		$essb_off = get_post_meta(get_the_ID(),'essb_off',true);
		
		if ($essb_off == "true") {
			$is_lists_authorized = false;
			$is_singular = false;
		}
				
		// deactivate on mobile devices if selected
		if ($this->is_mobile()) {
			if (ESSBOptionValuesHelper::options_value($this->options, $location.'_mobile_deactivate')) {
				$is_singular = false;
				$is_lists_authorized = false;
			}
		}
		
		if ($is_exclusive_active) {
			$is_singular = true;
		}
		
		// check current location settings
		if ($is_singular || $is_lists_authorized) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function check_applicability_excerpt($post_types = array(), $location = '') {
		global $post;
	
		$current_active_post_type = "";
		if ($this->general_options['reset_posttype'] && isset($post)) {
			$current_active_post_type = isset($post->post_type) ? $post->post_type : "";
		}
	
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}
	
		// @since 3.0
		// another check to avoid buttons appear on unwanted post types
	
		$is_exclusive_active = false;
		if (isset($post)) {
			$is_exclusive_active = $this->is_plugin_activated_on();
		}
	
		if ($this->general_options['reset_posttype'] && !empty($current_active_post_type)) {
			if (!in_array($current_active_post_type, $post_types)) {
				if (!$is_exclusive_active) {
					return false;
				}
			}
		}
	
	
		//if (isset($post)) {
		//	print " parsing post type=".$post->post_type;
		//}
	
		$is_all_lists = in_array('all_lists', $post_types);
		$is_set_list = count($post_types) > 0 ?  true: false;
	
		unset($post_types['all_lists']);
		$is_lists_authorized = (is_archive() || is_front_page() || is_search() || is_tag() || is_post_type_archive() || is_home());
		$is_singular = is_singular($post_types);
		if ($is_singular && !$is_set_list) {
			$is_singular = false;
		}
		
		if (isset($post)) {
			if (!in_array($post->post_type, $post_types, false)) {
				$is_lists_authorized = false;
				$is_singular = false;
			}
		}
	
		if ($this->general_options['deactivate_homepage']) {
			if (is_home() || is_front_page()) {
				$is_lists_authorized = false;
				$is_singular = false;
			}
		}
	
		if ($this->general_options['display_exclude_from'] != "") {
			$excule_from = explode(',', $this->general_options['display_exclude_from']);
				
			$excule_from = array_map('trim', $excule_from);
				
			if (in_array(get_the_ID(), $excule_from, false)) {
				$is_singular = false;
				$is_lists_authorized = false;
			}
		}
		
		if (ESSBCoreHelper::is_module_deactivate_on('share')) {
			$is_singular = false;
			$is_lists_authorized = false;
		}
	
		// additional plugin hacks
		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		if ($request_uri != '') {
			$exist_ai1ec_export = strpos($request_uri, 'ai1ec_exporter_controller');
			if ($exist_ai1ec_export !== false) {
				$is_singular = false; $is_lists_authorized = false;
			}
	
			$exist_tribe_cal = strpos($request_uri, 'ical=');
			if ($exist_tribe_cal !== false) {
				$is_singular = false; $is_lists_authorized = false;
			}
		}
	
		// check post meta for turned off
		$essb_off = get_post_meta(get_the_ID(),'essb_off',true);
	
		if ($essb_off == "true") {
			$is_lists_authorized = false;
			$is_singular = false;
		}
	
		// deactivate on mobile devices if selected
		if ($this->is_mobile()) {
			if (ESSBOptionValuesHelper::options_value($this->options, $location.'_mobile_deactivate')) {
				$is_singular = false;
				$is_lists_authorized = false;
			}
		}
	
		if ($is_exclusive_active) {
			$is_singular = true;
		}
	
		// check current location settings
		if ($is_singular || $is_lists_authorized) {
			return true;
		}
		else {
			return false;
		}
	}
	
	// -- additional plugin special integration hooks
	
	function handle_woocommerce_integration() {
		if ($this->check_applicability_module('woocommerce')) {
			printf('%1$s<div style="clear: both;"></div>', $this->generate_share_buttons('woocommerce', 'share', array('only_share' => false, 'post_type' => 'woocommerce')));
		}
	}

	function handle_wpecommerce_integration() {
		if ($this->check_applicability_module('wpecommerce')) {
			printf('%1$s<div style="clear: both;"></div>', $this->generate_share_buttons('wpecommerce', 'share', array('only_share' => false, 'post_type' => 'wpecommerce')));
		}
	}

	function handle_jigoshop_integration() {
		if ($this->check_applicability_module('jigoshop')) {
			printf('%1$s<div style="clear: both;"></div>', $this->generate_share_buttons('jigoshop', 'share', array('only_share' => false, 'post_type' => 'jigoshop')));
		}
	}

	function handle_ithemes_integration() {
		if ($this->check_applicability_module('ithemes')) {
			printf('%1$s<div style="clear: both;"></div>', $this->generate_share_buttons('ithemes', 'share', array('only_share' => false, 'post_type' => 'ithemes')));
		}
	}

	function handle_ithemes_purchase_integration() {
		if ($this->check_applicability_module('ithemes')) {
			$activity_link = it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'product_id', 'return' => true ) );
			$activity_title =  it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'title', 'return' => true ) );
			$activity_link = get_permalink($activity_link);
				
			printf('%1$s<div style="clear: both;"></div>', $this->generate_share_buttons('ithemes', 'share', array('only_share' => false, 'post_type' => 'ithemes', 'url' => $activity_link, 'title' => $activity_title)));
		}
	}
	
	function handle_bbpress_integration() {
		if ($this->check_applicability_module('bbpress')) {
			printf('%1$s<div style="clear: both;"></div>', $this->generate_share_buttons('bbpress', 'share', array('only_share' => false, 'post_type' => 'bbpres')));
		}
	}

	function handle_buddypress_group_integration() {
		if ($this->check_applicability_module('buddypress')) {
			$activity_link = bp_get_group_permalink();
			$activity_title =  bp_get_group_name();
			printf('%1$s<div style="clear: both;"></div>', $this->generate_share_buttons('buddypress', 'share', array('only_share' => false, 'post_type' => 'buddypress', 'url' => $activity_link, 'title' => $activity_title)));
		}
	}

	function handle_buddypress_activity_integration() {
		if ($this->check_applicability_module('buddypress')) {
			$activity_type = bp_get_activity_type();
			$activity_link = bp_get_activity_thread_permalink();
			$activity_title = bp_get_activity_feed_item_title();
			printf('%1$s<div style="clear: both;"></div>', $this->generate_share_buttons('buddypress', 'share', array('only_share' => false, 'post_type' => 'buddypress', 'url' => $activity_link, 'title' => $activity_title)));
		}
	}
	
	
	function after_comment_trigger( $location ){

		$newurl = $location;
	
		if (ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_display_comment') || ESSBOptionValuesHelper::options_bool_value($this->options, 'flyin_display_comment')) {
			$newurl = substr( $location, 0, strpos( $location, '#comment' ) );
			$delimeter = false === strpos( $location, '?' ) ? '?' : '&';
			$params = 'essb_popup=true';
	
			$newurl .= $delimeter . $params;
		}
	
		return $newurl;
	}
	
	function trigger_bottom_mark($content) {
		return $content.'<div class="essb_break_scroll"></div>';	
	}	
	
	function display_sharebottom($is_shortcode = false, $shortcode_options = array(), $share_options = array()) {
		if (!$this->is_mobile_safecss()) { return; }
		$post_types = $this->general_options['display_in_types'];
		
		$is_valid = false;
		
		$output = "";
		
		if ($is_shortcode == true) {
			$is_valid = true;
		} 
		else {
			$is_valid = $this->check_applicability($post_types, 'sharebottom');
		}
		
		if ($is_valid) {
			
			if (!$is_shortcode) {
				printf('<div class="essb-mobile-sharebottom">%1$s</div>', $this->generate_share_buttons('sharebottom'));
			}
			else {
				$output = sprintf('<div class="essb-mobile-sharebottom">%1$s</div>', $this->generate_share_buttons('sharebottom', 'share', $share_options, true, $shortcode_options));
			}
		}
		
		if ($is_shortcode) {
			return $output;
		}
	}
	
	function display_sharebar($is_shortcode = false, $shortcode_options = array(), $share_options = array()) {
		if (!$this->is_mobile_safecss()) {
			return;
		}
		$post_types = $this->general_options['display_in_types'];
		
		$is_valid = false;
		
		if ($is_shortcode == true) {
			$is_valid = true;
		}
		else {
			$is_valid = $this->check_applicability($post_types, 'sharebar');
		}
		
		$output = "";
		if ($is_valid) {
			$mobile_sharebar_text = ESSBOptionValuesHelper::options_value($this->options, 'mobile_sharebar_text');
			if ($mobile_sharebar_text == "") { $mobile_sharebar_text = "Share this"; }
			
			$output = "";
			
			$output .= '<div class="essb-mobile-sharebar" onclick="essb_mobile_sharebar_open();">';
			$output .= sprintf ('<div class="essb-mobile-sharebar-icon"></div><div class="essb-mobile-sharebar-text">%1$s</div>', $mobile_sharebar_text);
			$output .= '</div>';
			
			$output .= '<div class="essb-mobile-sharebar-window">';
			$output .= '<div class="essb-mobile-sharebar-window-close-title" onclick="essb_mobile_sharebar_close(); return false;">';
			$output .= '<a href="#" class="essb-mobile-sharebar-window-close" ></a>';
			$output .= '</div>';
			$output .= '<div class="essb-mobile-sharebar-window-content">';
			if (!$is_shortcode) {
				$output .= $this->generate_share_buttons('sharebar');
			}
			else {
				$output .= $this->generate_share_buttons('sharebar', 'share', $share_options, true, $shortcode_options);
			}
			$output .= '</div>';
			$output .= '</div>';
			$output .= '<div class="essb-mobile-sharebar-window-shadow"></div>';
			
			if (!$is_shortcode) {
				echo $output;
			}			
		}
		
		if ($is_shortcode) {
			return $output;
		}
	}
	
	
	function display_sharepoint($is_shortcode = false, $shortcode_options = array(), $share_options = array()) {
		if (!$this->is_mobile_safecss()) {
			return;
		}
		$post_types = $this->general_options['display_in_types'];
	
		$is_valid = false;
		
		if ($is_shortcode == true) {
			$is_valid = true;
		}
		else {
			$is_valid = $this->check_applicability($post_types, 'sharepoint');
		}
		
		$output = "";
		
		if ($is_valid) {
				
			$output .= '<div class="essb-mobile-sharepoint" onclick="essb_mobile_sharebar_open();">';
			$output .= '<div class="essb-mobile-sharepoint-icon"></div>';
			$output .= '</div>';
				
			$output .= '<div class="essb-mobile-sharebar-window">';
			$output .= '<div class="essb-mobile-sharebar-window-close-title" onclick="essb_mobile_sharebar_close(); return false;">';
			$output .= '<a href="#" class="essb-mobile-sharebar-window-close" ></a>';
			$output .= '</div>';
			$output .= '<div class="essb-mobile-sharebar-window-content">';
			if (!$is_shortcode) {
				$output .= $this->generate_share_buttons('sharepoint');
			}
			else {
				$output .= $this->generate_share_buttons('sharepoint', 'share', $share_options, true, $shortcode_options);
			}
			$output .= '</div>';
			$output .= '</div>';
			$output .= '<div class="essb-mobile-sharebar-window-shadow"></div>';
			
			if (!$is_shortcode) {
				echo $output;
			}
		}
		
		if ($is_shortcode) {
			return $output;
		}
	}
	
	function display_topbar ($is_shortcode = false, $shortcode_options = array(), $share_options = array()) {
		$post_types = $this->general_options['display_in_types'];
		
		$is_valid = false;
		
		if ($is_shortcode == true) {
			$is_valid = true;
		} 
		else {
			$is_valid = $this->check_applicability($post_types, 'topbar');
		}
		
		$output = "";
		
		if ($is_valid) {
			$topbar_content_area = ESSBOptionValuesHelper::options_bool_value($this->options, 'topbar_contentarea');
			$topbar_content_area_pos = ESSBOptionValuesHelper::options_value($this->options, 'topbar_contentarea_pos');
			$topbar_buttons_align = ESSBOptionValuesHelper::options_value($this->options, 'topbar_buttons_align', 'left');
			$topbar_usercontent = ESSBOptionValuesHelper::options_value($this->options, 'topbar_usercontent');
				
			if ($is_shortcode) {
				if (!empty($shortcode_options['topbar_contentarea'])){
					$topbar_content_area = ESSBOptionValuesHelper::unified_true($shortcode_options['topbar_contentarea']);
				}
				if (!empty($shortcode_options['topbar_contentarea_pos'])) {
					$topbar_contentarea_pos = $shortcode_options['topbar_contentarea_pos'];
				}
				if (!empty($shortcode_options['topbar_buttons_align'])) {
					$topbar_buttons_align = $shortcode_options['topbar_buttons_align'];
				}
				if (!empty($shortcode_options['topbar_usercontent'])) {
					$topbar_usercontent = $shortcode_options['topbar_usercontent'];
				}
			}
			
			$topbar_usercontent = stripslashes($topbar_usercontent);
			$topbar_usercontent = do_shortcode($topbar_usercontent);
				
			if ($topbar_usercontent != '') {
				$topbar_usercontent = preg_replace(array('#%%title%%#', '#%%siteurl%%#', '#%%permalink%%#'), array(get_the_title(), get_site_url(), get_permalink()), $topbar_usercontent);				
			}
			
			if (!$is_shortcode) {
				$ssbuttons = $this->generate_share_buttons('topbar');
			}
			else {
				$ssbuttons = $this->generate_share_buttons('topbar', 'share', $share_options, true, $shortcode_options);
			}
			
			$output = '';
			
			$output .= '<div class="essb_topbar">';
			$output .= '<div class="essb_topbar_inner">';
			
			if (!$topbar_content_area) {
				$output .= sprintf('<div class="essb_topbar_inner_buttons essb_bar_withoutcontent essb_topbar_align_%1$s">', $topbar_buttons_align);
				$output .= $ssbuttons; 
				$output .= '</div>';
			}
			else {
				if ($topbar_content_area_pos == "left") {
					$output .= '<div class="essb_topbar_inner_content">';
					$output .= stripslashes($topbar_usercontent);
					$output .= '</div>';
					$output .= sprintf('<div class="essb_topbar_inner_buttons essb_topbar_align_%1$s">', $topbar_buttons_align);
					$output .= $ssbuttons;
					$output .= '</div>';
				}
				else {
					$output .= sprintf('<div class="essb_topbar_inner_buttons essb_topbar_align_%1$s">', $topbar_buttons_align);
					$output .= $ssbuttons;
					$output .= '</div>';
					$output .= '<div class="essb_topbar_inner_content">';
					$output .= stripslashes($topbar_usercontent);
					$output .= '</div>';
				}
			}
			$output .= '</div>';
			$output .= '</div>';
			
			if (!$is_shortcode) {
				echo $output;
			}
			
			$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_generate_topbar_reveal_code(), true, 'essb-topbar-code');
		}
		
		if ($is_shortcode) {
			return $output;
		}
	}
	
	function display_bottombar ($is_shortcode = false, $shortcode_options = array(), $share_options = array()) {
		$post_types = $this->general_options['display_in_types'];
	
		$is_valid = false;
		if ($is_shortcode) {
			$is_valid = true;
		}
		else {
			$is_valid = $this->check_applicability($post_types, 'bottombar');
		}
		
		$output = '';
		
		if ($is_valid) {
			$bottombar_content_area = ESSBOptionValuesHelper::options_bool_value($this->options, 'bottombar_contentarea');
			$bottombar_content_area_pos = ESSBOptionValuesHelper::options_value($this->options, 'bottombar_contentarea_pos');
			$bottombar_usercontent = ESSBOptionValuesHelper::options_value($this->options, 'bottombar_usercontent');
			$bottombar_buttons_align = ESSBOptionValuesHelper::options_value($this->options, 'bottombar_buttons_align', 'left');
			
			if ($is_shortcode) {
				if (!empty($shortcode_options['bottombar_contentarea'])){
					$bottombar_contentarea = ESSBOptionValuesHelper::unified_true($shortcode_options['bottombar_contentarea']);
				}
				if (!empty($shortcode_options['bottombar_contentarea_pos'])) {
					$bottombar_contentarea_pos = $shortcode_options['bottombar_contentarea_pos'];
				}
				if (!empty($shortcode_options['bottombar_buttons_align'])) {
					$bottombar_buttons_align = $shortcode_options['bottombar_buttons_align'];
				}
				if (!empty($shortcode_options['bottombar_usercontent'])) {
					$bottombar_usercontent = $shortcode_options['bottombar_usercontent'];
				}
			}
			
			$bottombar_usercontent = stripslashes($bottombar_usercontent);
			$bottombar_usercontent = do_shortcode($bottombar_usercontent);
				
			if ($bottombar_usercontent != '') {
				$bottombar_usercontent = preg_replace(array('#%%title%%#', '#%%siteurl%%#', '#%%permalink%%#'), array(get_the_title(), get_site_url(), get_permalink()), $bottombar_usercontent);
			}

			if (!$is_shortcode) {
				$ssbuttons = $this->generate_share_buttons('bottombar');
			}
			else {
				$ssbuttons = $this->generate_share_buttons('bottombar', 'share', $share_options, true, $shortcode_options);
			}
				
			$output .= '<div class="essb_bottombar">';
			$output .= '<div class="essb_bottombar_inner">';
				
			if (!$bottombar_content_area) {
				$output .= sprintf('<div class="essb_bottombar_inner_buttons essb_bar_withoutcontent essb_bottombar_align_%1$s">', $bottombar_buttons_align);
				$output .= $ssbuttons;
				$output .= '</div>';
			}
			else {
				if ($bottombar_content_area_pos == "left") {
					$output .= '<div class="essb_bottombar_inner_content">';
					$output .= stripslashes($bottombar_usercontent);
					$output .= '</div>';
					$output .= sprintf('<div class="essb_bottombar_inner_buttons essb_bottombar_align_%1$s">', $bottombar_buttons_align);
					$output .= $ssbuttons;
					$output .= '</div>';
				}
				else {
					$output .= sprintf('<div class="essb_bottombar_inner_buttons essb_bottombar_align_%1$s">', $bottombar_buttons_align);
					$output .= $ssbuttons;
					$output .= '</div>';
					$output .= '<div class="essb_bottombar_inner_content">';
					$output .= stripslashes($bottombar_usercontent);
					$output .= '</div>';
				}
			}
			$output .= '</div>';
			$output .= '</div>';
				
			if (!$is_shortcode) {
				echo $output;
			}
			
			$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_generate_bottombar_reveal_code(), true, 'essb-bottombar-code');
		}
	
		if ($is_shortcode) {
			return $output;
		}
	}
	
	function display_sidebar($is_shortcode = false, $shortcode_options = array(), $share_options = array()) {
		$post_types = $this->general_options['display_in_types'];
		
		$is_valid = false;
		if ($is_shortcode) {
			$is_valid = true;
		}
		else {
			$is_valid = $this->check_applicability($post_types, 'sidebar');
		}
		
		$output = '';
		
		if ($is_valid) {
			if (!$is_shortcode) {
				$output .= $this->generate_share_buttons('sidebar');
			}
			else {
				$output .= $this->generate_share_buttons('sidebar', 'share', $share_options, true, $shortcode_options);
			}
		
			if (!$is_shortcode) {
				echo $output;
			}
			$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_generate_sidebar_reveal_code(), true, 'essb-sidebar-code');
		}
		
		if ($is_shortcode) {
			return $output;
		}
	}

	function display_popup($is_shortcode = true, $shortcode_popafter = '', $shortcode_options = array(), $share_options = array()) {
		$post_types = $this->general_options['display_in_types'];
	
		$is_valid = false;
		if ($is_shortcode) {
			$is_valid = true;
		}
		else {
			$is_valid = $this->check_applicability($post_types, 'popup');
		}
		
		// @since 3.0.4 - avoid display popup for logged in users
		$popup_avoid_logged_users = ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_avoid_logged_users');
		if ($popup_avoid_logged_users) {
			if (is_user_logged_in()) {
				$is_valid = false;
			}
		}
		
		$output = '';
		
		if ($is_valid) {
			// loading popup display settings
			$popup_window_title = ESSBOptionValuesHelper::options_value($this->options, 'popup_window_title');
			$popup_user_message = ESSBOptionValuesHelper::options_value($this->options, 'popup_user_message');
			$popup_user_autoclose = ESSBOptionValuesHelper::options_value($this->options, 'popup_user_autoclose');
			
			// display settings
			$popup_user_width = ESSBOptionValuesHelper::options_value($this->options, 'popup_user_width');
			$popup_window_popafter = ESSBOptionValuesHelper::options_value($this->options, 'popup_window_popafter');
			$popup_user_percent = ESSBOptionValuesHelper::options_value($this->options, 'popup_user_percent');
			$popup_display_end = ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_display_end');
			$popup_user_manual_show = ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_user_manual_show');
			$popup_window_close_after = ESSBOptionValuesHelper::options_value($this->options, 'popup_window_close_after');
			$popup_user_notshow_onclose = ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_user_notshow_onclose');
			$popup_user_notshow_onclose_all = ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_user_notshow_onclose_all');
			
			if ($is_shortcode) {
				if (!empty($shortcode_popafter)) {
					$popup_window_popafter = $shortcode_popafter;
				}
				
				$shortcode_window_title = isset($shortcode_options['popup_title']) ? $shortcode_options['popup_title'] : '';
				$shortcode_window_message = isset($shortcode_options['popup_message']) ? $shortcode_options['popup_message'] : '';
				$shortcode_pop_on_percent = isset($shortcode_options['popup_percent']) ? $shortcode_options['popup_percent'] : '';
				$shortcode_pop_end = isset($shortcode_options['popup_end']) ? $shortcode_options['popup_end'] : '';
				
				if (!empty($shortcode_window_title)) {
					$popup_window_title = $shortcode_window_title;
				}
				if (!empty($shortcode_window_message)) {
					$popup_user_message = $shortcode_window_message;
				}
				if (!empty($shortcode_pop_on_percent)) {
					$popup_user_percent = $shortcode_pop_on_percent;
				}
				if (!empty($shortcode_pop_end)) {
					$popup_display_end = ESSBOptionValuesHelper::unified_true($shortcode_pop_end);
				}
			}
			
			$popup_trigger_oncomment = ESSBOptionValuesHelper::options_bool_value($this->options, 'popup_display_comment') ? " essb-popup-oncomment" : "";
			
			$output .= sprintf('<div class="essb-popup%10$s" data-width="%1$s" data-load-percent="%2$s" data-load-end="%3$s" data-load-manual="%4$s" data-load-time="%5$s" data-close-after="%6$s" data-close-hide="%7$s" data-close-hide-all="%8$s" data-postid="%9$s">', 
					$popup_user_width, $popup_user_percent, $popup_display_end, $popup_user_manual_show, $popup_window_popafter,
					$popup_window_close_after, $popup_user_notshow_onclose, $popup_user_notshow_onclose_all, get_the_ID(), $popup_trigger_oncomment);
			$output .= '<a href="#" class="essb-popup-close" onclick="essb_popup_close(); return false;"></a>';
			$output .= '<div class="essb-popup-content">';	
			
			if ($popup_window_title != '') {
				$output .= sprintf('<h3>%1$s</h3>', stripslashes($popup_window_title));
			}
			if ($popup_user_message != '') {
				$output .= sprintf('<div class="essb-popup-content-message">%1$s</div>', stripslashes($popup_user_message));
			}
			
			if (!$is_shortcode) {
				$output .= $this->generate_share_buttons('popup');
			}
			else {
				$output .= $this->generate_share_buttons('popup', 'share', $share_options, true, $shortcode_options);
			}
			
			if ($popup_window_close_after != '') {
				$output .= '<div class="essb_popup_counter_text"></div>';
			}
			
			$output .= '</div>';
			$output .= "</div>";
			$output .= '<div class="essb-popup-shadow" onclick="essb_popup_close(); return false;"></div>';
			
			if ($popup_window_popafter != '') {
				$output .= '<div style="display: none;" id="essb_settings_popafter_counter"></div>';
			}
			if ($popup_user_autoclose != '') {
				$output .= sprintf('<div id="essb_settings_popup_user_autoclose" style="display: none;">%1$s</div>', $popup_user_autoclose);
			}
			
			if (!$is_shortcode) {
				echo $output;
			}
		}
		
		if ($is_shortcode) {
			return $output;
		}
	}
	
	function display_flyin($is_shortcode = false, $shortcode_options = array(), $share_options = array()) {
		$post_types = $this->general_options['display_in_types'];
		
		$is_valid = false;
		if ($is_shortcode) {
			$is_valid = true;
		}
		else {
			$is_valid = $this->check_applicability($post_types, 'flyin');
		}
		
		$output = '';
	
		if ($is_valid) {
			// loading flyin display settings
			$flyin_window_title = ESSBOptionValuesHelper::options_value($this->options, 'flyin_window_title');
			$flyin_user_message = ESSBOptionValuesHelper::options_value($this->options, 'flyin_user_message');
			$flyin_user_autoclose = ESSBOptionValuesHelper::options_value($this->options, 'flyin_user_autoclose');
			$flyin_position = ESSBOptionValuesHelper::options_value($this->options, 'flyin_position');	
			// display settings
			$flyin_user_width = ESSBOptionValuesHelper::options_value($this->options, 'flyin_user_width');
			$flyin_window_popafter = ESSBOptionValuesHelper::options_value($this->options, 'flyin_window_popafter');
			$flyin_user_percent = ESSBOptionValuesHelper::options_value($this->options, 'flyin_user_percent');
			$flyin_display_end = ESSBOptionValuesHelper::options_bool_value($this->options, 'flyin_display_end');
			$flyin_user_manual_show = ESSBOptionValuesHelper::options_bool_value($this->options, 'flyin_user_manual_show');
			$flyin_window_close_after = ESSBOptionValuesHelper::options_value($this->options, 'flyin_window_close_after');
			$flyin_user_notshow_onclose = ESSBOptionValuesHelper::options_bool_value($this->options, 'flyin_user_notshow_onclose');
			$flyin_user_notshow_onclose_all = ESSBOptionValuesHelper::options_bool_value($this->options, 'flyin_user_notshow_onclose_all');
				
			$flyin_trigger_oncomment = ESSBOptionValuesHelper::options_bool_value($this->options, 'flyin_display_comment') ? " essb-flyin-oncomment" : "";
				
			
			if ($is_shortcode) {
				$shortcode_window_title = isset($shortcode_options['flyin_title']) ? $shortcode_options['flyin_title'] : '';
				$shortcode_window_message = isset($shortcode_options['flyin_message']) ? $shortcode_options['flyin_message'] : '';
				$shortcode_pop_on_percent = isset($shortcode_options['flyin_percent']) ? $shortcode_options['flyin_percent'] : '';
				$shortcode_pop_end = isset($shortcode_options['flyin_end']) ? $shortcode_options['flyin_end'] : '';
				
				if (!empty($shortcode_window_title)) {
					$flyin_window_title = $shortcode_window_title;
				}
				if (!empty($shortcode_window_message)) {
					$flyin_user_message = $shortcode_window_message;
				}
				if (!empty($shortcode_pop_on_percent)) {
					$flyin_user_percent = $shortcode_pop_on_percent;
				}
				if (!empty($shortcode_pop_end)) {
					$flyin_display_end = ESSBOptionValuesHelper::unified_true($shortcode_pop_end);
				}
			}
			
			$output .= sprintf('<div class="essb-flyin%10$s essb-flyin-%11$s" data-width="%1$s" data-load-percent="%2$s" data-load-end="%3$s" data-load-manual="%4$s" data-load-time="%5$s" data-close-after="%6$s" data-close-hide="%7$s" data-close-hide-all="%8$s" data-postid="%9$s">',
					$flyin_user_width, $flyin_user_percent, $flyin_display_end, $flyin_user_manual_show, $flyin_window_popafter,
					$flyin_window_close_after, $flyin_user_notshow_onclose, $flyin_user_notshow_onclose_all, get_the_ID(), $flyin_trigger_oncomment, $flyin_position);
			$output .= '<a href="#" class="essb-flyin-close" onclick="essb_flyin_close(); return false;"></a>';
			$output .= '<div class="essb-flyin-content">';
				
			if ($flyin_window_title != '') {
				$output .= sprintf('<h3>%1$s</h3>', stripslashes($flyin_window_title));
			}
			if ($flyin_user_message != '') {
				$output .= sprintf('<div class="essb-flyin-content-message">%1$s</div>', stripslashes($flyin_user_message));
			}
				
			if (!$is_shortcode) {
				$flyin_noshare = ESSBOptionValuesHelper::options_bool_value($this->options, 'flyin_noshare');
				if (!$flyin_noshare) {
					$output .= $this->generate_share_buttons('flyin');
				}
			}
			else {
				$output .= $this->generate_share_buttons('flyin', 'share', $share_options, true, $shortcode_options);
			}
				
			if ($flyin_window_close_after != '') {
				$output .= '<div class="essb_flyin_counter_text"></div>';
			}
				
			$output .= '</div>';
			$output .= "</div>";
				
			if ($flyin_window_popafter != '') {
				$output .= '<div style="display: none;" id="essb_settings_flyafter_counter"></div>';
			}
			if ($flyin_user_autoclose != '') {
				$output .= sprintf('<div id="essb_settings_flyin_user_autoclose" style="display: none;">%1$s</div>', $flyin_user_autoclose);
			}
			
			if (!$is_shortcode) {
				echo $output;
			}
		}
		
		if ($is_shortcode) {
			return $output;
		}
	}	
	
	function shortcode_display_postfloat($shortcode_options = array(), $share_options = array()) {
		$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_generate_postfloat_reveal_code(), true, 'essb-postfloat-code');
		
		$display_key = "postfloat";
		$float_onsingle_only = ESSBOptionValuesHelper::options_bool_value($this->options, 'float_onsingle_only');
		if ($float_onsingle_only) {
			if (is_archive() || is_front_page() || is_search() || is_tag() || is_post_type_archive() || is_home()) {
				$display_key = "top";
			}
		}
		
		return $this->generate_share_buttons($display_key, 'share', $share_options, true, $shortcode_options);
	}
	
	function display_postfloat($content) {
		//
		$links_before = "";
		$links_after = "";
		
		$display_key = "postfloat";
		$float_onsingle_only = ESSBOptionValuesHelper::options_bool_value($this->options, 'float_onsingle_only');
		if ($float_onsingle_only) {
			if (is_archive() || is_front_page() || is_search() || is_tag() || is_post_type_archive() || is_home()) {
				$display_key = "top";
			}
		}
		
		$post_types = $this->general_options['display_in_types'];
		if ($this->check_applicability($post_types, $display_key)) {
			$links_before = $this->generate_share_buttons($display_key);
			$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_generate_postfloat_reveal_code(), true, 'essb-postfloat-code');
		}
		
		return $links_before.$content.$links_after;
	}
	
	function display_excerpt($content) {
		$post_types = $this->general_options['display_in_types'];
		
		$links_before = "";
		$links_after = "";
		
		//print "is possible on excerpt: ".$this->check_applicability($post_types, 'excerpt');
		
		if ($this->check_applicability_excerpt($post_types, 'excerpt')) {
			
			if ($this->general_options['display_excerpt_pos'] == "top") {
				$links_before = $this->generate_share_buttons('excerpt');
			}
			if ($this->general_options['display_excerpt_pos'] == "bottom") {
				$links_after = $this->generate_share_buttons('excerpt');
			}
			
		}
		return $links_before.$content.$links_after;
	}
	
	function display_excerpt_avada() {
		echo $this->generate_share_buttons('excerpt');
	}
	
	function display_inline($content) {
		$links_before = "";
		$links_after = "";
				
		$post_types = $this->general_options['display_in_types'];
		$content_position = $this->general_options['content_position'];
		
		$check_location_options_top = "";
		$check_location_options_bottom = "";
		if ($content_position == "content_top" || $content_position == "content_both" || $content_position == "content_sharenative") {			
			if ($this->check_applicability($post_types, 'top')) {

				$share_buttons_only = ($content_position == "content_sharenative") ? true : false;
				
				$links_before = $this->generate_share_buttons('top', 'share', array('only_share' => $share_buttons_only));
			}
		}
		
		if ($content_position == "content_float" || $content_position == "content_floatboth") {
			
			$display_key = "float";
			$float_onsingle_only = ESSBOptionValuesHelper::options_bool_value($this->options, 'float_onsingle_only');
			if ($float_onsingle_only) {
				if (is_archive() || is_front_page() || is_search() || is_tag() || is_post_type_archive() || is_home()) {
					$display_key = "top";
				}
			}
			
			if ($this->check_applicability($post_types, $display_key)) {
				$links_before = $this->generate_share_buttons($display_key);
			}
		}
		
		if ($content_position == "content_bottom" || $content_position == "content_both" || $content_position == "content_nativeshare" || $content_position == "content_floatboth") {
		
			if ($this->check_applicability($post_types, 'bottom')) {
				$share_buttons_only = ($content_position == "content_nativeshare") ? true : false;
				$links_after = $this->generate_share_buttons('bottom', 'share', array('only_share' => $share_buttons_only));
			}
		}
		
		if ($content_position == "content_nativeshare") {
			if ($this->check_applicability($post_types, 'top')) {
				$links_before = $this->generate_like_buttons('top');
			}
		}

		if ($content_position == "content_sharenative") {
			if ($this->check_applicability($post_types, 'bottom')) {
				$links_after = $this->generate_like_buttons('bottom');
			}
		}
		
		return $links_before.$content.$links_after;
	}
 	
	// -- end: content display methods
	
	// start: buttons drawer
	
	function generate_like_buttons($position) {
		global $post;
		
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}	
		
		$cache_key = "";
		
		if (isset($post) && defined('ESSB3_CACHE_ACTIVE')) {
			$cache_key = sprintf('essb_cache_like_%1$s_%2$s', $post->ID, $position);
				
			$cached_data = ESSBDynamicCache::get($cache_key);
			
			if (!empty($cached_data)) {
				return $cached_data;
			}
		}
		
		$post_share_details = $this->get_post_share_details();
		
		$post_native_details['withshare'] = false;
		
		// generate native button main settings
		$post_native_details = $this->get_native_button_settings($position);
		$post_native_details['order'] = ($post_native_details['active']) ? ESSBNativeButtonsHelper::active_native_buttons() : array();
		$ssbuttons = "";

		if ($post_native_details['active']) {
			if (!$post_native_details['sameline']) {
				$post_native_details['withshare'] = false;
				$ssbuttons .= ESSBNativeButtonsHelper::draw_native_buttons($post_native_details, $post_native_details['order'], $post_native_details['counters'],
						$post_native_details['sameline'], $post_native_details['skinned']);
			}
		}
		
		// apply clean of new lines
		if (!empty($ssbuttons)) {
			$ssbuttons = trim(preg_replace('/\s+/', ' ', $ssbuttons));
		}
		
		if (!empty($cache_key)) {
			ESSBDynamicCache::put($cache_key, $ssbuttons);
		}
		
		return $ssbuttons;
	}
	
	function generate_share_buttons($position, $likeshare = 'share', $share_options = array(), $is_shortcode = false, $shortcode_options = array(), $media_url = '') {
		global $post;
		
		$only_share = ESSBOptionValuesHelper::options_bool_value($share_options, 'only_share');
		$post_type = ESSBOptionValuesHelper::options_value($share_options, 'post_type');

		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}
		
		$cache_key = "";
		
		if (isset($post) && defined('ESSB3_CACHE_ACTIVE') && !$is_shortcode) {
			$cache_key = sprintf('essb_cache_share_%1$s_%2$s', $post->ID, $position);
			
			$cached_data = ESSBDynamicCache::get($cache_key);
			if (!empty($cached_data)) {
				return $cached_data;
			}
		}
		
		if (empty($post_type) && isset($post)) {
			$post_type = $post->post_type;
		}
		
		// -- getting main share details based on current post
		$post_share_details = $this->get_post_share_details();
		
		// generate native button main settings
		$post_native_details = $this->get_native_button_settings($position, $only_share);		
		$post_native_details['order'] = ($post_native_details['active']) ? ESSBNativeButtonsHelper::active_native_buttons() : array();
		
		// apply shortcode options
		if ($is_shortcode) {
			if ($shortcode_options['forceurl']) {
				$post_share_details['url'] = ESSBUrlHelper::get_current_page_url();
			}
			
			if ($shortcode_options['url'] != '') {
				$post_share_details['url'] = $shortcode_options['url'];
			}
			if ($shortcode_options['title'] != '') {
				$post_share_details['title'] = $shortcode_options['title'];
				$post_share_details['title_plain'] = $shortcode_options['title'];
			}
			if ($shortcode_options['image'] != '') {
				$post_share_details['image'] = $shortcode_options['image'];
			}
			if ($shortcode_options['description'] != '') {
				$post_share_details['description'] = $shortcode_options['description'];
			}	

			// customize tweet message
			if ($shortcode_options['twitter_user'] != '') {
				$post_share_details['twitter_user'] = $shortcode_options['twitter_user'];
			}
			if ($shortcode_options['twitter_hashtags'] != '') {
				$post_share_details['twitter_hashtags'] = $shortcode_options['twitter_hashtags'];
			}
			if ($shortcode_options['twitter_tweet'] != '') {
				$post_share_details['twitter_tweet'] = $shortcode_options['twitter_tweet'];
			}
			else {
				if ($shortcode_options['title'] != '') {
					$post_share_details['twitter_tweet'] = $shortcode_options['title'];
				}
			}
			
			$affwp_active_shortcode = ESSBOptionValuesHelper::options_bool_value($this->options, 'affwp_active_shortcode');
			if ($affwp_active_shortcode) {
				$post_share_details['url'] = ESSBUrlHelper::generate_affiliatewp_referral_link($post_share_details['url']);
			}
		}
		else {
			// activate short url and custom campaign tracking codes
			// apply custom share options
			if (!empty($share_options['url'])) {
				$post_share_details['url'] = $share_options['url'];
			}
			if (!empty($share_options['title'])) {
				$post_share_details['title'] = $share_options['title'];
				$post_share_details['title_plain'] = $share_options['title_plain'];
			}
			if (!empty($share_options['image'])) {
				$post_share_details['image'] = $share_options['image'];
			}
			if (!empty($share_options['description'])) {
				$post_share_details['description'] = $share_options['description'];
			}
			
			// customize tweet message
			if (!empty($share_options['twitter_user'])) {
				$post_share_details['twitter_user'] = $share_options['twitter_user'];
			}
			if (!empty($share_options['twitter_hashtags'])) {
				$post_share_details['twitter_hashtags'] = $share_options['twitter_hashtags'];
			}
			if (!empty($share_options['twitter_tweet'])) {
				$post_share_details['twitter_tweet'] = $share_options['twitter_tweet'];
			}
				
			if ($media_url != '') {
				$post_share_details['image'] = $media_url;
				$post_share_details['user_image_url'] = $media_url;
			}
			
			// Google Campaign Tracking code
			$ga_campaign_tracking = $this->general_options['activate_ga_campaign_tracking'];
			$post_ga_campaign_tracking = get_post_meta(get_the_ID(), 'essb_activate_ga_campaign_tracking', true);
			if ($post_ga_campaign_tracking != '') {
				$ga_campaign_tracking = $post_ga_campaign_tracking;
			}
			
			if ($ga_campaign_tracking != '') {
				$post_share_details['url'] = ESSBUrlHelper::attach_tracking_code($post_share_details['url'], $ga_campaign_tracking);
			}						
		}
			
		// @since 3.1.2 exist filter to control the share address
		if (has_filter('essb3_share_url')) {
			$post_share_details['url'] = apply_filters('essb3_share_url', $post_share_details['url']);
		}
		
	    // -- short url code block
		$post_share_details ['full_url'] = $post_share_details ['url'];
		if ($this->network_options ['twitter_shareshort'] || $this->general_options ['shorturl_activate'] || $this->network_options ['whatsapp_shareshort']) {
			$global_provider = $this->general_options ['shorturl_type'];
			if ($this->general_options ['shorturl_activate']) {
				$post_share_details ['short_url'] = ESSBUrlHelper::short_url ( $post_share_details ['url'], $global_provider, get_the_ID (), $this->general_options ['shorturl_bitlyuser'], $this->general_options ['shorturl_bitlyapi'] );
				
				$post_share_details ['short_url_twitter'] = $post_share_details ['short_url'];
				$post_share_details ['short_url_whatsapp'] = $post_share_details ['short_url'];
			} else {
				if ($this->network_options ['twitter_shareshort']) {
					$provider = $this->network_options ['twitter_shareshort_service'];
					$post_share_details ['short_url_twitter'] = ESSBUrlHelper::short_url ( $post_share_details ['url'], $provider, get_the_ID (), $this->general_options ['shorturl_bitlyuser'], $this->general_options ['shorturl_bitlyapi'] );
				}
				
				if ($this->network_options ['whatsapp_shareshort']) {
					$provider = $this->network_options ['whatsapp_shareshort_service'];
					
					if ($provider == $this->network_options ['twitter_shareshort_service'] && $this->network_options ['twitter_shareshort']) {
						$post_share_details ['short_url_whatsapp'] = $post_share_details ['short_url_twitter'];
					} else {
						$post_share_details ['short_url_whatsapp'] = ESSBUrlHelper::short_url ( $post_share_details ['url'], $provider, get_the_ID (), $this->general_options ['shorturl_bitlyuser'], $this->general_options ['shorturl_bitlyapi'] );
					}
				}
				
				if ($post_share_details ['short_url_twitter'] == '') {
					$post_share_details ['short_url_twitter'] = $post_share_details ['url'];
				}
				if ($post_share_details ['short_url_whatsapp'] == '') {
					$post_share_details ['short_url_whatsapp'] = $post_share_details ['url'];
				}
			}
			
		}
		else {
			$post_share_details ['short_url'] = $post_share_details ['url'];
			$post_share_details ['short_url_twitter'] = $post_share_details ['url'];
			$post_share_details ['short_url_whatsapp'] = $post_share_details ['url'];
		}		
		//-- end: short url code block
		
		// -- main button design
		$button_style = $this->get_buttons_visual_options($position);
				
		$social_networks = $this->network_options['networks'];
		$social_networks_order = $this->network_options['networks_order'];
		$social_networks_names = $this->network_options['default_names'];
		
		// apply settings based on position when active
		$check_position_settings_key = $position;
		
		if ($this->is_mobile() && ESSBOptionValuesHelper::is_active_position_settings('mobile')) {
			$check_position_settings_key = 'mobile';
		}
		
		// double check to avoid missconfiguration based on mobile specific settings
		if ($check_position_settings_key != 'sharebar' && $check_position_settings_key != 'sharepoint' && $check_position_settings_key != 'sharebottom') {
			// first check for post type settins - if there are such that will be the settings key. If nothing is active switch to button position
			// settings
			if (!empty($post_type)) {
				if (ESSBOptionValuesHelper::is_active_position_settings(sprintf('post-type-%1$s', $post_type))) {
					$check_position_settings_key = sprintf('post-type-%1$s', $post_type);
				}
			}
			
			if (ESSBOptionValuesHelper::is_active_position_settings($check_position_settings_key)) {
				$button_style = ESSBOptionValuesHelper::apply_position_style_settings($check_position_settings_key, $button_style);
				
				$instance_template = $button_style['template'];
				$instance_template_slug = ESSBCoreHelper::template_folder($instance_template);
				
				if ($instance_template_slug != $this->design_options['template_slug']) {
					$use_minifed_css = ($this->general_options['use_minified_css']) ? ".min" : "";
					$template_url = ESSB3_PLUGIN_URL.'/assets/css/'.$instance_template_slug.'/easy-social-share-buttons'.$use_minifed_css.'.css';
					
					$this->resource_builder->add_static_footer_css($template_url, 'easy-social-share-buttons-'.$instance_template_slug);
				}
				
				$personalized_networks = ESSBOptionValuesHelper::get_active_social_networks_by_position($check_position_settings_key);
				$personalized_network_order = ESSBOptionValuesHelper::get_order_of_social_networks_by_position($check_position_settings_key);
				
				if (is_array($personalized_networks) && count($personalized_networks) > 0) {
					$social_networks = $personalized_networks;
				}
				
				if (is_array($personalized_network_order) && count($personalized_network_order) > 0) {
					$social_networks_order = $personalized_network_order;
				}
				
				$social_networks_names = ESSBOptionValuesHelper::apply_position_network_names($position, $social_networks_names);
			}
			
		}
		
		// apply safe default of mobile styles to avoid miss configured display
		$share_bottom_networks = array();
		if ($position == 'sharebar' || $position == 'sharepoint' || $position == 'sharebottom') {			
			// apply mobile personalizations by display methods
			if (ESSBOptionValuesHelper::is_active_position_settings($position)) {
				$button_style = ESSBOptionValuesHelper::apply_mobile_position_style_settings($position, $button_style);
				$instance_template = $button_style['template'];
				$instance_template_slug = ESSBCoreHelper::template_folder($instance_template);
				
				if ($instance_template_slug != $this->design_options['template_slug']) {
					$use_minifed_css = ($this->general_options['use_minified_css']) ? ".min" : "";
					$template_url = ESSB3_PLUGIN_URL.'/assets/css/'.$instance_template_slug.'/easy-social-share-buttons'.$use_minifed_css.'.css';
						
					$this->resource_builder->add_static_footer_css($template_url, 'easy-social-share-buttons-'.$instance_template_slug);
				}
				
				$personalized_networks = ESSBOptionValuesHelper::get_active_social_networks_by_position($position);
				$personalized_network_order = ESSBOptionValuesHelper::get_order_of_social_networks_by_position($position);
				
				if (is_array($personalized_networks) && count($personalized_networks) > 0) {
					$social_networks = $personalized_networks;
				}
				
				if (is_array($personalized_network_order) && count($personalized_network_order) > 0) {
					$social_networks_order = $personalized_network_order;
				}
				
				$social_networks_names = ESSBOptionValuesHelper::apply_position_network_names($position, $social_networks_names);
			}
			
			// apply sharebar and sharepoint default styles
			if ($position == 'sharebar' || $position == 'sharepoint') {
				
				// for those display methods the more buttons is not needed
				if (in_array('more', $social_networks)) {
					if(($key = array_search('more', $social_networks)) !== false) {
						unset($social_networks[$key]);
					}
				}
				
				$button_style['button_style'] = "button";				
				if ($button_style['show_counter']) {
					if (strpos($button_style['counter_pos'], 'inside') === false && strpos($button_style['counter_pos'], 'hidden') === false) {
						$button_style['counter_pos'] = "insidename";
					}
		
					if ($button_style['total_counter_pos'] != 'hidden' && $button_style['total_counter_pos'] != 'after') {
						$button_style['total_counter_pos'] = "before";
					}
				}
				$button_style['button_width'] = "column";
				$button_style['button_width_columns'] = "1";
			}
			
			if ($position == 'sharebottom') {
				if (in_array('more', $social_networks)) {
					if(($key = array_search('more', $social_networks)) !== false) {
						unset($social_networks[$key]);
					}
				}

				$button_style['button_style'] = "icon";
				$button_style['show_counter'] = false;
				$button_style['nospace'] = true;
				$button_style['button_width'] = "column";
				
				
				$available_networks_count = ESSBOptionValuesHelper::options_value($this->options, 'mobile_sharebuttonsbar_count');
				$mobile_sharebuttonsbar_names = ESSBOptionValuesHelper::options_bool_value($this->options, 'mobile_sharebuttonsbar_names');
				if ($mobile_sharebuttonsbar_names) {
					$button_style['button_style'] = 'button';
				}
				
				if (intval($available_networks_count) == 0) {
					$available_networks_count = 4;
				}
				if (count($social_networks) > intval($available_networks_count)) {
					$share_bottom_networks = $social_networks;
					array_splice($social_networks, intval($available_networks_count) - 1);
					$social_networks[] = "more";
					//$button_style['more_button_icon'] = "dots";
				}
				
				$button_style['button_width_columns'] = intval($available_networks_count);

			}
		}
		
		if (!is_array($social_networks)) { $social_networks = array(); }
		if (!is_array($social_networks_order) || count($social_networks_order) == 0) {
			$social_networks_order = ESSBCoreHelper::generate_network_list();
		}

		// apply shortcode customizations
		if ($is_shortcode) {
			
			// apply personalization of social networks if set from shortcode
			if (count($shortcode_options['networks']) > 0) {
				$social_networks = $shortcode_options['networks'];
				$social_networks_order = $shortcode_options['networks'];				
			}
			
			if ($shortcode_options['customize_texts']) {
				$social_networks_names = $shortcode_options['network_texts'];
			}
			
			// apply shortcode counter options
			if ($shortcode_options['counters'] == 1) {
				$button_style['show_counter'] = true;
			}
			else {
				$button_style['show_counter'] = false;
			}
			
			if (!empty($shortcode_options['style'])) {
				$button_style['button_style'] = $shortcode_options['style'];
			}
			if (!empty($shortcode_options['counters_pos'])) {
				$button_style['counter_pos'] = $shortcode_options['counters_pos'];
			}
			if (!empty($shortcode_options['total_counter_pos'])) {
				$button_style['total_counter_pos'] = $shortcode_options['total_counter_pos'];
			}
			if ($shortcode_options['hide_total']) {
				$button_style['total_counter_pos'] = "hidden";
			}
			
			if ($shortcode_options['fullwidth']) {
				$button_style['button_width'] = "full";
				
				if (!empty($shortcode_options['fullwidth_fix'])) {
					$button_style['button_width_full_button'] = $shortcode_options['fullwidth_fix'];
				}
				if (!empty($shortcode_options['fullwidth_align'])) {
					$button_style['fullwidth_align'] = $shortcode_options['fullwidth_align'];
				}
			}
			
			if ($shortcode_options['fixedwidth']) {
				$button_style['button_width'] = "fixed";
				
				if (!empty($shortcode_options['fixedwidth_px'])) {
					$button_style['button_width_fixed_value'] = $shortcode_options['fixedwidth_px'];
				}
				if (!empty($shortcode_options['fixedwidth_align'])) {
					$button_style['button_width_fixed_align'] = $shortcode_options['fixedwidth_align'];
				}
			}
			
			if (!empty($shortcode_options['morebutton'])) {
				$button_style['more_button_func'] = $shortcode_options['morebutton'];
			}
			
			if (!empty($shortcode_options['morebutton_icon'])) {
				$button_style['more_button_icon'] = $shortcode_options['morebutton_icon'];
			}
			
			if ($shortcode_options['column']) {
				$button_style['button_width'] = "column";
				if (!empty($shortcode_options['columns'])) {
					$button_style['button_width_columns'] = $shortcode_options['columns'];
				}
			}
			
			if (!empty($shortcode_options['template'])) {
				$instance_template_slug = $shortcode_options['template'];
				if ($instance_template_slug != $this->design_options['template_slug']) {
					$use_minifed_css = ($this->general_options['use_minified_css']) ? ".min" : "";
					$template_url = ESSB3_PLUGIN_URL.'/assets/css/'.$instance_template_slug.'/easy-social-share-buttons'.$use_minifed_css.'.css';
						
					$this->resource_builder->add_static_footer_css($template_url, 'easy-social-share-buttons-'.$instance_template_slug);
				}
				$button_style['template'] = $shortcode_options['template'];
			}
			
			if (!empty($shortcode_options['sidebar_pos'])) {
				$button_style['sidebar_pos'] = $shortcode_options['sidebar_pos'];
			}
			
			$button_style['nospace'] = $shortcode_options['nospace'];
			$button_style['nostats'] = $shortcode_options['nostats'];
			
			if (!empty($shortcode_options['fblike'])) {
				$post_native_details['facebook_url'] = $shortcode_options['fblike'];
			}
			if (!empty($shortcode_options['plusone'])) {
				$post_native_details['google_url'] = $shortcode_options['plusone'];
			}
			
			if (!$shortcode_options['message']) {
				$button_style['message_share_buttons'] = "";
				$button_style['message_before_share_buttons'] = "";
			}
			
			//apply again mobile settings for the mobile buttons bar
			if ($position == 'sharebottom') {
				if (in_array('more', $social_networks)) {
					if(($key = array_search('more', $social_networks)) !== false) {
						unset($social_networks[$key]);
					}
				}
			
				$button_style['button_style'] = "icon";
				$button_style['show_counter'] = false;
				$button_style['nospace'] = true;
				$button_style['button_width'] = "column";
			
			
				$available_networks_count = ESSBOptionValuesHelper::options_value($this->options, 'mobile_sharebuttonsbar_count');
				$mobile_sharebuttonsbar_names = ESSBOptionValuesHelper::options_bool_value($this->options, 'mobile_sharebuttonsbar_names');
				if ($mobile_sharebuttonsbar_names) {
					$button_style['button_style'] = 'button';
				}
			
				if (intval($available_networks_count) == 0) {
					$available_networks_count = 4;
				}
				if (count($social_networks) > intval($available_networks_count)) {
					$share_bottom_networks = $social_networks;
					array_splice($social_networks, intval($available_networks_count) - 1);
					$social_networks[] = "more";
					$social_networks_order[] = "more";
					//$button_style['more_button_icon'] = "dots";
				}
			
				$button_style['button_width_columns'] = intval($available_networks_count);

			}
		}
		
		// generate unique instance key
		$salt = mt_rand();
		// attache compliled mail message data
		if (in_array("mail", $social_networks)) {
			//if ($this->network_options['mail_function'] == "form") {
				$base_subject = $this->network_options['mail_subject'];
				$base_body = $this->network_options['mail_body'];
			
				$base_subject = preg_replace(array('#%%title%%#', '#%%siteurl%%#', '#%%permalink%%#', '#%%image%%#', '#%%shorturl%%#'), array($post_share_details['title_plain'], get_site_url(), $post_share_details['url'], $post_share_details['image'], $post_share_details['short_url']), $base_subject);
				$base_body = preg_replace(array('#%%title%%#', '#%%siteurl%%#', '#%%permalink%%#', '#%%image%%#', '#%%shorturl%%#'), array($post_share_details['title_plain'], get_site_url(), $post_share_details['url'], $post_share_details['image'], $post_share_details['short_url']), $base_body);

				$post_share_details['mail_subject'] = $base_subject;
				$post_share_details['mail_body'] = $base_body;
				
				$ga_tracking = ESSBOptionValuesHelper::options_value($this->options, 'activate_ga_campaign_tracking');
				if ($ga_tracking != '') {
					$post_share_details['mail_subject'] = str_replace('{network}', 'mail', $post_share_details['mail_subject']);
					
					$post_share_details['mail_body'] = str_replace('{title}', $post_share_details['title_plain'], $post_share_details['mail_body']);
				}
			//}
			//else {
			//	$post_share_details['mail_subject'] = '';
			//	$post_share_details['mail_body'] = '';
			//}
			$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_generate_popup_mailform(), true, 'essb-mailform');
			$this->resource_builder->add_js(ESSBButtonHelper::print_mailer_code($post_share_details['mail_subject'], $post_share_details['mail_body'], 
					$salt, $post_share_details["post_id"], $position), true, 'essb-mailform-'.$salt);
			
		}
		
		$button_style['included_button_count'] = count($social_networks);
		
		$intance_morebutton_func = $this->network_options['more_button_func'];
		if ($position == "sidebar" || $position == "postfloat") {
			//$intance_morebutton_func = "2";
			if ($button_style['more_button_func'] == '1') {
				$button_style['more_button_func'] = "2";
			}
		}
		if ($position == "sharebottom") {
			//$intance_morebutton_func = "3";
			$button_style['more_button_func'] = "3";
		}
		
		//$button_style['more_button_func'] = $intance_morebutton_func;
		
		// sidebar close button option if activated into settings
		if ($this->design_options['sidebar_leftright_close'] && $position == "sidebar") {
			$social_networks[] = "sidebar-close";
			$social_networks_order[] = "sidebar-close";
		}
		
		// apply additional native button options
		if ($post_native_details['active']) {
			$post_native_details['url'] = $post_share_details['url'];
			$post_native_details['text'] = $post_share_details['title'];
		}
		
		// @since 3.0 beta 4 - check if on post settings we have set counters that are not active generally
		if ($button_style['show_counter']) {
			if (!isset($this->activated_resources['counters'])) {
				if (!defined('ESSB3_COUNTER_LOADED')) {
					$script_url = ESSB3_PLUGIN_URL .'/assets/js/easy-social-share-buttons'.$this->use_minified_js.'.js';
					$this->resource_builder->add_static_resource_footer($script_url, 'easy-social-share-buttons', 'js');
					$this->activated_resources['counters'] = true;
					define('ESSB3_COUNTER_LOADED', true);
				}
			}				
		}
		
		// @since 3.0.3 fix for the mail function
		$button_style['mail_function'] = $this->network_options['mail_function'];
		
		// @since 3.2 - passing mobile state to button style to allow deactivate advaned share on mobile (does not work);
		$button_style['is_mobile'] = $this->is_mobile();

		$ssbuttons = ESSBButtonHelper::draw_share_buttons($post_share_details, $button_style, 
				$social_networks, $social_networks_order, $social_networks_names, $position, $salt, $likeshare, $post_native_details);
		
		//print_r($post_native_details);
		if ($post_native_details['active']) {
			if (!$post_native_details['sameline']) {
				$post_native_details['withshare'] = true;
				//@fixed display of native for float in 3.0beta5
				$native_buttons_code = ESSBNativeButtonsHelper::draw_native_buttons($post_native_details, $post_native_details['order'], $post_native_details['counters'], 
						$post_native_details['sameline'], $post_native_details['skinned']);
				
				$ssbuttons = str_replace('</div>', $native_buttons_code.'</div>', $ssbuttons);
			}
		}
		
		if ($button_style['button_width'] == "fixed") {
			//print "fixed button code adding";
			$fixedwidth_key = $button_style['button_width_fixed_value'] . "-" . $button_style['button_width_fixed_align'];
			$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_fixedwidth_button($salt, $button_style['button_width_fixed_value'], $button_style['button_width_fixed_align']), 'essb-fixed-width-'.$fixedwidth_key, 'footer');
		}
		if ($button_style['button_width'] == "full") {
			//print_r($button_style);
			//print "fixed button code adding";
			$container_width = $button_style['button_width_full_container'];

			$single_button_width = intval($container_width) / count($social_networks);
			//print "calculate width = ".$single_button_width . ' - '.$container_width;
			$single_button_width = floor($single_button_width);
			$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_fullwidth_button($single_button_width, $button_style['button_width_full_button'], $button_style['button_width_full_container']), 'essb-full-width-'.$single_button_width.'-'.$button_style['button_width_full_button'].'-'.$button_style['button_width_full_container'], 'footer');
		}
		
		// more buttons code append
		if (in_array("more", $social_networks)) {
			
			//print "position = ".$position. ", more button = ".$intance_morebutton_func;
			$user_set_morebutton_func = $button_style['more_button_func'];
			if ($user_set_morebutton_func == '1') {
				$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_generate_more_button_inline(), true, 'essb-inlinemore-code');
			}				
			if (($user_set_morebutton_func == '2' || $user_set_morebutton_func == '3')) {
				$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_generate_more_button_popup(), true, 'essb-popupmore-code');
				
				$listAllNetworks = ($user_set_morebutton_func == '2') ? true: false;
				$more_social_networks = ESSBCoreHelper::generate_list_networks($listAllNetworks);

				$more_social_networks_order = ESSBCoreHelper::generate_network_list();
				
				if ($position == "sharebottom") {
					$more_social_networks = $share_bottom_networks;
					$more_social_networks_order = $social_networks_order;
					//$button_style['more_button_icon'] = "dots";
				}
				
				$button_style['button_style'] = "button";
				$button_style['show_counter'] = false;
				$button_style['button_width'] = "column";
				$button_style['button_width_columns'] = ($this->is_mobile() ? "1" : "3");
				$button_style['counter_pos'] = "left";
				
				if ($position == "sharebottom") {
					$button_style['button_width_columns'] = "1";
				}
				
				$more_salt = mt_rand();
				
				$ssbuttons .= sprintf('<div class="essb_morepopup essb_morepopup_%1$s" style="display:none;">
						<a href="#" class="essb_morepopup_close" onclick="essb_toggle_less_popup(\'%1$s\'); return false;"></a>
						<div class="essb_morepopup_content essb_morepopup_content_%1$s">%2$s</div></div>
						<div class="essb_morepopup_shadow essb_morepopup_shadow_%1$s" onclick="essb_toggle_less_popup(\'%1$s\'); return false;"></div>', 
						$salt, 
						ESSBButtonHelper::draw_share_buttons($post_share_details, $button_style, 
				$more_social_networks, $more_social_networks_order, $social_networks_names, "more_popup", $more_salt, 'share'));
				
				$this->resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_morepopup_css(), 'essb-morepopup-css', 'footer');
			}			
		}
		
		// apply clean of new lines
		if (!empty($ssbuttons)) {
			$ssbuttons = trim(preg_replace('/\s+/', ' ', $ssbuttons));
		}
		
		if (!empty($cache_key)) {
			ESSBDynamicCache::put($cache_key, $ssbuttons);
		}
		
		return $ssbuttons;
	}
	
	function get_native_button_settings($position = '', $only_share = false) {
		$are_active = true;
		
		if (!defined('ESSB3_NATIVE_ACTIVE')) {
			$are_active = false;
		}
		else {
			if (!ESSB3_NATIVE_ACTIVE) {
				$are_active = false;
			}
 		}
 		
 		if (defined('ESSB3_NATIVE_DEACTIVE')) {
 			$are_active = false;
 		}
		
		if ($this->is_mobile()) {
			if (!ESSBOptionValuesHelper::options_bool_value($this->options, 'allow_native_mobile')) {
				$are_active = false;
			}
		}
		
		if (!empty($position)) {
			if (ESSBOptionValuesHelper::options_bool_value($this->options, $position.'_native_deactivate')) {
				$are_active = false;
			}
		}
		
		if ($only_share) {
			$are_active = false;
		}
		
		if (ESSBCoreHelper::is_module_deactivate_on('native')) {
			$are_active = false;
		}
		
		if (!$are_active) {
			return array("active" => false);
		}
		
		$native_options = ESSBNativeButtonsHelper::native_button_defaults();
		$native_options['active'] = $are_active;
		
		return $native_options;
	}
	
	function get_post_share_details() {
		global $post, $essb_options;
		
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}
		
		if (ESSBOptionValuesHelper::options_bool_value($this->options, 'force_wp_query_postid')) {
			$current_query_id = get_queried_object_id();
			$post = get_post($current_query_id);
		}
		
		$url = "";
		$title = "";
		$image = "";
		$description = "";
		$title_plain = "";
		
		$twitter_user = $this->network_options['twitter_user'];
		$twitter_hashtags = $this->network_options['twitter_hashtags'];
		$twitter_customtweet = "";
		

		$url = $post ? get_permalink() : ESSBUrlHelper::get_current_url( 'raw' );
		
		if (ESSBOptionValuesHelper::options_bool_value($this->options, 'avoid_nextpage')) {
			$url = $post ? get_permalink(get_the_ID()) : ESSBUrlHelper::get_current_url( 'raw' );
		}
		
		if (ESSBOptionValuesHelper::options_bool_value($this->options, 'force_wp_fullurl')) {
			$url = ESSBUrlHelper::get_current_page_url();
		}
		
		if (ESSBOptionValuesHelper::options_bool_value($this->options, 'always_use_http')) {
			$url = str_replace("https://", "http://", $url);
		}
		
		$mycred_referral_activate = ESSBOptionValuesHelper::options_bool_value($this->options, 'mycred_referral_activate');
		if ($mycred_referral_activate && function_exists('mycred_render_affiliate_link')) {
			$url = mycred_render_affiliate_link( array( 'url' => $url ) );
		}
		
		if (isset($post)) {
			$title = esc_attr(urlencode($post->post_title));
			$title_plain = $post->post_title;
			$post_image = has_post_thumbnail( $post->ID ) ? wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ) : '';
			$image = ($post_image != '') ? $post_image[0] : '';
			$description = $post->post_excerpt;
		}

		// apply custom share options
		if ($this->general_options['customshare']) {
			if ($this->general_options['customshare_text'] != '') {
				$title = $this->general_options['customshare_text'];
				$title_plain = $title;
			}
			if ($this->general_options['customshare_url'] != '') {
				$url = $this->general_options['customshare_url'];
			}
			if ($this->general_options['customshare_image'] != '') {
				$image = $this->general_options['customshare_image'];
			}
			if ($this->general_options['customshare_description'] != '') {
				$description = $this->general_options['customshare_description'];
			}
		}
		
		$twitter_customtweet = $title;		
		$post_pin_image = "";
		// apply post custom share options
		if (isset($post)) {
			
			$twitter_message_tags_to_hashtags = ESSBOptionValuesHelper::options_bool_value($essb_options, 'twitter_message_tags_to_hashtags');
			if ($twitter_message_tags_to_hashtags) {
				$post_tags = wp_get_post_tags($post->ID);
				if ($post_tags) {
					$generated_tags = array();
					foreach($post_tags as $tag) {
						$current_tag = $tag->name;
						$current_tag = str_replace(' ', '', $current_tag);
						$generated_tags[] = $current_tag;
					}
					
					if (count($generated_tags) > 0) {
						$twitter_hashtags = implode(',', $generated_tags);
					}
				}
			}
			
			$post_essb_post_share_message = get_post_meta($post->ID, 'essb_post_share_message', true);
			$post_essb_post_share_url = get_post_meta($post->ID, 'essb_post_share_url', true);
			$post_essb_post_share_image = get_post_meta($post->ID, 'essb_post_share_image', true);
			$post_essb_post_share_text = get_post_meta($post->ID, 'essb_post_share_text', true);

			$post_pin_image = get_post_meta($post->ID, 'essb_post_pin_image', true);
			
			$post_essb_twitter_username = get_post_meta($post->ID, 'essb_post_twitter_username', true);
			$post_essb_twitter_hastags = get_post_meta($post->ID, 'essb_post_twitter_hashtags', true);
			$post_essb_twitter_tweet = get_post_meta($post->ID, 'essb_post_twitter_tweet', true);
			
			if ($post_essb_post_share_image != '') {
				$image = $post_essb_post_share_image;
			}
			if ($post_essb_post_share_message != '') {
				$description = $post_essb_post_share_message;
			}
			if ($post_essb_post_share_text != '') {
				$title = $post_essb_post_share_text;
				$title_plain = $post_essb_post_share_text;
			}
			if ($post_essb_post_share_url != '') {
				$url = $post_essb_post_share_url;
			}
			
			if ($post_essb_twitter_hastags != '') {
				$twitter_hashtags = $post_essb_twitter_hastags;
			}
			if ($post_essb_twitter_tweet != '') {
				$twitter_customtweet = $post_essb_twitter_tweet;
			}
			if ($post_essb_twitter_username != '') {
				$twitter_user = $post_essb_twitter_username;
			}
		}
		
		$affwp_active = ESSBOptionValuesHelper::options_bool_value($essb_options, 'affwp_active');
		if ($affwp_active) {
			$url = ESSBUrlHelper::generate_affiliatewp_referral_link($url);
		}
		
		$title= str_replace("'", "\'", $title);
		$description= str_replace("'", "\'", $description);
		$twitter_customtweet= str_replace("'", "\'", $twitter_customtweet);
		$title_plain= str_replace("'", "\'", $title_plain);
		
		return array("url" => $url, "title" => $title, "image" => $image, "description" => $description, "twitter_user" => $twitter_user,
				"twitter_hashtags" => $twitter_hashtags, "twitter_tweet" => $twitter_customtweet, "post_id" => isset($post) ? $post->ID : 0, "user_image_url" => "", "title_plain" => $title_plain, 
				'short_url_whatsapp' => '', 'short_url_twitter' => '', 'short_url' => '', 'pinterest_image' => $post_pin_image);
	}
	

	
	function get_buttons_visual_options($position = '') {
		if ($this->general_options['reset_postdata']) {
			wp_reset_postdata();
		}
		
		$style = array();
		$style['template'] = $this->design_options['template'];
		$style['button_style'] = $this->design_options['button_style'];
		$style['button_align'] = $this->design_options['button_align'];
		$style['button_width'] = $this->design_options['button_width'];
		$style['button_width_fixed_value'] = $this->design_options['button_width_fixed_value'];
		$style['button_width_fixed_align'] = $this->design_options['button_width_fixed_align'];
		$style['button_width_full_container'] = $this->design_options ['button_width_full_container'];
		$style['button_width_full_button'] = $this->design_options ['button_width_full_button'];
		$style['button_width_full_button_mobile'] = $this->design_options ['button_width_Full_button_mobile'];
		$style['button_width_columns'] = $this->design_options['button_width_columns']; 
		$style['show_counter'] = $this->button_style ['show_counter'];
		$style['counter_pos'] = $this->button_style ['counter_pos'];
		$style['active_internal_counters'] = $this->button_style ['active_internal_counters'];
		$style['total_counter_pos'] = $this->button_style ['total_counter_pos'];
		$style['message_share_buttons'] = $this->button_style ['message_share_buttons'];
		$style['message_share_before_buttons'] = $this->button_style['message_share_before_buttons'];
		$style['message_like_buttons'] = $this->button_style['message_like_buttons'];
		$style['total_counter_afterbefore_text'] = $this->general_options['total_counter_afterbefore_text'];
		$style['total_counter_hidden_till'] = $this->general_options['total_counter_hidden_till'];
		$style['button_counter_hidden_till'] = $this->general_options['button_counter_hidden_till'];
		$style['nospace'] = $this->design_options['nospace'];
		$style['more_button_func'] = $this->network_options['more_button_func'];
		$style['fullwidth_align'] = $this->design_options['fullwidth_align'];
		$style['fullwidth_share_buttons_columns_align'] = $this->design_options['fullwidth_share_buttons_columns_align'];
				
		if (intval($style['button_width_full_container']) == 0) {
			$style['button_width_full_container'] = "100";
		}
		
		if ($this->is_mobile()) {
			if ($style['button_width_full_button_mobile'] != '') {
				$style['button_width_full_button'] = $style['button_width_full_button_mobile'];
			}
		}
		
		if ($style['button_width_full_button'] == '') {
			$style['button_width_full_button'] = "80";
		}
		
		return $style;
		//$this->general_options['metabox_visual']
	}
	
	// end: button drawer
	
	function is_mobile() {
		if (!isset($this->mobile_detect)) {
			$this->mobile_detect = new ESSB_Mobile_Detect();
		}
		
		$isMobile = $this->mobile_detect->isMobile();
		
		if ($this->general_options['mobile_exclude_tablet'] && $this->mobile_detect->isTablet()) {
			$isMobile = false;
		}
		return $isMobile;
	}
	
	function is_mobile_safecss() {
		if ($this->general_options['mobile_css_activate']) {
			return true;
		}
		else {
			if (!isset($this->mobile_detect)) {
				$this->mobile_detect = new ESSB_Mobile_Detect();
			}
			
			$isMobile = $this->mobile_detect->isMobile();
			
			if ($this->general_options['mobile_exclude_tablet'] && $this->mobile_detect->isTablet()) {
				$isMobile = false;
			}
			return $isMobile;
		}
	}
	
	// --------------------------------------------------
	// Actions
	// --------------------------------------------------
	
	function actions_update_post_count() {
		global $wpdb, $blog_id;
		
		$post_id = isset($_POST["post_id"]) ? $_POST["post_id"] : '';
		$service_id = isset($_POST["service"]) ? $_POST["service"] : '';
		$post_id = intval($post_id);
		
		if ($service_id == "print_friendly") {
			$service_id = "print";
		}
		
		$current_value = get_post_meta($post_id, 'essb_pc_'.$service_id, true);
		$current_value = intval($current_value) + 1;
		update_post_meta ( $post_id, 'essb_pc_'.$service_id, $current_value );
		
		die(json_encode(array(" post_id ".$post_id.", service = ".$service_id.", current_value = ".$current_value)));
	}
	
	function actions_get_share_counts() {
		$networks = isset($_REQUEST['nw']) ? $_REQUEST['nw'] : '';
		$url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
		$instance = isset($_REQUEST['instance']) ? $_REQUEST['instance'] : '';
		$post = isset($_REQUEST['post']) ? $_REQUEST['post'] : '';
		
		header('content-type: application/json');
		
		// check if cache is present
		//print_r($this->general_options);
		$is_active_cache = $this->general_options['admin_ajax_cache'];
		$cache_ttl = intval($this->general_options['admin_ajax_cache_time']);
		if ($cache_ttl == 0) { 
			$cache_ttl = 600;
		}
		
		$list = explode(',', $networks);
		$output = array();
		$output['url'] = $url;
		$output['instance'] = $instance;
		$output['post'] = $post;
		$output['network'] = $networks;

		foreach ($list as $nw) {
			$transient_key = 'essb_'.$nw.'_'.$url;
			$exist_in_cache = false;
			if ($is_active_cache) {
				$cached_value = get_transient($transient_key);
				if ($cached_value) {
					$output[$nw] = $cached_value;
					$exist_in_cache = true;
				}
			}
			
			if (!$exist_in_cache) {
				$count = ESSBCountersHelper::get_shared_counter($nw, $url, $post);
				$output[$nw] = $count;
				if ($is_active_cache) {
					delete_transient($transient_key);
					set_transient( $transient_key, $count, $cache_ttl );
				}
			}
		}
		
		echo str_replace('\\/','/',json_encode($output));
		
		die();
		
	}
	
	function essb_proccess_light_ajax() {
		$current_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
		
		if ($current_action == "essb_counts") {
			define('DOING_AJAX', true);
			
			send_nosniff_header();
			header('content-type: application/json');
			header('Cache-Control: no-cache');
			header('Pragma: no-cache');
			
			if(is_user_logged_in())
				do_action('wp_ajax_essb_counts');
			else
				do_action('wp_ajax_nopriv_essb_counts');
			
			exit;
		}
	
	}
	
	/*** shortcode functions ***/
	function essb_shortcode_profiles($atts) {
		global $essb_available_social_profiles;
		
		$sc_networks = isset($atts['networks']) ? $atts['networks'] : '';
		$sc_button_type = isset($atts['type']) ? $atts['type'] : 'square';
		$sc_button_size = isset($atts['size']) ? $atts['size'] : 'small';
		$sc_button_fill = isset($atts['style']) ? $atts['style'] : 'fill';
		$sc_nospace = isset($atts['nospace']) ? $atts['nospace'] : 'false';
		
		$sc_usetexts = isset($atts['allowtext']) ? $atts['allowtext'] : 'false';
		$sc_width = isset($atts['width']) ? $atts['width'] : '';
		
		$sc_nospace = ESSBOptionValuesHelper::unified_true($sc_nospace);
		$sc_usetexts = ESSBOptionValuesHelper::unified_true($sc_usetexts);
		
		$profile_networks = array();
		if ($sc_networks != '') {
			$profile_networks = explode(',', $sc_networks);
		}
		else {
			$profile_networks = ESSBOptionValuesHelper::advanced_array_to_simple_array($essb_available_social_profiles);
		}
		
		
		// prepare network values
		$sc_network_address = array();
		foreach ($profile_networks as $network) {
			$value = isset($atts[$network]) ? $atts[$network] : '';
			
			if (empty($value)) {
				$value = isset($atts['profile_'.$network]) ? $atts['profile_'.$network] : '';
			}
			
			if (empty($value)) {
				$value = ESSBOptionValuesHelper::options_value($this->options, 'profile_'.$network);
			}
			
			if (!empty($value)) {
				$sc_network_address[$network] = $value;
			}
		}
		
		$sc_network_texts = array();
		if ($sc_usetexts) {
			foreach ($profile_networks as $network) {
				$value = isset($atts[$network]) ? $atts[$network] : '';
					
				if (empty($value)) {
					$value = isset($atts['profile_text_'.$network]) ? $atts['profile_text_'.$network] : '';
				}
					
				if (empty($value)) {
					$value = ESSBOptionValuesHelper::options_value($this->options, 'profile_text_'.$network);
				}
					
				if (!empty($value)) {
					$sc_network_texts[$network] = $value;
				}
			}
		}
		
		// check if module is not activated yet
		if (!defined('ESSB3_SOCIALPROFILES_ACTIVE')) {
			include_once (ESSB3_PLUGIN_ROOT . 'lib/modules/social-profiles/essb-social-profiles.php');
			define('ESSB3_SOCIALPROFILES_ACTIVE', 'true');
			$template_url = ESSB3_PLUGIN_URL.'/assets/css/essb-profiles.css';
			$this->resource_builder->add_static_footer_css($template_url, 'easy-social-share-buttons-profiles');
		}
		
		
		return ESSBSocialProfiles::generate_social_profile_icons($sc_network_address, $sc_button_type, $sc_button_size, $sc_button_fill, 
				$sc_nospace, '', $sc_usetexts, $sc_network_texts, $sc_width);
	}
	
	function essb_shortcode_native($atts) {
		global $post;
		
		$atts = shortcode_atts ( array (
				'facebook' => 'false',
				'facebook_url' => '', 
				'facebook_width' => '',
				'facebook_skinned_text' => '', 
				'facebook_skinned_width' => '',
				'facebook_follow' => 'false', 
				'facebook_follow_url' => '',
				'facebook_follow_width' => '', 
				'facebook_follow_skinned_text' => '',
				'facebook_follow_skinned_width' => '', 
				'twitter_follow' => 'false', 
				'twitter_follow_user' => '', 
				'twitter_follow_skinned_text' => '', 
				'twitter_follow_skinned_width' => '', 
				'twitter_tweet' => 'false', 
				'twitter_tweet_message' => '', 
				'twitter_tweet_skinned_text' => '', 
				'twitter_tweet_skinned_width' => '', 
				'google' => 'false', 
				'google_url' => '',
				'google_skinned_text' => '', 
				'google_skinned_width' => '', 
				'google_follow' => 'false', 
				'google_follow_url' => '', 
				'google_follow_skinned_text' => '', 
				'google_follow_skinned_width' => '', 
				'vk' => 'false', 
				'vk_skinned_text' => '', 
				'vk_skinned_width' => '', 
				'youtube' => 'false', 
				'youtube_chanel' => '', 
				'youtube_skinned_text' => '', 
				'youtube_skinned_width' => '', 
				'linkedin' => 'false', 
				'linkedin_company' => '', 
				'linkedin_skinned_text' => '', 
				'linkedin_skinned_width' => '', 
				'pinterest_pin' => 'false', 
				'pinterest_pin_skinned_text' => '', 
				'pinterest_pin_skinned_width' => '', 
				'pinterest_follow' => 'false', 
				'pinterest_follow_display' => '', 
				'pinterest_follow_url' => '', 
				'pinterest_follow_skinned_text' => '',
				'pinterest_follow_skinned_width' => '', 
				'skinned' => 'false', 
				'skin' => 'flat', 
				'message' => '', 
				'align' => '', 
				'counters' => 'false', 
				'hide_mobile' => 'false', 
				'order' => '' 
				), $atts );
		
		$hide_mobile = isset ( $atts ['hide_mobile'] ) ? $atts ['hide_mobile'] : '';
		$hide_mobile_state = ESSBOptionValuesHelper::unified_true($hide_mobile);
		
		if ($hide_mobile_state && $this->is_mobile()) {
			return "";
		}
		
		$order = isset ( $atts ['order'] ) ? $atts ['order'] : '';
		
		// @ since 2.0 order of buttons
		if ($order == '') {
			$order = 'facebook,facebook_follow,twitter_follow,twitter_tweet,google,google_follow,vk,youtube,linkedin,pinterest_pin,pinterest_follow';
		}		
		
		$align = isset ( $atts ['align'] ) ? $atts ['align'] : '';
		$facebook = isset ( $atts ['facebook'] ) ? $atts ['facebook'] : '';
		$facebook_url = isset ( $atts ['facebook_url'] ) ? $atts ['facebook_url'] : '';
		$facebook_width = isset ( $atts ['facebook_width'] ) ? $atts ['facebook_width'] : '';
		$facebook_skinned_text = isset ( $atts ['facebook_skinned_text'] ) ? $atts ['facebook_skinned_text'] : '';
		$facebook_skinned_width = isset ( $atts ['facebook_skinned_width'] ) ? $atts ['facebook_skinned_width'] : '';
		
		$facebook_follow = isset ( $atts ['facebook_follow'] ) ? $atts ['facebook_follow'] : '';
		$facebook_follow_url = isset ( $atts ['facebook_follow_url'] ) ? $atts ['facebook_follow_url'] : '';
		$facebook_follow_width = isset ( $atts ['facebook_follow_width'] ) ? $atts ['facebook_follow_width'] : '';
		$facebook_follow_skinned_text = isset ( $atts ['facebook_follow_skinned_text'] ) ? $atts ['facebook_follow_skinned_text'] : '';
		$facebook_follow_skinned_width = isset ( $atts ['facebook_follow_skinned_width'] ) ? $atts ['facebook_follow_skinned_width'] : '';
		
		$twitter_follow = isset ( $atts ['twitter_follow'] ) ? $atts ['twitter_follow'] : '';
		$twitter_follow_user = isset ( $atts ['twitter_follow_user'] ) ? $atts ['twitter_follow_user'] : '';
		$twitter_follow_skinned_text = isset ( $atts ['twitter_follow_skinned_text'] ) ? $atts ['twitter_follow_skinned_text'] : '';
		$twitter_follow_skinned_width = isset ( $atts ['twitter_follow_skinned_width'] ) ? $atts ['twitter_follow_skinned_width'] : '';
		
		$twitter_tweet = isset ( $atts ['twitter_tweet'] ) ? $atts ['twitter_tweet'] : '';
		$twitter_tweet_message = isset ( $atts ['twitter_tweet_message'] ) ? $atts ['twitter_tweet_message'] : '';
		$twitter_tweet_skinned_text = isset ( $atts ['twitter_tweet_skinned_text'] ) ? $atts ['twitter_tweet_skinned_text'] : '';
		$twitter_tweet_skinned_width = isset ( $atts ['twitter_tweet_skinned_width'] ) ? $atts ['twitter_tweet_skinned_width'] : '';
		
		$google = isset ( $atts ['google'] ) ? $atts ['google'] : '';
		$google_url = isset ( $atts ['google_url'] ) ? $atts ['google_url'] : '';
		$google_skinned_text = isset ( $atts ['google_skinned_text'] ) ? $atts ['google_skinned_text'] : '';
		$google_skinned_width = isset ( $atts ['google_skinned_width'] ) ? $atts ['google_skinned_width'] : '';
		
		$google_follow = isset ( $atts ['google_follow'] ) ? $atts ['google_follow'] : '';
		$google_follow_url = isset ( $atts ['google_follow_url'] ) ? $atts ['google_follow_url'] : '';
		$google_follow_skinned_text = isset ( $atts ['google_follow_skinned_text'] ) ? $atts ['google_follow_skinned_text'] : '';
		$google_follow_skinned_width = isset ( $atts ['google_follow_skinned_width'] ) ? $atts ['google_follow_skinned_width'] : '';
		
		$vk = isset ( $atts ['vk'] ) ? $atts ['vk'] : '';
		$vk_skinned_text = isset ( $atts ['vk_skinned_text'] ) ? $atts ['vk_skinned_text'] : '';
		$vk_skinned_width = isset ( $atts ['vk_skinned_width'] ) ? $atts ['vk_skinned_width'] : '';
		
		$youtube = isset ( $atts ['youtube'] ) ? $atts ['youtube'] : '';
		$youtube_chanel = isset ( $atts ['youtube_chanel'] ) ? $atts ['youtube_chanel'] : '';
		$youtube_skinned_text = isset ( $atts ['youtube_skinned_text'] ) ? $atts ['youtube_skinned_text'] : '';
		$youtube_skinned_width = isset ( $atts ['youtube_skinned_width'] ) ? $atts ['youtube_skinned_width'] : '';
		
		$linkedin = isset ( $atts ['linkedin'] ) ? $atts ['linkedin'] : '';
		$linkedin_comapny = isset ( $atts ['linkedin_company'] ) ? $atts ['linkedin_company'] : '';
		$linkedin_skinned_text = isset ( $atts ['linkedin_skinned_text'] ) ? $atts ['linkedin_skinned_text'] : '';
		$linkedin_skinned_width = isset ( $atts ['linkedin_skinned_width'] ) ? $atts ['linkedin_skinned_width'] : '';
		
		$pinterest_pin = isset ( $atts ['pinterest_pin'] ) ? $atts ['pinterest_pin'] : '';
		$pinterest_pin_skinned_text = isset ( $atts ['pinterest_pin_skinned_text'] ) ? $atts ['pinterest_pin_skinned_text'] : '';
		$pinterest_pin_skinned_width = isset ( $atts ['pinterest_pin_skinned_width'] ) ? $atts ['pinterest_pin_skinned_width'] : '';
		
		$pinterest_follow = isset ( $atts ['pinterest_follow'] ) ? $atts ['pinterest_follow'] : '';
		$pinterest_follow_display = isset ( $atts ['pinterest_follow_display'] ) ? $atts ['pinterest_follow_display'] : '';
		$pinterest_follow_url = isset ( $atts ['pinterest_follow_url'] ) ? $atts ['pinterest_follow_url'] : '';
		$pinterest_follow_skinned_text = isset ( $atts ['pinterest_follow_skinned_text'] ) ? $atts ['pinterest_follow_skinned_text'] : '';
		$pinterest_follow_skinned_width = isset ( $atts ['pinterest_follow_skinned_width'] ) ? $atts ['pinterest_follow_skinned_width'] : '';
		
		$skinned = isset ( $atts ['skinned'] ) ? $atts ['skinned'] : 'false';
		$skin = isset ( $atts ['skin'] ) ? $atts ['skin'] : '';
		$message = isset ( $atts ['message'] ) ? $atts ['message'] : '';
		$counters = isset ( $atts ['counters'] ) ? $atts ['counters'] : 'false';
		
		// init global options
		$options = $this->options;
		$native_lang = isset ( $options ['native_social_language'] ) ? $options ['native_social_language'] : "en";
		
		$css_class_align = "";
		$css_class_noskin = ($skinned != 'true') ? ' essb-noskin' : '';
		
		// register like buttons CSS
		$this->resource_builder->add_static_resource_footer( ESSB3_PLUGIN_URL . '/assets/css/essb-social-like-buttons.css', 'essb-social-like-shortcode-css', 'css');
		
		if (!defined('ESSB3_NATIVE_ACTIVE')) {
			include_once (ESSB3_PLUGIN_ROOT . 'lib/core/native-buttons/essb-skinned-native-button.php');
			include_once (ESSB3_PLUGIN_ROOT . 'lib/core/native-buttons/essb-social-privacy.php');
			include_once (ESSB3_PLUGIN_ROOT . 'lib/core/native-buttons/essb-native-buttons-helper.php');
			define('ESSB3_NATIVE_ACTIVE', true);
		
			$essb_spb = ESSBSocialPrivacyNativeButtons::get_instance();
			ESSBNativeButtonsHelper::$essb_spb = $essb_spb;
			foreach ($essb_spb->resource_files as $key => $object) {
				$this->resource_builder->add_static_resource_footer($object["file"], $object["key"], $object["type"]);
			}
			foreach (ESSBSkinnedNativeButtons::get_assets() as $key => $object) {
				$this->resource_builder->add_static_resource_footer($object["file"], $object["key"], $object["type"]);
			}
			$this->resource_builder->add_css(ESSBSkinnedNativeButtons::generate_skinned_custom_css(), 'essb-skinned-native-buttons', 'footer');
				
			// asign instance of native buttons privacy class to helper
				
			// register active social network apis
			foreach (ESSBNativeButtonsHelper::get_list_of_social_apis() as $key => $code) {
				$this->resource_builder->add_social_api($key);
			}
		}
		
		
		$counters_bool = ESSBOptionValuesHelper::unified_true($counters);
		$skinned_buttons = ESSBOptionValuesHelper::unified_true($skinned);
		
		
		$text = esc_attr ( urlencode ( $post->post_title ) );
		$url = $post ? get_permalink () : ESSBUrlHelper::get_current_url ( 'raw' );
		
		if ($align == "right" || $align == "center") {
			$css_class_align = $align;
		}
				
		$output = "";
		$output .= '<div class="essb-like ' . $css_class_align . '">';
		
		if ($message != '') {
			$output .= '<div class="essb-message">' . $message . '</div>';
		}
		
		$networks = preg_split ( '#[\s+,\s+]#', $order );
		
		$required_apis = array();
		
		foreach ( $networks as $network ) {
			
			if ($network == "facebook") {
				if (ESSBOptionValuesHelper::unified_true ( $facebook )) {
					if ($facebook_url == "") {
						$facebook_url = $url;
					}
					
					$native_button_options = array();
					$native_button_options['facebook_type'] = 'like';
					$native_button_options['facebook_url'] = $facebook_url;
					$native_button_options['facebook_width'] = $facebook_skinned_width;
					$native_button_options['facebook_text'] = $facebook_skinned_text;	
					$native_button_options['skin'] = $skin;				
					
					$output .= '<div class="essb-like-facebook essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('facebook', $native_button_options, $counters_bool, $skinned_buttons);
					$output .= '</div>';
					
					if (ESSBNativeButtonsHelper::is_active_network('facebook')) {
						$required_apis['facebook'] = 'facebook';
					}
				}
			}
			
			if ($network == "facebook_follow") {
				if (ESSBOptionValuesHelper::unified_true ( $facebook_follow )) {
					
					$native_button_options = array();
					$native_button_options['facebook_type'] = 'follow';
					$native_button_options['facebook_url'] = $facebook_follow_url;
					$native_button_options['facebook_width'] = $facebook_follow_skinned_width;
					$native_button_options['facebook_text'] = $facebook_follow_skinned_text;
					$native_button_options['skin'] = $skin;
					$output .= '<div class="essb-like-facebook essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('facebook', $native_button_options, $counters_bool, $skinned_buttons);
					$output .= '</div>';
					if (ESSBNativeButtonsHelper::is_active_network('facebook')) {
						$required_apis['facebook'] = 'facebook';
					}
						
				}
			}
			
			if ($network == "twitter_tweet") {
				if (ESSBOptionValuesHelper::unified_true ( $twitter_tweet )) {
					
					$native_button_options = array();
					$native_button_options['twitter_type'] = 'tweet';
					$native_button_options['twitter_user'] = $twitter_follow_user;
					$native_button_options['twitter_tweet'] = $twitter_tweet_message;
					$native_button_options['url'] = $url;
					$native_button_options['twitter_width'] = $twitter_tweet_skinned_width;
					$native_button_options['twitter_text'] = $twitter_tweet_skinned_text;
					$native_button_options['skin'] = $skin;
					
					$output .= '<div class="essb-like-twitter essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('twitter', $native_button_options, $counters_bool, $skinned_buttons);
					//$output .= $this->print_twitter_tweet_button ( $twitter_follow_user, $counters, $native_lang, $twitter_tweet_message, $twitter_tweet_skinned_text, $twitter_tweet_skinned_width );
					$output .= '</div>';
					if (ESSBNativeButtonsHelper::is_active_network('twitter')) {
						$required_apis['twitter'] = 'twitter';
					}
						
				}
			}
			
			if ($network == "twitter_follow") {
				if (ESSBOptionValuesHelper::unified_true ( $twitter_follow )) {
					$native_button_options = array();
					$native_button_options['twitter_type'] = 'follow';
					$native_button_options['twitter_user'] = $twitter_follow_user;
					$native_button_options['twitter_tweet'] = $twitter_tweet_message;
					$native_button_options['url'] = $url;
					$native_button_options['twitter_width'] = $twitter_follow_skinned_width;
					$native_button_options['twitter_text'] = $twitter_follow_skinned_text;
					$native_button_options['skin'] = $skin;
					
					$output .= '<div class="essb-like-twitter-follow essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('twitter', $native_button_options, $counters_bool, $skinned_buttons);
					//$output .= $this->print_twitter_follow_button ( $twitter_follow_user, $counters, $native_lang, $twitter_follow_skinned_text, $twitter_follow_skinned_width );
					$output .= '</div>';
					if (ESSBNativeButtonsHelper::is_active_network('twitter')) {
						$required_apis['twitter'] = 'twitter';
					}
				}
			}
			
			if ($network == "google") {
				if (ESSBOptionValuesHelper::unified_true ( $google )) {
					if ($google_url == "") {
						$google_url = $url;
					}
					
					$native_button_options = array();
					$native_button_options['google_type'] = 'plus';
					$native_button_options['google_url'] = $google_url;
					$native_button_options['google_width'] = $google_skinned_width;
					$native_button_options['google_text'] = $google_skinned_text;
					$native_button_options['skin'] = $skin;
					$output .= '<div class="essb-like-google essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('google', $native_button_options, $counters_bool, $skinned_buttons);
					//$output .= $this->print_plusone_button ( $google_url, $counters, $native_lang, $google_skinned_text, $google_skinned_width );
					$output .= '</div>';
					if (ESSBNativeButtonsHelper::is_active_network('google')) {
						$required_apis['google'] = 'google';
					}
				}
			}
			
			if ($network == "google_follow") {
				if (ESSBOptionValuesHelper::unified_true ( $google_follow )) {
					$native_button_options = array();
					$native_button_options['google_type'] = 'follow';
					$native_button_options['google_url'] = $google_follow_url;
					$native_button_options['google_width'] = $google_follow_skinned_width;
					$native_button_options['google_text'] = $google_follow_skinned_text;
					$native_button_options['skin'] = $skin;
					$output .= '<div class="essb-like-google essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('google', $native_button_options, $counters_bool, $skinned_buttons);
					//$output .= $this->print_plusfollow_button ( $google_follow_url, $counters, $native_lang, $google_follow_skinned_text, $google_follow_skinned_width );
					$output .= '</div>';
					if (ESSBNativeButtonsHelper::is_active_network('google')) {
						$required_apis['google'] = 'google';
					}
				}
			}
			
			if ($network == "vk") {
				if (ESSBOptionValuesHelper::unified_true ( $vk )) {
					$native_button_options = array();
					$native_button_options['vk_width'] = $google_follow_skinned_width;
					$native_button_options['vk_text'] = $google_follow_skinned_text;
					$native_button_options['skin'] = $skin;
					
					$output .= '<div class="essb-like-vk essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('vk', $native_button_options, $counters_bool, $skinned_buttons);
					//$output .= $this->print_vklike_button ( $url, $counters, $vk_skinned_text, $vk_skinned_width );
					$output .= '</div>';
					
					if (ESSBNativeButtonsHelper::is_active_network('vk')) {
						$required_apis['vk'] = 'vk';
					}
				}
			}
			
			if ($network == "youtube") {
				if (ESSBOptionValuesHelper::unified_true ( $youtube )) {
					$native_button_options = array();
					$native_button_options['youtube_channel'] = $youtube_chanel;
					$native_button_options['youtube_width'] = $youtube_skinned_width;
					$native_button_options['youtube_text'] = $youtube_skinned_text;
					$native_button_options['skin'] = $skin;
					$output .= '<div class="essb-like-youtube essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('youtube', $native_button_options, $counters_bool, $skinned_buttons);
					//$output .= $this->print_youtube_button ( $youtube_chanel, $counters, $youtube_skinned_text, $youtube_skinned_width );
					$output .= '</div>';
					if (ESSBNativeButtonsHelper::is_active_network('google')) {
						$required_apis['google'] = 'google';
					}
				}
			}
			
			if ($network == "pinterest_pin") {
				if (ESSBOptionValuesHelper::unified_true ( $pinterest_pin )) {
					$native_button_options = array();
					$native_button_options['pinterest_type'] = 'pin';
					$native_button_options['pinterest_url'] = $pinterest_follow_url;
					$native_button_options['pinterest_text'] = $pinterest_follow_display;
					$native_button_options['pinterest_width'] = $pinterest_pin_skinned_width;
					$native_button_options['pinterest_text'] = $pinterest_pin_skinned_text;
					$native_button_options['skin'] = $skin;
					
					$output .= '<div class="essb-like-pin essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('pinterest', $native_button_options, $counters_bool, $skinned_buttons);
					//$output .= $this->print_pinterest_follow ( $pinterest_follow_display, $pinterest_follow_url, 'pin', $pinterest_pin_skinned_text, $pinterest_pin_skinned_width );
					$output .= '</div>';
					
					if (ESSBNativeButtonsHelper::is_active_network('pinterest')) {
						$required_apis['pinterest'] = 'pinterest';
					}
				}
			}
			
			if ($network == "pinterest_follow") {
				if (ESSBOptionValuesHelper::unified_true ( $pinterest_follow )) {
					$native_button_options = array();
					$native_button_options['pinterest_type'] = 'follow';
					$native_button_options['pinterest_url'] = $pinterest_follow_url;
					$native_button_options['pinterest_display'] = $pinterest_follow_display;
					$native_button_options['pinterest_width'] = $pinterest_follow_skinned_width;
					$native_button_options['pinterest_text'] = $pinterest_follow_skinned_text;
					$native_button_options['skin'] = $skin;
					$output .= '<div class="essb-like-pin-follow essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('pinterest', $native_button_options, $counters_bool, $skinned_buttons);
					//$output .= $this->print_pinterest_follow ( $pinterest_follow_display, $pinterest_follow_url, 'follow', $pinterest_follow_skinned_text, $pinterest_follow_skinned_width );
					$output .= '</div>';
					
					if (ESSBNativeButtonsHelper::is_active_network('pinterest')) {
						$required_apis['pinterest'] = 'pinterest';
					}
				}
			}
			
			if ($network == "linkedin") {
				if (ESSBOptionValuesHelper::unified_true ( $linkedin )) {
					$native_button_options = array();
					$native_button_options['linkedin_company'] = $linkedin_comapny;
					$native_button_options['linkedin_width'] = $linkedin_skinned_width;
					$native_button_options['linkedin_text'] = $linkedin_skinned_text;
					$native_button_options['skin'] = $skin;
						
					$output .= '<div class="essb-like-linkedin essb-block' . $css_class_noskin . '">';
					$output .= ESSBNativeButtonsHelper::draw_single_native_button('linkedin', $native_button_options, $counters_bool, $skinned_buttons);
					//$output .= $this->print_linkedin_button($linkedin_comapny, $counters, $linkedin_skinned_text, $linkedin_skinned_width);
					$output .= '</div>';
					
					if (ESSBNativeButtonsHelper::is_active_network('linkedin')) {
						$required_apis['linkedin'] = 'linkedin';
					}
				}
			}
		}
		
		foreach ($required_apis as $key => $code) {
			$this->resource_builder->add_social_api($key);
		}
		$output .= '</div>';
		
		return $output;
	}
	
	function essb_shortcode_share_vk($atts) {
		//$atts['native'] = "no";
		
		$total_counter_pos = isset($atts['total_counter_pos']) ? $atts['total_counter_pos'] : '';
		if ($total_counter_pos == "none") {
			$atts['hide_total'] = "yes";
		}
		
		$counter_pos = isset($atts['counter_pos']) ? $atts['counter_pos'] : '';
		if ($counter_pos == "none") {
			$atts['counter_pos'] = "hidden";
		}
		
		return $this->essb_shortcode_share($atts);
	}
	
	function essb_shortcode_share($atts) {
		global $essb_networks;
		
		$shortcode_extended_options = array();
		
		$shortcode_custom_display_texts = array();
		$exist_personalization = false;
		foreach ($essb_networks as $key => $data) {
			$text_key = sprintf('%1$s_text', $key);
			$value = isset($atts[$text_key]) ? $atts[$text_key] : '';
			
			if (!empty($value)) {
				$shortcode_custom_display_texts[$key] = $value;
				$exist_personalization = true;
			}
		}
		
		// add other names from default settings that are not modified
		if ($exist_personalization) {
			foreach ($essb_networks as $key => $object) {
				$search_for = "user_network_name_".$key;
				$user_network_name = ESSBOptionValuesHelper::options_value($this->options, $search_for, $object['name']);
				if (!isset($shortcode_custom_display_texts[$key])) {
					$shortcode_custom_display_texts[$key] = $user_network_name;
				}
			}
		}
		
		// parsing extended shortcode options
		if (is_array($atts)) {
			foreach ($atts as $key => $value) {
				if (strpos($key, "extended_") !== false) {
					$key = str_replace("extended_", "", $key);
					$shortcode_extended_options[$key] = $value;
				}
			}
 		}
		
		$shortcode_automatic_parse_args = array(
				'counters' => 'number',
				'current' => 'number',
				'text' => 'text',
				'title' => 'text',
				'url' => 'text',
				'native' => 'bool',
				'sidebar' => 'bool',
				'popup'=> 'bool',
				'flyin' => 'bool',
				'popafter' => 'text',
				'message' => 'bool',
				'description' => 'text',
				'image' => 'text',
				'fblike' => 'text',
				'plusone' => 'text',
				'style' => 'text',
				'hide_names' => 'text',
				'hide_icons' => 'text',
				'counters_pos' => 'text',
				'counter_pos' => 'text',
				'sidebar_pos' => 'text',
				'nostats' => 'bool',
				'hide_total' => 'bool',
				'total_counter_pos' => 'text',
				'fullwidth' =>  'bool',
				'fullwidth_fix' => 'text',
				'fixedwidth' => 'bool',
				'fixedwidth_px' => 'text',
				'fixedwidth_align' => 'text',
				'float' => 'bool',
				'postfloat' => 'bool',
				'morebutton' => 'text',
				'morebutton_icon' => 'text',
				'forceurl' => 'bool',
				'videoshare' => 'bool',
				'template' => 'text',
				'query' => 'bool',
				'column' => 'bool',
				'columns' => 'text',
				'topbar' => 'bool',
				'bottombar' => 'bool',
				'twitter_user' => 'text',
				'twitter_hashtags' => 'text',
				'twitter_tweet' => 'text',
				'nospace' => 'bool',
				'post_float' => 'text',
				'fullwidth_align' => 'text',
				'mobilebar' => 'bool',
				'mobilebuttons' => 'bool',
				'mobilepoint' => 'bool'
				); 
		$shortcode_options = array(
				'buttons' => '',
				'counters'	=> 0,
				'current'	=> 1,
				'text' => '',
				'url' => '',
				'native' => 'no',
				'sidebar' => 'no',
				'popup'=> 'no',
				'flyin' => 'no',
				'hide_names' => '',
				'popafter' => '',
				'message' => 'no',
				'description' => '',
				'image' => '',
				'fblike' => '',
				'plusone' => '',
				'style' => '',
				'counters_pos' => '',
				'counter_pos' => '',
				'sidebar_pos' => '',
				'nostats' => 'no',
				'hide_total' => 'no',
				'total_counter_pos' => '',
				'fullwidth' =>  'no',
				'fullwidth_fix' => '',
				'fullwidth_align' => '',
				'fixedwidth' => 'no',
				'fixedwidth_px' => '',
				'fixedwidth_align' => '',
				'float' => 'no',
				'postfloat' => 'no',
				'morebutton' => '',
				'forceurl' => 'no',
				'videoshare' => 'no',
				'template' => '',
				'hide_mobile' => 'no',
				'only_mobile' => 'no',
				'query' => 'no',
				'column' => 'no',
				'columns' => '5',
				'morebutton_icon' => '',
				'topbar' => 'no',
				'bottombar' => 'no',
				'twitter_user' => '',
				'twitter_hashtags' => '',
				'twitter_tweet' => '',
				'nospace' => 'false',
				'post_float' => '',
				'hide_icons' => '',
				'mobilebar' => 'no',
				'mobilebuttons' => 'no',
				'mobilepoint' => 'no'
		);
		
		$atts = shortcode_atts($shortcode_options, $atts);

		$hide_mobile = isset($atts['hide_mobile']) ? $atts['hide_mobile'] : '';
		$hide_mobile = ESSBOptionValuesHelper::unified_true($hide_mobile);
		
		$only_mobile = isset($atts['only_mobile']) ? $atts['only_mobile'] : '';
		$only_mobile = ESSBOptionValuesHelper::unified_true($only_mobile);
		
		if ($hide_mobile && $this->is_mobile()) {
			return "";
		}
		
		if ($only_mobile && !$this->is_mobile()) {
			return "";
		}
		
		// initialize list of availalbe networks
		if ( $atts['buttons'] == '') {
			$networks = $this->network_options['networks'];
		}
		else {
			$networks = preg_split('#[\s+,\s+]#', $atts['buttons']);
		}
		
		$shortcode_parameters = array();
		$shortcode_parameters['networks'] = $networks;
		$shortcode_parameters['customize_texts'] = $exist_personalization;
		$shortcode_parameters['network_texts'] = $shortcode_custom_display_texts;
		
		foreach ($shortcode_automatic_parse_args as $key => $type) {
			$value = isset($atts[$key]) ? $atts[$key] : '';
			
			if ($type == "number") {
				$value = intval($value);
			}
			if ($type == "bool") {
				$value = ESSBOptionValuesHelper::unified_true($value);
			}
			
			// @since 3.0.3 - fixed the default button style when used with shortcode
			if ($key == "style") {
				if (empty($value)) {
					$value = $this->design_options['button_style'];
				}
			}
			
			$shortcode_parameters[$key] = $value;
		}

		if (!empty($shortcode_parameters['post_float'])) {
			$shortcode_parameters['postfloat'] = ESSBOptionValuesHelper::unified_true($shortcode_parameters['post_float']);
		}
		
		// @since 3.0 query handling parameters is set as default in shortcode
		
		if ($shortcode_parameters['query']) {
			$query_url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
			if (!empty($query_url)) {
				$shortcode_parameters['url'] = $query_url;
				$url = $query_url;
			}
			
			$query_text = isset($_REQUEST['post_title']) ? $_REQUEST['post_title'] : '';
			
			if (!empty($query_text)) {
				$shortcode_parameters['text'] = $query_text;
				$shortcode_parameters['title'] = $query_text;
				$text = $query_text;
			}
		}
		
		if ($shortcode_parameters['counters_pos'] == "" && $shortcode_parameters['counter_pos'] != '') {
			$shortcode_parameters['counters_pos'] = $shortcode_parameters['counter_pos'];
		}
		if ($shortcode_parameters['text'] != '' && $shortcode_parameters['title'] == '') {
			$shortcode_parameters['title'] = $shortcode_parameters['text'];
		}
		
		if (!empty($shortcode_parameters['hide_names'])) {
			if ($shortcode_parameters['hide_names'] == "yes") {
				$shortcode_parameters['style'] = "icon_hover";
			}
			if ($shortcode_parameters['hide_names'] == "no") {
				$shortcode_parameters['style'] = "button";
			}
			if ($shortcode_parameters['hide_names'] == "force") {
				$shortcode_parameters['style'] = "icon";
			}
		}
		if (!empty($shortcode_parameters['hide_icons'])) {
			if ($shortcode_parameters['hide_icons'] == "yes") {
				$shortcode_parameters['style'] = "button_name";
			}
		}
		
		// shortcode extended options
		foreach ($shortcode_extended_options as $key => $value) {
			$shortcode_parameters[$key] = $value;
		}
		
		if ($shortcode_parameters['sidebar']) {
			if ($shortcode_parameters['sidebar_pos'] == "bottom") {
				$shortcode_parameters['sidebar'] = false;
				$shortcode_parameters['bottombar'] = true;
			}
			if ($shortcode_parameters['sidebar_pos'] == "top") {
				$shortcode_parameters['sidebar'] = false;
				$shortcode_parameters['topbar'] = true;
			}
		}
		
		$use_minifed_css = ($this->general_options['use_minified_css']) ? ".min" : "";
		$use_minifed_js = ($this->general_options['use_minified_js']) ? ".min" : "";
		// load mail resource on access
		if (in_array('mail', $networks)) {
			if (!isset($this->activated_resources['mail'])) {
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-mailform'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-mailform', 'css');
					
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-mailform.js';
				$this->resource_builder->add_static_resource_footer($script_url, 'essb-mailform', 'js', true);
				
				$this->activated_resources['mail'] = 'true';
			}
		}
		
		if (in_array('print', $networks)) {
			if (!isset($this->activated_resources['print'])) {
				if (!ESSBOptionValuesHelper::options_bool_value($this->options, 'print_use_printfriendly')) {
					$this->resource_builder->add_js(ESSBResourceBuilderSnippets::js_build_window_print_code(), true, 'essb-printing-code');
					$this->activated_resources['print'] = 'true';
				}
			}
		}
		
		if ($shortcode_parameters['counters'] == 1) {
			if (!isset($this->activated_resources['counters'])) {
				if (!defined('ESSB3_COUNTER_LOADED')) {
					$script_url = ESSB3_PLUGIN_URL .'/assets/js/easy-social-share-buttons'.$use_minifed_js.'.js';
					$this->resource_builder->add_static_resource_footer($script_url, 'easy-social-share-buttons', 'js');
					$this->activated_resources['counters'] = 'true';
					define('ESSB3_COUNTER_LOADED', true);
				}
			}
		}
		
		$display_as_key = "shortcode";
		if ($shortcode_parameters['sidebar']) {
			$display_as_key = "sidebar";
			
			if (!isset($this->activated_resources['sidebar'])) {
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-sidebar'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-sidebar', 'css');
				
				$this->activated_resources['sidebar'] = 'true';
			}
		}
		if ($shortcode_parameters['popup']) {
			$display_as_key = "popup";
			
			if (!isset($this->activated_resources['popup'])) {
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-popup'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-popup', 'css');
					
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-popup'.$use_minifed_js.'.js';
				$this->resource_builder->add_static_resource_footer($script_url, 'essb-popup', 'js', true);
				$this->activated_resources['popup'] = 'true';
			}
		}
		if ($shortcode_parameters['flyin']) {
			$display_as_key = "flyin";
			
			if (!isset($this->activated_resources['flyin'])) {
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-flyin'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-flyin', 'css');
					
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-flyin'.$use_minifed_js.'.js';
				$this->resource_builder->add_static_resource_footer($script_url, 'essb-flyin', 'js');
				$this->activated_resources['flyin'] = 'true';
			}
		}
		if ($shortcode_parameters['postfloat']) {
			$display_as_key = "postfloat";
			if (!isset($this->activated_resources['postfloat'])) {
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-postfloat'.$use_minifed_js.'.js';
				$this->resource_builder->add_static_resource_footer($script_url, 'essb-postfloat', 'js');
				$this->activated_resources['postfloat'] = 'true';
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-sidebar'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-sidebar', 'css');
				
			}
		}
		if ($shortcode_parameters['float']) {
			$display_as_key = "float";
			
			if (!isset($this->activated_resources['float'])) {
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-float'.$use_minifed_js.'.js';
				$this->resource_builder->add_static_resource_footer($script_url, 'essb-float', 'js');
				$this->activated_resources['float'] = 'true';
			}
		}
		if ($shortcode_parameters['topbar']) {
			$display_as_key = "topbar";
			
			if (!isset($this->activated_resources['topbottombar'])) {				
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-topbottom-bar'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-topbottom-bar', 'css');
				$this->activated_resources['topbottombar'] = 'true';
			}
		}
		if ($shortcode_parameters['bottombar']) {
			$display_as_key = "bottombar";
			if (!isset($this->activated_resources['topbottombar'])) {				
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-topbottom-bar'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-topbottom-bar', 'css');
				$this->activated_resources['topbottombar'] = 'true';
			}
		}
		
		if ($shortcode_parameters['mobilebar'] || $shortcode_parameters['mobilebuttons'] || $shortcode_parameters['mobilepoint']) {
			if (!$this->is_mobile()) {
				return "";
			}
		}
		
		// @since version 3.0.4
		if ($shortcode_parameters['mobilebar']) {
			$display_as_key = "sharebar";
			if (!isset($this->activated_resources['mobile'])) {
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-mobile'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-mobile', 'css');

				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-mobile'.$use_minifed_js.'.js';
				$this->resource_builder->add_static_resource_footer($script_url, 'essb-mobile', 'js');
				$this->activated_resources['mobile'] = 'true';
			}
		}
		if ($shortcode_parameters['mobilebuttons']) {
			$display_as_key = "sharebottom";
			if (!isset($this->activated_resources['mobile'])) {
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-mobile'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-mobile', 'css');
		
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-mobile'.$use_minifed_js.'.js';
				$this->resource_builder->add_static_resource_footer($script_url, 'essb-mobile', 'js');
				$this->activated_resources['mobile'] = 'true';
			}
		}
		if ($shortcode_parameters['mobilepoint']) {
			$display_as_key = "sharepoint";
			if (!isset($this->activated_resources['mobile'])) {
				$style_url = ESSB3_PLUGIN_URL .'/assets/css/essb-mobile'.$use_minifed_css.'.css';
				$this->resource_builder->add_static_resource_footer($style_url, 'easy-social-share-buttons-mobile', 'css');
		
				$script_url = ESSB3_PLUGIN_URL .'/assets/js/essb-mobile'.$use_minifed_js.'.js';
				$this->resource_builder->add_static_resource_footer($script_url, 'essb-mobile', 'js');
				$this->activated_resources['mobile'] = 'true';
			}
		}
				
		$special_shortcode_options = array();
		//print_r($shortcode_parameters);
		if (!$shortcode_parameters['native']) {
			$special_shortcode_options['only_share'] = true;
		}
		
		if ($display_as_key == "sidebar") {
			return $this->display_sidebar(true, $shortcode_parameters, $special_shortcode_options);
		}
		else if ($display_as_key == "popup") {
			return $this->display_popup(true, $shortcode_parameters['popafter'], $shortcode_parameters, $special_shortcode_options);
		}
		else if ($display_as_key == "flyin") {
			return $this->display_flyin(true, $shortcode_parameters, $special_shortcode_options);
		}
		else if ($display_as_key == "postfloat") {
			return $this->shortcode_display_postfloat($shortcode_parameters, $special_shortcode_options);
		}
		else if ($display_as_key == "topbar") {
			return $this->display_topbar(true, $shortcode_parameters, $special_shortcode_options);
		}
		else if ($display_as_key == "bottombar") {
			return $this->display_bottombar(true, $shortcode_parameters, $special_shortcode_options);
		}
		else if ($display_as_key == "sharebar") {
			return $this->display_sharebar(true, $shortcode_parameters, $special_shortcode_options);
		}
		else if ($display_as_key == "sharebottom") {
			return $this->display_sharebottom(true, $shortcode_parameters, $special_shortcode_options);
		}
		else if ($display_as_key == "sharepoint") {
			return $this->display_sharepoint(true, $shortcode_parameters, $special_shortcode_options);
		}
		else {
			return $this->generate_share_buttons($display_as_key, 'share', $special_shortcode_options, true, $shortcode_parameters);
		}
	}
	
	function essb_shortcode_total_shares($atts) {
		global $post;
		
		$atts = shortcode_atts(array(
				'message' => '',
				'align' => '',
				'url' => '',
				'share_text' => '',
				'fullnumber' => 'no',
				'networks' => ''
		), $atts);
		
		$align = isset($atts['align']) ? $atts['align'] : '';
		$message = isset($atts['message']) ? $atts['message'] : '';
		$url = isset($atts['url']) ? $atts['url'] : '';
		$share_text = isset($atts['share_text']) ? $atts['share_text'] : '';
		$fullnumber = isset($atts['fullnumber']) ? $atts['fullnumber'] : 'no';
		$networks = isset($atts['networks']) ? $atts['networks'] : 'no';
		
		$data_full_number = "false";
		if ($fullnumber == 'yes') {
			$data_full_number = "true";
		}
		
		// init global options
		$options = $this->options;
		
		
		if ($networks != '') {
			$buttons = $networks;
		}
		else {
			$buttons = implode(',', $this->network_options['networks']);
		}
				
		
		$css_class_align = "";
		
		$data_url = $post ? get_permalink() : ESSBUrlHelper::get_current_url( 'raw' );
		
		if (ESSBOptionValuesHelper::options_bool_value($this->options, 'avoid_nextpage')) {
			$data_url = $post ? get_permalink(get_the_ID()) : ESSBUrlHelper::get_current_url( 'raw' );
		}
		
		if (ESSBOptionValuesHelper::options_bool_value($this->options, 'force_wp_fullurl')) {
			$data_url = ESSBUrlHelper::get_current_page_url();
		}
		
		if (ESSBOptionValuesHelper::options_bool_value($this->options, 'always_use_http')) {
			$data_url = str_replace("https://", "http://", $data_url);
		}
				
		if ($url != '' ) {
			$data_url = $url;
		}		
		
		$data_post_id = "";
		if (isset($post)) {
			$data_post_id = $post->ID;
		}
		
		if ($align == "right" || $align == "center") {
			$css_class_align = $align;
		}
		
		$total_counter_hidden = $this->general_options['total_counter_hidden_till'];
		
		$use_minifed_js = ($this->general_options['use_minified_js']) ? ".min" : "";		
		$script_url = ESSB3_PLUGIN_URL .'/assets/js/easy-social-share-buttons-total'.$use_minifed_js.'.js';
		$this->resource_builder->add_static_resource_footer($script_url, 'easy-social-share-buttons-total', 'js');
		$css_hide_total_counter = "";
		if ($total_counter_hidden != '') {
			$css_hide_total_counter = ' style="display: none !important;" data-essb-hide-till="' . $total_counter_hidden . '"';
		}
		
		$output = "";
		$output .= '<div class="essb-total '.$css_class_align.'" data-network-list="'.$buttons.'" data-url="'.$data_url.'" data-full-number="'.$data_full_number.'" data-post="'.$data_post_id.'" '.$css_hide_total_counter.'>';
		
		if ($message != '') {
			$output .= '<div class="essb-message essb-block">'.$message.'</div>';
		}
		
		$output .= '<div class="essb-total-value essb-block">0</div>';
		if ($share_text != '') {
			$output .= '<div class="essb-total-text essb-block">'.$share_text.'</div>';
		}
		
		$output .= '</div>';
		
		
		return $output;
		
	}
	
	function essb_shortcode_share_flyin($atts) {
		$shortcode_options = array();

		if (is_array($atts)) {
			$flyin_title = ESSBOptionValuesHelper::options_value($atts, 'flyin_title');
			$flyin_message = ESSBOptionValuesHelper::options_value($atts, 'flyin_message');
			$flyin_percent = ESSBOptionValuesHelper::options_value($atts, 'flyin_percent');
			$flyin_end = ESSBOptionValuesHelper::options_value($atts, 'flyin_end');
			
			foreach ($atts as $key => $value) {
				if ($key != 'flyin_title' && $key != 'flyin_message' && $key != 'flyin_percent' && $key != 'flyin_end') {
					$shortcode_options[$key] = $value;
				}
			}
			
			$shortcode_options['extended_flyin_title'] = $flyin_title;
			$shortcode_options['extended_flyin_message'] = $flyin_message;
			$shortcode_options['extended_flyin_percent'] = $flyin_percent;
			$shortcode_options['extended_flyin_end'] = $flyin_end;
		}
		
		$shortcode_options['flyin'] = "yes";
		
		return $this->essb_shortcode_share($shortcode_options);
	}

	function essb_shortcode_share_popup($atts) {
		$shortcode_options = array();
	
		if (is_array($atts)) {
			$flyin_title = ESSBOptionValuesHelper::options_value($atts, 'popup_title');
			$flyin_message = ESSBOptionValuesHelper::options_value($atts, 'popup_message');
			$flyin_percent = ESSBOptionValuesHelper::options_value($atts, 'popup_percent');
			$flyin_end = ESSBOptionValuesHelper::options_value($atts, 'popup_end');
				
			foreach ($atts as $key => $value) {
				if ($key != 'popup_title' && $key != 'popup_message' && $key != 'popup_percent' && $key != 'popup_end') {
					$shortcode_options[$key] = $value;
				}
			}
				
			$shortcode_options['extended_popup_title'] = $flyin_title;
			$shortcode_options['extended_popup_message'] = $flyin_message;
			$shortcode_options['extended_popup_percent'] = $flyin_percent;
			$shortcode_options['extended_popup_end'] = $flyin_end;
		}
		
		$shortcode_options['popup'] = "yes";
	
		return $this->essb_shortcode_share($shortcode_options);
	}
	
}

if (!function_exists ('str_replace_first')) {
function str_replace_first($search, $replace, $subject) {
	$pos = strpos($subject, $search);
	if ($pos !== false) {
		$subject = substr_replace($subject, $replace, $pos, strlen($search));
	}
	return $subject;
}
}
?>