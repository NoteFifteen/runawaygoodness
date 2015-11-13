<?php
class Genesis_Dambuster_Template_Admin extends Genesis_Dambuster_Admin {
    const INDICATOR = 'gcbc_dambuster_template';

	private $tips = array(
			'enabled' => array('heading' => 'Enable Tweaks', 'tip' => 'Click to enable for this page'),
         'remove_header' => array('heading' => 'Remove Header', 'tip' => 'Remove the entire header area'),
			'remove_primary_navigation' => array('heading' => 'Remove Prim. Nav', 'tip' => 'Remove the primary navigation area'),
			'remove_secondary_navigation' => array('heading' => 'Remove Second. Nav', 'tip' => 'Remove the secondary navigation area'),
			'remove_post_title' => array('heading' => 'Remove Post Title', 'tip' => 'Remove the post title.'),
			'remove_post_image' => array('heading' => 'Remove Post Image', 'tip' => 'Remove the featured image.'),
			'remove_entry_header' => array('heading' => 'Remove Entry Header', 'tip' => 'Remove the header  markup around the page title'),
			'remove_breadcrumbs' => array('heading' => 'Remove Breadcrumbs', 'tip' => 'Remove the Breadcrumbs'),
			'remove_post_info' => array('heading' => 'Remove Post Info', 'tip' => 'Remove the Post Info (author, date, comments, etc)'),
			'remove_edit_link' => array('heading' => 'Remove Edit Link', 'tip' => 'Remove the edit link that appears only when you are logged in'),
			'remove_post_meta' => array('heading' => 'Remove Post Meta', 'tip' => 'Remove the Post Meta (categories, tags, etc)'),
			'remove_entry_footer' => array('heading' => 'Remove Entry Footer', 'tip' => 'Remove the footer markup around the post meta'),
			'remove_author_box' => array('heading' => 'Remove Author Box', 'tip' => 'Remove the Author Box'),
			'remove_comments' => array('heading' => 'Remove Comments', 'tip' => 'Remove the comments section'),
			'remove_after_entry' => array('heading' => 'Remove After Entry', 'tip' => 'Remove the After Entry Widget Area'),
			'remove_footer_widgets' => array('heading' => 'Remove Widgets', 'tip' => 'Remove any footer widgets'),
			'remove_footer' => array('heading' => 'Remove Footer', 'tip' => 'Remove any footer section'),
			'remove_background' => array('heading' => 'Remove Background', 'tip' => 'Remove any custom background color or image'),
			'full_width' => array('heading' => 'Full Width Page', 'tip' => 'Make the page full width'),
			'enable_helpers' => array('heading' => 'Enable Helpers', 'tip' => 'Click to enable the helper classes such as <i>inner</i> and <i>clearfix</i>'),
			'max_content_width' => array('heading' => 'Inner Content Width', 'tip' => 'Set the maximum width of the inner content - typically somewhere between 640px and 1140px - or leave blank to set no restriction '),
			'content_padding' => array('heading' => 'Content Padding', 'tip' => 'You maybe want to add vertical padding at the top and bottom of each section. For example, to add 20px padding above and below the content use: <i>20px 0</i><br/><br/>You may also want to add horizontal padding when using borders or backgrounds. For example, to add 20px padding above and below the content and 30px to both side of the content use: <i>20px 30px</i>'),
         'custom_post_types' => array('heading' => 'Enable Plugin On', 'tip' => 'Click to enable the plugin to operate on selected custom post types.'), 
   );
   
	private $template;
	
	function init() {
      $this->template = $this->plugin->get_template();
		add_action('load-post.php', array($this, 'load_post_page'));	
		add_action('load-post-new.php', array($this, 'load_post_page'));	
		add_action('save_post', array($this, 'save_postmeta'));
		add_action('do_meta_boxes', array($this, 'do_meta_boxes'), 30, 2 );
		add_action('admin_menu',array($this, 'admin_menu'));
	}

	function admin_menu() {
		$plugin_name = __('Dambuster', GENESIS_DAMBUSTER_DOMAIN);	
		$this->screen_id = add_submenu_page('genesis', __(GENESIS_DAMBUSTER_FRIENDLY_NAME), $plugin_name,  'manage_options',
			$this->get_slug(), array($this,'page_content'));
		add_action('load-'.$this->get_screen_id(), array($this, 'load_page'));
 		add_action('admin_enqueue_scripts', array($this, 'register_tooltip_styles'));		
	}

	function page_content() {
		$title = $this->admin_heading(sprintf('Genesis Dambuster (v%1$s) Settings', GENESIS_DAMBUSTER_VERSION));
		$this->print_admin_form($title, __CLASS__, $this->get_keys());
	}

	function load_page() {
 		if (isset($_POST['options_update'])) $this->save_tweaks();
		add_action('admin_enqueue_scripts', array($this, 'register_admin_styles'));
		$callback_params = array ('options' => $this->template->get_options(false));
		$this->add_meta_box('intro', 'Introduction',  'intro_panel', null);
		$this->add_meta_box('defaults', 'Default Template Tweaks', 'display_panel', $callback_params);
      if ($this->plugin->custom_post_types_exist())
         $this->add_meta_box('control', 'Control Panel', 'control_panel', $callback_params);
		$this->add_meta_box('news', 'Genesis Club News', 'news_panel', null, 'advanced');
		$this->set_tooltips($this->tips);

		add_action ('admin_enqueue_scripts',array($this, 'enqueue_admin_styles'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_metabox_scripts'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
	}
 		
 
	function load_post_page() {
		$this->set_tooltips($this->tips);
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_metabox_scripts'));
	}


	function save_tweaks() {
		check_admin_referer(__CLASS__);
		return $this->save_options($this->template, __('Dambuster Settings', GENESIS_DAMBUSTER_DOMAIN ));
	}

	function save_postmeta($post_id) {
		if (array_key_exists(self::INDICATOR, $_POST)) {
         $metakey = Genesis_Dambuster_Template::DAMBUSTER_METAKEY;
         $val = array_key_exists($metakey, $_POST) ?  $this->options->validate_options($this->template->get_defaults(), $_POST[$metakey] ) : false;
			update_post_meta( $post_id, $metakey, $val );
		}	
	}

	function do_meta_boxes( $post_type, $context) {
      if ($this->is_metabox_active($post_type, $context)) {
         add_meta_box('genesis-dambuster-settings', 'Genesis Dambuster',  array($this,'post_panel'), $post_type);		
			$current_screen = get_current_screen();
			if (method_exists($current_screen,'add_help_tab'))
	    		$current_screen->add_help_tab( array(
			      'id'	=> 'genesis_dambuster_help_tab',
    			   'title'	=> __('Genesis Dambuster'),
        			'content'	=> __('
<p>In the <b>Genesis Dambuster</b> section below you can choose to remove certain elements from the page and make it full width.</p>')) );
		}
	}

   function upgrade() {
      $defaults = $this->template->get_defaults();
      $options = $this->template->get_options(false);
      if (isset($options['custom_post_types'])) unset($options['custom_post_types']);
      if (count(array_diff($options, $defaults))  == 0) { //if all fields are false then make them all true
         foreach ($options as $key => $val) {
            if ((strpos($key, 'remove_') !== FALSE) 
            || (strpos($key, 'full_') !== FALSE)) 
               $options[$key] = true;
         }
         return $this->template->save_options($options);      
      }
   }

	function control_panel($post,$metabox) {
      $options = $metabox['args']['options'];
      return $this->display_metabox( array('Custom Post Types' => $this->cpt_panel($options['custom_post_types'])), 1);
   }	

	private function cpt_panel($post_types){	
      $options = array();
		$all_custom_post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');	
		foreach ($all_custom_post_types as $post_type) $options[$post_type->name] = $post_type->labels->name;
      return $this->fetch_form_field('custom_post_types', $post_types, 'checkboxes', $options);
	}

	function display_panel($post, $metabox) {
		$options = $metabox['args']['options'];	 	
      $this->display_metabox( array (
         'Template Width' => $this->width_panel($options),
         'Header' => $this->header_panel($options),
         'Above Content' => $this->above_panel($options),
         'Below Content' => $this->below_panel($options),
         'Footer' => $this->footer_panel($options),
         'Background' => $this->background_panel($options),
         'Helper Classes' => $this->helper_panel($options),
      ));
   }	

	function post_panel($post) {
      $meta = $this->get_meta_form_data(Genesis_Dambuster_Template::DAMBUSTER_METAKEY, '', $this->template->get_options() );
      $this->display_metabox( array(
         'Enable' => $this->enable_post_panel($meta), 
         'Template Width' => $this->width_post_panel($meta), 
         'Header' => $this->header_post_panel($meta), 
         'Above Content' => $this->above_post_panel($meta), 
         'Below Content' => $this->below_post_panel($meta), 
         'Footer' => $this->footer_post_panel($meta),
         'Background' => $this->background_post_panel($meta),
         'Helper Classes' => $this->helper_post_panel($meta),
      ));
      
   }

	function enable_post_panel($meta) {
      return $this->form_field(self::INDICATOR, self::INDICATOR, '', 1, 'hidden') . 
         $this->meta_form_field($meta, 'enabled', 'checkbox');
   }

	function width_post_panel($meta) {
      return 
         $this->meta_form_field($meta, 'full_width', 'checkbox');
   }

	function width_panel($options) {
      return  
         $this->fetch_form_field('full_width', $options['full_width'], 'checkbox');
   }

	function header_post_panel($meta) {
	   return $this->meta_form_field($meta, 'remove_header', 'checkbox').
         $this->meta_form_field($meta, 'remove_primary_navigation', 'checkbox').
         $this->meta_form_field($meta, 'remove_secondary_navigation', 'checkbox');
   }

	function header_panel($options) {
      return  
         $this->fetch_form_field('remove_header', $options['remove_header'], 'checkbox').
         $this->fetch_form_field('remove_primary_navigation', $options['remove_primary_navigation'], 'checkbox').
         $this->fetch_form_field('remove_secondary_navigation', $options['remove_secondary_navigation'], 'checkbox');
   }

	function above_post_panel($meta) {
	   return
         $this->meta_form_field($meta, 'remove_breadcrumbs', 'checkbox').
         $this->meta_form_field($meta, 'remove_post_title', 'checkbox').
         $this->meta_form_field($meta, 'remove_post_image', 'checkbox').
         $this->meta_form_field($meta, 'remove_entry_header', 'checkbox').
         $this->meta_form_field($meta, 'remove_post_info', 'checkbox').
         $this->meta_form_field($meta, 'remove_edit_link', 'checkbox');
   }

	function above_panel($options) {
      return  
         $this->fetch_form_field('remove_breadcrumbs', $options['remove_breadcrumbs'], 'checkbox').
         $this->fetch_form_field('remove_post_title', $options['remove_post_title'], 'checkbox').
         $this->fetch_form_field('remove_post_image', $options['remove_post_image'], 'checkbox').
         $this->fetch_form_field('remove_entry_header', $options['remove_entry_header'], 'checkbox').
         $this->fetch_form_field('remove_post_info', $options['remove_post_info'], 'checkbox').
         $this->fetch_form_field('remove_edit_link', $options['remove_edit_link'], 'checkbox');
   }

	function below_post_panel($meta) {
	   return 
         $this->meta_form_field($meta, 'remove_entry_footer', 'checkbox').
         $this->meta_form_field($meta, 'remove_post_meta', 'checkbox').
         $this->meta_form_field($meta, 'remove_author_box', 'checkbox').
         $this->meta_form_field($meta, 'remove_comments', 'checkbox').
         $this->meta_form_field($meta, 'remove_after_entry', 'checkbox');
   }

	function below_panel($options) {
      return  
         $this->fetch_form_field('remove_entry_footer', $options['remove_entry_footer'], 'checkbox').
         $this->fetch_form_field('remove_post_meta', $options['remove_post_meta'], 'checkbox').
         $this->fetch_form_field('remove_author_box', $options['remove_author_box'], 'checkbox').
         $this->fetch_form_field('remove_comments', $options['remove_comments'], 'checkbox').
         $this->fetch_form_field('remove_after_entry', $options['remove_after_entry'], 'checkbox');
   }

	function footer_post_panel($meta) {
	   return $this->meta_form_field($meta, 'remove_footer', 'checkbox').
         $this->meta_form_field($meta, 'remove_footer_widgets', 'checkbox');
   }

 	function footer_panel($options) {
      return  
         $this->fetch_form_field('remove_footer', $options['remove_footer'], 'checkbox').
         $this->fetch_form_field('remove_footer_widgets', $options['remove_footer_widgets'], 'checkbox');
   }


	function background_post_panel($meta) {
	   return 
         $this->meta_form_field($meta, 'remove_background', 'checkbox');
   }

 	function background_panel($options) {
      return  
         $this->fetch_form_field('remove_background', $options['remove_background'], 'checkbox');
   }


   function helper_intro(){
      return sprintf('<p><em>%1$s</em></p><p>%2$s</p>', 
         __('If you using a Page Builder such as Beaver Builder, you may ignore this tab.', GENESIS_DAMBUSTER_DOMAIN) , 
         __('However, if you are NOT using a Page Builder you may want to make use of some of our predefined helper classes by clicking the checkbox.', GENESIS_DAMBUSTER_DOMAIN) );      
   }

   function helper_warning(){
      return sprintf('<p>%1$s</p>', 
         __('The settings below allow you to customize the helper classes. Note these settings will be ignored if the <i>Enable Helpers</i> checkbox is not ticked.', GENESIS_DAMBUSTER_DOMAIN) );
   }

   function helper_example(){
      return sprintf('<p>%1$s</p>', 
         __( 'One such helper class is called <i>inner</i> which you need to refer to explicitly in the HTML of your page (&lt;div class="inner">Your Content&lt;/div>) on the sections where you want the content in the center with the width specified above rather than edge to edge.', GENESIS_DAMBUSTER_DOMAIN) );
   }

	function helper_panel($options) {
      return 
         $this->helper_intro().
         $this->fetch_form_field('enable_helpers', $options['enable_helpers'], 'checkbox').
         $this->helper_warning().
         $this->fetch_form_field('max_content_width', $options['max_content_width'], 'text', array(), array('size' => 4, 'suffix' => 'px')).
         $this->fetch_form_field('content_padding', $options['content_padding'], 'text', array(), array('size' => 20)).
         $this->helper_example() ;
   }
   
	function helper_post_panel($meta) {
      return 
         $this->helper_intro().
         $this->meta_form_field($meta, 'enable_helpers', 'checkbox').
         $this->helper_warning().
         $this->meta_form_field($meta, 'max_content_width', 'text', array(), array('size' => 4, 'suffix' => 'px')).
         $this->meta_form_field($meta, 'content_padding', 'text', array(), array('size' => 20)).
         $this->helper_example() ;
   }

 	function intro_panel(){	
		$email = GENESIS_DAMBUSTER_HELP; 	
		print <<< INTRO
<p>This early version of the plugin works with most but not all Genesis child themes. The list of themes we have tested is found <a href="http://www.genesisdambuster.com/supported-genesis-child-themes/">here.</a></p>
<p>If you have problems then please contact us at stating your site URL, your Genesis child theme and a description of the problem.</p>
<p id="support"><strong>CONTACT EMAIL: <a href="mailto:{$email}">{$email}</a></strong></p>
<p>You may need to send us a ZIP file of your theme if it is not a StudioPress Genesis child theme so we can recreate the issue.</p>
<p>The following section allows you to edit the default setup of the dambuster. The settings will be appear as the default template tweaks for new posts and pages however the <i>Enable Tweaks</i> checkbox will NOT be ticked so the feature is not enabled automatically on new pages.</p>
<p>In the Page Editor, you will need to switch on the feature explicitly on individual pages and posts by clicking the <i>Enable Tweaks</i> checkbox. You can override the defaults if required. We expect this feature to be used on your landing pages, galleries pages and when using content builders such as Beaver Builder.</p>
INTRO;
	}
}