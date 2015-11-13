<?php
class Genesis_Dambuster_Template {
   const OPTIONS_NAME = 'tweaks';
   const DAMBUSTER_METAKEY = '_genesis_dambuster_template';

	protected $defaults  = array(
      'enabled' => false,
      'remove_header' => false,
		'remove_primary_navigation' => false,
		'remove_secondary_navigation' => false,
		'remove_entry_header' => false,
		'remove_post_title' => false,
		'remove_post_image' => false,
		'remove_breadcrumbs' => false,
		'remove_edit_link' => false,
		'remove_post_info' => false,
		'remove_post_meta' => false,
		'remove_entry_footer' => false,
		'remove_author_box' => false,
		'remove_comments' => false,
		'remove_after_entry' => false,
		'remove_footer_widgets' => false,
		'remove_footer' => false,
		'remove_background' => false,
		'full_width' => false,
		'enable_helpers' => false,
		'max_content_width' => 1140,
		'content_padding' => '20px 30px',
		'custom_post_types' => array()
	);
   protected $utils;
   protected $options;
   protected $post_options;
	protected $post_id = false;
	protected $is_html5 = false;
	protected $is_landing = false;

	function __construct() {
		$this->init();
	}

	private function init() {  //the init function is intended to run at the WordPress hook called "init"
      $the_plugin = Genesis_Dambuster_Plugin::get_instance();
      $this->utils = $the_plugin->get_utils();
      $this->options = $the_plugin->get_options();
		$this->options->add_defaults(array( self::OPTIONS_NAME => $this->defaults));		
		if (! is_admin() ) {
			add_action('wp', array($this,'prepare'));
		}
	}	

	function get_defaults() {
    	return $this->defaults;
   }

	function get_option($option_name, $cache = true) {
    	$options = $this->get_options($cache);
    	if ($option_name && $options && array_key_exists($option_name,$options))
        	return $options[$option_name];
    	else
        	return false;
    }

	function get_options($cache = true) {
      return $this->options->get_option(self::OPTIONS_NAME, $cache);
    }
	
	function save_options($options) {
      return $this->options->save_options(array(self::OPTIONS_NAME => $options)) ;
	}
	
   function prepare() {

		if (is_singular() 
      && ($this->post_id = $this->utils->get_post_id()) //get post/page id
		&& ($meta = $this->utils->get_meta($this->post_id,  self::DAMBUSTER_METAKEY))
		&&	($this->post_options = $this->options->validate_options($this->defaults, $meta))	      
		&& $this->post_options['enabled']) {  //we are tweaking this page
         $this->is_html5 = $this->utils->is_html5();
         $this->is_landing = $this->utils->is_landing_page();
			if ($this->post_options['remove_header']) $this->remove_header();	    
 			if ($this->post_options['remove_primary_navigation']) $this->remove_primary_navigation();
			if ($this->post_options['remove_secondary_navigation']) $this->remove_secondary_navigation();
			if ($this->post_options['remove_entry_header']) $this->remove_entry_header();
			if ($this->post_options['remove_post_title']) $this->remove_post_title();
			if ($this->post_options['remove_post_image']) $this->remove_post_image();
			if ($this->post_options['remove_post_info']) $this->remove_post_info();
			if ($this->post_options['remove_edit_link']) $this->remove_edit_link();
			if ($this->post_options['remove_breadcrumbs']) $this->remove_breadcrumbs();
			if ($this->post_options['remove_entry_footer']) $this->remove_entry_footer();
			if ($this->post_options['remove_post_meta']) $this->remove_post_meta();
			if ($this->post_options['remove_author_box']) $this->remove_author_box();
			if ($this->post_options['remove_comments']) $this->remove_comments();
			if ($this->post_options['remove_after_entry']) $this->remove_after_entry(); 
			if ($this->post_options['remove_footer_widgets']) $this->remove_footer_widgets();
         if ($this->post_options['remove_footer']) $this->remove_footer();
			if ($this->post_options['remove_background']) $this->remove_background();
			if ($this->post_options['full_width']) $this->full_width();
			if ($this->post_options['enable_helpers']) $this->enable_helpers();
		}
   }

	function remove_header() {
      remove_action( 'genesis_header', 'genesis_header_markup_open', 5 );
      remove_action( 'genesis_header', 'genesis_do_header' );
      remove_action( 'genesis_header', 'genesis_header_markup_close', 15 );
      add_filter( 'theme_mod_header_image', '__return_empty_string' );
      do_action( 'genesis_dambuster_remove_header', $this->post_id );
	}

	function remove_primary_navigation() {
	  remove_action( 'genesis_before_header', 'genesis_do_nav' );        
	  remove_action( 'genesis_header', 'genesis_do_nav' ); 
	  remove_action( 'genesis_header', 'genesis_do_nav', 5 );
	  remove_action( 'genesis_header', 'genesis_do_nav', 12 );
	  remove_action( 'genesis_header', 'genesis_do_nav', 14 );
	  remove_action( 'genesis_after_header', 'genesis_do_nav' ); 
	  remove_action( 'genesis_before_content_sidebar_wrap', 'genesis_do_nav' );        
     do_action( 'genesis_dambuster_remove_primary_navigation', $this->post_id );
	}						

	function remove_secondary_navigation() {
	  remove_action( 'genesis_before_header', 'genesis_do_subnav' );        
	  remove_action( 'genesis_header', 'genesis_do_subnav' ); 
	  remove_action( 'genesis_header', 'genesis_do_subnav', 6 ); 
	  remove_action( 'genesis_after_header', 'genesis_do_subnav' ); 
	  remove_action( 'genesis_footer', 'genesis_do_subnav', 7 );
     do_action( 'genesis_dambuster_remove_secondary_navigation', $this->post_id );
	}		


   function remove_entry_header() {
      remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_open', 5 ); // Remove entry header start
      remove_action( 'genesis_entry_header', 'genesis_entry_header_markup_close', 15 );  // Remove entry header end       
      do_action( 'genesis_dambuster_remove_entry_header', $this->post_id );
   }

   function remove_post_title() {
      remove_action( $this->is_html5 ? 'genesis_entry_header' : 'genesis_post_title', 'genesis_do_post_title' ); // Remove Title    
      do_action( 'genesis_dambuster_remove_post_title', $this->post_id );
   }

   function remove_post_image() {
      if ($this->is_html5 )
         remove_action( 'genesis_entry_content', 'genesis_do_post_image' , 8); 
      else 
         remove_action( 'genesis_post_content', 'genesis_do_post_image' ); 
      do_action( 'genesis_dambuster_remove_post_image', $this->post_id );
   }

   function remove_post_info() {
      if ($this->is_html5 ) {
         remove_action( 'genesis_entry_header' , 'genesis_post_info', 5);         
         remove_action( 'genesis_entry_header' , 'genesis_post_info', 7); 
         remove_action( 'genesis_entry_header' , 'genesis_post_info', 12);
      }
      else {
         remove_action('genesis_before_post_content', 'genesis_post_info' );         
      }
      do_action( 'genesis_dambuster_remove_post_info', $this->post_id );
   }


   function remove_edit_link() {
	  add_filter( 'genesis_edit_post_link' ,'__return_false' );// Remove edit link
	  do_action( 'genesis_dambuster_remove_edit_link', $this->post_id );
	}

            
   function remove_breadcrumbs() {
      add_filter( 'genesis_pre_get_option_breadcrumb_' .(is_page() ? 'page':'single'),  '__return_false', 10, 2);            
      do_action( 'genesis_dambuster_remove_breadcrumbs', $this->post_id );
	}

   function remove_entry_footer() {
      remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_open', 5 ); // Remove entry footer start
      remove_action( 'genesis_entry_footer', 'genesis_entry_footer_markup_close', 15 );  // Remove entry footer end            
      do_action( 'genesis_dambuster_remove_entry_footer', $this->post_id );
	}

   function remove_post_meta() {
      remove_action( $this->is_html5 ? 'genesis_entry_footer' : 'genesis_after_post_content', 'genesis_post_meta' );
      do_action( 'genesis_dambuster_remove_post_meta', $this->post_id );
   }

	function remove_author_box() {
      if ($this->is_html5)
         remove_action( 'genesis_after_entry', 'genesis_do_author_box_single', 8 );
      else
         remove_action( 'genesis_after_post', 'genesis_do_author_box_single' );
      do_action( 'genesis_dambuster_remove_author_box', $this->post_id );
   }

	function remove_comments() {
      remove_action( $this->is_html5 ? 'genesis_after_entry' : 'genesis_after_post', 'genesis_get_comments_template' );
      do_action( 'genesis_dambuster_remove_comments', $this->post_id );
   }
         

	function remove_after_entry() {
      remove_action( 'genesis_after_entry', 'genesis_after_entry_widget_area', 5 );
      remove_action( 'genesis_after_entry', 'genesis_after_entry_widget_area' );
      do_action( 'genesis_dambuster_remove_after_entry', $this->post_id );
	}
	     
	function remove_footer() {
      remove_all_actions('genesis_footer');
      do_action( 'genesis_dambuster_remove_footer', $this->post_id );
	}

	function remove_footer_widgets() {
      remove_action( 'genesis_before_footer', 'genesis_footer_widget_areas' );
      remove_action( 'genesis_after_footer', 'genesis_footer_widget_areas' );
      do_action( 'genesis_dambuster_remove_footer_widgets', $this->post_id );
	}		

	function remove_background() {
      add_filter( 'theme_mod_background_image', '__return_empty_string' );
      add_filter( 'theme_mod_background_color', '__return_empty_string' );
		add_filter( 'body_class', array($this,'add_background_body_class') );
      do_action( 'genesis_dambuster_remove_background', $this->post_id );
	}

	function full_width() {
      add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
		add_filter( 'body_class',array($this,'add_full_width_body_class') );
		add_action( 'wp_enqueue_scripts',array($this,'enqueue_full_width_styles') );
   }

	function enable_helpers() {
		add_action( 'wp_enqueue_scripts', array($this,'enqueue_helper_styles') );
   }

	function add_full_width_body_class( $classes ) {
      $classes[] = apply_filters( 'genesis_dambuster_full_width_body_class', 'gd-full-width' );
      return $classes;
   }

	function add_background_body_class( $classes ) {
      $classes[] = apply_filters( 'genesis_dambuster_background_body_class', 'gd-transparent-background' );
      return $classes;
   }

	function enqueue_full_width_styles() {
	   $fullwidth = $this->is_html5 ? 'full-width.css' : 'full-width-xhtml.css' ;
		wp_enqueue_style( 'gd-full-width', plugins_url(  'styles/'.$fullwidth, dirname(__FILE__)), array(), GENESIS_DAMBUSTER_VERSION );
      do_action( 'genesis_dambuster_full_width', $this->post_id );
	} 

	function enqueue_helper_styles() {
		wp_enqueue_style( 'gd-helpers', plugins_url(  'styles/helpers.css', dirname(__FILE__)), array(), GENESIS_DAMBUSTER_VERSION );
      wp_add_inline_style( 'gd-helpers', $this->inline_helper_styles() );	
      do_action( 'genesis_dambuster_helpers', $this->post_id );
	} 

	function inline_helper_styles() {
	   $max_content_width = $this->post_options['max_content_width'];
	   if ( empty($max_content_width) ) $max_content_width = 'none';
	   $content_padding = $this->post_options['content_padding'];
	   if ( empty($content_padding) ) $content_padding = '0';
    	return sprintf( '.gd-full-width .entry .inner { max-width: %1$spx; padding:%2$s', $max_content_width, $content_padding ); 	
   }

}