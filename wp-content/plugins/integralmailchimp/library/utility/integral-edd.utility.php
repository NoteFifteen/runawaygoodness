<?php

namespace IMC\Library\Utility;

use IMC\I_Conf;
use IMC\Library\Framework\Logger;

if (!class_exists('Integral_EDD')) {


    /**
     * Provides functionality for activating, deactivating and validating Plugin licenses
     * 
     */
    class Integral_EDD {


        public static function activate_license($license_key) {
            
            $api_params = array(
                'edd_action' => 'activate_license',
                'license' => $license_key,
                'item_name' => urlencode(I_Conf::PLUGIN_EDD_NAME),
                'url' => home_url()
            );

            //- Call the custom API
            $response = wp_remote_get(add_query_arg($api_params, I_Conf::EDD_URL), array('timeout' => 15, 'sslverify' => false));

            //- Make sure the response came back okay
            if (is_wp_error($response)) {
                $logger_message = '[EDD] Invalid response in ' . __FUNCTION__ . '()';
                $logger_items   = array('response' => $response, 'api_params' => $api_params, 'function_args' => func_get_args());
                Logger::log_error($logger_message, $logger_items);
            } else {

                //- Decode the license data
                $license_data = json_decode(wp_remote_retrieve_body($response));

                if (isset($license_data->license)) {
                    return $license_data;
                } else {                    
                    $logger_message = '[EDD] Activating License failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('license_data' => $license_data, 'response' => $response, 'api_params' => $api_params, 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }
                
            }
            
            return FALSE;


        }


        public static function deactivate_license($license_key) {

            $api_params = array(
                'edd_action' => 'deactivate_license',
                'license' => $license_key,
                'item_name' => urlencode(I_Conf::PLUGIN_EDD_NAME),
                'url' => home_url()
            );

            //- Call the custom API
            $response = wp_remote_get(add_query_arg($api_params, I_Conf::EDD_URL), array('timeout' => 15, 'sslverify' => false));

            //- Make sure the response came back okay
            if (is_wp_error($response)) {
                $logger_message = '[EDD] Invalid response in ' . __FUNCTION__ . '()';
                $logger_items   = array('response' => $response, 'api_params' => $api_params, 'function_args' => func_get_args());
                Logger::log_error($logger_message, $logger_items);
            } else {

                //- Decode the license data
                $license_data = json_decode(wp_remote_retrieve_body($response));

                //- $license_data->license will be either "deactivated" or "failed"
                if ($license_data->license == 'deactivated') {
                    return TRUE;
                } else {
                    $logger_message = '[EDD] Deactivating License failed in ' . __FUNCTION__ . '()';
                    $logger_items   = array('license_data' => $license_data, 'response' => $response, 'api_params' => $api_params, 'function_args' => func_get_args());
                    Logger::log_error($logger_message, $logger_items);
                }
            }

            return FALSE;


        }


    }


}