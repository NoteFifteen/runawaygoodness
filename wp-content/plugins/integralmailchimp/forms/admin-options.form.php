<?php

namespace IMC\Forms;

use IMC\Library\Framework\Integral_Plugin_Form;
use IMC\Library\Utility\Integral_Form_API;
use IMC\I_Conf;

/**
 * Handles the form building for viewing and processing of options for this plugin
 * 
 * 
 */
if (!class_exists('Admin_Options_Forms')) {

    class Admin_Options_Forms extends Integral_Plugin_Form {


        public static function load_general_options_form($options = array()) {
            self::$form_array = array(
                Integral_Form_API::FORM_ARRAY_INFO => array(
                    'form_name' => 'integral-mailchimp-general-options',
                    'form_title' => __('MailChimp Account Settings', 'integral-mailchimp'),
                    'form_method' => 'post',
                    'form_action' => I_Conf::ADMIN_GENERAL_OPTIONS_ACTION
                ),
                Integral_Form_API::FORM_ARRAY_FIELDS => array(
                    array(
                        'field_name' => I_Conf::OPT_LICENSEKEY,
                         'field_id' => I_Conf::OPT_LICENSEKEY,
                         'field_type' => 'text',
                         'field_size' => 39,
                         'field_maxlength' => 32,
                         'field_label' => __('Plugin License Key', 'integral-mailchimp'),
                         'field_class' => '',
                         'field_description' => __('The license key that was emailed to you', 'integral-mailchimp'),
                         'field_validation' => array(
                            'minLength' => array('length' => 32, 'message' => __('Invalid License Key. The key must be at least 32 characters', 'integral-mailchimp')),
                            'maxLength' => array('length' => 32, 'message' => __('Invalid License Key. The key cannot be greater than 36 characters', 'integral-mailchimp')),
                        )
                    ),
                    array(
                        'field_name' => I_Conf::OPT_APIKEY,
                         'field_id' => I_Conf::OPT_APIKEY,
                         'field_type' => 'text',
                         'field_size' => 39,
                         'field_maxlength' => 37,
                         'field_label' => __('MailChimp API Key', 'integral-mailchimp'),
                         'field_class' => '',
                         'field_description' => sprintf(__('You can find this key by %1$s logging into your MailChimp account %2$s and then going %3$s here %4$s. %5$s (What is a MailChimp API Key?) %6$s', 'integral-mailchimp'), '<a target="_blank" href="https://login.mailchimp.com">', '</a>', '<a target="_blank" href="http://admin.mailchimp.com/account/api">', '</a>', '<a target="_blank" href="http://kb.mailchimp.com/accounts/management/about-api-keys">', '</a>'),
                         'field_validation' => array(
                            'minLength' => array('length' => 32, 'message' => __('Invalid MailChimp API Key. The key must be at least 32 characters', 'integral-mailchimp')),
                            'maxLength' => array('length' => 37, 'message' => __('Invalid MailChimp API Key. The key cannot be greater than 36 characters', 'integral-mailchimp')),
                        )
                    ),
                    /**
                     * This is for the future oAuth functionality
                     * array(
                      'field_name' => 'oauth-authorize',
                      'field_container' => 'div',
                      'field_id' => 'oauth-authorize',
                      'field_content' => "<a href='". add_query_arg(array('imc_init_action' => 'authorize'), home_url('index.php')) ."' class='mailchimp-login'>Authorize</a>",
                      'field_type' => 'html'
                      ), */
                    array(
                        'field_name' => I_Conf::OPT_DEFAULT_USER_LIST,
                         'field_id' => I_Conf::OPT_DEFAULT_USER_LIST,
                         'field_type' => 'select',
                         'field_options' => $options[I_Conf::OPT_DEFAULT_USER_LIST],
                         'field_label' => __('Select the Default Mailing List', 'integral-mailchimp'),
                         'field_editor_class' => 'form-control',
                         'field_description' => __('New members will be automatically added to this list', 'integral-mailchimp')
                    ),
                    array(
                        'field_container' => 'p',
                        'field_id' => 'formsubmit_general',
                        'field_name' => 'formsubmit_general',
                        'field_class' => 'submit',
                        'field_type' => 'html',
                        'field_content' => '<input type="submit" id="formsubmit" name="formsubmit" class="btn button-primary clearfix admin" value="'. __('Submit', 'integral-mailchimp') .'">',
                    ),
                    array(
                        'field_container' => 'div',
                        'field_id' => 'imc_reset_data',
                        'field_name' => 'imc_reset_data',
                        'field_class' => '',
                        'field_type' => 'html',
                        'field_description' => __('Clears all cached data in the system. This happens every hour anyway, but is useful when MailChimp List changes have occurred recently.', 'integral-mailchimp'),
                        'field_content' => '<input type="button" id="imc_reset_data" name="imc_reset_data" data-key-item="imc-reset-data" class="imc-options-key btn button-primary clearfix admin" value="'. __('Reset API Data', 'integral-mailchimp') .'">',
                    ),
                    array(
                        'field_container' => 'div',
                        'field_id' => 'imc_register_webhooks',
                        'field_name' => 'imc_register_webhooks',
                        'field_class' => '',
                        'field_type' => 'html',
                        'field_description' => sprintf(__('Registers the API Webhooks with MailChimp. %1$s (What is this?) %2$s', 'integral-mailchimp'), '<a href="http://apidocs.mailchimp.com/webhooks/" target="_blank">', '</a>'),
                        'field_content' => '<input type="button" id="imc_register_webhooks" name="imc_register_webhooks" data-key-item="imc-register-webhooks" class="btn button-primary clearfix admin" value="'. __('Register Webhooks', 'integral-mailchimp') .'">',
                    ),
                    array(
                        'field_name' => I_Conf::OPT_ENABLE_DEBUG_MODE,
                         'field_id' => I_Conf::OPT_ENABLE_DEBUG_MODE,
                         'field_type' => 'checkbox',
                         'default_value' => 0,
                         'field_label' => __('Turn on debugging mode', 'integral-mailchimp'),
                         'field_description' => sprintf(__('Enabling debug mode will write errors and some useful status info regarding the MailChimp API requests to two places: %1$s 1) A log file in %2$s (or on a multi-site install, %3$s ); and %4$s 2) The Chrome browser console. (Note: You will need the Chrome Logger extension to view the log)', 'integral-mailchimp'), '<br>&nbsp;&nbsp;', 'wp-content/uploads/integral_log_files', 'wp-content/uploads/sites/blog_number/integral_log_files', '<br>&nbsp;&nbsp;'),
                    ),
                    /*array(
                        'field_name' => I_Conf::OPT_SSL_VERIFY_PEER,
                         'field_id' => I_Conf::OPT_SSL_VERIFY_PEER,
                         'field_type' => 'checkbox',
                         'default_value' => 0,
                         'field_label' => __('Turn off SSL Peer Verification', 'integral-mailchimp'),
                         'field_description' => sprintf(__('Some servers are not configured correctly for SSL peer verification, especially Windows servers. If you are getting an error that your MailChimp API key is invalid and you know you are entering a valid key, try disabling verification. Note: Check this %s article %s for information on how to properly configure your server.', 'integral-mailchimp'), '<a href="http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/">', '</a>')
                    ),*/
                ),
                Integral_Form_API::FORM_ARRAY_FIELDSETS => array(
                    array(
                        'fieldset_name' => 'general-options',
                         'fieldset_label' => __('General Options', 'integral-mailchimp'),
                         'fieldset_fields' => array(I_Conf::OPT_LICENSEKEY, I_Conf::OPT_APIKEY, /* 'oauth-authorize', */ I_Conf::OPT_DEFAULT_USER_LIST, 'formsubmit_general')
                    ),
                    array(
                        'fieldset_name' => 'setup-options',
                         'fieldset_label' => __('Setup', 'integral-mailchimp'),
                         'fieldset_fields' => array('imc_reset_data', 'imc_register_webhooks')
                    ),
                    array(
                        'fieldset_name' => 'debug-options',
                         'fieldset_label' => __('Debug Options', 'integral-mailchimp'),
                         'fieldset_fields' => array(I_Conf::OPT_ENABLE_DEBUG_MODE, I_Conf::OPT_SSL_VERIFY_PEER)
                    )
                )
            );


        }


        public static function run_final_form_setup() {
            if (get_option(I_Conf::OPT_LICENSEKEY)) {
                self::$form_array['fields'][I_Conf::OPT_LICENSEKEY]['field_disabled'] = TRUE;
                self::$form_array['fields'][I_Conf::OPT_LICENSEKEY]['field_suffix']   = '&nbsp;&nbsp;<a data-key-item="imc-deactivate-license" class="imc-options-key button button-small">'. __('Deactivate License', 'integral-mailchimp') .'</a>';

                if (get_option(I_Conf::OPT_APIKEY)) {
                    self::$form_array['fields'][I_Conf::OPT_APIKEY]['field_disabled'] = TRUE;
                    self::$form_array['fields'][I_Conf::OPT_APIKEY]['field_suffix']   = '&nbsp;&nbsp;<a data-key-item="imc-deactivate-apikey" class="imc-options-key button button-small">'. __('Deactivate API Key', 'integral-mailchimp') .'</a>';
                } else {
                    unset(self::$form_array['fields'][I_Conf::OPT_DEFAULT_USER_LIST]);
                    unset(self::$form_array['fields']['imc_reset_data']);
                    unset(self::$form_array['fields']['imc_register_webhooks']);
                }
            } else {
                unset(self::$form_array['fields'][I_Conf::OPT_APIKEY]);
                unset(self::$form_array['fields'][I_Conf::OPT_DEFAULT_USER_LIST]);
                unset(self::$form_array['fields']['imc_reset_data']);
                unset(self::$form_array['fields']['imc_register_webhooks']);
            }


        }


    }


}

