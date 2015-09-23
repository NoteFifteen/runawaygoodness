<?php

/*
 * Plugin Name: Easy Social Share Buttons for WordPress
 * Description: Easy Social Share Buttons automatically adds share bar to your post or pages with support of Facebook, Twitter, Google+, LinkedIn, Pinterest, Digg, StumbleUpon, VKontakte, Tumblr, Reddit, Print, E-mail. Easy Social Share Buttons for WordPress is compatible with WooCommerce, bbPress and BuddyPress
 * Plugin URI: http://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476?ref=appscreo
 * Version: 3.2.2
 * Author: CreoApps
 * Author URI: http://codecanyon.net/user/appscreo/portfolio?ref=appscreo
 */


if (! defined ( 'WPINC' ))
	die ();

//error_reporting( E_ALL | E_STRICT );

define ( 'ESSB3_SELF_ENABLED', false );

define ( 'ESSB3_VERSION', '3.2.2' );
define ( 'ESSB3_PLUGIN_ROOT', dirname ( __FILE__ ) . '/' );
define ( 'ESSB3_PLUGIN_URL', plugins_url () . '/' . basename ( dirname ( __FILE__ ) ) );
define ( 'ESSB3_PLUGIN_BASE_NAME', plugin_basename ( __FILE__ ) );
define ( 'ESSB3_OPTIONS_NAME', 'easy-social-share-buttons3');
define ( 'ESSB3_NETWORK_LIST', 'easy-social-share-buttons3-networks');
define ( 'ESSB3_OPTIONS_NAME_FANSCOUNTER', 'easy-social-share-buttons3-fanscounter');
define ( 'ESSB3_TEXT_DOMAIN', 'essb');
define ( 'ESSB3_TRACKER_TABLE', 'essb3_click_stats');
define ( 'ESSB3_MAIL_SALT', 'easy-social-share-buttons-mailsecurity');

define ( 'ESSB3_DEMO_MODE', true);
define ( 'ESSB3_ADDONS_ACTIVE', true);

final class EasySocialShareButtons3 {
	public static $instance = null;
	
	public $essb;
	public $resource_builder;
	
	public static function get_instance() {
	
		// If the single instance hasn't been set, set it now.
		if (null == self::$instance)
			self::$instance = new self ();
	
		return self::$instance;
	
	}
	
	function __construct() {				
		include_once (ESSB3_PLUGIN_ROOT . 'lib/essb-core-includes.php');
		
		// begin plugin front end code execution
		$this->essb_resource_builder = ESSBResourceBuilder::get_instance();
		$this->essb = ESSBCore::get_instance();
		
		// loading social share optimization only when it is active
		if (defined('ESSB3_SSO_ACTIVE')) {
			ESSBSocialShareOptimization::get_instance();
		}
		if (defined('ESSB3_SSA_ACTIVE')) {
			$tracker = ESSBSocialShareAnalytics::get_instance();
			$this->essb_resource_builder->add_js($tracker->generate_tracker_code(), true, 'essb-stats-tracker');
		}
		
		if (defined('ESSB3_AFTERSHARE_ACTIVE')) {
			$essb_asc = ESSBAfterCloseShare3::get_instance();
			
			foreach ($essb_asc->resource_files as $key => $object) {
				$this->essb_resource_builder->add_static_resource($object["file"], $object["key"], $object["type"]);
			}
			
			foreach ($essb_asc->js_code as $key => $code) {
				$this->essb_resource_builder->add_js($code, false, 'essbasc_custom'.$key);
			}

			foreach ($essb_asc->social_apis as $key => $code) {
				$this->essb_resource_builder->add_social_api($key);
			}
		}
		
		if (defined('ESSB3_LOVEYOU_ACTIVE')) {
			$essb_loveyou = ESSBNetworks_LoveThis::get_instance();
			$this->essb_resource_builder->add_js($essb_loveyou->generate_js_code(), true, 'essb-loveyou-code');
		}
		
		if (defined('ESSB3_IMAGESHARE_ACTIVE')) {
			ESSBSocialImageShare::get_instance();
			$this->essb_resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_imageshare_customizer(), 'essb-imageshare-customizer', 'footer');
				
		}
		
		if (defined('ESSB3_SOCIALPROFILES_ACTIVE')) {
			ESSBSocialProfiles::get_instance();
			$this->essb_resource_builder->add_static_resource(ESSB3_PLUGIN_URL . '/assets/css/essb-profiles.css', 'easy-social-share-buttons-profles', 'css');
		}
		
		if (defined('ESSB3_SOCIALFANS_ACTIVE')) {
			ESSBSocialFansCounter::get_instance();
			
			$this->essb_resource_builder->add_css(ESSBResourceBuilderSnippets::css_build_fanscounter_customizer(), 'essb-fanscounter-customizer', 'footer');
		}
		
		if (defined('ESSB3_NATIVE_ACTIVE')) {
			// Social Privacy Buttons when active include resources
			$essb_spb = ESSBSocialPrivacyNativeButtons::get_instance();	
			ESSBNativeButtonsHelper::$essb_spb = $essb_spb;
			foreach ($essb_spb->resource_files as $key => $object) {
				$this->essb_resource_builder->add_static_resource($object["file"], $object["key"], $object["type"]);
			}			
			foreach (ESSBSkinnedNativeButtons::get_assets() as $key => $object) {
				$this->essb_resource_builder->add_static_resource($object["file"], $object["key"], $object["type"]);
			}
			$this->essb_resource_builder->add_css(ESSBSkinnedNativeButtons::generate_skinned_custom_css(), 'essb-skinned-native-buttons');
			
			// asign instance of native buttons privacy class to helper			
			
			// register active social network apis
			foreach (ESSBNativeButtonsHelper::get_list_of_social_apis() as $key => $code) {
				$this->essb_resource_builder->add_social_api($key);
			}
		}
		
		if (is_admin()) {
			include_once (ESSB3_PLUGIN_ROOT . 'lib/admin/essb-admin-includes.php');			
			ESSBAdminControler::get_instance();
			
			$exist_user_purchase_code = ESSBOptionValuesHelper::options_value($essb_options, 'purchase_code');
			
			if (!empty($exist_user_purchase_code) || defined('ESSB3_THEME_INTEGRATED')) {
				include (ESSB3_PLUGIN_ROOT . 'lib/external/autoupdate/plugin-update-checker.php');
				// @since 1.3.3
				// autoupdate
				// activating autoupdate option
				$essb_autoupdate = PucFactory::buildUpdateChecker ( 'http://update.creoworx.com/essb3/', __FILE__, 'easy-social-share-buttons3' );
				// @since 1.3.7.2 - update to avoid issues with other plugins that uses same
				// method
				function addSecretKeyESSB3($query) {
					global $exist_user_purchase_code;
					$query ['license'] = $exist_user_purchase_code;
					return $query;
				}
				$essb_autoupdate->addQueryArgFilter ( 'addSecretKeyESSB3' );				
			}
		}
		
		
	}
	
	public function essb_register_admin_menu() {
		
	}
	
	public function deactive_execution() {
		$this->essb->temporary_deactivate_content_filters();
	}
	
	public function reactivate_execution() {
		$this->essb->reactivate_content_filters_after_temporary_deactivate();
	}
	
	public static function activate() {
		global $essb_networks;
		
		update_option(ESSB3_NETWORK_LIST, $essb_networks);
		
		$mail_salt_check = get_option(ESSB3_MAIL_SALT);
		if (!$mail_salt_check || empty($mail_salt_check)) {
			$new_salt = mt_rand();
			update_option(ESSB3_MAIL_SALT, $new_salt);
		}
		
		$exist_settings = get_option(ESSB3_OPTIONS_NAME);
		if (!$exist_settings) {
			$default_options = 'eyJidXR0b25fc3R5bGUiOiJidXR0b24iLCJzdHlsZSI6IjIyIiwiY3NzX2FuaW1hdGlvbnMiOiJubyIsImZ1bGx3aWR0aF9zaGFyZV9idXR0b25zX2NvbHVtbnMiOiIxIiwibmV0d29ya3MiOlsiZmFjZWJvb2siLCJ0d2l0dGVyIiwiZ29vZ2xlIiwicGludGVyZXN0IiwibGlua2VkaW4iXSwibmV0d29ya3Nfb3JkZXIiOlsiZmFjZWJvb2siLCJ0d2l0dGVyIiwiZ29vZ2xlIiwicGludGVyZXN0IiwibGlua2VkaW4iLCJkaWdnIiwiZGVsIiwic3R1bWJsZXVwb24iLCJ0dW1ibHIiLCJ2ayIsInByaW50IiwibWFpbCIsImZsYXR0ciIsInJlZGRpdCIsImJ1ZmZlciIsImxvdmUiLCJ3ZWlibyIsInBvY2tldCIsInhpbmciLCJvayIsIm13cCIsIm1vcmUiLCJ3aGF0c2FwcCIsIm1lbmVhbWUiLCJibG9nZ2VyIiwiYW1hem9uIiwieWFob29tYWlsIiwiZ21haWwiLCJhb2wiLCJuZXdzdmluZSIsImhhY2tlcm5ld3MiLCJldmVybm90ZSIsIm15c3BhY2UiLCJtYWlscnUiLCJ2aWFkZW8iLCJsaW5lIiwiZmxpcGJvYXJkIiwiY29tbWVudHMiLCJ5dW1tbHkiXSwibW9yZV9idXR0b25fZnVuYyI6IjEiLCJtb3JlX2J1dHRvbl9pY29uIjoicGx1cyIsInR3aXR0ZXJfc2hhcmVzaG9ydF9zZXJ2aWNlIjoid3AiLCJtYWlsX2Z1bmN0aW9uIjoiZm9ybSIsIndoYXRzYXBwX3NoYXJlc2hvcnRfc2VydmljZSI6IndwIiwiZmxhdHRyX2xhbmciOiJzcV9BTCIsImNvdW50ZXJfcG9zIjoicmlnaHRtIiwiZm9yY2VfY291bnRlcnNfYWRtaW5fdHlwZSI6IndwIiwidG90YWxfY291bnRlcl9wb3MiOiJsZWZ0YmlnIiwidXNlcl9uZXR3b3JrX25hbWVfZmFjZWJvb2siOiJGYWNlYm9vayIsInVzZXJfbmV0d29ya19uYW1lX3R3aXR0ZXIiOiJUd2l0dGVyIiwidXNlcl9uZXR3b3JrX25hbWVfZ29vZ2xlIjoiR29vZ2xlKyIsInVzZXJfbmV0d29ya19uYW1lX3BpbnRlcmVzdCI6IlBpbnRlcmVzdCIsInVzZXJfbmV0d29ya19uYW1lX2xpbmtlZGluIjoiTGlua2VkSW4iLCJ1c2VyX25ldHdvcmtfbmFtZV9kaWdnIjoiRGlnZyIsInVzZXJfbmV0d29ya19uYW1lX2RlbCI6IkRlbCIsInVzZXJfbmV0d29ya19uYW1lX3N0dW1ibGV1cG9uIjoiU3R1bWJsZVVwb24iLCJ1c2VyX25ldHdvcmtfbmFtZV90dW1ibHIiOiJUdW1ibHIiLCJ1c2VyX25ldHdvcmtfbmFtZV92ayI6IlZLb250YWt0ZSIsInVzZXJfbmV0d29ya19uYW1lX3ByaW50IjoiUHJpbnQiLCJ1c2VyX25ldHdvcmtfbmFtZV9tYWlsIjoiRW1haWwiLCJ1c2VyX25ldHdvcmtfbmFtZV9mbGF0dHIiOiJGbGF0dHIiLCJ1c2VyX25ldHdvcmtfbmFtZV9yZWRkaXQiOiJSZWRkaXQiLCJ1c2VyX25ldHdvcmtfbmFtZV9idWZmZXIiOiJCdWZmZXIiLCJ1c2VyX25ldHdvcmtfbmFtZV9sb3ZlIjoiTG92ZSBUaGlzIiwidXNlcl9uZXR3b3JrX25hbWVfd2VpYm8iOiJXZWlibyIsInVzZXJfbmV0d29ya19uYW1lX3BvY2tldCI6IlBvY2tldCIsInVzZXJfbmV0d29ya19uYW1lX3hpbmciOiJYaW5nIiwidXNlcl9uZXR3b3JrX25hbWVfb2siOiJPZG5va2xhc3NuaWtpIiwidXNlcl9uZXR3b3JrX25hbWVfbXdwIjoiTWFuYWdlV1Aub3JnIiwidXNlcl9uZXR3b3JrX25hbWVfbW9yZSI6Ik1vcmUgQnV0dG9uIiwidXNlcl9uZXR3b3JrX25hbWVfd2hhdHNhcHAiOiJXaGF0c0FwcCIsInVzZXJfbmV0d29ya19uYW1lX21lbmVhbWUiOiJNZW5lYW1lIiwidXNlcl9uZXR3b3JrX25hbWVfYmxvZ2dlciI6IkJsb2dnZXIiLCJ1c2VyX25ldHdvcmtfbmFtZV9hbWF6b24iOiJBbWF6b24iLCJ1c2VyX25ldHdvcmtfbmFtZV95YWhvb21haWwiOiJZYWhvbyBNYWlsIiwidXNlcl9uZXR3b3JrX25hbWVfZ21haWwiOiJHbWFpbCIsInVzZXJfbmV0d29ya19uYW1lX2FvbCI6IkFPTCIsInVzZXJfbmV0d29ya19uYW1lX25ld3N2aW5lIjoiTmV3c3ZpbmUiLCJ1c2VyX25ldHdvcmtfbmFtZV9oYWNrZXJuZXdzIjoiSGFja2VyTmV3cyIsInVzZXJfbmV0d29ya19uYW1lX2V2ZXJub3RlIjoiRXZlcm5vdGUiLCJ1c2VyX25ldHdvcmtfbmFtZV9teXNwYWNlIjoiTXlTcGFjZSIsInVzZXJfbmV0d29ya19uYW1lX21haWxydSI6Ik1haWwucnUiLCJ1c2VyX25ldHdvcmtfbmFtZV92aWFkZW8iOiJWaWFkZW8iLCJ1c2VyX25ldHdvcmtfbmFtZV9saW5lIjoiTGluZSIsInVzZXJfbmV0d29ya19uYW1lX2ZsaXBib2FyZCI6IkZsaXBib2FyZCIsInVzZXJfbmV0d29ya19uYW1lX2NvbW1lbnRzIjoiQ29tbWVudHMiLCJ1c2VyX25ldHdvcmtfbmFtZV95dW1tbHkiOiJZdW1tbHkiLCJnYV90cmFja2luZ19tb2RlIjoic2ltcGxlIiwidHdpdHRlcl9jYXJkX3R5cGUiOiJzdW1tYXJ5IiwibmF0aXZlX29yZGVyIjpbImdvb2dsZSIsInR3aXR0ZXIiLCJmYWNlYm9vayIsImxpbmtlZGluIiwicGludGVyZXN0IiwieW91dHViZSIsIm1hbmFnZXdwIiwidmsiXSwiZmFjZWJvb2tfbGlrZV90eXBlIjoibGlrZSIsImdvb2dsZV9saWtlX3R5cGUiOiJwbHVzIiwidHdpdHRlcl90d2VldCI6ImZvbGxvdyIsInBpbnRlcmVzdF9uYXRpdmVfdHlwZSI6ImZvbGxvdyIsInNraW5fbmF0aXZlX3NraW4iOiJmbGF0IiwicHJvZmlsZXNfYnV0dG9uX3R5cGUiOiJzcXVhcmUiLCJwcm9maWxlc19idXR0b25fZmlsbCI6ImZpbGwiLCJwcm9maWxlc19idXR0b25fc2l6ZSI6InNtYWxsIiwicHJvZmlsZXNfZGlzcGxheV9wb3NpdGlvbiI6ImxlZnQiLCJwcm9maWxlc19vcmRlciI6WyJ0d2l0dGVyIiwiZmFjZWJvb2siLCJnb29nbGUiLCJwaW50ZXJlc3QiLCJmb3Vyc3F1YXJlIiwieWFob28iLCJza3lwZSIsInllbHAiLCJmZWVkYnVybmVyIiwibGlua2VkaW4iLCJ2aWFkZW8iLCJ4aW5nIiwibXlzcGFjZSIsInNvdW5kY2xvdWQiLCJzcG90aWZ5IiwiZ3Jvb3Zlc2hhcmsiLCJsYXN0Zm0iLCJ5b3V0dWJlIiwidmltZW8iLCJkYWlseW1vdGlvbiIsInZpbmUiLCJmbGlja3IiLCI1MDBweCIsImluc3RhZ3JhbSIsIndvcmRwcmVzcyIsInR1bWJsciIsImJsb2dnZXIiLCJ0ZWNobm9yYXRpIiwicmVkZGl0IiwiZHJpYmJibGUiLCJzdHVtYmxldXBvbiIsImRpZ2ciLCJlbnZhdG8iLCJiZWhhbmNlIiwiZGVsaWNpb3VzIiwiZGV2aWFudGFydCIsImZvcnJzdCIsInBsYXkiLCJ6ZXJwbHkiLCJ3aWtpcGVkaWEiLCJhcHBsZSIsImZsYXR0ciIsImdpdGh1YiIsImNoaW1laW4iLCJmcmllbmRmZWVkIiwibmV3c3ZpbmUiLCJpZGVudGljYSIsImJlYm8iLCJ6eW5nYSIsInN0ZWFtIiwieGJveCIsIndpbmRvd3MiLCJvdXRsb29rIiwiY29kZXJ3YWxsIiwidHJpcGFkdmlzb3IiLCJhcHBuZXQiLCJnb29kcmVhZHMiLCJ0cmlwaXQiLCJsYW55cmQiLCJzbGlkZXNoYXJlIiwiYnVmZmVyIiwicnNzIiwidmtvbnRha3RlIiwiZGlzcXVzIiwiaG91enoiLCJtYWlsIiwicGF0cmVvbiIsInBheXBhbCIsInBsYXlzdGF0aW9uIiwic211Z211ZyIsInN3YXJtIiwidHJpcGxlaiIsInlhbW1lciIsInN0YWNrb3ZlcmZsb3ciLCJkcnVwYWwiLCJvZG5va2xhc3NuaWtpIiwiYW5kcm9pZCIsIm1lZXR1cCIsInBlcnNvbmEiXSwiYWZ0ZXJjbG9zZV90eXBlIjoiZm9sbG93IiwiYWZ0ZXJjbG9zZV9saWtlX2NvbHMiOiJvbmVjb2wiLCJlc21sX3R0bCI6IjEiLCJlc21sX3Byb3ZpZGVyIjoic2hhcmVkY291bnQiLCJlc21sX2FjY2VzcyI6Im1hbmFnZV9vcHRpb25zIiwic2hvcnR1cmxfdHlwZSI6IndwIiwiZGlzcGxheV9pbl90eXBlcyI6WyJwb3N0Il0sImRpc3BsYXlfZXhjZXJwdF9wb3MiOiJ0b3AiLCJ0b3BiYXJfYnV0dG9uc19hbGlnbiI6ImxlZnQiLCJ0b3BiYXJfY29udGVudGFyZWFfcG9zIjoibGVmdCIsImJvdHRvbWJhcl9idXR0b25zX2FsaWduIjoibGVmdCIsImJvdHRvbWJhcl9jb250ZW50YXJlYV9wb3MiOiJsZWZ0IiwiZmx5aW5fcG9zaXRpb24iOiJyaWdodCIsInNpc19uZXR3b3JrX29yZGVyIjpbImZhY2Vib29rIiwidHdpdHRlciIsImdvb2dsZSIsImxpbmtlZGluIiwicGludGVyZXN0IiwidHVtYmxyIiwicmVkZGl0IiwiZGlnZyIsImRlbGljaW91cyIsInZrb250YWt0ZSIsIm9kbm9rbGFzc25pa2kiXSwic2lzX3N0eWxlIjoiZmxhdC1zbWFsbCIsInNpc19hbGlnbl94IjoibGVmdCIsInNpc19hbGlnbl95IjoidG9wIiwic2lzX29yaWVudGF0aW9uIjoiaG9yaXpvbnRhbCIsIm1vYmlsZV9zaGFyZWJ1dHRvbnNiYXJfY291bnQiOiIyIiwic2hhcmViYXJfY291bnRlcl9wb3MiOiJpbnNpZGUiLCJzaGFyZWJhcl90b3RhbF9jb3VudGVyX3BvcyI6ImJlZm9yZSIsInNoYXJlYmFyX25ldHdvcmtzX29yZGVyIjpbImZhY2Vib29rfEZhY2Vib29rIiwidHdpdHRlcnxUd2l0dGVyIiwiZ29vZ2xlfEdvb2dsZSsiLCJwaW50ZXJlc3R8UGludGVyZXN0IiwibGlua2VkaW58TGlua2VkSW4iLCJkaWdnfERpZ2ciLCJkZWx8RGVsIiwic3R1bWJsZXVwb258U3R1bWJsZVVwb24iLCJ0dW1ibHJ8VHVtYmxyIiwidmt8VktvbnRha3RlIiwicHJpbnR8UHJpbnQiLCJtYWlsfEVtYWlsIiwiZmxhdHRyfEZsYXR0ciIsInJlZGRpdHxSZWRkaXQiLCJidWZmZXJ8QnVmZmVyIiwibG92ZXxMb3ZlIFRoaXMiLCJ3ZWlib3xXZWlibyIsInBvY2tldHxQb2NrZXQiLCJ4aW5nfFhpbmciLCJva3xPZG5va2xhc3NuaWtpIiwibXdwfE1hbmFnZVdQLm9yZyIsIm1vcmV8TW9yZSBCdXR0b24iLCJ3aGF0c2FwcHxXaGF0c0FwcCIsIm1lbmVhbWV8TWVuZWFtZSIsImJsb2dnZXJ8QmxvZ2dlciIsImFtYXpvbnxBbWF6b24iLCJ5YWhvb21haWx8WWFob28gTWFpbCIsImdtYWlsfEdtYWlsIiwiYW9sfEFPTCIsIm5ld3N2aW5lfE5ld3N2aW5lIiwiaGFja2VybmV3c3xIYWNrZXJOZXdzIiwiZXZlcm5vdGV8RXZlcm5vdGUiLCJteXNwYWNlfE15U3BhY2UiLCJtYWlscnV8TWFpbC5ydSIsInZpYWRlb3xWaWFkZW8iLCJsaW5lfExpbmUiLCJmbGlwYm9hcmR8RmxpcGJvYXJkIiwiY29tbWVudHN8Q29tbWVudHMiLCJ5dW1tbHl8WXVtbWx5Il0sInNoYXJlcG9pbnRfY291bnRlcl9wb3MiOiJpbnNpZGUiLCJzaGFyZXBvaW50X3RvdGFsX2NvdW50ZXJfcG9zIjoiYmVmb3JlIiwic2hhcmVwb2ludF9uZXR3b3Jrc19vcmRlciI6WyJmYWNlYm9va3xGYWNlYm9vayIsInR3aXR0ZXJ8VHdpdHRlciIsImdvb2dsZXxHb29nbGUrIiwicGludGVyZXN0fFBpbnRlcmVzdCIsImxpbmtlZGlufExpbmtlZEluIiwiZGlnZ3xEaWdnIiwiZGVsfERlbCIsInN0dW1ibGV1cG9ufFN0dW1ibGVVcG9uIiwidHVtYmxyfFR1bWJsciIsInZrfFZLb250YWt0ZSIsInByaW50fFByaW50IiwibWFpbHxFbWFpbCIsImZsYXR0cnxGbGF0dHIiLCJyZWRkaXR8UmVkZGl0IiwiYnVmZmVyfEJ1ZmZlciIsImxvdmV8TG92ZSBUaGlzIiwid2VpYm98V2VpYm8iLCJwb2NrZXR8UG9ja2V0IiwieGluZ3xYaW5nIiwib2t8T2Rub2tsYXNzbmlraSIsIm13cHxNYW5hZ2VXUC5vcmciLCJtb3JlfE1vcmUgQnV0dG9uIiwid2hhdHNhcHB8V2hhdHNBcHAiLCJtZW5lYW1lfE1lbmVhbWUiLCJibG9nZ2VyfEJsb2dnZXIiLCJhbWF6b258QW1hem9uIiwieWFob29tYWlsfFlhaG9vIE1haWwiLCJnbWFpbHxHbWFpbCIsImFvbHxBT0wiLCJuZXdzdmluZXxOZXdzdmluZSIsImhhY2tlcm5ld3N8SGFja2VyTmV3cyIsImV2ZXJub3RlfEV2ZXJub3RlIiwibXlzcGFjZXxNeVNwYWNlIiwibWFpbHJ1fE1haWwucnUiLCJ2aWFkZW98VmlhZGVvIiwibGluZXxMaW5lIiwiZmxpcGJvYXJkfEZsaXBib2FyZCIsImNvbW1lbnRzfENvbW1lbnRzIiwieXVtbWx5fFl1bW1seSJdLCJzaGFyZWJvdHRvbV9uZXR3b3Jrc19vcmRlciI6WyJmYWNlYm9va3xGYWNlYm9vayIsInR3aXR0ZXJ8VHdpdHRlciIsImdvb2dsZXxHb29nbGUrIiwicGludGVyZXN0fFBpbnRlcmVzdCIsImxpbmtlZGlufExpbmtlZEluIiwiZGlnZ3xEaWdnIiwiZGVsfERlbCIsInN0dW1ibGV1cG9ufFN0dW1ibGVVcG9uIiwidHVtYmxyfFR1bWJsciIsInZrfFZLb250YWt0ZSIsInByaW50fFByaW50IiwibWFpbHxFbWFpbCIsImZsYXR0cnxGbGF0dHIiLCJyZWRkaXR8UmVkZGl0IiwiYnVmZmVyfEJ1ZmZlciIsImxvdmV8TG92ZSBUaGlzIiwid2VpYm98V2VpYm8iLCJwb2NrZXR8UG9ja2V0IiwieGluZ3xYaW5nIiwib2t8T2Rub2tsYXNzbmlraSIsIm13cHxNYW5hZ2VXUC5vcmciLCJtb3JlfE1vcmUgQnV0dG9uIiwid2hhdHNhcHB8V2hhdHNBcHAiLCJtZW5lYW1lfE1lbmVhbWUiLCJibG9nZ2VyfEJsb2dnZXIiLCJhbWF6b258QW1hem9uIiwieWFob29tYWlsfFlhaG9vIE1haWwiLCJnbWFpbHxHbWFpbCIsImFvbHxBT0wiLCJuZXdzdmluZXxOZXdzdmluZSIsImhhY2tlcm5ld3N8SGFja2VyTmV3cyIsImV2ZXJub3RlfEV2ZXJub3RlIiwibXlzcGFjZXxNeVNwYWNlIiwibWFpbHJ1fE1haWwucnUiLCJ2aWFkZW98VmlhZGVvIiwibGluZXxMaW5lIiwiZmxpcGJvYXJkfEZsaXBib2FyZCIsImNvbW1lbnRzfENvbW1lbnRzIiwieXVtbWx5fFl1bW1seSJdLCJjb250ZW50X3Bvc2l0aW9uIjoiY29udGVudF9ib3R0b20iLCJlc3NiX2NhY2hlX21vZGUiOiJmdWxsIiwidHVybm9mZl9lc3NiX2FkdmFuY2VkX2JveCI6InRydWUiLCJlc3NiX2FjY2VzcyI6Im1hbmFnZV9vcHRpb25zIiwiYXBwbHlfY2xlYW5fYnV0dG9uc19tZXRob2QiOiJkZWZhdWx0IiwibWFpbF9zdWJqZWN0IjoiVmlzaXQgdGhpcyBzaXRlICUlc2l0ZXVybCUlIiwibWFpbF9ib2R5IjoiSGksIHRoaXMgbWF5IGJlIGludGVyZXN0aW5nIHlvdTogJSV0aXRsZSUlISBUaGlzIGlzIHRoZSBsaW5rOiAlJXBlcm1hbGluayUlIiwiZmFjZWJvb2t0b3RhbCI6InRydWUiLCJhY3RpdmF0ZV90b3RhbF9jb3VudGVyX3RleHQiOiJzaGFyZXMifQ==';
			
			$options_base = EasySocialShareButtons3::comvert_ready_made_option($default_options);
			if ($options_base) {
				update_option(ESSB3_OPTIONS_NAME, $options_base);
			}
		}
		
		// activate redirection hook
		if ( ! is_network_admin() ) {
			set_transient( '_essb_page_welcome_redirect', 1, 30 );
		}
	}
	
	public static function deactivate() {
		delete_option(ESSB3_MAIL_SALT);
	}
	
	public static function comvert_ready_made_option($options) {
		$options = base64_decode ( $options );
		
		$options = htmlspecialchars_decode ( $options );
		$options = stripslashes ( $options );

		if ($options != '') {
			$imported_options = json_decode ( $options, true );

			return $imported_options;
		}
		else {
			return null;
		}
	}
}

global $essb3;
function ESSB() {
	global $essb3;	
	$essb3 = EasySocialShareButtons3::get_instance();
}

add_action('plugins_loaded', 'ESSB');
register_activation_hook ( __FILE__, array ('EasySocialShareButtons3', 'activate' ) );
register_deactivation_hook ( __FILE__, array ('EasySocialShareButtons3', 'deactivate' ) );

if (is_admin()) {
	if (!defined('ESSB3_AVOID_WELCOME')) {
		function essb_page_welcome_redirect() {
			$redirect = get_transient( '_essb_page_welcome_redirect' );
			delete_transient( '_essb_page_welcome_redirect' );
			$redirect && wp_redirect( admin_url( 'admin.php?page=essb_about' ) );
		}
		add_action( 'init', 'essb_page_welcome_redirect' );
	}
}
if (!function_exists('easy_share_deactivate')) {
	function easy_share_deactivate() {
		global $essb3;
		$essb3->deactive_execution();
	}
}

if (!function_exists('easy_share_reactivate')) {
	function easy_share_reactivate() {
		global $essb3;
		$essb3->reactivate_execution();
	}
}

	
