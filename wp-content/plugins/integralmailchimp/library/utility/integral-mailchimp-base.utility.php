<?php

namespace IMC\Library\Utility;

use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/* * *
 * This is the Integral Mailchimp Base class 
 * 
 * !!!!! This class should only be extended !!!!! 
 * 
 * This plugin is responsible for:
 * -- providing essential mailchimp high-level API utilities for each Mailchimp utility child class
 * 
 */
if (!class_exists('Integral_MailChimp_Base')) {

    class Integral_MailChimp_Base {


        final private function __construct() {
            wp_die('THIS CLASS MAY NOT BE INSTATIATED [' . __CLASS__ . ' - ' . __FILE__ . ']');


        }


        protected static function _initialize_utility() {
            if (!class_exists('IMC\Library\Utility\Integral_MailChimp_API')) {
                wp_die(__('Must preload Integral_MailChimp_API class', 'integral-mailchimp'));
            }

            if (!I_Conf::$mcAPI) {
                $logger_message = 'MailChimp API activated before API_KEY available in ' . __FUNCTION__ . '()';
                $logger_items   = array();
                Logger::log_warning($logger_message, $logger_items);

                return FALSE;
            } else {
                return TRUE;
            }


        }


    }


}