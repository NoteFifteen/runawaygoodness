<?php

class ESSBSocialFansCounter {
	
	public $resource_files = array();
	
	private $module_version = "2.0";
	private $settings_name = "essb3-fanscounter";
	
	private static $instance = null;
	public static function get_instance() {
	
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
	
		return self::$instance;
	
	} // end get_instance;
	
	function __construct() {
		if ( !defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS' , (60 * 60 * 24 ) );
		}
		
		add_action( 'wp_ajax_essbfanscounter' , array ( $this , 'register_plugin_ajax' ) );
		add_action( 'wp_ajax_nopriv_essbfanscounter' , array ( $this , 'register_plugin_ajax' ) );
		add_shortcode( 'essb-fans' , array ( $this , 'register_plugin_shortcodes' ) );
		add_shortcode( 'easy-fans' , array ( $this , 'register_plugin_shortcodes' ) );
		add_filter( 'http_request_timeout' , array ( $this , 'filter_timeout_time' ) );
		add_action( 'wp_enqueue_scripts' , array ( $this , 'register_front_assets' ) );				
	}		
	
	public function filter_timeout_time () {
	
		$time = 25; //new number of seconds
		return $time;
	}
	
	public function register_plugin_shortcodes ( $attrs ) {
	
		$defaults = array (
				'title' => 'Social Fans' ,
				'new_window' => 1 ,
				'nofollow' => 1 ,
				'hide_numbers' => 0 ,
				'hide_title' => 0 ,
				'show_total' => 1 ,
				'box_width' => '' ,
				'is_lazy' => 0 ,
				'animate_numbers' => 0 ,
				'max_duration' => 5 ,
				'columns' => 3 ,
				'effects' => 'essbfc-no-effect' ,
				'shake' => '' ,
				'icon_color' => 'light' ,
				'bg_color' => 'colord' ,
				'hover_text_color' => 'light' ,
				'hover_text_bg_color' => 'colord' ,
				'show_diff' => 0 ,
				'show_diff_lt_zero' => 0 ,
				'diff_count_text_color' => '' ,
				'diff_count_bg_color' => '' ,
				'template' => 'flat'
		);
	
		$attrs = shortcode_atts( $defaults , $attrs );
	
		ESSBSocialFansCounterUtils::register_options( (array) $attrs );
		
		extract( $attrs );
	
		ob_start();
		include ESSB3_PLUGIN_ROOT . 'lib/modules/social-fans-counter/essb-social-fanscounter-view.php';
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	public function register_front_assets () {
	
		if (ESSBCoreHelper::is_plugin_deactivated_on() || ESSBCoreHelper::is_module_deactivate_on('fanscounter')) {
			return;
		}
		wp_enqueue_script( 'jquery' );
	
		wp_register_style( 'essb-social-fanscounter' , ESSB3_PLUGIN_URL . '/lib/modules/social-fans-counter/assets/css/essb-social-fanscounter.css' , false , ESSB3_VERSION );
		//wp_enqueue_script( 'essb-social-fanscounter-script' , ESSB3_PLUGIN_URL . '/lib/modules/social-fans-counter/assets/js/essb-social-fanscounter.js' , false , ESSB3_VERSION );
	
		wp_enqueue_style( 'essb-social-fanscounter' );
		//wp_enqueue_script( 'essb-social-fanscounter-script' );
	
		wp_localize_script( 'essb-social-fanscounter-script' , 'essb3fanscounter_object' , array ( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
	
	// AJAX counter update
	public function register_plugin_ajax() {
		$result = array ();
		$result['status'] = 'success';
		
		$total = 0;
		
		foreach ( ESSBSocialFansCounterUtils::enabled_socials() as $social ) {
		
			$count = ESSBSocialFansCounterUtils::fans_count( $social , false );
			$result['social'][$social]['count'] = $count;
			$result['social'][$social]['count_formated'] = ESSBSocialFansCounterUtils::format_count( $count );
		
			$total += $count;
			$result['social']['total']['count'] = $total;
			$result['social']['total']['count_formated'] = ESSBSocialFansCounterUtils::format_count( $total );
		
		}
		
		echo json_encode( $result );
		exit;
	}
	
	public static function clear_cached_data () {
		foreach ( ESSBSocialFansCounterHelper::available_social_networks() as $social => $title ) {
	
			$key = 'essbfcounter_' . $social . '_expire';
			delete_option( $key );
	
		}
	}
}

?>