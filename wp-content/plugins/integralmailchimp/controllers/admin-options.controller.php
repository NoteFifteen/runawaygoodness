<?php

namespace IMC\Controllers;

use IMC\Library\Utility\I_Cache;
use IMC\Library\Utility\I_Tools;
use IMC\Views\Admin_View;
use IMC\Forms\Admin_Options_Forms;
use IMC\Models\Admin_Options_Model;
use IMC\Library\Utility\Integral_Form_API;
use IMC\Library\Utility\Integral_MailChimp_Helper;
use IMC\Library\Utility\Integral_MailChimp_Lists;
use IMC\Library\Utility\Integral_EDD;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/**
 * Handles the viewing and processing of options for this plugin
 * 
 */
if (!class_exists('Admin_Options_Controller')) {

    class Admin_Options_Controller {


        private static $form_options = NULL;
        private static $form_output  = NULL;


        private static function _initialize_controller($with_view = TRUE) {

            I_Tools::initialize_controller(__FILE__, TRUE, TRUE, TRUE, TRUE, TRUE);

            I_Tools::load_utilities(array('integral-form-api', 'integral-edd'));

            if ($with_view) {
                I_Tools::load_view_master('admin');
                Admin_View::initialize_view();
                Admin_View::set_layout('admin');
                Admin_View::set_view(I_Tools::get_file_slug());
                Admin_View::set_title('Integral MailChimp');
            }


        }


        /**
         * GENERAL OPTIONS FORM
         * ************************************************************************ */
        public static function general_options_form_view() {

            self::_initialize_controller();

            wp_enqueue_script('jquery-form');
            wp_enqueue_script('jquery-validate');
            wp_enqueue_script('imc-bootstrap-js');

            wp_enqueue_style('imc-bootstrap');
            wp_enqueue_style('integral-mailchimp-admin');


            Admin_Options_Forms::initialize_forms();

            Admin_Options_Forms::set_form_view(I_Tools::get_file_slug());

            self::_build_general_options_form();

            Admin_View::$view->options_form_output = self::$form_output;

            Admin_View::render_view();


        }


        /**
         * Builds and prepares the form for output
         * 
         */
        private static function _build_general_options_form() {

            //- Builds the form array
            self::_build_general_options_form_array();

            //- Renders the form elements
            self::_render_general_options_form_fields();

            //- Renders the form html output
            self::_render_general_options_form();


        }


        /**
         * Builds the form array
         * 
         */
        private static function _build_general_options_form_array() {

            //- Builds the form array
            self::_build_general_options_form_options();

            Admin_Options_Forms::set_form_action(I_Conf::ADMIN_GENERAL_OPTIONS_ACTION);

            Admin_Options_Forms::load_general_options_form(self::$form_options);

            Admin_Options_Forms::run_default_form_setup();

            Admin_Options_Forms::run_final_form_setup();


        }


        /**
         * Renders the form elements
         * 
         */
        private static function _render_general_options_form_fields() {

            //- Renders the form elements
            $options_form_data = Admin_Options_Model::load_general_options_database_values();

            Admin_Options_Forms::populate_form_values($options_form_data);

            Admin_Options_Forms::build_form_fields();


        }


        /**
         * Renders the form
         * 
         */
        private static function _render_general_options_form() {

            Admin_Options_Forms::build_form();

            self::$form_output = Admin_Options_Forms::render_form();


        }


        /**
         * Builds the form options array
         * 
         */
        private static function _build_general_options_form_options() {
            $options = array();

            I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists'));

            //- Build options list for the Mailing Lists
            $options[I_Conf::OPT_DEFAULT_USER_LIST] = Integral_MailChimp_Lists::load_mailchimp_lists();

            self::$form_options = $options;


        }


        /**
         * Processes and saves the form submission
         * 
         */
        public static function general_options_form_process() {

            self::_initialize_controller();

            Admin_Options_Forms::initialize_forms();

            $form_data = I_Tools::fetch_ajax_post();

            Admin_Options_Forms::set_ajax_data($form_data);

            //- Build the form to make it's components available
            self::_build_general_options_form();

            //- Confirm this form has been submitted
            if (!Admin_Options_Forms::confirm_form_submission('ajax')) {
                wp_die(__('Invalid Submission', 'integral-mailchimp'));
            }

            //- Check the nonce field
            Admin_Options_Forms::check_ajax_nonce();

            $form_errors = array();

            $existing_license_key = get_option(I_Conf::OPT_LICENSEKEY, -1);

            //- Save the License Key
            if ($existing_license_key === -1 && isset($form_data[I_Conf::OPT_LICENSEKEY]) && strlen($form_data[I_Conf::OPT_LICENSEKEY]) == 32) {
                $license_key = sanitize_text_field($form_data[I_Conf::OPT_LICENSEKEY]);

                if ($license_key) {
                    $activation_success = Integral_EDD::activate_license($license_key);

                    if ($activation_success && is_object($activation_success) && isset($activation_success->license)) {
                        //- $license_data->license will be either "valid" or "invalid"
                        if ($activation_success->license != 'valid') {
                            $form_errors[I_Conf::OPT_LICENSEKEY] = __('Invalid License Key provided', 'integral-mailchimp');
                        } else {
                            update_option(I_Conf::OPT_LICENSEKEY_STATUS, $activation_success->license);
                            update_option(I_Conf::OPT_LICENSEKEY, $license_key);
                        }
                    } else {
                        $form_errors[I_Conf::OPT_LICENSEKEY] = __('Error trying to activate the License Key provided', 'integral-mailchimp');
                    }
                } else {
                    $form_errors[I_Conf::OPT_LICENSEKEY] = __('Error trying to activate the License Key provided', 'integral-mailchimp');
                }
            }

            //- Save the API Key
            if (empty($form_errors) && isset($form_data[I_Conf::OPT_APIKEY])) {

                $api_key = sanitize_text_field($form_data[I_Conf::OPT_APIKEY]);

                I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-helper'));

                $old_api_key = Admin_Options_Model::load_general_options_database_values(I_Conf::OPT_APIKEY);

                if ($api_key && $old_api_key !== $api_key) {
                    //- Confirm the API key is valid
                    $success = Integral_MailChimp_Helper::confirm_api_key($api_key);
                    if ($success === TRUE) {
                        $saved = Admin_Options_Model::save_general_options_database_values(array(I_Conf::OPT_APIKEY => $api_key));
                        self::_purge_imc_api_data();

                        update_option(I_Conf::OPT_FRESH_INSTALL, FALSE);

                        if (!$saved) {
                            $form_errors[][I_Conf::OPT_APIKEY] = __('Error trying to save the MailChimp API Key, please try again.', 'integral-mailchimp');
                        }
                    } else {
                        $form_errors[I_Conf::OPT_APIKEY] = __('That MailChimp API Key appears to be invalid.', 'integral-mailchimp');
                        //$form_errors[I_Conf::OPT_SSL_VERIFY_PEER] = __('Disabling SSL Verification below may resolve it. See the setting for details.', 'integral-mailchimp');
                    }
                }
            }

            //- Save the remaining form values. Unchecked checkboxes don't get passed so we need to look for them and 
            //- and handle unchecked ones manually
            $form_save = array();

            if (isset($form_data[I_Conf::OPT_DEFAULT_USER_LIST]) && $form_data[I_Conf::OPT_DEFAULT_USER_LIST]) {
                $form_save[I_Conf::OPT_DEFAULT_USER_LIST] = $form_data[I_Conf::OPT_DEFAULT_USER_LIST][0];
            }
            if (isset($form_data[I_Conf::OPT_ENABLE_DEBUG_MODE]) && $form_data[I_Conf::OPT_ENABLE_DEBUG_MODE]) {
                $form_save[I_Conf::OPT_ENABLE_DEBUG_MODE] = $form_data[I_Conf::OPT_ENABLE_DEBUG_MODE];
            } else {
                $form_save[I_Conf::OPT_ENABLE_DEBUG_MODE] = 0;
            }
            if (isset($form_data[I_Conf::OPT_SSL_VERIFY_PEER]) && $form_data[I_Conf::OPT_SSL_VERIFY_PEER]) {
                $form_save[I_Conf::OPT_SSL_VERIFY_PEER] = $form_data[I_Conf::OPT_SSL_VERIFY_PEER];
            } else {
                $form_save[I_Conf::OPT_SSL_VERIFY_PEER] = 0;
            }

            if ($form_save) {
                Admin_Options_Model::save_general_options_database_values($form_save);
            }

            if (empty($form_errors)) {
                $status_message = __('The settings have been updated', 'integral-mailchimp');
                wp_send_json(array('msg' => $status_message));
            } else {

                wp_send_json($form_errors);
            }


        }


        /**
         * Deactivates the currently active License Key
         * 
         */
        public static function general_options_process_key() {
            self::_initialize_controller(I_Conf::CONTROLLER_WITHOUT_VIEW);

            $form_data = I_Tools::fetch_get_all();

            $status_message = '';
            $form_errors    = array();
            if (is_array($form_data) && isset($form_data['item_key']) && $form_data['item_key']) {

                switch ($form_data['item_key']) {

                    case I_Conf::OPT_LICENSEKEY:
                        $license_key = get_option(I_Conf::OPT_LICENSEKEY);

                        if ($license_key && isset($form_data['item_value']) && $license_key == $form_data['item_value']) {

                            $deactivate_success = Integral_EDD::deactivate_license($license_key);

                            if ($deactivate_success) {
                                delete_option(I_Conf::OPT_LICENSEKEY);
                                self::_purge_imc_api_data();
                                $status_message = __('The License Key was Deactivated for this plugin on this site', 'integral-mailchimp');
                            } else {
                                $form_errors[I_Conf::OPT_LICENSEKEY] = __('There was an issue deactivating the License Key', 'integral-mailchimp');
                            }
                        } else {
                            $form_errors[I_Conf::OPT_LICENSEKEY] = __('There was an issue deactivating the License Key', 'integral-mailchimp');

                            $logger_message = 'Deactivating License failed in ' . __FUNCTION__ . '()';
                            $logger_items   = array('license_key' => $license_key, 'form_data' => $form_data);
                            Logger::log_error($logger_message, $logger_items);
                        }
                        break;


                    case I_Conf::OPT_APIKEY:
                        $apikey_key = get_option(I_Conf::OPT_APIKEY);

                        if ($apikey_key && isset($form_data['item_value']) && $apikey_key == $form_data['item_value']) {
                            delete_option(I_Conf::OPT_APIKEY);
                            self::_purge_imc_api_data();
                            $status_message = __('The API Key was Deactivated', 'integral-mailchimp');
                        } else {
                            $form_errors[I_Conf::OPT_APIKEY] = __('There was an issue deactivating the API Key', 'integral-mailchimp');

                            $logger_message = 'Deactivating API Key failed in ' . __FUNCTION__ . '()';
                            $logger_items   = array('apikey_key' => $apikey_key, 'form_data' => $form_data);
                            Logger::log_error($logger_message, $logger_items);
                        }
                        break;


                    case 'imc_reset_data':
                        self::_reset_imc_api_data();
                        $status_message = __('The API Data was Reset', 'integral-mailchimp');

                        break;

                    case 'imc_register_webhooks':


                        break;

                    default:
                        $form_errors['integral-form'] = __('There was an issue deactivating the Key', 'integral-mailchimp');

                        $logger_message = 'Deactivating a KEY failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('form_data' => $form_data);
                        Logger::log_error($logger_message, $logger_items);
                        break;
                }
            }

            if (empty($form_errors)) {
                wp_send_json(array('msg' => $status_message));
            } else {
                wp_send_json($form_errors);
            }


        }


        public static function init_hook() {
            if (isset($_GET['imc_init_action'])) {
                switch ($_GET['imc_init_action']) {
                    case 'authorize':
                        self::_oauth_authorize();
                        break;
                    case 'authorized':
                        self::_oauth_authorized();
                        break;
                }
            }

            //- Clear out any cached variables if the API_KEY is missing and this is NOT a fresh install
            $api_key       = get_option(I_Conf::OPT_APIKEY);
            $fresh_install = get_option(I_Conf::OPT_FRESH_INSTALL);

            if (!$api_key && !$fresh_install) {
                self::_purge_imc_api_data();
            }


        }


        private static function _reset_imc_api_data($hard_reset = TRUE) {
            global $wpdb;

            delete_option(I_Conf::OPT_MC_LIST_GROUPINGS);
            delete_option(I_Conf::OPT_MC_LOCAL_MERGETAGS);
            delete_option(I_Conf::OPT_MC_ADVANCED_MERGETAGS);

            I_Cache::delete_transient(I_Conf::TRANS_MC_LISTS, FALSE);

            $lists = I_Cache::load_transient(I_Conf::TRANS_MC_LISTS, FALSE);

            $logger_message = 'Deleted API related options and transients in ' . __FUNCTION__ . '()';
            $logger_items   = array('cleaned_lists_should_be_empty', $lists);
            Logger::log_info($logger_message, $logger_items);

            //- Delete transients that match the "per" items (ie per list, per campaign, etc)
            $per_item_transients = array(
                I_Conf::TRANS_MC_LIST_SEGMENTS,
                I_Conf::TRANS_MC_LIST_MERGE_TAGS,
                I_Conf::TRANS_MC_LIST_FOR_EMAIL,
                I_Conf::TRANS_MC_LIST_GROUPINGS,
                I_Conf::TRANS_MC_SUMMARY_REPORT
            );

            $hard_reset_transients = array(
                I_Conf::TRANS_MC_CAMPAIGN_CONTENT,
                I_Conf::TRANS_MC_CAMPAIGN_INFO,
                I_Conf::TRANS_MC_TEMPLATE_INFO,
            );

            if ($hard_reset) {
                $per_item_transients = array_merge($per_item_transients, $hard_reset_transients);
            }

            $where = array();

            foreach ($per_item_transients as $trans) {
                $where[] = "option_name LIKE '%{$trans}%'";
            }

            $where = join(" OR ", $where);

            $query = "DELETE FROM $wpdb->options WHERE {$where}";

            $result = $wpdb->query($query);

            $logger_message = 'Deleting unique database transients in ' . __FUNCTION__ . '()';
            $logger_items   = array('db_result' => $result, 'db_query' => $query);
            Logger::log_info($logger_message, $logger_items);

            wp_cache_flush();


        }


        private static function _purge_imc_api_data() {
            global $wpdb;

            delete_option(I_Conf::OPT_DEFAULT_USER_LIST);
            delete_option(I_Conf::OPT_USERS_SYNCED);
            delete_option(I_Conf::OPT_WEBHOOKS_REGISTERED);
            delete_option(I_Conf::OPT_MC_LIST_GROUPINGS);
            delete_option(I_Conf::OPT_MC_LOCAL_MERGETAGS);
            delete_option(I_Conf::OPT_MC_ADVANCED_MERGETAGS);

            I_Cache::delete_transient(I_Conf::TRANS_MC_LISTS, FALSE);

            $lists = I_Cache::load_transient(I_Conf::TRANS_MC_LISTS, FALSE);

            $logger_message = 'Deleted API related options and transients in ' . __FUNCTION__ . '()';
            $logger_items   = array('cleaned_lists_should_be_empty', $lists);
            Logger::log_info($logger_message, $logger_items);

            //- Delete transients that match the "per" items (ie per list, per campaign, etc)
            $per_item_transients = array(
                I_Conf::TRANS_MC_LIST_SEGMENTS,
                I_Conf::TRANS_MC_LIST_MERGE_TAGS,
                I_Conf::TRANS_MC_LIST_FOR_EMAIL,
                I_Conf::TRANS_MC_LIST_GROUPINGS,
                I_Conf::TRANS_MC_SUMMARY_REPORT,
                I_Conf::TRANS_MC_CAMPAIGN_CONTENT,
                I_Conf::TRANS_MC_CAMPAIGN_INFO,
                I_Conf::TRANS_MC_TEMPLATE_INFO,
            );

            $where = array();

            foreach ($per_item_transients as $trans) {
                $where[] = "option_name LIKE '%{$trans}%'";
            }

            $where = join(" OR ", $where);

            $query = "DELETE FROM $wpdb->options WHERE {$where}";

            $result = $wpdb->query($query);

            $logger_message = 'Deleting user-unique database transients in ' . __FUNCTION__ . '()';
            $logger_items   = array('db_result' => $result, 'db_query' => $query);
            Logger::log_info($logger_message, $logger_items);

            wp_cache_flush();

            update_option(I_Conf::OPT_FRESH_INSTALL, TRUE);


        }


        private static function _oauth_authorize() {
            $proxy = ''; //- This will be the url that Jamie gives me
            $salt  = I_Tools::auth_nonce_salt();
            $id    = I_Tools::create_nonce(I_Tools::auth_nonce_key($salt));

            $url  = home_url('index.php');
            $args = array(
                'imc_init_action' => 'authorized',
                'salt' => $salt,
                'user_id' => get_current_user_id(),
            );

            $proxy = add_query_arg(array(
                'id' => $id,
                'response_url' => urlencode(add_query_arg($args, $url))
                ), $proxy);

            wp_redirect($proxy);
            exit;


        }


        private static function _oauth_authorized() {
            // User ID on the request? Must be set before nonce comparison
            $user_id = stripslashes($_GET['user_id']);
            if ($user_id !== null) {
                wp_set_current_user($user_id);
            }

            $nonce = stripslashes($_POST['id']);
            $salt  = stripslashes($_GET['salt']);

            if (I_Tools::verify_nonce($nonce, I_Tools::auth_nonce_key($salt)) === false) {
                wp_die('Cheatin&rsquo; huh?');
            }

            $response = stripslashes_deep($_POST['response']);

            if (!isset($response['keys']) || !isset($response['user'])) {
                wp_die(__('Something went wrong, please try again', 'integral-mailchimp'));
            }

            update_option('imc_auth_user', $response['user']);
            update_option('imc_auth_dc', $response['dc']);
            update_option('imc_auth_public_key', $response['keys']['public']);
            update_option('imc_auth_secret_key', $response['keys']['secret']);
            exit;


        }


        public static function skip_user_sync() {
            self::_initialize_controller(FALSE);

            update_option(I_Conf::OPT_USERS_SYNCED, TRUE);
            $message = __('The user syncing process has been skipped', 'integral-mailchimp');

            wp_send_json(array('msg' => $message));


        }


        public static function api_notices() {

            //- If the License Key has not been set
            $license_key         = get_option(I_Conf::OPT_LICENSEKEY);
            //- If the API Key has not been set
            $api_key             = get_option(I_Conf::OPT_APIKEY);
            //- If no default list chosen yet
            $default_list        = get_option(I_Conf::OPT_DEFAULT_USER_LIST);
            //- If no user syncing has occured yet
            $users_synced        = get_option(I_Conf::OPT_USERS_SYNCED);
            //- If the webhooks have NOT already been registered
            $webhooks_registered = get_option(I_Conf::OPT_WEBHOOKS_REGISTERED);


            if (!$license_key) {

                $imc_license_url = admin_url('admin.php?page=integral_mailchimp/general');

                $script = <<<SCRIPT
                    
                    $('#imc_opt_licensekey').addClass('highlight-glow').focus(function () {
                        $('#imc_opt_licensekey').removeClass('highlight-glow');
                    });
                    
SCRIPT;
                $script = I_Tools::format_inline_javascript($script);

                ?>
                <br />
                <?php echo $script; ?>
                <style type="text/css">
                    #imc_opt_licensekey.highlight-glow {
                        -webkit-animation: glow 800ms ease-out infinite alternate;
                        -moz-animation: glow 800ms ease-out infinite alternate;
                        -o-animation: glow 800ms ease-out infinite alternate;
                        -ms-animation: glow 800ms ease-out infinite alternate;
                        animation: glow 800ms ease-out infinite alternate;
                        border-color: #393;
                    }
                </style>
                <div class="update-nag well imc-setup-wizard">
                <?= '<h3>Integral MailChimp</h3><div class="wizard-message"><h4>' . strtoupper(__('SETUP WIZARD', 'integral-mailchimp')) . '</h4><h5>' . strtoupper(sprintf(__('Step 1 - %1$s Plugin License Key', 'integral-mailchimp'), 'Integral')) . '</h5><p>' . sprintf(__('Please click here to %1$s set your %2$s Plugin License key %3$s', 'integral-mailchimp'), ' <a href="' . $imc_license_url . '" class="button button-small">', 'Integral', '</a>') . '</p></div>' ?>
                </div>
                <?php
            } else if (!$api_key) {

                $mailchimp_license_url = admin_url('admin.php?page=integral_mailchimp/general');

                $script = <<<SCRIPT
                    
                    $('#imc_opt_apikey').addClass('highlight-glow').focus(function () {
                        $('#imc_opt_apikey').removeClass('highlight-glow');
                    });
                    
SCRIPT;
                $script = I_Tools::format_inline_javascript($script);

                ?>
                <br />
                <?php echo $script; ?>
                <style>
                    #imc_opt_apikey.highlight-glow {
                        -webkit-animation: glow 800ms ease-out infinite alternate;
                        -moz-animation: glow 800ms ease-out infinite alternate;
                        -o-animation: glow 800ms ease-out infinite alternate;
                        -ms-animation: glow 800ms ease-out infinite alternate;
                        animation: glow 800ms ease-out infinite alternate;
                        border-color: #393;
                    }
                </style>
                <div class="update-nag well imc-setup-wizard">
                <?= '<h3>Integral MailChimp</h3><div class="wizard-message"><h4>' . strtoupper(__('SETUP WIZARD', 'integral-mailchimp')) . '</h4><h5>' . strtoupper(__('Step 2 - MailChimp API Key', 'integral-mailchimp')) . '</h5><p>' . sprintf(__('Please click here to %1$s set your MailChimp API key %2$s (%3$s What is this? %4$s)', 'integral-mailchimp'), ' <a href="' . $mailchimp_license_url . '" class="button button-small">', '</a>', '<a href="http://www.mailchimp.com/kb/article/where-can-i-find-my-api-key" target="_blank">', '</a>') . '</p></div>' ?>
                </div>
                <?php
            } else if (!$default_list) {

                $imc_default_list_url = admin_url('admin.php?page=integral_mailchimp/general');

                $script = <<<SCRIPT
                    
                    $('#imc_opt_default_user_list').addClass('highlight-glow').focus(function () {
                        $('#imc_opt_default_user_list').removeClass('highlight-glow');
                    });
                    
SCRIPT;
                $script = I_Tools::format_inline_javascript($script);

                ?>
                <br />
                <?php echo $script; ?>
                <style>
                    #imc_opt_default_user_list.highlight-glow {
                        -webkit-animation: glow 800ms ease-out infinite alternate;
                        -moz-animation: glow 800ms ease-out infinite alternate;
                        -o-animation: glow 800ms ease-out infinite alternate;
                        -ms-animation: glow 800ms ease-out infinite alternate;
                        animation: glow 800ms ease-out infinite alternate;
                        border-color: #393;
                    }
                </style>
                <div class="update-nag well imc-setup-wizard">
                <?= '<h3>Integral MailChimp</h3><div class="wizard-message"><h4>' . strtoupper(__('SETUP WIZARD', 'integral-mailchimp')) . '</h4><h5>' . strtoupper(__('Step 3 - Default Email List ', 'integral-mailchimp')) . '</h5><p>' . sprintf(__('Please click here to %1$s select a default MailChimp Email List %2$s', 'integral-mailchimp'), '<a href="' . $imc_default_list_url . '" class="button button-small">', '</a>') . '</p></div>' ?>
                </div>
                <?php
            } else if (!$users_synced) {

                $imc_lists_url = admin_url('admin.php?page=integral_mailchimp/lists');

                $default_error = __('An Error Occurred', 'integral-mailchimp');

                $script = <<<SCRIPT
                
                    $('#imc_user_sync').on('click', '.register-mailchimp-user-sync', function(event) {
                        event.preventDefault();
                        var skip_set = $(this).data('skipSync');
                        console.log('skip_set: '+ skip_set);
                        var skipped = (skip_set === true) ? 1 : 0;
                        $.post(
                                ajaxurl,
                                {'skip': skipped,
                                    'action': 'skip_user_sync'},
                        //- On Success
                        function (resp) {
                            var message_class = 'updated';
                            if (resp instanceof Array || resp instanceof Object) {
                                for (var key in resp) {
                                    if (key == 'msg') {
                                        window.location = '{$imc_lists_url}';
                                        return false;
                                    } else {
                                        var this_message = resp[key] ? resp[key] : resp ? resp : '{$default_error}';
                                        message_class = 'error';
                                    }

                                    $('#imc_user_sync .wizard-message').prepend('<p>' + this_message + '</p>');
                                }
                            }

                            $('#imc_user_sync .wizard-message').css('opacity', 0)
                                    .removeClass('error')
                                    .removeClass('updated')
                                    .addClass(message_class)
                                    .css('opacity', 1)
                                    .show('slow');
                            ;
                        });

                        return false;
                    });
                    
SCRIPT;

                $script = I_Tools::format_inline_javascript($script);

                print $script;

                ?>
                <br />
                <div id="imc_user_sync" class="update-nag well imc-setup-wizard">
                <?= '<h3>Integral MailChimp</h3><div class="wizard-message"><h4>' . strtoupper(__('SETUP WIZARD', 'integral-mailchimp')) . '</h4><h5>' . strtoupper(__('Step 4 - Merge Tags and User Syncing', 'integral-mailchimp')) . '</h5><p>' . sprintf(__('Please click here to %1$s sync your Merge Tags and Users %2$s to your MailChimp Email List or %3$s skip this process %4$s', 'integral-mailchimp'), '<a href="' . $imc_lists_url . '" class="button button-small">', '</a> ', '<a href="' . $imc_lists_url . '" data-skip-sync="true" class="register-mailchimp-user-sync button button-small">', '</a>') . '</p></div>' ?>
                </div>
                <?php
            } else if (!$webhooks_registered) {

                $default_error = __('An Error Occurred', 'integral-mailchimp');

                $script = <<<SCRIPT
                
                    $('#imc_webhooks_register').on('click', '.register-mailchimp-webhooks', function(event) {
                        event.preventDefault();
                        var skip_set = $(this).data('skipWebhooks');
                        var skipped = (skip_set === true) ? 1 : 0;
                        $.post(
                                ajaxurl,
                                {'skip': skipped,
                                    'action': 'webhook_registration'},
                        //- On Success
                        function (resp) {
                            var message_class = 'updated';
                            if (resp instanceof Array || resp instanceof Object) {
                                for (var key in resp) {
                                    if (key == 'msg') {
                                        var this_message = resp[key];
                                        setTimeout(function() { $('#imc_webhooks_register').slideUp('slow');}, 3000);
                                    } else {
                                        var this_message = resp[key] ? resp[key] : resp ? resp : '{$default_error}';
                                        message_class = 'error';
                                    }

                                    $('#imc_webhooks_register .wizard-message').html('<p>' + this_message + '</p>');
                                }
                            }

                            $('#imc_webhooks_register .wizard-message').css('opacity', 0)
                                    .removeClass('error')
                                    .removeClass('updated')
                                    .addClass(message_class)
                                    .css('opacity', 1)
                                    .show('slow');
                            ;
                        });

                        return false;
                    });
                    
SCRIPT;

                $script = I_Tools::format_inline_javascript($script);


                print $script;

                ?>
                <br />
                <div id="imc_webhooks_register" class="update-nag well imc-setup-wizard">
                <?= '<h3>Integral MailChimp</h3><div class="wizard-message"><h4>' . strtoupper(__('SETUP WIZARD', 'integral-mailchimp')) . '</h4><h5>' . strtoupper(__('Step 5 - MailChimp Event Webhooks', 'integral-mailchimp')) . '</h5><p>' . sprintf(__('Please click here to %1$s register the MailChimp webhooks %2$s for your site or %3$s skip this process (%4$s What is this? %5$s)', 'integral-mailchimp'), '<a href="javascript:void(0);" data-skip-webhooks="false" class="register-mailchimp-webhooks button button-small">', '</a>', '<a href="javascript:void(0);" class="register-mailchimp-webhooks button button-small" data-skip-webhooks="true">', '</a> <a href="http://apidocs.mailchimp.com/webhooks/" target="_blank">', '</a>') . '</p></div>' ?>
                </div>
                <?php
            }


        }


    }


}

