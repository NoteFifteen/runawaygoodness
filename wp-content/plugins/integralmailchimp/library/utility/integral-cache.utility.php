<?php

namespace IMC\Library\Utility;

use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/**
 * A session/cookie/cache driven transient mechanism
 */
if (!class_exists('I_Cache')) {

    class I_Cache {


        public static $debug = FALSE;


        /**
         * Creates a new transient
         * 
         * @param string $key           - Unique key name
         * @param mixed $value          - Value to store
         * @param int $timeout          - (default = HOUR_IN_SECONDS) How long the transient should live (use HOUR_IN_SECONDS & MINUTE_IN_SECONDS constants)
         * @param boolean $per_session  - (default = TRUE) Set this to true if the value needs to be stored per user vs a sitewide setting
         * @param boolean $network_wide - this transient should be made available across all sites in a multisite network
         * 
         * @return bit masked int       - first digit indicates transient success, second indicates cache success
         */
        public static function save_transient($key, $value, $timeout = HOUR_IN_SECONDS, $per_session = TRUE, $network_wide = FALSE) {

            $trans_key = I_Conf::build_transient_key($key, $per_session);

            if (IS_MULTISITE && $network_wide) {
                $trans_success = set_site_transient($trans_key, $value, $timeout);
            } else {
                $trans_success = set_transient($trans_key, $value, $timeout);
            }

            $cache_success = wp_cache_set($trans_key, $value, I_Conf::PLUGIN_TEXT_DOMAIN, $timeout);

            if (self::$debug) {
                $logger_message = 'Response in ' . __FUNCTION__ . '()';
                $logger_items   = array('$trans_key' => $trans_key, '$trans_success' => $trans_success, '$cache_success' => $cache_success, 'function_args' => func_get_args());
                Logger::log_error($logger_message, $logger_items);
            }

            return (int) $trans_success . (int) $cache_success;


        }


        /**
         * Loads a transient value
         * 
         * @param string $key          - Unique key name
         * @param boolean $per_session - (default = TRUE) Needs to match whatever was chosen when the transient was created
         * @param boolean $network_wide - this transient should be made available across all sites in a multisite network
         * 
         * @return mixed
         */
        public static function load_transient($key, $per_session = TRUE, $network_wide = FALSE) {

            $trans_key = I_Conf::build_transient_key($key, $per_session);

            $value = wp_cache_get($trans_key, I_Conf::PLUGIN_TEXT_DOMAIN);

            //- Cache was empty, try transient
            if (!$value) {
                if (IS_MULTISITE && $network_wide) {
                    $value = get_site_transient($trans_key);
                } else {
                    $value = get_transient($trans_key);
                }
            }
            
            if (self::$debug) {
                $logger_message = 'Response in ' . __FUNCTION__ . '()';
                $logger_items   = array('$trans_key' => $trans_key, '$value' => $value, 'function_args' => func_get_args());
                Logger::log_error($logger_message, $logger_items);
            }

            return $value;


        }


        /**
         * Delete a transient
         * 
         * @param string $key          - Unique key name
         * @param boolean $per_session - (default = TRUE) Needs to match whatever was chosen when the transient was created
         * @param boolean $network_wide - this transient should be made available across all sites in a multisite network
         * 
         * @return bit masked int      - first digit indicates transient success, second indicates cache success
         */
        public static function delete_transient($key, $per_session = TRUE, $network_wide = FALSE) {

            $trans_key = I_Conf::build_transient_key($key, $per_session);

            $cache_success = wp_cache_delete($trans_key, I_Conf::PLUGIN_TEXT_DOMAIN);

            if (IS_MULTISITE && $network_wide) {
                $trans_success = delete_site_transient($trans_key);
            } else {
                $trans_success = delete_transient($trans_key);
            }
            
            if (self::$debug) {
                $logger_message = 'Response in ' . __FUNCTION__ . '()';
                $logger_items   = array('$trans_key' => $trans_key, '$trans_success' => $trans_success, '$cache_success' => $cache_success, 'function_args' => func_get_args());
                Logger::log_error($logger_message, $logger_items);
            }

            return (int) $trans_success . (int) $cache_success;


        }


    }


}