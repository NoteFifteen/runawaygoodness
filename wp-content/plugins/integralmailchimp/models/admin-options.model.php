<?php

namespace IMC\Models;

use IMC\Library\Utility\I_Tools;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/**
 * Handles the model functionality for viewing and processing of options for this plugin
 * 
 * 
 */
if (!class_exists('Admin_Options_Model')) {

    class Admin_Options_Model {


        /**
         * Wrapper for load_options() that passes in the full array of options for this model
         * 
         * @param string $key
         * @return boolean or mixed
         */
        public static function load_general_options_database_values($key = NULL) {
            $all_keys = array(
                I_Conf::OPT_LICENSEKEY,
                I_Conf::OPT_APIKEY,
                I_Conf::OPT_DEFAULT_USER_LIST,
                I_Conf::OPT_ENABLE_DEBUG_MODE,
                I_Conf::OPT_SSL_VERIFY_PEER,
            );

            return I_Tools::load_options($all_keys, $key);


        }


        /**
         * Wrapper for save_options() that preps the options array
         * 
         * @param type $values
         * @return type
         */
        public static function save_general_options_database_values($values) {
            $options = array();
            foreach ($values as $key => $value) {
                $options[$key] = array('value' => $value);
            }

            return I_Tools::save_options($options);


        }


    }


}

