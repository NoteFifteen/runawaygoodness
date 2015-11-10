<?php

namespace IMC\Library\Utility;

use IMC\Library\API\MailChimp;
use IMC\Library\API\Call;
use IMC\Library\API\Call\MailChimp_Error;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

I_Conf::include_file(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'mailchimp.class.php');

if (!class_exists('Integral_MailChimp_API')) {

    class Integral_MailChimp_API {


        private $api_key  = NULL;
        private $mcAPI    = NULL;
        private $response = NULL;
        private $success  = FALSE;


        public function __construct($api_key) {
            $this->api_key = $api_key;


        }


        /**
         * IMC Wrapper for the MailChimp API's Campaign calls
         * 
         * API Reference - "Campaigns Methods"
         * http://apidocs.mailchimp.com/api/2.0/#campaigns-methods
         * 
         * @param string $call
         * @param array $args
         * @param boolean $reply_response
         * @return boolean or mixed
         */
        public function mcCampaign($call, $args = array(), $reply_response = TRUE) {

            if ($call) {

                $this->success  = FALSE;
                $this->response = NULL;

                $this->mcAPI = new MailChimp($this->api_key, I_Conf::CALL_CAMPAIGNS);
                $campaigns   = $this->mcAPI->campaigns;

                try {

                    switch ($call) {
                        case 'create':
                            $type         = 'regular';
                            $options      = array();
                            $content      = array();
                            $segment_opts = null;
                            $type_opts    = null;
                            extract($args);

                            $this->response = $campaigns->create($type, $options, $content, $segment_opts, $type_opts);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }
                            break;


                        case 'getList':
                            $filters    = array();
                            $start      = 0;
                            $limit      = 1000;
                            $sort_field = 'create_time';
                            $sort_dir   = 'DESC';
                            extract($args);

                            $this->response = $campaigns->getList($filters, $start, $limit, $sort_field, $sort_dir);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }
                            break;


                        case 'ready':
                            $cid = 0;
                            extract($args);

                            $this->response = $campaigns->ready($cid);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }
                            break;


                        case 'content':
                            $cid = 0;
                            extract($args);

                            $this->response = $campaigns->content($cid, array('view' => 'raw'));
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }
                            break;


                        case 'send':
                            $cid = 0;
                            extract($args);

                            $this->response = $campaigns->send($cid);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }
                            break;


                        case 'sendTest':
                            $cid         = 0;
                            $test_emails = array();
                            $send_type   = 'html';
                            extract($args);

                            $this->response = $campaigns->sendTest($cid, $test_emails, $send_type);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }
                            break;


                        case 'update':
                            $cid   = 0;
                            $name  = '';
                            $value = '';
                            extract($args);

                            $this->response = $campaigns->update($cid, $name, $value);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }
                            break;
                    }
                } catch (MailChimp_Error $e) {
                    $this->_handleMailChimpException($e);
                } catch (Exception $e) {
                    $this->_handleMailChimpException($e, TRUE);
                }

                if ($reply_response) {
                    return $this->response;
                } else {
                    return $this->success;
                }
            }


        }


        /**
         * IMC Wrapper for the MailChimp API's Helper calls
         * 
         * API Reference - "Helper Methods"
         * http://apidocs.mailchimp.com/api/2.0/#helper-methods
         * 
         * @param string $call
         * @param array $options
         * @param boolean $reply_response
         * @return boolean or mixed
         */
        public function mcHelper($call, $args = array(), $reply_response = FALSE) {

            if ($call) {

                $this->success  = FALSE;
                $this->response = NULL;

                $this->mcAPI = new MailChimp($this->api_key, I_Conf::CALL_HELPER);
                $helper      = $this->mcAPI->helper;

                try {

                    switch ($call) {
                        case 'ping':
                            $this->response    = $helper->ping();
                            $expected_response = "Everything's Chimpy!";
                            if ($expected_response == trim($this->response['msg'])) {
                                $this->success  = TRUE;
                                $this->response = TRUE;
                            }
                            break;


                        case 'listsForEmail':
                            $email          = array('email' => $args['email']);
                            $this->response = $helper->listsForEmail($email);
                            $this->success  = TRUE;
                            break;


                        case 'inlineCss':
                            $this->response = $helper->inlineCss($args['html_content']);
                            $this->success  = TRUE;
                            break;
                    }
                } catch (MailChimp_Error $e) {
                    $this->_handleMailChimpException($e);
                } catch (Exception $e) {
                    $this->_handleMailChimpException($e, TRUE);
                }

                if ($reply_response) {
                    return $this->response;
                } else {
                    return $this->success;
                }
            }


        }


        /**
         * IMC Wrapper for the MailChimp API's Lists calls
         * 
         * API Reference - "Lists Methods"
         * http://apidocs.mailchimp.com/api/2.0/#lists-methods
         * 
         * @param string $call
         * @param array $args
         * @param boolean $reply_response
         * @return boolean or mixed
         */
        public function mcLists($call, $args = array(), $reply_response = TRUE) {

            if ($call) {

                $this->success  = FALSE;
                $this->response = NULL;

                $this->mcAPI = new MailChimp($this->api_key, I_Conf::CALL_LISTS);
                $lists       = $this->mcAPI->lists;

                try {

                    switch ($call) {

                        case 'batchSubscribe':
                            $batch = $args['batch'];
                            extract($args['options']);

                            $this->response = $lists->batchSubscribe($args['list_id'], $batch, $double_optin, $update_existing, $replace_interests);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }

                            break;


                        case 'getList':
                            $this->response = $lists->getList();
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }
                            break;


                        case 'interestGroupings':
                            extract($args);

                            $this->response = $lists->interestGroupings($list_id, $counts);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = $this->response;
                            }

                            break;


                        case 'interestGroupingAdd':
                            /*
                             * STUB - Group Builder
                             */
                            extract($args);

                            $this->response = $lists->interestGroupingAdd($list_id, $name, $type, $groups);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;


                        case 'interestGroupingDel':
                            /*
                             * STUB - Group Builder
                             */
                            extract($args);

                            $this->response = $lists->interestGroupingDel($grouping_id);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;


                        case 'interestGroupingUpdate':
                            /*
                             * STUB - Group Builder
                             */
                            extract($args);

                            $this->response = $lists->interestGroupingUpdate($grouping_id, $name, $value);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;


                        case 'interestGroupAdd':
                            /*
                             * STUB - Group Builder
                             */
                            extract($args);

                            $this->response = $lists->interestGroupAdd($list_id, $group_name, $grouping_id);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;


                        case 'interestGroupDel':
                            /*
                             * STUB - Group Builder
                             */
                            extract($args);

                            $this->response = $lists->interestGroupDel($list_id, $group_name, $grouping_id);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;


                        case 'interestGroupUpdate':
                            /*
                             * STUB - Group Builder
                             */
                            extract($args);

                            $this->response = $lists->interestGroupUpdate($list_id, $old_name, $new_name, $grouping_id);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;


                        case 'mergeVars':
                            $this->response = $lists->mergeVars(array($args['list_id']));
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;


                        case 'mergeVarAdd':
                            extract($args);

                            $this->response = $lists->mergeVarAdd($list_id, $tag, $name, $options);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;


                        case 'mergeVarUpdate':
                            extract($args);

                            $this->response = $lists->mergeVarUpdate($list_id, $tag, $options);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;


                        case 'segments':
                            $this->response = $lists->segments($args['list_id'], $args['type']);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }

                            break;


                        case 'subscribe':
                            $email = array('email' => $args['email']);
                            extract($args['options']);

                            $this->response = $lists->subscribe($args['list_id'], $email, $merge_tags, $email_type, $double_optin, $update_existing, $replace_interests, $send_welcome);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }

                            break;


                        case 'unsubscribe':
                            $email = array('email' => $args['email']);
                            extract($args['options']);

                            $this->response = $lists->unsubscribe($args['list_id'], $email, $delete_member, $send_goodbye, $send_notify);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }

                            break;


                        case 'webhookAdd':
                            extract($args);

                            $this->response = $lists->webhookAdd($list_id, $url, $actions, $sources);
                            if (empty($this->response['errors'])) {
                                $this->success  = TRUE;
                                $this->response = isset($this->response['data']) ? $this->response['data'] : $this->response;
                            }

                            break;
                    }
                } catch (MailChimp_Error $e) {
                    $this->_handleMailChimpException($e);
                } catch (Exception $e) {
                    $this->_handleMailChimpException($e, TRUE);
                }

                if ($reply_response) {
                    return $this->response;
                } else {
                    return $this->success;
                }
            }


        }


        /**
         * IMC Wrapper for the MailChimp API's Reports calls
         * 
         * API Reference - "Reports Methods"
         * http://apidocs.mailchimp.com/api/2.0/#reports-methods
         * 
         * @param string $call
         * @param array $args
         * @param boolean $reply_response
         * @return boolean or mixed
         */
        public function mcReports($call, $args = array(), $reply_response = TRUE) {

            if ($call) {

                $this->success  = FALSE;
                $this->response = NULL;

                $this->mcAPI = new MailChimp($this->api_key, I_Conf::CALL_REPORTS);
                $reports     = $this->mcAPI->reports;

                try {

                    switch ($call) {
                        case 'summary':
                            $cid = 0;
                            extract($args);

                            $this->response = $reports->summary($cid);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }
                            break;
                    }
                } catch (MailChimp_Error $e) {
                    $this->_handleMailChimpException($e);
                } catch (Exception $e) {
                    $this->_handleMailChimpException($e, TRUE);
                }

                if ($reply_response) {
                    return $this->response;
                } else {
                    return $this->success;
                }
            }


        }


        /**
         * IMC Wrapper for the MailChimp API's Template calls
         * 
         * API Reference - "Templates Methods"
         * http://apidocs.mailchimp.com/api/2.0/#templates-methods
         * 
         * @param string $call
         * @param array $args
         * @param boolean $reply_response
         * @return boolean or mixed
         */
        public function mcTemplates($call, $args = array(), $reply_response = TRUE) {

            if ($call) {

                $this->success  = FALSE;
                $this->response = NULL;

                $this->mcAPI = new MailChimp($this->api_key, I_Conf::CALL_TEMPLATES);
                $templates   = $this->mcAPI->templates;

                try {

                    switch ($call) {
                        case 'info':
                            $template_id = 0;
                            extract($args);

                            if (is_numeric($template_id) && $template_id > 0) {
                                $this->response = $templates->info($template_id);
                                if (empty($this->response['errors'])) {
                                    $this->success = TRUE;
                                }
                            } else {
                                $this->response = array(
                                    'status' => 'error',
                                    'name' => __('Invalid_Template', 'integral-mailchimp'),
                                    'error' => __('Invalid Template ID provided', 'integral-mailchimp')
                                );
                                $this->success  = FALSE;
                            }
                            break;


                        case 'getList':
                            $types   = array();
                            $filters = array();
                            extract($args);

                            $this->response = $templates->getList($types, $filters);
                            if (empty($this->response['errors'])) {
                                $this->success = TRUE;
                            }
                            break;
                    }
                } catch (MailChimp_Error $e) {
                    $this->_handleMailChimpException($e);
                } catch (Exception $e) {
                    $this->_handleMailChimpException($e, TRUE);
                }

                if ($reply_response) {
                    return $this->response;
                } else {
                    return $this->success;
                }
            }


        }


        /**
         * Returns the response from the last API call
         * 
         * @return mixed
         */
        public function getResponse() {
            return $this->response;


        }


        /**
         * Extracts the type and the message from a MailChimp Exception
         * 
         * @param exception $exception
         * @param array $generic
         */
        private function _handleMailChimpException($exception, $generic = FALSE) {
            $this->success = FALSE;
            if ($exception) {

                $message = $exception->getMessage();

                if ($generic) {
                    $message = $message ? $message : __('A generic system error occured!', 'integral-mailchimp');
                }

                $type = $exception->type;

                $this->response = compact('message', 'type');
            }


        }


    }


}