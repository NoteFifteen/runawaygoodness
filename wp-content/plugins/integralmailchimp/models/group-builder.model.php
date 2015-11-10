<?php

namespace IMC\Models;

use IMC\Library\Framework\Integral_Plugin_Model;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/**
 * Handles the model functionality for viewing and processing of options for this plugin
 * 
 * 
 */
if (!class_exists('Group_Builder_Model')) {

    class Group_Builder_Model extends Integral_Plugin_Model {


        /**
         * Wrapper for load_options() that passes in the full array of options for this model
         * 
         * STUB - Group Builder
         * 
         * @param string $key
         * @return boolean or mixed
         */
        public static function load_database_values($key = NULL) {
            $all_keys = array(
                I_Conf::OPT_MC_LIST_GROUPINGS //- STUB - Group Builder - Serialized array??
            );

            return self::load_options($all_keys, $key);


        }


        /**
         * Wrapper for save_options() that preps the options array
         * 
         * @param type $values
         * @return type
         */
        public static function save_database_values($values) {
            $options = array();

            foreach ($values as $key => $value) {
                $options[$key] = array('value' => $value);
            }

            return self::save_options($options);


        }


    }


}

