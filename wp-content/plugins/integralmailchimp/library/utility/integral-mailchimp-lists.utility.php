<?php

namespace IMC\Library\Utility;

use IMC\Library\Utility\Integral_MailChimp_Base;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

if (!class_exists('Integral_MailChimp_Lists')) {

    class Integral_MailChimp_Lists extends Integral_MailChimp_Base {


        /**
         * Fetches all API MailChimp lists for this api_key
         * 
         * API Reference - "lists/list"
         * http://apidocs.mailchimp.com/api/2.0/lists/list.php
         * 
         * @param array $filters
         * @return boolean or array
         */
        public static function load_mailchimp_lists($filters = array()) {
            if (self::_initialize_utility()) {

                //- Filters
                $with_counts  = TRUE;
                $all_data     = FALSE;
                extract($filters); //- with_counts, all_data
                //- Check for a cached version of the info we need
                $trans_key    = I_Conf::TRANS_MC_LISTS;
                $options_list = I_Cache::load_transient($trans_key, FALSE);

                if (!$options_list) {
                    $options_list = array();
                    $success      = I_Conf::$mcAPI->mcLists('getList', array(), FALSE);

                    if ($success) {
                        $lists = I_Conf::$mcAPI->getResponse();
                        if (is_array($lists) && !empty($lists)) {

                            foreach ($lists as $list) {
                                $value  = $list['name'];
                                $counts = ' (' . $list['stats']['member_count'] . ' ' . __('recipients', 'integral-mailchimp') . ')';

                                $options_list['all'][$list['id']]    = $list;
                                $options_list['normal'][$list['id']] = $value;
                                $options_list['counts'][$list['id']] = $value . $counts;
                            }

                            I_Cache::save_transient($trans_key, $options_list, HOUR_IN_SECONDS, FALSE);
                        } else {
                            $logger_message = '[API] Invalid response from LISTS->getList() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {
                        $options_list   = FALSE;
                        $logger_message = '[API] Loading the MailChimp Lists failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('getLists_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                }

                if ($options_list) {
                    return ($all_data) ? $options_list['all'] : (($with_counts) ? $options_list['counts'] : $options_list['normal']);
                } else {
                    return $options_list;
                }
            }


        }


        /**
         * Fetches the LOCAL merge tags that should be sync'd from other plugins on the site
         * 
         * @staticvar array $merge_tags
         * @param boolean $as_options - TRUE = formats as simple select options ; FALSE = returns the original array
         * @return array
         */
        public static function load_mailchimp_plugin_sync_merge_tags($as_options = TRUE) {
            if (self::_initialize_utility()) {

                static $merge_tags = NULL;

                if (!$merge_tags) {
                    $tags       = array();
                    $merge_tags = apply_filters('integral_mailchimp_plugin_sync_merge_tags', $tags);
                }

                if ($as_options) {
                    $options = array();
                    foreach ($merge_tags as $tag_id => $tag_array) {
                        $options[$tag_id] = $tag_array['name'];
                    }

                    return $options;
                }

                return $merge_tags;
            }


        }


        /**
         * Fetches the API merge tags for the list_id
         * 
         * API Reference - "lists/mergevars"
         * http://apidocs.mailchimp.com/api/2.0/lists/merge-vars.php
         * 
         * @param string $list_id
         * @param array $filters
         * @return boolean or array
         */
        public static function load_mailchimp_merge_tags($list_id, $filters = array()) {
            if (self::_initialize_utility()) {

                //- Filters
                $all_data = FALSE;
                extract($filters);

                //- Check for a cached version of the info we need
                $trans_key    = I_Conf::TRANS_MC_LIST_MERGE_TAGS . $list_id;
                $options_list = I_Cache::load_transient($trans_key, FALSE);

                if (!$options_list) {
                    $options_list = array();
                    $success      = I_Conf::$mcAPI->mcLists('mergeVars', array('list_id' => $list_id), FALSE);

                    if ($success) {
                        $response = I_Conf::$mcAPI->getResponse();

                        if (is_array($response) && isset($response[0]) && is_array($response[0]) && !empty($response[0])) {
                            $response = $response[0];

                            if ($response['id'] == $list_id) {
                                $options_list['all'] = $response;

                                foreach ($response['merge_vars'] as $merge_tag) {
                                    $options_list['normal'][$merge_tag['tag']] = $merge_tag;
                                }

                                I_Cache::save_transient($trans_key, $options_list, HOUR_IN_SECONDS, FALSE);
                            } else {
                                $options_list   = FALSE;
                                $logger_message = '[API] The received list_id does NOT match the supplied list_id in ' . __FUNCTION__ . '()';
                                $logger_items   = array('getLists_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                                Logger::log_error($logger_message, $logger_items);
                            }
                        } else {
                            $logger_message = '[API] Invalid response from LISTS->mergeVars() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {
                        $options_list   = FALSE;
                        $logger_message = '[API] Loading the MailChimp List Merge Tags failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('getLists_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                }


                if ($options_list) {
                    //- remove EMAIL from the MC Merge Tags
                    unset($options_list['normal']['EMAIL']);
                    return ($all_data) ? $options_list['all'] : $options_list['normal'];
                } else {
                    return $options_list;
                }
            }


        }


        /**
         * Saves the chosen merge tags to the API
         * 
         * API Reference - "lists/merege-var-add"
         * http://apidocs.mailchimp.com/api/2.0/lists/merge-var-add.php
         * 
         * @param string $list_id
         * @param string $tag
         * @param string $name
         * @param array $options
         * @return boolean or array
         */
        public static function save_mailchimp_merge_tags($list_id, $tag, $name, $options) {
            if (self::_initialize_utility()) {

                //- Merge Var Tag must be 10 bytes or less
                $test_tag = mb_strcut($tag, 0, 10);

                if ($tag === $test_tag) {

                    $data = compact('list_id', 'tag', 'name', 'options');

                    $success = I_Conf::$mcAPI->mcLists('mergeVarAdd', $data, FALSE);

                    if ($success) {
                        //- Clear the cache and transients
                        I_Cache::delete_transient(I_Conf::TRANS_MC_LIST_MERGE_TAGS . $list_id, FALSE);
                    } else {
                        $logger_message = '[API] The Add Merge Tag called failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('getLists_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }

                    $response = I_Conf::$mcAPI->getResponse();
                } else {

                    $logger_message = '[API] The Merge Tag was too long in ' . __FUNCTION__ . '()';
                    $logger_items   = array('original_tag' => $tag, 'test_tag' => $test_tag, 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);

                    $success  = FALSE;
                    $response = $logger_message;
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Updates the API with the provided merge tag
         * 
         * API Reference - "lists/merge-var-update"
         * http://apidocs.mailchimp.com/api/2.0/lists/merge-var-update.php
         * 
         * @param string $list_id
         * @param string $tag
         * @param array $options
         * @return array
         */
        public static function update_mailchimp_merge_tags($list_id, $tag, $options) {
            if (self::_initialize_utility()) {

                $data = compact('list_id', 'tag', 'options');

                $success = I_Conf::$mcAPI->mcLists('mergeVarUpdate', $data, FALSE);

                if ($success) {
                    //- Clear the cache and transients
                    I_Cache::delete_transient(I_Conf::TRANS_MC_LIST_MERGE_TAGS . $list_id, FALSE);
                } else {
                    $logger_message = '[API] Updating Merge Tags failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }

                $response = I_Conf::$mcAPI->getResponse();

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Fetches the API segments for the api_key provided
         * 
         * API Reference - "lists/segments"
         * http://apidocs.mailchimp.com/api/2.0/lists/segments.php
         * 
         * @param string $list_id
         * @param string $list_name
         * @param string $load_type     (static or saved)
         * @param type $filters         (all_data)
         * @return array
         */
        public static function load_mailchimp_list_segments($list_id, $list_name = '', $load_type = NULL, $filters = array()) {
            if (self::_initialize_utility()) {

                //- Filters
                $all_data      = FALSE;
                extract($filters); //- all_data
                //- Check for a cached version of the info we need
                $trans_key     = I_Conf::TRANS_MC_LIST_SEGMENTS . $list_id;
                $options_list  = I_Cache::load_transient($trans_key, FALSE);
                $segment_types = array('static', 'saved');
                $type          = $load_type;


                if (!$options_list) {
                    $options_list = array();
                    $data         = compact('list_id', 'type');

                    $success = I_Conf::$mcAPI->mcLists('segments', $data, FALSE);

                    if ($success) {
                        $segments = I_Conf::$mcAPI->getResponse();

                        if (is_array($segments) && !empty($segments)) {

                            foreach ($segment_types as $segment_type) {
                                if (isset($segments[$segment_type]) && is_array($segments[$segment_type]) && !empty($segments[$segment_type])) {
                                    foreach ($segments[$segment_type] as $segment) {
                                        $segment_name = $segment['name'];
                                        $segment_id   = $segment['id'];

                                        $options_list['all'][$segment_type][$segment_id]    = $segment;
                                        $options_list['normal'][$segment_type][$segment_id] = $segment_name;
                                    }
                                } else {

                                    $options_list['all'][$segment_type]    = array();
                                    $options_list['normal'][$segment_type] = array();
                                }
                            }

                            I_Cache::save_transient($trans_key, $options_list, HOUR_IN_SECONDS, FALSE);
                        } else {
                            $options_list   = FALSE;
                            $logger_message = '[API] Invalid response from LISTS->segments() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {
                        //- TODO: Need to handle errors better here
                        $options_list = FALSE;
                        $response     = I_Conf::$mcAPI->getResponse();

                        if (isset($response['type'])) {
                            switch ($response['type']) {
                                case 'MailChimp_List_InvalidOption':
                                default:
                                    $response['message'] = sprintf(__('There was a problem loading the segments for this list (%1$s).', 'integral-mailchimp'), $list_name);
                                    $options_list        = array();
                                    break;
                            }
                        }

                        $logger_message = '[API] Loading List Segments failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                }

                if ($options_list) {
                    if ($type && in_array($load_type, $segment_types)) {
                        return ($all_data) ? $options_list['all'][$load_type] : $options_list['normal'][$load_type];
                    } else {
                        return ($all_data) ? $options_list['all'] : $options_list['normal'];
                    }
                } else {
                    return $options_list;
                }
            }


        }


        /**
         * Updates the email for a subscriber in a given list
         * 
         * Wrapper for add_user_to_email_list()
         * 
         * @param string $user_email
         * @param string $list_id
         * @param array $options
         * @return array
         */
        public static function update_user_in_email_list($user_email, $list_id, $options) {
            return self::add_user_to_email_list($user_email, $list_id, $options);


        }


        /**
         * Subscribes an email to a MailChimp list
         * 
         * API Reference - "lists/subscribe"
         * http://apidocs.mailchimp.com/api/2.0/lists/subscribe.php
         * 
         * @param string $user_email
         * @param string $list_id
         * @param array $options
         * @return array
         */
        public static function add_user_to_email_list($user_email, $list_id, $options) {
            if (self::_initialize_utility()) {

                $success  = array();
                $response = array();

                $success        = I_Conf::$mcAPI->mcLists('subscribe', array('email' => $user_email, 'list_id' => $list_id, 'options' => $options), FALSE);
                $response       = I_Conf::$mcAPI->getResponse();
                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Subscribes a list of emails to a MailChimp list
         * 
         * API Reference - "lists/batchSubscribe"
         * http://apidocs.mailchimp.com/api/2.0/lists/batch-subscribe.php
         * 
         * @param array  $users
         * @param string $list_id
         * @param array  $options
         * @return array
         */
        public static function add_batch_users_to_email_list($users, $list_id, $options) {
            if (self::_initialize_utility()) {

                $success  = array();
                $response = array();

                $success        = I_Conf::$mcAPI->mcLists('batchSubscribe', array('batch' => $users, 'list_id' => $list_id, 'options' => $options), FALSE);
                $response       = I_Conf::$mcAPI->getResponse();
                $response_array = compact('success', 'response');

                return $response_array;
            }

            /* public function batchSubscribe($id, $batch, $double_optin = true, $update_existing = false, $replace_interests = true) {
              $_params = array("id" => $id, "batch" => $batch, "double_optin" => $double_optin, "update_existing" => $update_existing, "replace_interests" => $replace_interests);
              return $this->master->call('lists/batch-subscribe', $_params);
             */


        }


        /**
         * Unsubscribes an email from an array of lists
         * 
         * API Reference - "lists/unsubscribe"
         * http://apidocs.mailchimp.com/api/2.0/lists/unsubscribe.php
         * 
         * @param string $user_email
         * @param array $lists_array
         * @param array $options
         * @return array
         */
        public static function remove_user_from_email_lists($user_email, $lists_array, $options) {
            if (self::_initialize_utility()) {

                $success  = array();
                $response = array();

                if (is_array($lists_array) && !empty($lists_array)) {
                    foreach ($lists_array as $list_id => $list_name) {
                        $success[$list_name]  = I_Conf::$mcAPI->mcLists('unsubscribe', array('email' => $user_email, 'list_id' => $list_id, 'options' => $options), FALSE);
                        $response[$list_name] = I_Conf::$mcAPI->getResponse();
                    }
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


        /**
         * Loads the Groupings for a List
         * 
         * API Reference - "lists/interest-groupings"
         * http://apidocs.mailchimp.com/api/2.0/lists/interest-groupings.php
         * 
         * @param array $filters
         * @return boolean or array
         */
        public static function load_mailchimp_groupings($list_id, $list_name, $filters = array()) {
            if (self::_initialize_utility()) {
                //- Filters
                $with_counts  = TRUE;
                $all_data     = FALSE;
                extract($filters); //- with_counts, all_data
                $counts       = $with_counts;
                //- Check for a cached version of the info we need
                $trans_key    = I_Conf::TRANS_MC_LIST_GROUPINGS . $list_id;
                $options_list = I_Cache::load_transient($trans_key, FALSE);

                if (!$options_list) {
                    $options_list = array();

                    $data = compact('list_id', 'counts');

                    $success = I_Conf::$mcAPI->mcLists('interestGroupings', $data, FALSE);

                    if ($success) {
                        $groupings = I_Conf::$mcAPI->getResponse();

                        if (is_array($groupings) && !empty($groupings)) {
                            foreach ($groupings as $grouping) {
                                $grouping_name = $grouping['name'];
                                $grouping_id   = $grouping['id'];


                                $options_list['all'][$grouping_id] = $grouping;

                                if (isset($grouping['groups']) && is_array($grouping['groups']) && !empty($grouping['groups'])) {

                                    foreach ($grouping['groups'] as $group) {

                                        $counts = ' (' . $group['subscribers'] . ' ' . __('subscribers', 'integral-mailchimp') . ')';

                                        $options_list['normal'][$grouping_id][$group['id']] = $group['name'];
                                        $options_list['counts'][$grouping_id][$group['id']] = $group['name'] . $counts;
                                    }
                                }
                            }

                            I_Cache::save_transient($trans_key, $options_list, HOUR_IN_SECONDS, FALSE);
                        } else {
                            $options_list = FALSE;

                            $logger_message = '[API] Invalid response from LISTS->interestGroupings() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                        //- Create $options_list from $groupings
                    } else {
                        $options_list = FALSE;
                        $response     = I_Conf::$mcAPI->getResponse();

                        if (isset($response['type'])) {
                            switch ($response['type']) {
                                case 'MailChimp_List_InvalidOption':

                                    if (strpos($response['message'], 'This list does not have interest groups enabled') !== FALSE) {

                                        //- If the only reason we didn't get a valid group list back is because there ARE NO GROUPS for this List
                                        //- then save the "empty" group list so we don't keep calling the API for that list
                                        $options_list           = array();
                                        $options_list['all']    = array();
                                        $options_list['normal'] = array();
                                        $options_list['counts'] = array();

                                        I_Cache::save_transient($trans_key, $options_list, HOUR_IN_SECONDS, FALSE);

                                        $response['message'] = sprintf(__('Interest Groups have not been enabled for this list (%1$s).', 'integral-mailchimp'), $list_name);

                                        $logger_message = $response['message'];
                                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args(), 'backtrace' => debug_backtrace());
                                        Logger::log_info($logger_message, $logger_items);
                                        break;
                                    } else {
                                        //- If the preceding conditional fails, the switch needs to fall through to the 'default' option below
                                    }

                                default:
                                    $logger_message = '[API] Loading List Groupings failed in ' . __FUNCTION__ . '()';
                                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                                    Logger::log_error($logger_message, $logger_items);
                                    break;
                            }
                        }
                    }
                }

                if ($options_list) {
                    return ($all_data) ? $options_list['all'] : (($with_counts) ? $options_list['counts'] : $options_list['normal']);
                } else {
                    return $options_list;
                }
            }


        }


        /**
         * Adds a new Grouping to a List
         * 
         * API Reference - "lists/interest-grouping-add"
         * http://apidocs.mailchimp.com/api/2.0/lists/interest-grouping-add.php
         * 
         * @param array $filters
         * @return boolean or array
         */
        public static function add_grouping_to_email_list($list_id, $name, $type, $groups) {
            if (self::_initialize_utility()) {


                $data = compact('list_id', 'name', 'type', 'groups');

                $success = I_Conf::$mcAPI->mcLists('interestGroupingAdd', $data, FALSE);

                if ($success) {
                    
                } else {
                    //- TODO: Need to handle errors better here
                }
            }


        }


        /**
         * Removes a Grouping from a List
         * 
         * API Reference - "lists/interest-grouping-del"
         * http://apidocs.mailchimp.com/api/2.0/lists/interest-grouping-del.php
         * 
         * @param string $grouping_id
         * @return boolean or array
         */
        public static function remove_grouping_from_email_list($grouping_id) {
            if (self::_initialize_utility()) {

                $data = compact('grouping_id');

                $success = I_Conf::$mcAPI->mcLists('interestGroupingDel', $data, FALSE);

                if ($success) {
                    
                } else {
                    $logger_message = '[API] Removing Grouping from List failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }
            }


        }


        /**
         * Updates a Grouping in a List
         * 
         * API Reference - "lists/interest-grouping-update"
         * http://apidocs.mailchimp.com/api/2.0/lists/interest-grouping-update.php
         * 
         * @param array $filters
         * @return boolean or array
         */
        public static function update_grouping_in_email_list($grouping_id, $name, $value) {
            if (self::_initialize_utility()) {

                $data = compact('grouping_id', 'name', 'value');

                $success = I_Conf::$mcAPI->mcLists('interestGroupingUpdate', $data, FALSE);

                if ($success) {
                    
                } else {
                    $logger_message = '[API] Updating Grouping in List failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }
            }


        }


        /**
         * Adds a new Group to a Grouping
         * 
         * API Reference - "lists/interest-group-add"
         * http://apidocs.mailchimp.com/api/2.0/lists/interest-group-add.php
         * 
         * STUB - Group Builder
         * 
         * @param array $filters
         * @return boolean or array
         */
        public static function add_group_to_grouping($list_id, $group_name, $grouping_id) {
            if (self::_initialize_utility()) {

                $data = compact('list_id', 'group_name', 'grouping_id');

                $success = I_Conf::$mcAPI->mcLists('interestGroupAdd', $data, FALSE);

                if ($success) {
                    
                } else {
                    $logger_message = '[API] Adding a Group to a Grouping in List failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }
            }


        }


        /**
         * Removes a Group from a Grouping
         * 
         * API Reference - "lists/interest-grouping-del"
         * http://apidocs.mailchimp.com/api/2.0/lists/interest-grouping-del.php
         * 
         * STUB - Group Builder
         * 
         * @param array $filters
         * @return boolean or array
         */
        public static function remove_group_from_grouping($list_id, $group_name, $grouping_id) {
            if (self::_initialize_utility()) {

                $data = compact('list_id', 'group_name', 'grouping_id');

                $success = I_Conf::$mcAPI->mcLists('interestGroupDel', $data, FALSE);

                if ($success) {
                    
                } else {
                    $logger_message = '[API] Removing a Group from a Grouping in List failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }
            }


        }


        /**
         * Updates a Group in a Grouping
         * 
         * API Reference - "lists/interest-group-update"
         * http://apidocs.mailchimp.com/api/2.0/lists/interest-group-update.php
         * 
         * STUB - Group Builder
         * 
         * @param array $filters
         * @return boolean or array
         */
        public static function update_group_in_grouping($list_id, $old_name, $new_name, $grouping_id) {
            if (self::_initialize_utility()) {

                $data = compact('list_id', 'old_name', 'new_name', 'grouping_id');

                $success = I_Conf::$mcAPI->mcLists('interestGroupUpdate', $data, FALSE);

                if ($success) {
                    
                } else {
                    $logger_message = '[API] Loading List Segments failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }
            }


        }


        /**
         * Registers a Webhook callback with MailChimp
         * 
         * http://apidocs.mailchimp.com/api/2.0/lists/webhook-add.php
         * 
         * @param string $list_id
         * @param string $url
         * @param array $actions
         * @param array $sources
         */
        public static function add_new_webhooks_to_mailchimp($list_id, $url, $key, $actions, $sources) {
            if (self::_initialize_utility()) {

                $og_url = $url;
                $url .= "&key={$key}";

                $data = compact('list_id', 'url', 'actions', 'sources');

                $success = I_Conf::$mcAPI->mcLists('webhookAdd', $data, FALSE);

                if ($success) {
                    return $success;
                } else {
                    //- TODO: Need to handle errors better here
                    $response = I_Conf::$mcAPI->getResponse();

                    if (isset($response['type'])) {
                        switch ($response['type']) {
                            case 'MailChimp_Invalid_URL':
                                $is_duplicate = strpos($response['message'], 'multiple');
                                if ($is_duplicate) {
                                    return TRUE;
                                }
                                $og_url              = make_clickable($og_url);
                                $response['message'] = sprintf(__('MailChimp was unable to validate the URL that was provided (%1$s). Please confirm that the url is live and available to the web.', 'integral-mailchimp'), $og_url);
                                break;
                            default:
                                break;
                        }
                    }

                    return $response;
                }
            }


        }


        /**
         * Builds the default settings for subscribing to a MailChimp list
         * 
         * @return array
         */
        public static function build_list_subscribe_defaults() {

            //- TODO:  This should pull from admin options at some point
            $merge_tags        = NULL;
            $email_type        = 'html';
            $double_optin      = FALSE;
            $update_existing   = TRUE;
            $replace_interests = FALSE;
            $send_welcome      = FALSE;

            $options = compact('merge_tags', 'email_type', 'double_optin', 'update_existing', 'replace_interests', 'send_welcome');

            return $options;


        }


        /**
         * Builds the default settings for unsubscribing from a MailChimp list
         * 
         * @return array
         */
        public static function build_list_unsubscribe_defaults() {

            //- TODO:  This should pull from admin options at some point
            $delete_member = FALSE;
            $send_goodbye  = TRUE;
            $send_notify   = TRUE;

            $options = compact('delete_member', 'send_goodbye', 'send_notify');

            return $options;


        }


        /**
         * Fetches the merge tags from other plugins on the site
         * 
         * @param object $user
         * @return array
         */
        public static function get_plugins_merge_tags($user) {
            $tags       = array();
            $merge_tags = apply_filters('integral_mailchimp_plugin_get_merge_tags', $tags, $user);
            return $merge_tags;


        }


    }


}