<?php

namespace IMC\Library\Utility;

use IMC\I_Conf;
use IMC\Library\Framework\Logger;

if (!class_exists('Integral_MailChimp_Helper')) {

    class Integral_MailChimp_Helper extends Integral_MailChimp_Base {


        /**
         * Confirms via the API that the api_key is valid
         * 
         * API Reference - "helper/ping"
         * http://apidocs.mailchimp.com/api/2.0/helper/ping.php
         * 
         * @param string $api_key
         * @return boolean
         */
        public static function confirm_api_key($api_key) {

            $success = FALSE;

            if ($api_key) {
                $mcAPI   = new Integral_MailChimp_API($api_key);
                $success = $mcAPI->mcHelper('ping', array(), TRUE);
                
                if ($success === TRUE) {
                    I_Conf::$mcAPI = $mcAPI;
                }
            } else {
                $logger_message = '[API] API Key missing or invalid in ' . __FUNCTION__ . '()';
                $logger_items   = array('api_response' => $mcAPI->getResponse(), 'function_args' => func_get_args());
                Logger::log_warning($logger_message, $logger_items);
            }

            return $success;


        }


        /**
         * Returns all lists that the email is subscribed to
         * 
         * API Reference - "helper/lists-for-email"
         * http://apidocs.mailchimp.com/api/2.0/helper/lists-for-email.php
         * 
         * @param string $email
         * @return boolean or array
         */
        public static function load_mailchimp_lists_by_email($email) {
            if (self::_initialize_utility()) {

                //- Check for a cached version of the info we need
                $trans_key        = I_Conf::TRANS_MC_LIST_FOR_EMAIL . $email;
                $subscribed_lists = I_Cache::load_transient($trans_key, FALSE);

                if (!$subscribed_lists) {
                    $options = compact('email');

                    $success = I_Conf::$mcAPI->mcHelper('listsForEmail', $options);

                    $response = I_Conf::$mcAPI->getResponse();

                    $subscribed_lists = FALSE;
                    if ($success) {
                        if (is_array($response) && !empty($response)) {

                            $subscribed_lists = array();
                            foreach ($response as $list) {
                                if (isset($list['id']) && isset($list['name'])) {
                                    $subscribed_lists[$list['id']] = $list['name'];
                                }
                            }

                            I_Cache::save_transient($trans_key, $subscribed_lists, HOUR_IN_SECONDS, FALSE);
                        } else {
                            $logger_message = '[API] Invalid response from LISTS->listsForEmail() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {

                        if (isset($response['type'])) {
                            switch ($response['type']) {
                                case 'MailChimp_Email_NotExists':
                                    $logger_message = '[API] The email does not exist in any lists in ' . __FUNCTION__ . '()';
                                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                                    Logger::log_info($logger_message, $logger_items);
                                    break;
                                default:
                                    $logger_message = '[API] Loading Email Lists by email failed in ' . __FUNCTION__ . '()';
                                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                                    Logger::log_error($logger_message, $logger_items);
                                    break;
                            }
                        }
                    }
                }

                return $subscribed_lists;
            }


        }


        public static function inline_email_css($html_content) {
            if (self::_initialize_utility()) {

                $options = compact('html_content');

                $success = I_Conf::$mcAPI->mcHelper('inlineCss', $options);

                $response = I_Conf::$mcAPI->getResponse();

                if (!$success) {

                    if (isset($response['type'])) {
                        switch ($response['type']) {
                            case 'Campaign_InvalidContent':
                                $logger_message = '[API] Invalid Content provided in ' . __FUNCTION__ . '()';
                                $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                                Logger::log_info($logger_message, $logger_items);
                                break;
                            default:
                                $logger_message = '[API] Inlining the CSS failed in ' . __FUNCTION__ . '()';
                                $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                                Logger::log_error($logger_message, $logger_items);
                                break;
                        }
                    } else {
                        $logger_message = '[API] Inlining the CSS failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                }


                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


    }


}