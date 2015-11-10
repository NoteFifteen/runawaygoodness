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
if (!class_exists('User_Lists_Model')) {

    class User_Lists_Model {


        /**
         * Loads database values for this class
         * 
         * @param string $key
         * @return mixed
         */
        public static function load_user_lists_database_values($key = NULL) {
            $all_keys = array(
                I_Conf::OPT_DEFAULT_USER_LIST,
                I_Conf::OPT_MC_LOCAL_MERGETAGS
            );

            return I_Tools::load_options($all_keys, $key);


        }


        /**
         * Loads the saved merge tags for an array of MailChimp lists
         * 
         * @param array $list_array
         * @param string $key
         * @return array
         */
        public static function load_list_management_database_values($list_array, $key = NULL) {
            $merge_tags = array();
            foreach ($list_array as $list_id => $list_name) {
                $key              = self::build_merge_tags_key($list_id);
                $merge_tags[$key] = self::load_user_lists_defined_merge_tags($list_id);
            }

            $options = $merge_tags;

            return $options;


        }


        /**
         * Loads the saved merge tags for a specific MailChimp list
         * 
         * @param int $list_id
         * @return mixed
         */
        public static function load_user_lists_defined_merge_tags($list_id) {

            return get_option('imc_list_sync_tags_' . $list_id);


        }


        /**
         * Assembles a key to be used for accessing list merge tags
         * 
         * @param string $list_id
         * @return string
         */
        public static function build_merge_tags_key($list_id) {
            $key = I_Conf::OPT_SYNC_MERGE_TAGS . '_' . $list_id;

            return $key;


        }


        /**
         * Extracts the list_id from an assembled merge tag key
         * 
         * @param string $key
         * @return string
         */
        public static function extract_merge_tags_list_id($key) {
            $option  = I_Conf::OPT_SYNC_MERGE_TAGS . '_';
            $list_id = substr($key, strlen($option));

            return $list_id;


        }


        /**
         * Saves the list values in the database
         * 
         * @param array $values
         * @return array
         */
        public static function save_list_management_database_values($values) {
            $options = array();

            foreach ($values as $key => $value) {
                $options[$key] = array('value' => $value);
            }

            return I_Tools::save_options($options);


        }


    }


}

