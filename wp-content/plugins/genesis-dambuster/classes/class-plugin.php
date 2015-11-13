<?php
class Genesis_Dambuster_Plugin {

   const OPTIONS_NAME = 'genesis_dambuster_options';
  
 	private $path = GENESIS_DAMBUSTER_PLUGIN_PATH;
 	private $slug = GENESIS_DAMBUSTER_PLUGIN_NAME;
 	private $version = GENESIS_DAMBUSTER_VERSION;

	private $modules = array(
//		'template' => array('class'=> 'Genesis_Dambuster_Template',  'theme' => 'Genesis'),
		'agency' => array('class'=> 'Genesis_Dambuster_Agency',   'theme' => 'Agency Pro Theme'),
		'altitude' => array('class'=> 'Genesis_Dambuster_Altitude',   'theme' => 'Altitude Pro Theme'),
		'ambiance' => array('class'=> 'Genesis_Dambuster_Ambiance',  'theme' => 'Ambiance Pro Theme'),
		'author' => array('class'=> 'Genesis_Dambuster_Author',  'theme' => 'Author Pro Theme'),
		'beautiful' => array('class'=> 'Genesis_Dambuster_Beautiful',  'theme' => 'Beautiful Pro Theme'),
		'cafe' => array('class'=> 'Genesis_Dambuster_Cafe',  'theme' => 'Cafe Pro Theme'),
		'centric' => array('class'=> 'Genesis_Dambuster_Centric',  'theme' => 'Centric Theme'),
		'community' => array('class'=> 'Genesis_Dambuster_Community', 'theme' => 'Community Pro Theme'),
		'daily-dish' => array('class'=> 'Genesis_Dambuster_DailyDish', 'theme' => 'Daily Dish Pro Theme'),
		'decor' => array('class'=> 'Genesis_Dambuster_Decor', 'theme' => 'Decor Child Theme'),
		'dynamik' => array('class'=> 'Genesis_Dambuster_Dynamik', 'theme' => 'Dynamik-Gen'),
		'eleven40' => array('class'=> 'Genesis_Dambuster_Eleven40',  'theme' => 'eleven40 Pro Theme'),
		'epik' => array('class'=> 'Genesis_Dambuster_Epik',  'theme' => 'Epik'),
		'expose' => array('class'=> 'Genesis_Dambuster_Expose',  'theme' => 'Expose Pro Theme'),
		'metro' => array('class'=> 'Genesis_Dambuster_Metro', 'theme' => 'Metro Pro Theme'),
		'mindstream' => array('class'=> 'Genesis_Dambuster_Mindstream', 'theme' => 'Mindstream Child Theme'),
		'minimum' => array('class'=> 'Genesis_Dambuster_Minimum', 'theme' => 'Minimum Pro Theme'),
		'mocha' => array('class'=> 'Genesis_Dambuster_Mocha', 'theme' => 'Mocha Child Theme'),
		'modern-studio' => array('class'=> 'Genesis_Dambuster_ModernStudio', 'theme' => 'Modern Studio Pro Theme'),
		'outreach' => array('class'=> 'Genesis_Dambuster_Outreach', 'theme' => 'Outreach Pro'),
		'prose' => array('class'=> 'Genesis_Dambuster_Prose', 'theme' => 'Prose'),
		'sixteen-nine' => array('class'=> 'Genesis_Dambuster_SixteenNine', 'theme' => 'Sixteen Nine Pro Theme'),
		'streamline' => array('class'=> 'Genesis_Dambuster_Streamline', 'theme' => 'Streamline Pro Theme'),
		'swank' => array('class'=> 'Genesis_Dambuster_Swank', 'theme' => 'Swank Theme'),
		'the-411' => array('class'=> 'Genesis_Dambuster_The411', 'theme' => 'The 411 Pro Theme'),		
		'wintersong' => array('class'=> 'Genesis_Dambuster_Wintersong', 'theme' => 'Wintersong Pro Theme'),	
	);
	
	private $template = false;
	private $template_admin = false;
  	private $options;
 	private $utils;

	static function get_instance() {
        static $instance = null;
        if (null === $instance) {
            // $instance = new static(); //use self instead of static to support 5.2 - not the same but okay as the plugin class is not extended 
            $instance = new self(); 
        }
        return $instance;
	}
   
  	protected function __construct() {}

  	private function __clone() {}

  	private function __wakeup() {}

	public function init() {
		$d = dirname(__FILE__) . '/';
		require_once ($d . 'class-options.php');
		require_once ($d . 'class-utils.php');
		$this->utils = new Genesis_Dambuster_Utils();
		$this->options = new Genesis_Dambuster_Options( self::OPTIONS_NAME);
		if ($this->is_genesis_loaded()) { 
         require_once ($d . 'class-template.php');
         $current_theme = wp_get_theme();
         foreach ($this->modules as $module => $module_data) //load optional theme module if required
            if (apply_filters('genesis_dambuster_init-'.$module,  $current_theme->name == $module_data['theme']))  { //load on a matching theme or if forced by a filter hook
               $file = $d . 'class-'.$module.'.php';
               $class = $module_data['class'];
               if (file_exists($file)) require_once($file);
               if (class_exists($class)) $this->template = new $class();
            }
         if (! $this->template) $this->template = new Genesis_Dambuster_Template();  //install the default module if no theme specific module
      }
	}

	public function admin_init() {
		$d = dirname(__FILE__) . '/';
		require_once ($d . 'class-tooltip.php');		
		require_once ($d . 'class-feed-widget.php');
		require_once ($d . 'class-admin.php');		
		require_once ($d . 'class-template-admin.php');
		$this->template_admin = new Genesis_Dambuster_Template_Admin($this->version, $this->path, $this->slug, 'template');
 		if ($this->get_activation_key()) add_action('admin_init', array($this, 'upgrade'));          
	}

	public function get_options(){
		return $this->options;
	}

	public function get_utils(){
		return $this->utils;
	}
	
   public function get_path(){
		return $this->path;
	}

   public function get_slug(){
		return $this->slug;
	}
	
	public function get_version(){
		return $this->version;
	}

	public function get_modules(){
		return $this->modules;
	}

	public function activate() { //called on plugin activation
    	if ( $this->is_genesis_present() ) {
    	    $this->set_activation_key();
		} else {
        	$this->deactivate();
       		wp_die(  __( sprintf('Sorry, you cannot use %1$s unless you are using a child theme based on the StudioPress Genesis theme framework. The %1$s plugin has been deactivated. Go to the WordPress <a href="%2$s"><em>Plugins page</em></a>.',
        		GENESIS_DAMBUSTER_FRIENDLY_NAME, get_admin_url(null, 'plugins.php')), GENESIS_DAMBUSTER_DOMAIN ));
   		} 
	}

	public function upgrade() { //apply any upgrades
		$this->options->upgrade_options();
		$this->template_admin->upgrade();
		$this->unset_activation_key();
	}	

	private function deactivate($path ='') {
		if (empty($path)) $path = $this->path;
		if (is_plugin_active($path)) deactivate_plugins( $path );
	}

   private function get_activation_key() { 
    	return get_option($this->activation_key_name()); 
   }

   private function set_activation_key() { 
    	return update_option($this->activation_key_name(), true); 
   }

   private function unset_activation_key() { 
    	return delete_option($this->activation_key_name(), true); 
   }

   private function activation_key_name() { 
    	return strtolower(__CLASS__) . '_activation'; 
   }

	public function is_genesis_present() {
		return substr(basename( TEMPLATEPATH ), 0,7) == 'genesis' ; //is genesis the current parent theme
	}

	public function is_genesis_loaded() {
		return defined('GENESIS_LIB_DIR'); //is genesis actually loaded? (ie not been nobbled by another plugin) 
	}

	function is_post_type_enabled($post_type){
		return in_array($post_type, array('post', 'page')) || $this->is_custom_post_type_enabled($post_type);
	}

	function is_custom_post_type_enabled($post_type){
		return in_array($post_type, (array)$this->template->get_option('custom_post_types'));
	}

   function custom_post_types_exist() {
       $cpt = get_post_types(array('public' => true, '_builtin' => false));
       return is_array($cpt) && (count($cpt) > 0);
   }

   public function get_template() { 
      return $this->template;
   }

}
