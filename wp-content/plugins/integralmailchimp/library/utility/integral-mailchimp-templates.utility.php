<?php

namespace IMC\Library\Utility;

use IMC\I_Conf;
use IMC\Library\Framework\Logger;

if (!class_exists('Integral_MailChimp_Templates')) {

    class Integral_MailChimp_Templates extends Integral_MailChimp_Base {


        /**
         * Fetches the API MailChimp templates for an api_key
         * 
         * API Reference - "templates/list"
         * http://apidocs.mailchimp.com/api/2.0/templates/list.php
         * 
         * @param array $type     (user or gallery or all)
         * @return boolean or array
         */
        public static function load_mailchimp_templates($type = 'all') {
            if (self::_initialize_utility()) {

                //- Excluded Templates as template id
                //- CURRENTLY DEPRICATED
                $exclude_templates = array(
                    '1000158'
                );


                //- Filters
                $options_list = FALSE;
                //- Check for a cached version of the info we need
                $trans_key    = I_Conf::TRANS_MC_TEMPLATES;
                $options_list = I_Cache::load_transient($trans_key, FALSE);

                //- temporarily disable cacheing for template lists
                if (!$options_list || TRUE) {
                    $options_list = array();


                    $filters = array(
                        //- If a user has saved a modified drag-and-drop template
                        'include_drag_and_drop' => TRUE
                    );

                    //- Types, can be user, gallery or all
                    $types   = array('user' => true, 'gallery' => true);
                    $request = compact('types', 'filters');
                    $success = I_Conf::$mcAPI->mcTemplates('getList', $request);

                    $response = I_Conf::$mcAPI->getResponse();

                    if ($success) {

                        //- There are two possible types of templates being returned -- base has been deprecated
                        if ((isset($response['user']) && is_array($response['user'])) || (isset($response['gallery']) && is_array($response['gallery']))) {
                            $templates = $response;
                            $user      = FALSE;
                            $gallery   = FALSE;

                            extract($templates);

                            $options_list['user']    = array();
                            $options_list['gallery'] = array();

                            if ($user) {
                                foreach ($user as $template) {
                                    $value = $template['name'];

                                    $options_list['user'][$template['id']]        = $value;
                                    $options_list['all']['user'][$template['id']] = $value;
                                }
                            }

                            if ($gallery) {

                                //- For gallery templates we need to split into opt groups for each category
                                foreach ($gallery as $template) {

                                    //- skip the auto-connect & alert category templates for now
                                    //- if (in_array($template['category'], array('AutoConnect', 'Alerts', 'eCommerce', 'Stationery', 'Sports')) || in_array($template['id'], $exclude_templates)) {
                                    //- exclude templates with ids starting with 20000
                                    if (in_array($template['category'], array('AutoConnect', 'Alerts', 'eCommerce', 'Stationery', 'Sports')) || strpos($template['id'], '20000') !== false) {
                                        continue;
                                    }

                                    $value = $template['name'];

                                    $options_list['gallery'][$template['category']][$template['id']]        = $value;
                                    $options_list['all']['gallery'][$template['category']][$template['id']] = $value;
                                }

                                //- Sort by the category name
                                ksort($options_list['gallery']);
                                ksort($options_list['all']['gallery']);
                            }

                            I_Cache::save_transient($trans_key, $options_list, HOUR_IN_SECONDS, FALSE);
                        } else {
                            $logger_message = '[API] Invalid response from TEMPLATES->getList() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {
                        $logger_message = '[API] Loading the Templates failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                }

                if ($options_list && is_array($options_list) && isset($options_list[$type])) {
                    return $options_list[$type];
                } else {
                    return $options_list;
                }
            }


        }


        public static function load_mailchimp_template_info_ajax() {
            if (self::_initialize_utility()) {

                $template_info = FALSE;

                if ($template_id = $_POST['value']) {

                    if (is_numeric($template_id) && $template_id > 0) {

                        $api_response = self::load_mailchimp_template_info($template_id);

                        extract($api_response); //- success, response

                        if ($success) {
                            $template_info = $response;
                        }
                    }
                }

                wp_send_json($template_info);
            }


        }


        public static function load_mailchimp_template_info($template_id = NULL) {
            if (self::_initialize_utility()) {

                if (is_numeric($template_id) && $template_id > 0) {

                    //- Check for a cached version of the info we need
                    $trans_key     = I_Conf::TRANS_MC_TEMPLATE_INFO . $template_id;
                    $template_info = I_Cache::load_transient($trans_key, FALSE);

                    if (!$template_info) {

                        $request = compact('template_id');

                        $success = I_Conf::$mcAPI->mcTemplates('info', $request);

                        $response = I_Conf::$mcAPI->getResponse();

                        if ($success) {
                            if (isset($response['source'])) {
                                $template_info = $response;

                                I_Cache::save_transient($trans_key, $template_info, HOUR_IN_SECONDS, FALSE);
                            } else {
                                $logger_message = '[API] Invalid response from TEMPLATES->info() in ' . __FUNCTION__ . '()';
                                $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                                Logger::log_error($logger_message, $logger_items);
                            }
                        } else {
                            $logger_message = '[API] Loading the Template Info failed in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {
                        $success  = TRUE;
                        $response = $template_info;
                    }
                } else {
                    $success  = FALSE;
                    $response = array();

                    $logger_message = '[API] Missing or invalid Template ID in ' . __FUNCTION__ . '()';
                    $logger_items   = array('template_id' => $template_id, 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


    }


}
