<?php

namespace IMC;

use IMC\Library\Framework\Logger;
use IMC\Library\Utility\Integral_MailChimp_API;
use IMC\Library\Utility\Integral_EDD_Updater;
use IMC\Controllers\Subscribe_Widget_Controller;

if (!defined('ABSPATH')) {
    die('Access denied.');
}

if (!defined('IMC_PLUGIN_PATH')) {
    define('IMC_PLUGIN_PATH', trailingslashit(plugin_dir_path(__FILE__)));
}
if (!defined('IMC_PLUGIN_ROOT_URL')) {
    define('IMC_PLUGIN_ROOT_URL', trailingslashit(plugins_url('', __FILE__)));
}
/**
 * Integral Config Class
 * 
 */
if (!class_exists('I_Conf')) {

    class I_Conf {


        //- Plugin Info
        const VERSION                        = '1.10.10';
        const MIN_WP_VERSION                 = '3.9';
        const PLUGIN_NAME                    = 'Integral MailChimp';
        const PLUGIN_EDD_NAME                = 'Integral MailChimp';
        const PLUGIN_TEXT_DOMAIN             = 'integral-mailchimp';
        const PLUGIN_COOKIE_KEY              = 'integral-mailchimp-cookie';
        const EDD_URL                        = 'http://integralwp.com/checkout';
        //
        //- General
        const IS_ADMIN                       = IS_ADMIN;
        const CONTROLLER_WITH_VIEW           = TRUE;
        const CONTROLLER_WITHOUT_VIEW        = FALSE;
        const IS_AJAX_FORM                   = TRUE;
        const IS_NOT_AJAX_FORM               = FALSE;
        const PURGE_CACHED_OPTION            = TRUE;
        //
        //- Paths
        const FRAMEWORK_PATH                 = 'library/framework/';
        const UTILITY_PATH                   = 'library/utility/';
        const RESOURCE_PATH                  = 'library/resource/';
        const API_PATH                       = 'library/api/';
        const CONTROLLER_PATH                = 'controllers/';
        const MODEL_PATH                     = 'models/';
        const FORM_PATH                      = 'forms/';
        const VIEW_PATH                      = 'views/';
        const LAYOUT_PATH                    = 'views/layouts/';
        const VIEW_SCRIPT_PATH               = 'views/scripts/';
        const VIEW_FORM_PATH                 = 'views/forms/';
        const JQUERY_JS_PATH                 = 'js/jquery/';
        const JS_PATH                        = 'js/';
        //
        //- Permissions
        const ADMIN_PERMISSION               = 'edit_others_posts';
        //
        //- API Calls
        const CALL_CAMPAIGNS                 = 'Campaigns';
        const CALL_EXCOMM                    = 'Excomm';
        const CALL_FOLDERS                   = 'Folders';
        const CALL_GALLERY                   = 'Gallery';
        const CALL_HELPER                    = 'Helper';
        const CALL_LISTS                     = 'Lists';
        const CALL_MOBILE                    = 'Mobile';
        const CALL_REPORTS                   = 'Reports';
        const CALL_TEMPLATES                 = 'Templates';
        const CALL_USERS                     = 'Users';
        const CALL_VIP                       = 'Vip';
        //
        //- Admin slugs
        const ADMIN_FORM_SUBMIT_PREFIX       = 'admin.php?page=';
        const ADMIN_GENERAL_OPTIONS_SLUG     = 'integral_mailchimp/general';
        const ADMIN_GENERAL_OPTIONS_ACTION   = 'general_options_form_process';
        const ADMIN_PROCESS_KEY_ACTION       = 'general_options_process_key';
        const ADMIN_LIST_MANAGEMENT_SLUG     = 'integral_mailchimp/lists';
        const ADMIN_LIST_SYNC_MERGE_TAG      = 'list_sync_merge_tag';
        const ADMIN_LIST_SYNC_USERS_ACTION   = 'list_sync_users';
        const EMAIL_CMPGN_GET_TMPLT_ACTION   = 'email_campaign_get_template_process';
        const EMAIL_CMPG_GET_TMPLT_INFO      = 'load_mailchimp_template_info_ajax';
        const EMAIL_CMPGN_METABOX_ACTION     = 'email_campaign_metabox_process';
        const EMAIL_CMPG_AJAX_ACTION         = 'ajax_build_campaign';
        const EMAIL_CMPG_AJAX_SAVE_ACTION    = 'ajax_save_campaign';
        const ADMIN_LIST_GROUPINGS_SLUG      = 'integral_mailchimp/groups';
        const ADMIN_LIST_GROUPINGS_ACTION    = 'group_builder_form_process';
        const ADMIN_DEBUG_LOG_SLUG           = 'integral_mailchimp/debug_log';
        const ADMIN_DEBUG_LOG_SEND_ACTION    = 'debug_log_send_process';
        const ADMIN_DEBUG_LOG_CLEAR_ACTION   = 'debug_log_clear_process';
        const ADMIN_LIST_MERGETAG_SLUG       = 'integral_mailchimp/mergetags';
        const ADMIN_LIST_MERGETAG_ACTION     = 'mergetag_builder_form_process';
        const ADMIN_LIST_MERGETAG_DEL_ACTION = 'mergetag_builder_delete_process';
        const ADMIN_NEW_USER_SLUG            = 'integral_mailchimp/newuser';
        const ADMIN_WEBHOOK_CALLBACK_ACTION  = 'process_webhook_response';
        const ADMIN_WEBHOOK_REGISTER_ACTION  = 'webhook_registration';
        const ADMIN_SKIP_USER_SYNC_ACTION    = 'skip_user_sync';
        const PUBLIC_WIDGET_REG_FORM_ACTION  = 'widget_reg_form_process';
        //
        //- Option names
        const OPT_VERSION                    = 'imc_opt_version';
        const OPT_FRESH_INSTALL              = 'imc_opt_fresh_install';
        const OPT_WEBHOOK_KEYS               = 'imc_opt_webhook_keys';
        const OPT_LICENSEKEY                 = 'imc_opt_licensekey';
        const OPT_LICENSEKEY_STATUS          = 'imc_opt_licensekey_status';
        const OPT_APIKEY                     = 'imc_opt_apikey';
        const OPT_DEFAULT_USER_LIST          = 'imc_opt_default_user_list';
        const OPT_SYNC_MERGE_TAGS            = 'imc_opt_sync_merge_tags';
        const OPT_USERS_SYNCED               = 'imc_opt_users_synced';
        const OPT_WEBHOOKS_REGISTERED        = 'imc_opt_webhooks_registered';
        const OPT_MC_LIST_GROUPINGS          = 'imc_opt_mc_list_groupings';
        const OPT_MC_LOCAL_MERGETAGS         = 'imc_opt_mc_local_mergetags';
        const OPT_MC_ADVANCED_MERGETAGS      = 'imc_opt_mc_advanced_mergetags';
        const OPT_SSL_VERIFY_PEER            = 'imc_opt_ssl_verify_peer';
        const OPT_ENABLE_DEBUG_MODE          = 'imc_opt_enable_debug_mode';
        //
        //- Form settings
        const FORM_PROCESS_JQUERY            = TRUE;
        //
        //- Form fields
        const FORM_NONCE                     = 'integral_mailchimp_nonce';
        const FIELD_NEW_USER_EMAIL           = 'imc_new_user_email';
        //
        //- Flushable transients
        const TRANS_MC_LISTS                 = 'imc_lists';
        const TRANS_MC_TEMPLATES             = 'imc_tmplts';
        const TRANS_MC_LIST_SEGMENTS         = 'imc_sgmnts_';
        const TRANS_MC_TEMPLATE_INFO         = 'imc_tmpltsnfo_';
        const TRANS_MC_CAMPAIGN_INFO         = 'imc_cmpgninfo_';
        const TRANS_MC_CAMPAIGN_CONTENT      = 'imc_cmpgncntnt_';
        const TRANS_MC_LIST_MERGE_TAGS       = 'imc_merge_tags_';
        const TRANS_MC_LIST_GROUPINGS        = 'imc_lst_grpngs_';
        const TRANS_MC_LIST_FOR_EMAIL        = 'imc_lsts_for_';
        const TRANS_MC_SUMMARY_REPORT        = 'imc_cmpgn_smry_';
        //
        //- Log file processors
        const TRACE                          = 'IntrospectionProcessor';
        const GIT                            = 'GitProcessor';


        //- Variables
        private static $session_code = NULL;
        public static $mcAPI         = FALSE;
        public static $debug_enabled = FALSE;


        public function __construct() {
            wp_die('THIS CLASS MAY NOT BE INSTATIATED [' . __CLASS__ . ' - ' . __FILE__ . ']');


        }


        /**
         * INITIALIZATION
         * ************************************************************************ */


        /**
         * Initializes the required Plugin assets
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function initialize_integral_mvc() {

            self::$debug_enabled = get_option(self::OPT_ENABLE_DEBUG_MODE);

            self::_initialize_session_cookie();

            self::_initialize_mailchimp_api();

            self::_edd_plugin_updater();


        }


        /**
         * INCLUDE MVC FILES
         * ************************************************************************ */


        /**
         * Framework includes
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function include_framework_files() {
            self::include_file(IMC_PLUGIN_PATH . self::UTILITY_PATH . 'integral-tools.utility.php');
            self::include_file(IMC_PLUGIN_PATH . self::UTILITY_PATH . 'integral-cache.utility.php');
            self::include_file(IMC_PLUGIN_PATH . self::UTILITY_PATH . 'integral-edd-updater.utility.php');
            self::include_file(IMC_PLUGIN_PATH . self::UTILITY_PATH . 'integral-mailchimp-api.utility.php');

            self::include_file(IMC_PLUGIN_PATH . self::FRAMEWORK_PATH . 'integral-plugin-view.class.php');
            self::include_file(IMC_PLUGIN_PATH . self::FRAMEWORK_PATH . 'integral-plugin-form.class.php');
            self::include_file(IMC_PLUGIN_PATH . self::FRAMEWORK_PATH . 'integral-plugin-logger.class.php');
            self::include_file(IMC_PLUGIN_PATH . self::FRAMEWORK_PATH . 'integral-plugin-cpt.class.php');


        }


        /**
         * Controller includes
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function include_controller_files() {

            self::include_file(IMC_PLUGIN_PATH . self::CONTROLLER_PATH . 'admin-options.controller.php');
            self::include_file(IMC_PLUGIN_PATH . self::CONTROLLER_PATH . 'user-lists.controller.php');
            self::include_file(IMC_PLUGIN_PATH . self::CONTROLLER_PATH . 'webhooks.controller.php');
            //self::include_file(IMC_PLUGIN_PATH . self::CONTROLLER_PATH . 'list-groups.controller.php');
            self::include_file(IMC_PLUGIN_PATH . self::CONTROLLER_PATH . 'email-campaign-cpt.controller.php');
            //self::include_file(IMC_PLUGIN_PATH . self::CONTROLLER_PATH . 'history-log-cpt.controller.php');
            self::include_file(IMC_PLUGIN_PATH . self::CONTROLLER_PATH . 'subscribe-widget.controller.php');
            self::include_file(IMC_PLUGIN_PATH . self::CONTROLLER_PATH . 'debug-log.controller.php');


        }


        /**
         * API includes
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function include_api_files() {

            self::include_file(IMC_PLUGIN_PATH . self::UTILITY_PATH . 'integral-mailchimp-base.utility.php');


        }


        /**
         * REGISTER WORDPRESS FILTERS
         * ************************************************************************ */


        /**
         * Register filters
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function register_filters() {

            //- Feed the mergetags built in the builder into the list mergetag selection form
            add_filter('integral_mailchimp_plugin_sync_merge_tags', array('IMC\Controllers\User_Lists_Controller', 'get_syncable_mergetags'));
            add_filter('integral_mailchimp_plugin_get_merge_tags', array('IMC\Controllers\User_Lists_Controller', 'get_mergetag_values_for_sync'), 10, 2);


        }


        /**
         * REGISTER WORDPRESS HOOKS
         * ************************************************************************ */


        /**
         * Register plugins_loaded hooks
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function register_plugins_loaded_hooks() {
            add_action('plugins_loaded', array(__CLASS__, 'plugins_loaded'), 0);


        }


        /**
         * Register init hooks
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function register_init_hooks() {
            add_action('init', array('IMC\Controllers\Admin_Options_Controller', 'init_hook'), 0);


        }


        /**
         * Register admin init hooks
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function register_admin_init_hooks() {
            
        }


        /**
         * Register wp_loaded hooks
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function register_wp_loaded_hooks() {
            if (IS_ADMIN) {
                add_action('wp_loaded', array(__CLASS__, 'register_admin_menu_hooks'));
                add_action('wp_loaded', array(__CLASS__, 'register_admin_form_hooks'));
            } else {
                
            }


        }


        /**
         * Register admin_enqueue_scripts hooks
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function register_enqueue_scripts_hooks() {
            global $hook;
            if (IS_ADMIN) {
                add_action('admin_enqueue_scripts', array(__CLASS__, 'register_general_admin_scripts'), $hook);
            } else {
                add_action('wp_enqueue_scripts', array(__CLASS__, 'register_general_public_scripts'), $hook);
            }


        }


        /**
         * Register admin_enqueue_styles hooks
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function register_enqueue_styles_hooks() {
            global $hook;
            if (IS_ADMIN) {
                add_action('admin_enqueue_scripts', array(__CLASS__, 'register_general_admin_styles'), $hook);
                add_action('wp_enqueue_scripts', array(__CLASS__, 'register_general_public_styles'), $hook);
            } else {
                add_action('wp_enqueue_scripts', array(__CLASS__, 'register_general_public_styles'), $hook);
            }


        }


        /**
         * Register user processing hooks
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function register_user_hooks() {

            $priority = 888;

            add_action('user_register', array('IMC\Controllers\User_Lists_Controller', 'add_user_to_default_list'), $priority);
            add_action('delete_user', array('IMC\Controllers\User_Lists_Controller', 'remove_user_from_all_lists'), $priority);
            add_action('profile_update', array('IMC\Controllers\User_Lists_Controller', 'update_user_in_all_lists'), $priority, 2);

            if (IS_MULTISITE) {
                add_action('add_user_to_blog', array('IMC\Controllers\User_Lists_Controller', 'add_blog_user_to_default_list'), $priority, 3);
                add_action('remove_user_from_blog', array('IMC\Controllers\User_Lists_Controller', 'remove_blog_user_from_all_lists'), $priority, 2);
            }


            //- For other plugins to trigger a user save and update
            add_action('integral_mailchimp_user_create', array('IMC\Controllers\User_Lists_Controller', 'add_user_to_default_list'), $priority);
            add_action('integral_mailchimp_user_update', array('IMC\Controllers\User_Lists_Controller', 'update_user_in_all_lists'), $priority, 2);
            add_action('integral_mailchimp_user_delete', array('IMC\Controllers\User_Lists_Controller', 'remove_user_from_all_lists'), $priority);

            //- For other plugins to update the local user after a MailChimp webhook update
            add_action('integral_mailchimp_plugin_update_merge_tags', array('IMC\Controllers\User_Lists_Controller', 'update_mergetag_values_from_sync'), $priority, 2);


        }


        /**
         * Register post processing hooks
         * 
         * Called directly from the Main Plugin Class (Integral_Plugin_Master) 
         */
        public static function register_notices_hooks() {
            if (IS_ADMIN) {
                add_action('all_admin_notices', array('IMC\Controllers\User_Lists_Controller', 'user_added_to_list'));
                add_action('all_admin_notices', array('IMC\Controllers\User_Lists_Controller', 'user_removed_from_lists'));
                add_action('all_admin_notices', array('IMC\Controllers\User_Lists_Controller', 'user_updated_in_all_lists'));
                add_action('all_admin_notices', array('IMC\Controllers\Admin_Options_Controller', 'api_notices'));
            } else {
                
            }


        }


        /**
         * Register admin_menu hooks
         * 
         * Called via the wp_loaded hook in register_wp_loaded_hooks()
         */
        public static function register_admin_menu_hooks() {
            if (IS_ADMIN) {
                add_action('admin_menu', array(__CLASS__, 'register_admin_menu_pages'));
            } else {
                
            }


        }


        /**
         * REGISTER PLUGIN HOOKS
         * ************************************************************************ */


        /**
         * Register form processing hooks
         * 
         * Called via the wp_loaded hook in register_wp_loaded_hooks()
         */
        public static function register_admin_form_hooks() {
            if (IS_ADMIN) {
                //- Hook for Admin Options page form
                add_action('wp_ajax_' . self::ADMIN_GENERAL_OPTIONS_ACTION, array('IMC\Controllers\Admin_Options_Controller', self::ADMIN_GENERAL_OPTIONS_ACTION));
                add_action('wp_ajax_' . self::ADMIN_PROCESS_KEY_ACTION, array('IMC\Controllers\Admin_Options_Controller', self::ADMIN_PROCESS_KEY_ACTION));

                //- Hook for List Management Merge Tag form
                add_action('wp_ajax_' . self::ADMIN_LIST_SYNC_MERGE_TAG, array('IMC\Controllers\User_Lists_Controller', self::ADMIN_LIST_SYNC_MERGE_TAG));

                //- Hook for List Management User Syncing form
                add_action('wp_ajax_' . self::ADMIN_LIST_SYNC_USERS_ACTION, array('IMC\Controllers\User_Lists_Controller', self::ADMIN_LIST_SYNC_USERS_ACTION));

                //- Hooks for Webhooks Registrations
                add_action('wp_ajax_nopriv_' . self::ADMIN_WEBHOOK_CALLBACK_ACTION, array('IMC\Controllers\Webhooks_Controller', self::ADMIN_WEBHOOK_CALLBACK_ACTION));
                add_action('wp_ajax_' . self::ADMIN_WEBHOOK_CALLBACK_ACTION, array('IMC\Controllers\Webhooks_Controller', self::ADMIN_WEBHOOK_CALLBACK_ACTION));
                add_action('wp_ajax_' . self::ADMIN_WEBHOOK_REGISTER_ACTION, array('IMC\Controllers\Webhooks_Controller', self::ADMIN_WEBHOOK_REGISTER_ACTION));

                //- Hook for skipping User Syncing in the Setup Wizard
                add_action('wp_ajax_' . self::ADMIN_SKIP_USER_SYNC_ACTION, array('IMC\Controllers\Admin_Options_Controller', self::ADMIN_SKIP_USER_SYNC_ACTION));

                //- Hooks for Widget User Registration forms
                add_action('wp_ajax_nopriv_' . self::PUBLIC_WIDGET_REG_FORM_ACTION, array('IMC\Controllers\Subscribe_Widget_Controller', self::PUBLIC_WIDGET_REG_FORM_ACTION));
                add_action('wp_ajax_' . self::PUBLIC_WIDGET_REG_FORM_ACTION, array('IMC\Controllers\Subscribe_Widget_Controller', self::PUBLIC_WIDGET_REG_FORM_ACTION));

                //- Hook for getting a list's segments and default info
                add_action('wp_ajax_' . self::EMAIL_CMPG_AJAX_ACTION, array('IMC\Controllers\Email_Campaigns_CPT_Controller', self::EMAIL_CMPG_AJAX_ACTION));

                //- Hook for getting a specific MC template's info
                add_action('wp_ajax_' . self::EMAIL_CMPG_GET_TMPLT_INFO, array('IMC\Library\Utility\Integral_MailChimp_Templates', self::EMAIL_CMPG_GET_TMPLT_INFO));

                //- Hooks for Email Campaign CPT forms
                add_action('wp_ajax_' . self::EMAIL_CMPG_AJAX_SAVE_ACTION, array('IMC\Controllers\Email_Campaigns_CPT_Controller', self::EMAIL_CMPG_AJAX_SAVE_ACTION));

                //- Hooks for Debug Log
                add_action('admin_post_' . self::ADMIN_DEBUG_LOG_CLEAR_ACTION, array('IMC\Controllers\Debug_Log_Controller', self::ADMIN_DEBUG_LOG_CLEAR_ACTION));
                add_action('wp_ajax_' . self::ADMIN_DEBUG_LOG_SEND_ACTION, array('IMC\Controllers\Debug_Log_Controller', self::ADMIN_DEBUG_LOG_SEND_ACTION));
            } else {
                
            }


        }


        /**
         * Register administration menu pages
         * 
         * Called via the admin_menu hook in register_admin_menu_hooks()
         */
        public static function register_admin_menu_pages() {
            $plugin_admin_pages         = array();
            $plugin_required_permission = apply_filters('imc_required_permission', I_Conf::ADMIN_PERMISSION);
            $menu_position              = 4.036373546;
            $edit_imc_history_logs_slug = 'edit.php?post_type=imc_history_log';

            $plugin_admin_pages[] = add_menu_page('Integral MailChimp Options', 'MailChimp Emails', $plugin_required_permission, self::ADMIN_GENERAL_OPTIONS_SLUG, array('IMC\Controllers\Admin_Options_Controller', 'general_options_form_view'), null, $menu_position);
            
            $plugin_admin_pages[] = add_submenu_page(self::ADMIN_GENERAL_OPTIONS_SLUG, 'MailChimp Options', 'MailChimp Options', $plugin_required_permission, self::ADMIN_GENERAL_OPTIONS_SLUG, array('IMC\Controllers\Admin_Options_Controller', 'general_options_form_view'));
            
            $plugin_admin_pages[] = add_submenu_page(self::ADMIN_GENERAL_OPTIONS_SLUG, 'List Management', 'List Management', $plugin_required_permission, self::ADMIN_LIST_MANAGEMENT_SLUG, array('IMC\Controllers\User_Lists_Controller', 'list_management_form_view'));
            
            $plugin_admin_pages[] = add_submenu_page(self::ADMIN_GENERAL_OPTIONS_SLUG, 'View Emails', 'View Emails', $plugin_required_permission, 'edit.php?post_type=imc_email_campaign', NULL);
            
            $plugin_admin_pages[] = add_submenu_page(self::ADMIN_GENERAL_OPTIONS_SLUG, 'Add New Email', 'Add New Email', $plugin_required_permission, 'post-new.php?post_type=imc_email_campaign', NULL);
            
            $plugin_admin_pages[] = add_submenu_page(self::ADMIN_GENERAL_OPTIONS_SLUG, 'Subscribe Widget', 'Subscribe Widget', $plugin_required_permission, 'widgets.php?highlight_imc=true', NULL);
            
                    
            if (get_option(self::OPT_ENABLE_DEBUG_MODE)) {
                $plugin_admin_pages[] = add_submenu_page(self::ADMIN_GENERAL_OPTIONS_SLUG, 'Debug Log', 'Debug Log', $plugin_required_permission, self::ADMIN_DEBUG_LOG_SLUG, array('IMC\Controllers\Debug_Log_Controller', 'debug_log_form_view'));
                global $submenu;
 
            }

            //$plugin_admin_pages[] = add_submenu_page(self::ADMIN_GENERAL_OPTIONS_SLUG, 'Group Management', 'Group Management', $plugin_required_permission, self::ADMIN_LIST_GROUPINGS_SLUG, array('IMC\Controllers\List_Groups_Controller', 'group_management_form'));            


        }


        /**
         * SCRIPT/STYLE ENQUEUE SUPPORT FUNCTIONS
         * ************************************************************************ */


        /**
         * Register general administration scripts
         * 
         * Called via the admin_enqueue_scripts hook in register_enqueue_scripts_hooks()
         */
        public static function register_general_admin_scripts($hook) {
            global $post;

            //- REGISTER SCRIPTS *******************************************************************
            //- JQuery Scripts
            wp_register_script('jquery-form', plugins_url(self::JQUERY_JS_PATH . 'jquery.form.min.js', __FILE__), array('jquery'), NULL, FALSE);
            wp_register_script('jquery-validate', plugins_url(self::JQUERY_JS_PATH . 'jquery.validate.min.js', __FILE__), array('jquery'), NULL, FALSE);

            //- Bootstrap Scripts
            wp_register_script('imc-bootstrap-js', plugins_url('js/bootstrap.min.js', __FILE__), array('jquery'), NULL, FALSE);
            wp_register_script('imc-bootstrap-datepicker', plugins_url('js/bootstrap-datepicker.js', __FILE__), array('jquery'), NULL, FALSE);

            //- We're forcing the un-minified js files for the time being due to some errors
            if (defined('INTEGRALDEV')) {
                $suffix = '';
            } else {
                $suffix = '.min';
            }

            //- Plugin Specific Scripts ********************
            //- Script to be loaded on every admin page
            wp_register_script('integral-mailchimp-admin', plugins_url('js/integral-mailchimp-admin' . $suffix . '.js', __FILE__), array('jquery', 'jquery-ui-progressbar'), NULL, FALSE);

            //- Email Campaign creation/editing script
            wp_register_script('imc-new-email', plugins_url('js/integral-mailchimp-new-email' . $suffix . '.js', __FILE__), array('jquery', 'jquery-effects-slide', 'jquery-effects-core'), true);

            //- List Management - Merge Tags Sync
            wp_register_script('imc-mergetag-sync', plugins_url('js/integral-mailchimp-mergetag-sync' . $suffix . '.js', __FILE__), array('jquery', 'jquery-ui-sortable'), true);

            //- History Logs 
            //wp_register_script('imc-history-log', plugins_url('js/integral-mailchimp-history-log.js', __FILE__), array('jquery'), true);



            //- ENQUEUE SCRIPTS ********************************************************************
            //- Items to load on EVERY admin page
            wp_enqueue_script('integral-mailchimp-admin');

            //- Scripts to load on pages that we don't have a Controller for
            switch ($hook) {

                //- Widgets
                case 'widgets.php':
                    wp_enqueue_script('imc-bootstrap-js');
                    wp_enqueue_script('imc-bootstrap-datepicker');
                    break;

                //- Post Listing
                case 'edit.php':

                    $screen = get_current_screen();
                    
                    if ('imc_history_log' === $screen->post_type) {
                        wp_enqueue_script('imc-history-log');
                    }

                    break;

                //- Edit Post 
                case 'post.php':

                    if ('imc_email_campaign' === $post->post_type) {
                        wp_enqueue_script('imc-bootstrap-js');
                        wp_enqueue_script('imc-bootstrap-datepicker');
                        wp_enqueue_script('imc-new-email');
                    }

                    break;

                //- New Post
                case 'post-new.php':

                    if ('imc_email_campaign' === $post->post_type) {
                        wp_enqueue_script('imc-bootstrap-js');
                        wp_enqueue_script('imc-bootstrap-datepicker');
                        wp_enqueue_script('imc-new-email');
                    }

                    break;
            }


        }


        /**
         * Register general administration styles
         * 
         * Called via the admin_enqueue_scripts hook in register_enqueue_styles_hooks()
         */
        public static function register_general_admin_styles($hook) {
            global $post;
            //- REGISTER STYLES *******************************************************************
            //- Bootstrap Styles
            wp_register_style('imc-bootstrap', plugins_url('css/bootstrap.min.css', __FILE__), array('wp-admin'), NULL);
            wp_register_style('imc-bootstrap-datepicker', plugins_url('css/bootstrap-datepicker.css', __FILE__), array(), NULL);

            if (defined('INTEGRALDEV')) {
                $suffix = '';
            } else {
                $suffix = '.min';
            }

            //- Plugin Specific Styles ********************
            wp_register_style('integral-mailchimp-admin', plugins_url('css/integral-mailchimp-admin' . $suffix . '.css', __FILE__), array(), NULL);
            wp_register_style('integral-mailchimp-admin-menu', plugins_url('css/integral-mailchimp-admin-menu' . $suffix . '.css', __FILE__), array(), NULL);


            //- ENQUEUE STYLES ********************************************************************
            //- Items to load on EVERY admin page
            wp_enqueue_style('integral-mailchimp-admin-menu');

            //- Styles to load on pages that we don't have a Controller for
            switch ($hook) {

                //- Widgets
                case 'widgets.php':
                    wp_enqueue_style('imc-bootstrap');
                    wp_enqueue_style('imc-bootstrap-datepicker');
                    break;

                //- Edit Post 
                case 'post.php':

                    if ('imc_email_campaign' === $post->post_type) {
                        wp_enqueue_style('imc-bootstrap');
                        wp_enqueue_style('imc-bootstrap-datepicker');
                        wp_enqueue_style('integral-mailchimp-admin');
                    }

                    break;

                //- New Post
                case 'post-new.php':
                    if ('imc_email_campaign' === $post->post_type) {
                        wp_enqueue_style('imc-bootstrap');
                        wp_enqueue_style('imc-bootstrap-datepicker');
                        wp_enqueue_style('integral-mailchimp-admin');
                    }

                    break;
            }


        }


        /**
         * Register general public scripts
         * 
         * Called via the wp_enqueue_scripts hook in register_enqueue_scripts_hooks()
         */
        public static function register_general_public_scripts() {

            //- REGISTER SCRIPTS *******************************************************************
            //- JQuery Scripts
            wp_register_script('jquery-form', plugins_url(self::JQUERY_JS_PATH . 'jquery.form.min.js', __FILE__), array('jquery'), NULL, FALSE);
            wp_register_script('jquery-validate', plugins_url(self::JQUERY_JS_PATH . 'jquery.validate.min.js', __FILE__), array('jquery'), NULL, FALSE);

            //- Bootstrap Scripts
            wp_register_script('imc-bootstrap-js', plugins_url('js/bootstrap.min.js', __FILE__), array('jquery'), NULL, FALSE);
            wp_register_script('imc-bootstrap-datepicker', plugins_url('js/bootstrap-datepicker.js', __FILE__), array('jquery'), NULL, FALSE);
            wp_register_script('imc-bootstrap-modal', plugins_url('js/bootstrap-modal.min.js', __FILE__), array('jquery'), NULL, FALSE);


        }


        /**
         * Register general public styles
         * 
         * Called via the wp_enqueue_scripts hook in register_enqueue_styles_hooks()
         */
        public static function register_general_public_styles() {

            //- REGISTER STYLES *******************************************************************
            //- Bootstrap Styles
            wp_register_style('imc-bootstrap', IMC_PLUGIN_ROOT_URL . 'css/bootstrap.min.css', array('wp-admin'), NULL);
            wp_register_style('imc-bootstrap-datepicker', IMC_PLUGIN_ROOT_URL . 'css/bootstrap-datepicker.css', array(), NULL);
            wp_register_style('imc-bootstrap-modal', IMC_PLUGIN_ROOT_URL . 'css/bootstrap-modal.css', array(), NULL);
            //- Load any front end signup form styles
            Subscribe_Widget_Controller::load_front_end_styles();


        }


        /**
         * GENERAL UTILITY SUPPORT FUNCTIONS
         * ************************************************************************ */
        private static function _edd_plugin_updater() {

            //- Retrieve our license key from the DB
            $license_key = trim(get_option(self::OPT_LICENSEKEY));

            if ($license_key && strlen($license_key) == 32) {
                //- Setup the updater
                $edd_updater = new Integral_EDD_Updater(self::EDD_URL, IMC_PLUGIN_FILE, array(
                    'version' => self::VERSION,
                    'license' => $license_key,
                    'item_name' => self::PLUGIN_EDD_NAME,
                    'author' => 'IntegralWP'
                    )
                );
            }


        }


        /**
         * Safely include a file
         * 
         * @param string $file_path
         */
        public static function include_file($file_path) {
            try {
                if (file_exists($file_path)) {
                    require_once($file_path);
                } else {
                    $logger_message = 'Include File does NOT exist in ' . __FUNCTION__ . '()';
                    $logger_items   = array('function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }
            } catch (Exception $e) {
                $logger_message = 'Error including file in ' . __FUNCTION__ . '()';
                $logger_items   = array('exception' => $e, 'function_args' => func_get_args());
                Logger::log_error($logger_message, $logger_items);
            }


        }


        public static function plugins_loaded() {
            
        }


        public static function register_widgets() {
            static $called = FALSE;

            if (!$called) {
                self::include_file(IMC_PLUGIN_PATH . self::CONTROLLER_PATH . 'subscribe-widget.controller.php');
                $called = TRUE;
            }


        }


        /**
         * Initializes the session code and the session cookie
         * 
         */
        private static function _initialize_session_cookie() {
            if (isset($_COOKIE[self::PLUGIN_COOKIE_KEY])) {
                self::$session_code = (string) sanitize_text_field($_COOKIE[self::PLUGIN_COOKIE_KEY]);
            } else {
                self::$session_code = md5(time() . rand());

                //- FIXME - Change this to use wp_session vs cookies
                
                //- Set cookie for 12 hour timeout
                if (!headers_sent()) {
                    setcookie(self::PLUGIN_COOKIE_KEY, self::$session_code, time() + 12 * HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
                }
            }


        }


        /**
         * Initializes the MailChimp API if an API key exists
         * 
         */
        private static function _initialize_mailchimp_api() {
            $api_key = get_option(I_Conf::OPT_APIKEY);
            if ($api_key) {
                self::$mcAPI = new Integral_MailChimp_API($api_key);
            }


        }


        /**
         * Returns the current session code
         * 
         * @return string
         */
        public static function get_session_code() {
            return self::$session_code;


        }


        /**
         * Builds the transient key used for saving session based transients
         * 
         * @param string $key
         * @param boolean $per_session
         * @return string
         */
        public static function build_transient_key($key, $per_session) {
            $max_trans_key_length = 45;

            $trans_key = ($per_session) ? $key . '_' . self::$session_code : $key;

            $trans_key_length = strlen($trans_key);

            $shorten_by = $trans_key_length - $max_trans_key_length;

            if ($shorten_by > 0) {

                $key = substr($key, 0, $trans_key_length - $shorten_by);

                $trans_key = ($per_session) ? $key . '_' . self::$session_code : $key;
            }

            return $trans_key;


        }


    }


}



//- Master class include
I_Conf::include_file(IMC_PLUGIN_PATH . I_Conf::FRAMEWORK_PATH . 'integral-plugin-master.class.php');

