<?php

namespace IMC\Library\Utility;

use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/**
 * Various Utility Functions to support the Integral Framework
 * 
 */
if (!class_exists('I_Tools')) {

    class I_Tools {


        public static $file_slug = NULL;
        public static $get_data  = NULL;
        public static $post_data = NULL;
        public static $ajax_data = NULL;

        /**
         * OPTIONS WRAPPERS
         * ************************************************************************ */


        /**
         * Loads the options for the given keys
         *
         * @staticvar array $values
         * @param array $all_keys
         * @param string $key
         * @return boolean or mixed
         */
        public static function load_options($all_keys = array(), $key = NULL, $refresh = FALSE) {
            static $values = array();

            if ($refresh && $key && isset($values[$key])) {
                unset($values[$key]);
            }

            //- If no key is passed then fetch all the provided key options and return the whole array
            if ($key === NULL) {

                if (empty($values) && is_array($all_keys) && !empty($all_keys)) {
                    foreach ($all_keys as $this_key) {
                        $values[$this_key] = get_option($this_key);
                    }
                }

                return $values;
            } else {

                //- Else if there is a key, then only fetch the key option and return the single value
                if ($key) {

                    if (!isset($values[$key])) {
                        $values[$key] = get_option($key);
                        return $values[$key];
                    } else if (isset($values[$key])) {
                        return $values[$key];
                    }
                }
            }

            return FALSE;


        }


        /**
         * Saves a set of key => value pairs either as an option or in a specified database table
         *
         * TODO: May want to rethink this function and remove the optional database table insert
         *
         * @global object $wpdb
         * @param array $options
         * @return array
         */
        public static function save_options($options) {
            $response = array();
            foreach ($options as $key => $values_array) {
                $db_table = (isset($values_array['db_table']) && $values_array['db_table']) ? $values_array['db_table'] : 'wp_options';
                switch ($db_table) {
                    case 'wp_options':
                        $response[$key] = update_option($key, $values_array['value']);
                        break;
                    default:
                        global $wpdb;
                        $wpdb->insert(
                            $db_table, $values_array['db_fields'], $values_array['db_field_types'] //- ie %s, %d, %f
                        );
                        $response[$key] = $wpdb->insert_id;
                }
            }

            return $response;


        }


        /**
         * Retrieves an option from a serialized option array
         * 
         * @param string $option_name -- the name of the option
         * @param string $option_key -- the array key where the specific value you want is stored
         */
        public static function get_option($option_name, $option_key) {
            $options = get_option($option_name);
            return $options[$option_key];


        }


        /**
         * Saves an option as a serialized array
         * 
         * @param string $option_name -- the name of the option
         * @param string $option_key -- the key to use
         * @param string $option_value -- the value to store
         */
        public static function set_option($option_name, $option_key, $option_value) {
            $options               = get_option($option_name);
            $options[$option_name] = $option_key;
            update_option($option_group, $options);
            return;


        }


        /**
         * Sets the file slug (aka filename without extension)
         * 
         * @param string $child_file_path
         */
        public static function _set_file_slug($child_file_path) {
            if (!$child_file_path) {
                $logger_message = 'Invalid file path in ' . __FUNCTION__ . '()';
                $logger_items   = array('function_args' => func_get_args());
                Logger::log_error($logger_message, $logger_items);
                wp_die(__('Invalid File Path Provided', 'integral-mailchimp'));
            }
            $file_array      = explode('.', basename($child_file_path));
            self::$file_slug = $file_array[0];


        }


        /**
         * Runs the initialization functions for the controller class
         * 
         * @param string $child_file_path
         * @param boolean $load_default_models
         * @param boolean $load_default_forms
         * @param boolean $load_default_utlities
         * @param boolean $load_default_dependencies
         */
        public static function initialize_controller($child_file_path, $load_default_models = TRUE, $load_default_forms = FALSE, $load_default_utlities = TRUE, $load_default_dependencies = TRUE, $check_security = TRUE) {

            self::_set_file_slug($child_file_path);

            if ($check_security) {
                //self::confirm_security_permissions();
            }

            $load_default_models ? self::load_models() : NULL;
            $load_default_forms ? self::load_forms() : NULL;
            $load_default_utlities ? self::load_utilities('integral-cache') : NULL;


        }


        /**
         * Sets the file slug (aka filename without extension)
         * 
         * @return string
         */
        public static function get_file_slug() {
            return self::$file_slug;


        }


        /**
         * LOAD CLASS INCLUDES
         * ************************************************************************ */


        /**
         * Load the requested utility classes
         * 
         * @param array $utilities
         */
        public static function load_utilities($utilities) {
            if ($utilities) {
                $utilities = (array) $utilities;
            } else {
                //- There are no default utilities
                $utilities = array();
            }

            foreach ($utilities as $utility) {
                $file_path = IMC_PLUGIN_PATH . I_Conf::UTILITY_PATH . $utility . '.utility.php';
                I_Conf::include_file($file_path);
            }


        }


        /**
         * Load the requested model classes
         * 
         * @param array $models
         */
        public static function load_models($models = NULL) {
            if ($models) {
                $models = (array) $models;
            } else {
                $models = array(self::$file_slug);
            }
            foreach ($models as $model) {
                $file_path = IMC_PLUGIN_PATH . I_Conf::MODEL_PATH . $model . '.model.php';
                I_Conf::include_file($file_path);
            }


        }


        /**
         * Load the requested form classes
         * 
         * @param array $forms
         */
        public static function load_forms($forms = NULL) {
            if ($forms) {
                $forms = (array) $forms;
            } else {
                $forms = array(self::$file_slug);
            }

            foreach ($forms as $form) {
                $file_path = IMC_PLUGIN_PATH . I_Conf::FORM_PATH . $form . '.form.php';
                I_Conf::include_file($file_path);
            }


        }


        /**
         * Load the requested view master class
         * 
         * @param string $view
         */
        public static function load_view_master($view = 'public') {
            $file_path = IMC_PLUGIN_PATH . I_Conf::VIEW_PATH . $view . '.view.php';
            I_Conf::include_file($file_path);


        }


        /**
         * $_GET & $_POST WRAPPERS
         * ************************************************************************ */


        /**
         * Returns a value from the stored $_GET array
         * 
         * @param string $key
         * @param string $default
         * @return string
         */
        public static function fetch_get_value($key, $default = null) {
            if ($key) {
                self::fetch_get_all();
                if (isset(self::$get_data[$key])) {
                    return self::$get_data[$key];
                }
            }

            return $default;


        }


        /**
         * Returns a value from the stored $_POST array
         * 
         * @param string $key
         * @param string $default
         * @return string
         */
        public static function fetch_post_value($key, $default = null) {
            if ($key) {
                self::fetch_post_all();
                if (isset(self::$post_data[$key])) {
                    return self::$post_data[$key];
                }
            }

            return $default;


        }


        /**
         * Returns a value from the stored $_REQUEST array
         * 
         * @param string $key
         * @param string $default
         * @return string
         */
        public static function fetch_ajax_value($key, $default = null) {
            if ($key) {
                self::fetch_ajax_post();
                if (isset(self::$ajax_data[$key])) {
                    return self::$ajax_data[$key];
                }
            }

            return $default;


        }


        /**
         * Returns cleaned values from the stored $_GET array
         * 
         * @return array
         */
        public static function fetch_get_all() {
            if (!self::$get_data) {
                self::$get_data = self::_clean_data_array($_GET);
            }

            return self::$get_data;


        }


        /**
         * Returns cleaned values from the stored $_POST array
         * 
         * @return array
         */
        public static function fetch_post_all() {
            if (!self::$post_data) {
                self::$post_data = self::_clean_data_array($_POST);
            }

            return self::$post_data;


        }


        /**
         * Recursive array cleaning function
         * 
         * @param array|object  $array
         * @return array|string
         */
        private static function _clean_data_array($array) {
            $cleaned = array();
            if (is_array($array) || is_object($array)) {
                foreach ($array as $key => $item) {
                    if (is_array($item) || is_object($item)) {
                        $cleaned[$key] = self::_clean_data_array($item);
                    } else {
                        $cleaned[$key] = sanitize_text_field($item);
                    }
                }

                return $cleaned;
            }

            return $array;


        }


        /**
         * Returns all values from the stored INPUT array
         * 
         * @return array
         */
        public static function fetch_ajax_post() {
            if (!self::$ajax_data) {
                $input = file_get_contents("php://input");
                parse_str($input, self::$ajax_data);
            }

            return self::$ajax_data;


        }


        /**
         * SECURITY WRAPPERS
         * ************************************************************************ */


        /**
         * Confirms the current user has permissions for the current action
         * 
         * TODO: At some point this needs to be more robust for allowing varying permissions
         */
        public static function confirm_security_permissions() {
            if (!current_user_can(apply_filters('imc_required_permission', I_Conf::ADMIN_PERMISSION))) {
                wp_die(__('Invalid Permissions.', 'integral-mailchimp'));
            }


        }


        /**
         * NOTE: Credit goes to the MailChimp development team for this funciton
         * 
         * MODIFIED VERSION of wp_create_nonce from WP Core. Core was not overridden to prevent problems when replacing 
         * something universally.
         *
         * Creates a cryptographic token tied to a specific action, user, and window of time.
         *
         * @param string $action Scalar value to add context to the nonce.
         * @return string The token.
         */
        public static function create_nonce($action = -1) {
            $user = wp_get_current_user();
            $uid  = (int) $user->ID;
            if (!$uid) {
                //-This filter is documented in wp-includes/pluggable.php
                $uid = apply_filters('nonce_user_logged_out', $uid, $action);
            }

            $token = 'INTEGRAL';
            $i     = wp_nonce_tick();

            return substr(wp_hash($i . '|' . $action . '|' . $uid . '|' . $token, 'nonce'), -12, 10);


        }


        public static function auth_nonce_key($salt = null) {
            if (is_null($salt)) {
                $salt = self::auth_nonce_salt();
            }
            return 'authentication' . md5(AUTH_KEY . $salt);


        }


        public static function auth_nonce_salt() {
            return md5(microtime() . $_SERVER['SERVER_ADDR']);


        }


        /**
         * NOTE: Credit goes to the MailChimp development team for this funciton
         * 
         * MODIFIED VERSION of wp_verify_nonce from WP Core. Core was not overridden to prevent problems when replacing 
         * something universally.
         *
         * Verify that correct nonce was used with time limit.
         *
         * The user is given an amount of time to use the token, so therefore, since the
         * UID and $action remain the same, the independent variable is the time.
         *
         * @param string $nonce Nonce that was used in the form to verify
         * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
         * @return bool Whether the nonce check passed or failed.
         */
        public static function verify_nonce($nonce, $action = -1) {
            $user = wp_get_current_user();
            $uid  = (int) $user->ID;
            if (!$uid) {
                $uid = apply_filters('nonce_user_logged_out', $uid, $action);
            }

            if (empty($nonce)) {
                return false;
            }

            $token = 'INTEGRAL';
            $i     = wp_nonce_tick();

            // Nonce generated 0-12 hours ago
            $expected = substr(wp_hash($i . '|' . $action . '|' . $uid . '|' . $token, 'nonce'), -12, 10);
            if (hash_equals($expected, $nonce)) {
                return 1;
            }

            // Nonce generated 12-24 hours ago
            $expected = substr(wp_hash(( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce'), -12, 10);
            if (hash_equals($expected, $nonce)) {
                return 2;
            }

            // Invalid nonce
            return false;


        }


        /**
         * Wraps the provided inline javascript with the correct wrappers
         * 
         * @param string $script
         * @param boolean $with_tag
         * @param boolean $with_ready
         * @return string
         */
        public static function format_inline_javascript($script, $with_tag = TRUE, $with_ready = TRUE) {

            $output = ($with_ready) ? "jQuery(document).ready( function($) {\n'use strict';\n {$script} \n});\n" : $script;
            $output = "//<![CDATA[ \n  {$output} \n //]]> \n";
            $output = ($with_tag) ? "<script type='text/javascript'>\n {$output} \n </script>" : $output;

            return $output;


        }


    }


}