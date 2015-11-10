<?php

namespace IMC\Library\Utility;

use IMC\Library\Utility\Integral_MailChimp_Base;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

if (!class_exists('Integral_MailChimp_Reports')) {

    class Integral_MailChimp_Reports extends Integral_MailChimp_Base {


        /**
         * Uses the load_mailchimp_campaign_report_summary() to get campaign report info for the provided post_id
         * 
         * @staticvar array $campaign_stats
         * @param int $post_id
         * @return array
         */
        public static function load_campaign_stats_by_post($post_id) {
            static $campaign_stats = array();

            if (!isset($campaign_stats[$post_id])) {
                $campaign_stats[$post_id] = array();

                $post_status = get_post_status($post_id);
                if ($post_status == 'publish') {

                    $cid = get_post_meta($post_id, 'imc_campaign_id', TRUE);

                    if ($cid) {
                        $response = self::load_mailchimp_campaign_report_summary($cid);

                        if ($response['success']) {
                            $campaign_stats[$post_id] = $response['response'];
                        } else {
                            $logger_message = '[API] Invalid response from load_mailchimp_campaign_report_summary() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('post_id' => $post_id, 'api_response' => $response, 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                            return array();
                        }
                    } else {
                        $logger_message = '[API] Campaign ID missing or invalid in ' . __FUNCTION__ . '()';
                        $logger_items   = array('post_id' => $post_id, 'campaign_id' => $cid, 'function_args' => func_get_args());
                        Logger::log_warning($logger_message, $logger_items);
                    }
                } else {
                    
                }
            }

            return $campaign_stats[$post_id];


        }


        /**
         * Load the summary statistics report for the Campaign
         * 
         * API Reference - "reports/summary"
         * http://apidocs.mailchimp.com/api/2.0/reports/summary.php
         * 
         * @param string $cid           Campaign ID
         * @return array $response      array(success, response)
         */
        public static function load_mailchimp_campaign_report_summary($cid) {
            if (self::_initialize_utility()) {

                //- Check for a cached version of the info we need
                $trans_key      = I_Conf::TRANS_MC_SUMMARY_REPORT . $cid;
                $report_summary = I_Cache::load_transient($trans_key, FALSE);

                if (!$report_summary) {
                    $success = I_Conf::$mcAPI->mcReports('summary', array('cid' => $cid));

                    $response = I_Conf::$mcAPI->getResponse();

                    if ($success) {
                        if (is_array($response) && isset($response['syntax_errors'])) {
                            $report_summary = $response;

                            I_Cache::save_transient($trans_key, $report_summary, 5 * MINUTE_IN_SECONDS, FALSE);
                        } else {
                            $success        = FALSE;
                            $logger_message = '[API] Invalid response from REPORTS->summary() in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                            Logger::log_error($logger_message, $logger_items);
                        }
                    } else {
                        $logger_message = '[API] Loading the Campaign Summary Report failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('api_response' => I_Conf::$mcAPI->getResponse(), 'function_args' => func_get_args());
                        Logger::log_error($logger_message, $logger_items);
                    }
                } else {
                    $success  = TRUE;
                    $response = $report_summary;
                }

                $response_array = compact('success', 'response');

                return $response_array;
            }


        }


    }


}

    