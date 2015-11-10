<?php

namespace IMC\Library\Utility;

use IMC\Library\Utility\Integral_MailChimp_Base;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

if (!class_exists('Integral_MailChimp_Campaigns')) {

    class Integral_MailChimp_Campaigns extends Integral_MailChimp_Base {


        /**
         * Creates an API campaign for the list_id
         * 
         * API Reference - "campaigns/create"
         * http://apidocs.mailchimp.com/api/2.0/campaigns/create.php
         * 
         * @param type $type            1 of    (regular, plaintext, absplit, rss, auto)
         * @param type $options         all of  (list_id,  subject, from_email, to_name)
         * @param type $content         1 of    (html, sections, text, url, archive)
         * @param type $filters         optional (TBD)
         * @param type $segment_opts    optional (http://apidocs.mailchimp.com/api/2.0/campaigns/segment-test.php)
         * @param type $type_opts       optional (http://apidocs.mailchimp.com/api/2.0/campaigns/create.php)
         * @return array $response      array(success, response) 
         *                              On success, response will be the new campaign's details similar to single campaign from campaigns/list()
         *                              (http://apidocs.mailchimp.com/api/2.0/campaigns/list.php)
         *                              
         */
        public static function create_mailchimp_campaign($type, $options, $content, $filters = array(), $segment_opts = NULL, $type_opts = NULL) {

            if (self::_initialize_utility()) {

                $pre_success = TRUE;
                $success     = FALSE;
                $response    = array();

                //- Need to define any used filters here
                extract($filters);

                //- Confirm we have all the required options
                $required_options = array('list_id', 'subject', 'from_email', 'to_name');

                //- Only need to have one of these elements present
                $required_content = array('html', 'sections', 'text', 'url', 'archive');

                if (count(array_intersect_key(array_flip($required_options), $options)) !== count($required_options)) { //- Make sure all required elements are there
                    $pre_success         = FALSE;
                    $response['message'] = __('Missing 1 of these required options (list_id, subject, from_email, to_name)', 'integral-mailchimp');
                    $response['type']    = 'MailChimp_Invalid_Options';
                }

                if (count(array_intersect_key(array_flip($required_content), $content)) !== 1) { //- Only need to match one content type
                    $pre_success         = FALSE;
                    $response['message'] = __('Please provide only 1 of these content fields (html, sections, text, url, archive)', 'integral-mailchimp');
                    $response['type']    = 'MailChimp_Invalid_Options';
                }

                if ($pre_success) {
                    $success = I_Conf::$mcAPI->mcCampaign('create', array('type' => $type, 'options' => $options, 'content' => $content, 'segment_opts' => $segment_opts, 'type_opts' => $type_opts), FALSE);

                    $response = I_Conf::$mcAPI->getResponse();

                    if ($success) {
                        if (isset($response['id']) && $cid = $response['id']) {
                            $campaign_info = $response;
                            $trans_key     = I_Conf::TRANS_MC_CAMPAIGN_INFO . $cid;
                            I_Cache::save_transient($trans_key, $campaign_info, HOUR_IN_SECONDS, FALSE);
                        } else {
                            $logger_message = '[API] Invalid response from CAMPAIGNS->create() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {
                        $logger_message = '[API] Creating the Campaign failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Update a key => value for a given Campaign
         * 
         * API Reference - "campaigns/update"
         * http://apidocs.mailchimp.com/api/2.0/campaigns/update.php
         * 
         * @param string $cid           Campaign ID
         * @param string $field         field name
         * @param string $value         field value
         * @return array $response      array(success, response) 
         *                              On success, response will be an array with campaign info
         */
        public static function update_mailchimp_campaign_field($cid, $field, $value) {
            if (self::_initialize_utility()) {

                $success = I_Conf::$mcAPI->mcCampaign('update', array('cid' => $cid, 'name' => $field, 'value' => $value), FALSE);

                $response = I_Conf::$mcAPI->getResponse();
                if ($success) {
                    if (isset($response['data']) && is_array($response['data'])) {
                        $campaign_info = $response      = $response['data'];

                        $trans_key = I_Conf::TRANS_MC_CAMPAIGN_INFO . $cid;
                        I_Cache::save_transient($trans_key, $campaign_info, HOUR_IN_SECONDS, FALSE);
                        $trans_key = I_Conf::TRANS_MC_CAMPAIGN_CONTENT . $cid;
                        I_Cache::delete_transient($trans_key, FALSE);
                    } else {
                        $logger_message = '[API] Invalid response from CAMPAIGNS->update() in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                } else {
                    $logger_message = '[API] Updating the Campaign failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }


                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Loads the Email content for a given Campaign
         * 
         * API Reference - "campaigns/content"
         * http://apidocs.mailchimp.com/api/2.0/campaigns/content.php
         * 
         * @param string $cid           Campaign ID
         * @return array $response      array(success, response) 
         *                              On success, response will be the full html content of the campaign
         */
        public static function load_mailchimp_campaign_content($cid) {
            if (self::_initialize_utility()) {

                //- Check for a cached version of the info we need
                $trans_key        = I_Conf::TRANS_MC_CAMPAIGN_CONTENT . $cid;
                $campaign_content = I_Cache::load_transient($trans_key, FALSE);

                if (!$campaign_content) {
                    $success = I_Conf::$mcAPI->mcCampaign('content', array('cid' => $cid), FALSE);

                    $response = I_Conf::$mcAPI->getResponse();

                    if ($success) {
                        if (isset($response['html']) || isset($response['text'])) {
                            $campaign_content = $response;

                            I_Cache::save_transient($trans_key, $campaign_content, HOUR_IN_SECONDS, FALSE);
                        } else {
                            $logger_message = '[API] Invalid response from CAMPAIGNS->content() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {
                        $logger_message = '[API] Loading Campaign content failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                } else {
                    $success  = TRUE;
                    $response = $campaign_content;
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Check the API ready status for a given Campaign
         * 
         * API Reference - "campaigns/ready"
         * http://apidocs.mailchimp.com/api/2.0/campaigns/ready.php
         * 
         * @param string $cid           Campaign ID
         * @return array $response      array(success, response) 
         *                              On success, response will be an array with is_ready and an array of steps
         */
        public static function ready_check_mailchimp_campaign($cid) {
            if (self::_initialize_utility()) {

                $success = I_Conf::$mcAPI->mcCampaign('ready', array('cid' => $cid), FALSE);

                $response = I_Conf::$mcAPI->getResponse();

                if ($success) {
                    if (!(isset($response['is_ready'], $response['items']))) {
                        $logger_message = '[API] Invalid response from CAMPAIGNS->ready() in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                } else {
                    $logger_message = '[API] Loading Campaign Ready failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Send a test email for a given Campaign
         * 
         * API Reference - "campaigns/send-test"
         * http://apidocs.mailchimp.com/api/2.0/campaigns/send-test.php
         * 
         * @param string $cid           Campaign ID
         * @param array $test_emails    array of emails
         * @param string $send_type     (html or text) defaults to html
         * @return array $response      array(success, response) 
         *                              On success, response will be an array with is_ready and an array of steps
         */
        public static function send_test_mailchimp_campaign($cid, $test_emails, $send_type = 'html') {
            if (self::_initialize_utility()) {

                $success = I_Conf::$mcAPI->mcCampaign('sendTest', array('cid' => $cid, 'test_emails' => $test_emails, 'send_type' => $send_type), FALSE);

                $response = I_Conf::$mcAPI->getResponse();

                if (!$success) {
                    $logger_message = '[API] Test Sending the Campaign failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Send a given Campaign
         * 
         * API Reference - "campaigns/send"
         * http://apidocs.mailchimp.com/api/2.0/campaigns/send.php
         * 
         * @param string $cid           Campaign ID
         * @return array $response      array(success, response)
         */
        public static function send_mailchimp_campaign($cid) {
            if (self::_initialize_utility()) {

                $success = I_Conf::$mcAPI->mcCampaign('send', array('cid' => $cid), FALSE);

                $response = I_Conf::$mcAPI->getResponse();

                if (!$success) {
                    $logger_message = '[API] Sending the Campaign failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Load Campaign for the provided Campaign ID
         * 
         * API Reference - "campaigns/list"
         * http://apidocs.mailchimp.com/api/2.0/campaigns/list.php
         * 
         * @param string $cid           Campaign ID
         * @param array $args           (start, limit , sort_field, sort_dir)
         * @return array $response      array(success, response)
         */
        public static function load_mailchimp_campaign($cid, $args = array()) {
            if (self::_initialize_utility()) {

                $start      = 0;
                $limit      = 1;
                $sort_field = 'create_time';
                $sort_dir   = 'DESC';
                extract($args);

                //- Check for a cached version of the info we need
                $trans_key     = I_Conf::TRANS_MC_CAMPAIGN_INFO . $cid;
                $campaign_info = I_Cache::load_transient($trans_key, FALSE);

                if (!$campaign_info) {
                    $success = I_Conf::$mcAPI->mcCampaign('getList', array('filters' => array('campaign_id' => $cid), 'start' => $start, 'limit' => $limit, 'sort_field' => $sort_field, 'sort_dir' => $sort_dir), FALSE);

                    $response = I_Conf::$mcAPI->getResponse();

                    if ($success) {
                        if (isset($response['data']) && is_array($response['data'])) {
                            $campaign_info = $response      = reset($response['data']);
                            I_Cache::save_transient($trans_key, $campaign_info, HOUR_IN_SECONDS, FALSE);
                        } else {
                            $logger_message = '[API] Invalid response from CAMPAIGNS->getList() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {
                        $logger_message = '[API] Loading the Campaign List failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                } else {
                    $success  = TRUE;
                    $response = $campaign_info;
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Load Campaigns for the provided List
         * 
         * API Reference - "campaigns/list"
         * http://apidocs.mailchimp.com/api/2.0/campaigns/list.php
         * 
         * @param string $list_id       List ID
         * @param array $args           (start, limit , sort_field, sort_dir)
         * @return array $response      array(success, response)
         */
        public static function load_mailchimp_campaigns_by_list($list_id, $args = array()) {
            if (self::_initialize_utility()) {
                $start      = 0;
                $limit      = 1000;
                $sort_field = 'create_time';
                $sort_dir   = 'DESC';
                extract($args);

                $success = I_Conf::$mcAPI->mcCampaign('getList', array('filters' => array('list_id' => $list_id), 'start' => $start, 'limit' => $limit, 'sort_field' => $sort_field, 'sort_dir' => $sort_dir), FALSE);

                $response = I_Conf::$mcAPI->getResponse();

                if ($success) {
                    if (isset($response['data']) && is_array($response['data'])) {
                        $response = $response['data'];
                        //I_Cache::save_transient($trans_key, $campaign_info, HOUR_IN_SECONDS, FALSE);
                    } else {
                        $logger_message = '[API] Invalid response from CAMPAIGNS->getList() with list_id in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                } else {
                    $logger_message = '[API] Loading the Campaign List by list_id failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Load Campaigns for the provided filter(s)
         * 
         * API Reference - "campaigns/list"
         * http://apidocs.mailchimp.com/api/2.0/campaigns/list.php
         * 
         * @param array $filters        any of (campaign_id, parent_id, list_id, folder_id, template_id, status, type, from_name, from_email, title, subject, sendtime_start, sendtime_end, uses_segment, exact)
         * @param array $args           all of (start, limit , sort_field, sort_dir)
         * @return array $response      array(success, response)
         */
        public static function load_mailchimp_campaigns_by_filters($filters, $args = array()) {
            if (self::_initialize_utility()) {
                $start      = 0;
                $limit      = 1000;
                $sort_field = 'create_time';
                $sort_dir   = 'DESC';
                extract($args);

                $success = I_Conf::$mcAPI->mcCampaign('getList', array('filters' => $filters, 'start' => $start, 'limit' => $limit, 'sort_field' => $sort_field, 'sort_dir' => $sort_dir), FALSE);

                $response = I_Conf::$mcAPI->getResponse();

                if ($success) {
                    if (isset($response['data']) && is_array($response['data'])) {
                        $response = $response['data'];
                        //I_Cache::save_transient($trans_key, $campaign_info, HOUR_IN_SECONDS, FALSE);
                    } else {
                        $logger_message = '[API] Invalid response from CAMPAIGNS->getList() with filters in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                } else {
                    $logger_message = '[API] Loading the Campaign List by filters failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }
                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


    }


}