<?php

namespace IMC;

use IMC\Library\Framework\Integral_Plugin_Master;

/*
  Plugin Name: Integral MailChimp
  Plugin URI: http://integralwp.com/plugins/complete-mailchimp-plugin-for-wordpress/
  Description: Fully integrated MailChimp plugin for WordPress
  Text-Domain: integral-mailchimp
  Version: 1.10.10
  Date: 2015-05-27
  Author: IntegralWP
  Author URI: http://integralwp.com
 */

if (!defined('IMC_PLUGIN_PATH')) {
    define('IMC_PLUGIN_PATH', trailingslashit(plugin_dir_path(__FILE__)));
}

if (!defined('IMC_PLUGIN_FILE')) {
    define('IMC_PLUGIN_FILE', __FILE__);
}

if (!defined("IS_ADMIN")) {
    define("IS_ADMIN", is_admin());
}

if (!defined("IS_MULTISITE")) {
    define("IS_MULTISITE", is_multisite());
}

//- Include composer autoloader 
require_once(IMC_PLUGIN_PATH . 'library/composer/autoload.php');

//- Configuration
require_once(IMC_PLUGIN_PATH . 'config.php');


/**
 * Primary Plugin Class
 * 
 */
if (!class_exists('Integral_MailChimp')) {

    class Integral_MailChimp extends Integral_Plugin_Master {


        public function __construct() {
            parent::__construct();

            add_action('init', array(__CLASS__, 'init_localization'));


        }


        static public function init_localization() {

            //- Enable Localization
            $loaded = load_plugin_textdomain('integral-mailchimp', false, basename(dirname(__FILE__)) . '/languages/');


        }


    }


}

new Integral_MailChimp();
