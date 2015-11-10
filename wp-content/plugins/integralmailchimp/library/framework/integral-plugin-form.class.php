<?php

namespace IMC\Library\Framework;

use IMC\I_Conf;
use IMC\Library\Utility\Integral_Form_API;
use IMC\Library\Framework\Logger;

/* * *
 * This is the core Integral Plugin Form class 
 * 
 * !!!!! This class should only be extended !!!!! 
 * 
 * This plugin is responsible for:
 * -- providing essential form level utilities for each plugin form child class
 * 
 */
if (!class_exists('Integral_Plugin_Form')) {

    class Integral_Plugin_Form {


        protected static $form_array            = NULL;
        private static $form_data               = NULL;
        private static $form_view_path          = NULL;
        private static $form_action             = NULL;
        //
        private static $post_data               = NULL;
        private static $get_data                = NULL;
        private static $ajax_data               = NULL;
        private static $primary_fields          = NULL;
        private static $keyed_primary_fields    = NULL;
        private static $form_info               = NULL;
        //
        private static $form_prerender_items    = NULL;
        private static $form_is_ajax            = NULL;
        private static $default_form_view_items = NULL;
        public static $active_form_tab          = 0;


        final private function __construct() {
            wp_die('THIS CLASS MAY NOT BE INSTATIATED [' . __CLASS__ . ' - ' . __FILE__ . ']');


        }


        /**
         * Intializes the master form class
         * 
         * @param boolean $form_is_ajax
         */
        public static function initialize_forms($form_is_ajax = TRUE) {
            //- Sets the $form_prerender_items variable to an object
            self::$form_prerender_items = (object) array();

            //- Stores whether or not this form will be an ajax form
            self::$form_is_ajax = $form_is_ajax;

            //- Sets up any values that may be needed during the form rendering ('i.e. the WordPress ajax submit path)
            self::_set_default_form_view_items();


        }


        public static function set_form_view($view_slug) {
            self::$form_view_path = IMC_PLUGIN_PATH . I_Conf::VIEW_FORM_PATH . $view_slug . '.phtml';


        }


        public static function set_form_action($form_action) {
            self::$form_action                  = $form_action;
            self::$form_prerender_items->action = $form_action;


        }


        public static function set_post_data($post_data) {
            self::$post_data = $post_data;


        }


        public static function set_get_data($get_data) {
            self::$get_data = $get_data;


        }


        public static function set_ajax_data($ajax_data) {
            self::$ajax_data = $ajax_data;


        }


        public static function get_form_submit() {
            return Integral_Form_API::get_form_submit();


        }


        private static function _set_form_info() {
            self::$form_info = self::$form_array[Integral_Form_API::FORM_ARRAY_INFO];


        }


        public static function get_form_info($item = FALSE) {
            return ($item && isset(self::$form_info[$item])) ? self::$form_info[$item] : self::$form_info;


        }


        public static function is_form_ajax() {
            return self::$form_is_ajax;


        }


        public static function get_form_array() {
            return self::$form_array;


        }


        /**
         * Run the essential default setup actions on the form
         * 
         */
        public static function run_default_form_setup() {

            self::_set_form_info();

            self::_merge_default_fields();

            self::_prepare_form_field_array();

            self::_prepare_form_fieldset_array();

            self::run_final_form_setup();

            $filter_name      = 'integral-modify-form-phase-one_' . self::get_form_info('form_name');
            self::$form_array = apply_filters($filter_name, self::$form_array);


        }


        /**
         * Create an override of this function in the child class for 
         * last minute changes to the form array before rendering begins
         * 
         */
        public static function run_final_form_setup() {
            
        }


        /**
         * Get the fields initially declared by the load_<form-name>_form() function in the child class
         * 
         * @param boolean $keyed_by_name
         * @return array
         */
        public static function get_primary_fields($keyed_by_name = TRUE) {
            if (!self::$keyed_primary_fields && $keyed_by_name) {
                foreach (self::$primary_fields as $field) {
                    self::$keyed_primary_fields[$field['field_name']] = $field;
                }
            }
            return $keyed_by_name ? self::$keyed_primary_fields : self::$primary_fields;


        }


        /**
         * Merge the essential default fields into the existing form array
         * 
         */
        private static function _merge_default_fields() {

            //- Save the primary form fields separately before we merge the default fields in
            self::$primary_fields = self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDS];

            self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDSETS][] = array(
                'fieldset_name' => Integral_Form_API::FORM_DEFAULT_FIELDSET
                , 'fieldset_label' => ''
                , 'fieldset_fields' => array()
            );

            self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDS][] = array(
                'field_order' => NULL
                , 'field_name' => 'integral-form'
                , 'field_type' => 'hidden'
                , 'field_value' => 1
            );

            self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDS][] = array(
                'field_order' => NULL
                , 'field_name' => 'action'
                , 'field_type' => 'hidden'
                , 'field_value' => self::$form_action
            );

            self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDS][] = array(
                'field_order' => NULL
                , 'field_name' => 'wp-nonce'
                , 'field_type' => 'nonce'
                , 'field_value' => self::$form_action
            );

            self::$form_array[Integral_Form_API::FORM_ARRAY_SUBMIT] = array(
                'field_order' => NULL
                , 'field_name' => 'formsubmit'
                , 'field_id' => 'formsubmit'
                , 'field_type' => 'submit'
                , 'field_value' => __('Submit', 'integral-mailchimp')
                , 'field_class' => 'btn button-primary clearfix'
            );


        }


        /**
         * Swap out the default numeric field array indexes with the corresponding field_name
         * 
         */
        private static function _prepare_form_field_array() {
            if (isset(self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDS]) && is_array(self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDS])) {
                foreach (self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDS] as $key => $field) {
                    if (isset($field['field_name']) && $field['field_name']) {
                        self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDS][$field['field_name']] = $field;
                        unset(self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDS][$key]);
                    }
                }
            }


        }


        /**
         * Swap out the default numeric field array indexes with the corresponding fieldset_name
         * 
         */
        private static function _prepare_form_fieldset_array() {
            if (isset(self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDSETS]) && is_array(self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDSETS])) {
                foreach (self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDSETS] as $key => $fieldset) {
                    if (isset($fieldset['fieldset_name']) && $fieldset['fieldset_name']) {
                        self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDSETS][$fieldset['fieldset_name']] = $fieldset;
                        unset(self::$form_array[Integral_Form_API::FORM_ARRAY_FIELDSETS][$key]);
                    }
                }
            }


        }


        private static function _set_default_form_view_items() {
            $ajax_url = admin_url('admin-ajax.php');

            self::$default_form_view_items = compact('ajax_url');


        }


        /**
         * Populate the form data array
         * 
         * @param array $form_data
         */
        public static function populate_form_values($form_data) {
            self::$form_data = $form_data;


        }


        /**
         * Assembles the individual form fields
         * 
         */
        public static function build_form_fields() {
            Integral_Form_API::build_form_fields(self::$form_array, self::$form_data, self::$form_is_ajax);

            //- TODO: Implement Hook here to allow add-ons to modify the third phase of the form


        }


        /**
         * Assembles the entire form into it's renderable pieces
         * 
         */
        public static function build_form() {
            self::$form_info = self::$form_array[Integral_Form_API::FORM_ARRAY_INFO];

            Integral_Form_API::build_form(self::$form_array);

            self::$form_prerender_items->fieldsets  = Integral_Form_API::get_form_fieldset_array('render');
            self::$form_prerender_items->validation = Integral_Form_API::get_form_validation_array();
            self::$form_prerender_items->submit     = Integral_Form_API::get_form_submit();


        }


        /**
         * Renders the final form from it's rendered pieces
         * 
         * @return string
         */
        public static function render_form($additional_form_view_items = array()) {

            if (file_exists(self::$form_view_path)) {

                extract((array) self::$default_form_view_items);

                extract((array) self::$form_info);
                //- generate actual form html
                $form = self::assemble_form_html();

                extract((array) self::$form_prerender_items);

                extract((array) $additional_form_view_items);


                $rules        = (!empty($validation->rules)) ? $validation->rules : (object) array();
                $imc_messages = (!empty($validation->messages)) ? $validation->messages : (object) array();

                $rules        = json_encode($rules);
                $imc_messages = json_encode($imc_messages);

                $form_action = self::$form_is_ajax ? $ajax_url : $form_action;

                ob_start();
                include(self::$form_view_path);
                $output = ob_get_contents();
                ob_end_clean();

                return $output;
            } else {
                //- TODO - Log a warning?
                $logger_message = 'The form view file does not exist in ' . __FUNCTION__ . '()';
                $logger_items   = array('form_view_path' => self::$form_view_path, 'backtrace' => debug_backtrace());
                Logger::log_warning($logger_message, $logger_items);
            }


        }


        public static function assemble_form_html() {
            $form_wrapper_id      = $form_wrapper_class   = $form_wrapper_element = $form_name            = $form_method          = $form_action          = $fieldsets            = $submit               = NULL;

            extract((array) self::$form_info);
            extract((array) self::$form_prerender_items);
            $active             = (self::$active_form_tab == 0) ? 'active' : NULL;
            $form_wrapper_id    = isset($form_wrapper_id) ? $form_wrapper_id : NULL;
            $form_wrapper_class = isset($form_wrapper_class) ? $form_wrapper_class : NULL;

            self::$active_form_tab++;
            $form_wrapper_open  = isset($form_wrapper_element) ? "<{$form_wrapper_element} class='{$form_wrapper_class} {$active}' id='{$form_wrapper_id}'>" : '';
            $form_wrapper_close = isset($form_wrapper_element) ? "</{$form_wrapper_element}>" : '';

            $form = <<<FORM
                    {$form_wrapper_open}
                    <form name='{$form_name}' class='{$form_name}' method='{$form_method}' action='{$form_action}'>
                    {$fieldsets}
                    {$submit}
                    </form>
                    {$form_wrapper_close}
FORM;
            return $form;


        }


        /**
         * Confirms a valid form submission based on the form's hidden action field
         * 
         * @param string $method
         * @return boolean
         */
        public static function confirm_form_submission($method = 'post') {

            switch ($method) {
                case 'post':
                    $form_data = (array) self::$post_data;
                    break;
                case 'ajax':
                    $form_data = (array) self::$ajax_data;
                    break;
                case 'get':
                    $form_data = (array) self::$get_data;
                    break;
            }

            if (empty($form_data)) {
                return FALSE;
            }

            if (isset($form_data['action'])) {
                if ($form_data['action'] == self::$form_action) {
                    return TRUE;
                } else {
                    $logger_message = 'The incoming form_data action does not match the assigned form_action in ' . __FUNCTION__ . '()';
                    $logger_items   = array('form_data_action' => $form_data['action'], 'self_form_action' => self::$form_action);
                    Logger::log_error($logger_message, $logger_items);
                }
            }

            return FALSE;


        }


        /**
         * Validates the form's nonce field
         * 
         */
        public static function check_nonce() {
            check_admin_referer(self::$form_action, I_Conf::FORM_NONCE);


        }


        /**
         * Validates the form's nonce field for ajax
         * 
         */
        public static function check_ajax_nonce() {
            return wp_verify_nonce(I_Conf::FORM_NONCE, self::$form_action);


        }


    }


}

