<?php

namespace IMC\Library\Framework;

use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/**
 * Integral Plugin Master Class
 * 
 * !!!!! This class should only be extended !!!!! 
 * 
 */
if (!class_exists('Integral_Plugin_Master')) {

    class Integral_Plugin_Master {


        private static $_this = NULL;


        public function __construct() {

            //- Restrict instancing
            if (isset(self::$_this)) {
                wp_die(sprintf(__('%1$s is a singleton class. Creating a second instance is prohibited.', 'integral-mailchimp'), get_class($this)));
            }

            self::$_this = $this;

            //- Plugin activation/deactivation hooks
            register_activation_hook(__FILE__, array($this, 'activate'));
            register_deactivation_hook(__FILE__, array($this, 'deactivate'));



            //- Include preliminary files
            I_Conf::include_framework_files();            
            
            //- Run initial setup
            I_Conf::initialize_integral_mvc();
            
            //- Include additional preliminary files
            I_Conf::include_controller_files();
            I_Conf::include_api_files();

            //- Register WP filters
            I_Conf::register_filters();

            //- Register WP hooks
            I_Conf::register_plugins_loaded_hooks();
            I_Conf::register_init_hooks();
            I_Conf::register_admin_init_hooks();
            I_Conf::register_wp_loaded_hooks();
            I_Conf::register_enqueue_scripts_hooks();
            I_Conf::register_enqueue_styles_hooks();
            I_Conf::register_user_hooks();
            I_Conf::register_notices_hooks();


        }


        /**
         * Activate the plugin
         * 
         */
        public static function activate() {
            
            $fresh_install = get_option(I_Conf::OPT_VERSION, -1);
            
            self::check_wp_ver();

            if ($fresh_install === -1) {
                update_option(I_Conf::OPT_FRESH_INSTALL, TRUE);
            }
            
            $debug_enabled = get_option(I_Conf::OPT_ENABLE_DEBUG_MODE, -1);
            
            if ($debug_enabled === -1) {
                update_option(I_Conf::OPT_ENABLE_DEBUG_MODE, TRUE);
            }


        }


        /**
         * Deactivate the plugin
         * 
         */
        public static function deactivate() {
            //- TODO - RELEASE - Delete webhooks in MailChimp


        }


        /**
         * WordPress version check
         * 
         */
        public static function check_wp_ver() {
            $plugin_name         = I_Conf::PLUGIN_NAME;
            $wp_min_version      = I_Conf::MIN_WP_VERSION;
            $plugin_version_name = I_Conf::OPT_VERSION;
            $plugin_version      = I_Conf::VERSION;

            if (version_compare(get_bloginfo('version'), $wp_min_version, '<')) {
                wp_die(sprintf(__('You must update to at least WordPress version %1$s to use this version of the %2$s plugin!', 'integral-mailchimp'), "<strong>{$wp_min_version}</strong>", $plugin_name));
            }

            if (get_option($plugin_version_name) === false) {
                add_option($plugin_version_name, $plugin_version);
            }


        }


    }


}
