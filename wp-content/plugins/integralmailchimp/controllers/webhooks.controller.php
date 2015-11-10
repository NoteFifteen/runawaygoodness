<?php

namespace IMC\Controllers;

use IMC\Library\Utility\I_Tools;
use IMC\Views\Admin_View;
use IMC\Library\Utility\Integral_MailChimp_Lists;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/**
 * Handles the processing of webhook callbacks from Mailchimp
 * 
 */
if (!class_exists('Webhooks_Controller')) {

    class Webhooks_Controller {


        private static $webhook_keys = array();


        private static function _initialize_controller($with_view = TRUE, $check_security = TRUE) {

            I_Tools::initialize_controller(__FILE__, FALSE, FALSE, TRUE, TRUE, $check_security);

            I_Tools::load_utilities(array('integral-form-api'));

            if ($with_view) {
                self::load_view_master('admin');
                Admin_View::initialize_view();
                Admin_View::set_layout('admin');
                Admin_View::set_view(I_Tools::get_file_slug());
                Admin_View::set_title('Integral MailChimp');
            }


        }


        /**
         * Processes webhook calls from MailChimp
         * 
         * TODO: Implement audit logging
         * 
         */
        public static function process_webhook_response() {
            self::_initialize_controller(FALSE, FALSE);

            $post = I_Tools::fetch_post_all();

            if (is_array($post) && !empty($post) && isset($post['type'])) {
                $type = $post['type'];
                $data = $post['data'];

                if (isset($data['list_id']) && $data['list_id']) {

                    //- Confirm this call is from MailChimp (check for the url query)
                    $incoming_key = I_Tools::fetch_get_value('key') ? : I_Tools::fetch_post_value('key');

                    $local_key = self::_get_webhooks_key($data['list_id']);

                    if ($incoming_key == $local_key) {
                        //- Filter out which type of call we're getting
                        switch ($type) {

                            //- Profile Update
                            case 'profile':

                                //- Allow the plugins to update the values for the Merge Tags they handle
                                if (isset($data['merges']) && isset($data['email'])) {

                                    $user = get_user_by('email', $data['email']);

                                    if (is_a($user, 'WP_User')) {

                                        do_action('integral_mailchimp_plugin_update_merge_tags', $data['merges'], $user);
                                    }
                                }

                                break;

                            //- Email Change
                            case 'upemail':

                                if (isset($data['old_email']) && isset($data['new_email'])) {

                                    //- Load the user with this email
                                    $user = get_user_by('email', $data['old_email']);

                                    if (is_a($user, 'WP_User')) {

                                        //- Change the email in the system
                                        if (update_user_meta($user->ID, 'user_email', $data['new_email'], $data['old_email'])) {

                                            //- TODO - send an email to the user notifying them (implement options to allow admins to control the content of that email)
                                        }
                                    }
                                }

                                break;

                            //- Unsubscribe
                            case 'unsubscribe':

                                if (isset($data['email']) && isset($data['list_id'])) {

                                    //- Load the user with this email
                                    $user = get_user_by('email', $data['email']);

                                    if (is_a($user, 'WP_User')) {

                                        $meta_key = "imc_list_status_{$data['list_id']}";
                                        $action   = (isset($data['action']) && $data['action']) ? $data['action'] : 'unsub';

                                        //- Change the list status in the system
                                        if (update_user_meta($user->ID, $meta_key, $action)) {

                                            //- TODO - send an email to the user notifying the admin (implement options to allow admins to control the content of that email)
                                        }
                                    }
                                }

                                break;
                        }
                        //- Send a response back confirming receipt
                        die(true);
                    } else {
                        $logger_message = 'Webhook key mis-match in ' . __FUNCTION__ . '()';
                        $logger_items   = array('$incoming_key' => $incoming_key, '$local_key' => $local_key, '$post' => $post);
                        Logger::log_error($logger_message, $logger_items);
                    }
                } else {
                    $logger_message = 'Missing "list_id" in ' . __FUNCTION__ . '()';
                    $logger_items   = array('$data' => $data, '$post' => $post);
                    Logger::log_error($logger_message, $logger_items);
                }
            } else {
                $logger_message = '$_POST empty or invalid (missing "type") in ' . __FUNCTION__ . '()';
                $logger_items   = array('$post' => $post);
                Logger::log_error($logger_message, $logger_items);
            }

            die(NULL);


        }


        /**
         * 
         * 
         */
        public static function webhook_registration() {
            self::_initialize_controller(FALSE);

            $skip     = I_Tools::fetch_ajax_value('skip', FALSE);
            $override = I_Tools::fetch_ajax_value('override', FALSE);

            $imc_options_url = admin_url('admin.php?page=integral_mailchimp/general');
            $imc_debug_url   = admin_url('admin.php?page=integral_mailchimp/debug_log');


            //- Skip if they are requesting that            
            if ($skip) {
                update_option(I_Conf::OPT_WEBHOOKS_REGISTERED, TRUE);
                $success = TRUE;
                $message = __('The webhook registration process has been skipped', 'integral-mailchimp');
            } else {

                //- If the webhooks have NOT already been registered OR we are doing an override from the options page
                $webhooks_registered = get_option(I_Conf::OPT_WEBHOOKS_REGISTERED);
                if (!$webhooks_registered || $override) {
                    //- Build webhooks options array
                    $webhooks_array = self::_build_webhooks_array();

                    $url = admin_url('admin-ajax.php?action=' . I_Conf::ADMIN_WEBHOOK_CALLBACK_ACTION);

                    //- Load the list id's
                    I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists'));
                    $lists = Integral_MailChimp_Lists::load_mailchimp_lists();

                    if (is_array($lists) && !empty($lists)) {
                        $lists   = array_keys($lists);
                        $success = FALSE;

                        //- Extract the $actions and $sources
                        extract($webhooks_array);

                        foreach ($lists as $list_id) {

                            //- Fetch or Generate the validation key for this list's webhook
                            $key = self::_get_webhooks_key($list_id);
                            if (!$key) {
                                self::_set_webhooks_key($list_id);
                                $key = self::_get_webhooks_key($list_id);
                            }

                            if ($key) {
                                //- Send API call to register the hooks
                                $response = Integral_MailChimp_Lists::add_new_webhooks_to_mailchimp($list_id, $url, $key, $actions, $sources);

                                if (is_bool($response) && $response) {
                                    $success = TRUE;
                                    $message = __('The webhooks were successfully registered!', 'integral-mailchimp');
                                } else {
                                    $success = FALSE;
                                    $message = $response['message'];
                                    if (!$override) {
                                        $message .= '<p>' . __('Although it is inadvisable, you may also choose to', 'integral-mailchimp') . ' <a class="register-mailchimp-webhooks" data-skip-webhooks="true" href="javascript:void(0);">' . __('skip this process', 'integral-mailchimp') . '</a>. (<a href="http://apidocs.mailchimp.com/webhooks/" target="_blank">' . __('What is this?', 'integral-mailchimp') . '</a>)</p>';
                                    }

                                    break;
                                }
                            } else {
                                $logger_message = 'Creating a new Webhook Validation Key failed in ' . __FUNCTION__ . '()';
                                $logger_items   = array('$list_id' => $list_id, '$key' => $key);
                                Logger::log_error($logger_message, $logger_items);

                                $success = FALSE;
                                $message = __('An error occured attempting to register the MailChimp Webhooks.', 'integral-mailchimp');
                                $message .= (I_Conf::$debug_enabled) ? sprintf(__('Please refer to the %1$s debug %2$s logs for more information.', 'integral-mailchimp'), "<a href='{$imc_debug_url}'>", "</a>") : sprintf(__('For additional information about this issue, enable debugging %1$s here %2$s and attempt the webhook registration again.', 'integral-mailchimp'), "<a href='{$imc_options_url}'>", "</a>");

                                break;
                            }
                        }

                        if ($success) {
                            update_option(I_Conf::OPT_WEBHOOKS_REGISTERED, TRUE);
                        }
                    } else {
                        $logger_message = 'No lists found while trying to register the MailChimp Webhooks in ' . __FUNCTION__ . '()';
                        $logger_items   = array('$lists' => $lists);
                        Logger::log_error($logger_message, $logger_items);

                        $success = FALSE;
                        $message = __('An error occured attempting to register the MailChimp Webhooks.', 'integral-mailchimp');
                        $message .= (I_Conf::$debug_enabled) ? sprintf(__('Please refer to the %1$s debug %2$s logs for more information.', 'integral-mailchimp'), "<a href='{$imc_debug_url}'>", "</a>") : sprintf(__('For additional information about this issue, enable debugging %1$s here %2$s and attempt the webhook registration again.', 'integral-mailchimp'), "<a href='{$imc_options_url}'>", "</a>");
                    }
                }
            }

            if ($success) {
                wp_send_json(array('msg' => $message));
            } else {
                wp_send_json(array('error' => $message));
            }


        }


        private static function _build_webhooks_array() {

            $webhooks = array(
                'actions' => array(
                    'subscribe' => FALSE,
                    'unsubscribe' => TRUE,
                    'profile' => TRUE,
                    'cleaned' => FALSE,
                    'upemail' => TRUE,
                    'campaign' => FALSE //- TODO - Consider implementing this
                ),
                'sources' => array(
                    'user' => TRUE,
                    'admin' => TRUE,
                    'api' => FALSE
                )
            );

            return $webhooks;


        }


        private static function _set_webhooks_key($list_id) {
            self::$webhook_keys = get_option(I_Conf::OPT_WEBHOOK_KEYS);

            $new_key = md5(microtime() * rand(1, 999) + wp_salt());

            self::$webhook_keys[$list_id] = $new_key;

            update_option(I_Conf::OPT_WEBHOOK_KEYS, self::$webhook_keys);


        }


        private static function _get_webhooks_key($list_id) {
            self::$webhook_keys = get_option(I_Conf::OPT_WEBHOOK_KEYS);

            return (isset(self::$webhook_keys[$list_id]) && self::$webhook_keys[$list_id]) ? self::$webhook_keys[$list_id] : FALSE;


        }


    }


}

