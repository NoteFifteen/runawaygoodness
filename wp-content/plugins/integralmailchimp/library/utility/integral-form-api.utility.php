<?php

namespace IMC\Library\Utility;

use IMC\I_Conf;
use IMC\Library\Utility\I_Tools;
use IMC\Library\Framework\Logger;

if (!class_exists('Integral_Form_API')) {

    class Integral_Form_API {


        //- Form process status message keys
        const RESPONSE_MSG_KEY_UPDATED = 'updated_msg';
        const RESPONSE_MSG_KEY_ERROR   = 'error_msg';
        const FORM_ARRAY_INFO          = 'info';
        const FORM_ARRAY_FIELDS        = 'fields';
        const FORM_ARRAY_FIELDSETS     = 'fieldsets';
        const FORM_ARRAY_SUBMIT        = 'submit';
        const FORM_ARRAY_RENDERED      = 'rendered';
        const FORM_DEFAULT_FIELDSET    = 'default-fieldset';


        private static $is_ajax                       = FALSE;
        private static $form_data                     = array();
        private static $form_array                    = array();
        private static $form_output_array             = array();
        private static $form_fieldset_array           = array();
        private static $form_data_array               = array();
        private static $form_validation_array         = array();
        //
        private static $form_fieldset_wrapper_element = "ul";
        private static $form_field_wrapper_element    = "li";
        private static $form_fieldset_wrapper_class   = NULL;
        private static $form_field_wrapper_class      = NULL;
        private static $col_count                     = 0;


        /**
         * Assembles the form from its components
         * 
         * @param array $form_array
         */
        public static function build_form($form_array) {
            $form_output_array   = array();
            $form_data_array     = array();
            $form_fieldset_array = array();

            //- filters to allow changing any of the default form elements and classes
            //apply_filters('integral_form_fieldset_wrapper_element', self::$form_fieldset_wrapper_element, $;
            //- Extract the 3 major form aspects into seperate arrays
            if (isset($form_array[self::FORM_ARRAY_FIELDS]) && !empty($form_array[self::FORM_ARRAY_FIELDS])) {
                foreach ($form_array[self::FORM_ARRAY_FIELDS] as $field_name => $field) {

                    $field[self::FORM_ARRAY_RENDERED]['html'] ? $form_output_array[$field_name] = $field[self::FORM_ARRAY_RENDERED]['html'] : NULL;

                    $field[self::FORM_ARRAY_RENDERED]['data'] ? $form_data_array[$field_name] = $field[self::FORM_ARRAY_RENDERED]['data'] : NULL;

                    $field[self::FORM_ARRAY_RENDERED]['validation'] ? $form_validation_array[$field_name] = $field[self::FORM_ARRAY_RENDERED]['validation'] : NULL;
                }

                self::$form_validation_array = $form_validation_array;

                self::render_jquery_validation();
            }

            //- Puts each field into its corresponding fieldset
            if (isset($form_array[self::FORM_ARRAY_FIELDSETS]) && !empty($form_array[self::FORM_ARRAY_FIELDSETS])) {
                $i = 1;
                foreach ($form_array[self::FORM_ARRAY_FIELDSETS] as $fieldset_name => $fieldset) {
                    $fieldset_fields = array();
                    foreach ($fieldset['fieldset_fields'] as $field_name) {

                        //- check for placeholder fields. use $i in the name to make them unique, ex: ROW1, ROW2, etc.
                        if ($html = self::_parse_form_placeholders($field_name)) {
                            $fieldset_fields[$field_name . $i] = $html;
                            $i++;
                        } else {
                            if (isset($form_output_array[$field_name])) {
                                $fieldset_fields[$field_name] = $form_output_array[$field_name];
                                unset($form_output_array[$field_name]);
                            }
                        }
                    }
                    $form_fieldset_array[$fieldset_name] = array(
                        'legend' => $fieldset['fieldset_label'],
                        'fields' => $fieldset_fields
                    );
                }

                $fieldset_fields = array();
                foreach ($form_output_array as $field_name => $field) {
                    $fieldset_fields[$field_name] = $field;
                    unset($form_output_array[$field_name]);
                }

                $form_fieldset_array[self::FORM_DEFAULT_FIELDSET] = array(
                    'legend' => NULL,
                    'fields' => $fieldset_fields
                );

                self::$form_fieldset_array = $form_fieldset_array;

                self::render_fieldset_fields();

                self::render_fieldsets();
            }


        }


        /**
         * Checks the formset fields for any layout placeholders, like ROW or COL6
         * 
         * @param string $field_name
         * @return either an html string or false
         */
        private static function _parse_form_placeholders($field_name) {
            $html    = false;
            $pattern = "/^\|([a-zA-Z\d]+)\|([a-zA-Z-]*)/";
            if (preg_match($pattern, $field_name, $matches) === 1) {
                $html = self::_render_form_placeholder($matches[1], $matches[2]);
            }
            return $html;


        }


        /**
         * Takes a layout placeholder from the form fields array and converts it
         * into the appropriate Bootstrap html elements and classes
         * 
         * @param string field -- expects ROW or COL#, ex: COL6
         */
        private static function _render_form_placeholder($placeholder, $class = NULL) {
            switch (strtoupper($placeholder)):
                case "ROW":
                    $html = "<div class='row {$class}'>";
                    break;

                case "ENDROW":
                    $html            = "</div></div>";
                    self::$col_count = 0;
                    break;

                default:
                    $cols = substr($placeholder, 3);
                    $html = (self::$col_count == 0) ? "<div class='col-md-{$cols}'>" : "</div><div class='col-md-{$cols}'>";
                    self::$col_count++;
                    break;

            endswitch;
            return $html;


        }


        /**
         * Combines the rendered fields into one rendered item
         * 
         */
        public static function render_fieldset_fields() {
            if (is_array(self::$form_fieldset_array) && !empty(self::$form_fieldset_array)) {
                foreach (self::$form_fieldset_array as $fieldset_name => $fieldset) {

                    $rendered_fields = join("\n", $fieldset['fields']);

                    self::$form_fieldset_array[$fieldset_name]['render'] = self::_render_fieldset($fieldset, $fieldset_name, $rendered_fields);
                }
            }


        }


        /**
         * Wraps the fieldset in the proper tag element
         * 
         * @param array $fieldset_array
         * @param string $fieldset_name
         * @param string $rendered_fields
         * @return string
         */
        private static function _render_fieldset($fieldset_array, $fieldset_name, $rendered_fields) {
            if (is_array($fieldset_array) && !empty($fieldset_array)) {
                $fieldset_class            = self::$form_fieldset_wrapper_class ? ' class="' . self::$form_fieldset_wrapper_class . '" ' : NULL;
                $rendered_fields           = self::$form_fieldset_wrapper_element ? '<' . self::$form_fieldset_wrapper_element . $fieldset_class . ' >' . $rendered_fields . '</' . self::$form_fieldset_wrapper_element . '>' : $rendered_fields;
                $additional_fieldset_class = ($fieldset_name == 'default-fieldset') ? '' : ' well ';
                $legend                    = ($fieldset_name == 'default-fieldset') ? '' : "<legend>{$fieldset_array['legend']}</legend>";
                return "<fieldset class='{$fieldset_name} {$additional_fieldset_class}'>{$legend}{$rendered_fields}</fieldset>";
            }


        }


        /**
         * Combines the rendered fieldsets into one rendered item
         * 
         */
        public static function render_fieldsets() {
            if (is_array(self::$form_fieldset_array) && !empty(self::$form_fieldset_array)) {
                $rendered_fieldsets = array();
                foreach (self::$form_fieldset_array as $fieldset_name => $fieldset) {
                    $rendered_fieldsets[] = $fieldset['render'];
                }

                self::$form_fieldset_array['render'] = join("\n", $rendered_fieldsets);
            }


        }


        /**
         * Combines all of the validation rules and messages into one array 
         * 
         */
        public static function render_jquery_validation() {
            if (is_array(self::$form_validation_array) && !empty(self::$form_validation_array)) {
                $rules        = (object) array();
                $imc_messages = (object) array();

                foreach (self::$form_validation_array as $field_name => $validation) {
                    if (is_array($validation) && !empty($validation)) {
                        if (is_object($validation['rules']) && !empty($validation['rules'])) {
                            foreach ($validation['rules'] as $key => $this_rule) {
                                $rules->$key = $this_rule;
                            }
                        }
                        if (is_object($validation['messages']) && !empty($validation['messages'])) {
                            foreach ($validation['messages'] as $key => $this_message) {
                                $imc_messages->$key = $this_message;
                            }
                        }
                    }
                }

                //- We need to create a PHP object that can be directly converted to Javascript via json_encode()
                //- to match what the JQuery Validation plugin is expecting (http://jqueryvalidation.org/validate)

                self::$form_validation_array           = (object) array();
                self::$form_validation_array->rules    = $rules;
                self::$form_validation_array->messages = $imc_messages;
            }


        }


        public static function get_form_output_array() {
            if (self::$form_output_array && is_array(self::$form_output_array)) {
                return self::$form_output_array;
            } else {
                //- Log warning
                return array();
            }


        }


        public static function get_form_data_array() {
            if (self::$form_data_array && is_array(self::$form_data_array)) {
                return self::$form_data_array;
            } else {
                //- Log warning
                return array();
            }


        }


        public static function get_form_fieldset_array($item = NULL) {
            if (self::$form_fieldset_array && is_array(self::$form_fieldset_array)) {
                if ($item && isset(self::$form_fieldset_array[$item])) {
                    return self::$form_fieldset_array[$item];
                } else {
                    return self::$form_fieldset_array;
                }
            } else {
                //- Log warning
                return array();
            }


        }


        public static function get_form_validation_array() {
            if (self::$form_validation_array && is_object(self::$form_validation_array)) {
                return self::$form_validation_array;
            } else {
                //- Log warning
                return array();
            }


        }


        public static function get_form_submit() {
            if (isset(self::$form_array[self::FORM_ARRAY_SUBMIT]) && is_array(self::$form_array[self::FORM_ARRAY_SUBMIT])) {
                return self::build_form_field(self::$form_array[self::FORM_ARRAY_SUBMIT]);
            }


        }


        public static function build_form_fields(&$form_array, $form_data, $is_ajax = TRUE) {
            self::$form_data = $form_data;
            self::$is_ajax   = $is_ajax;

            if (isset($form_array[self::FORM_ARRAY_FIELDS]) && !empty($form_array[self::FORM_ARRAY_FIELDS])) {
                foreach ($form_array[self::FORM_ARRAY_FIELDS] as $key => $field) {

                    $output = self::build_form_field($field);

                    $form_array[self::FORM_ARRAY_FIELDS][$key][self::FORM_ARRAY_RENDERED]['html']       = $output['html'];
                    $form_array[self::FORM_ARRAY_FIELDS][$key][self::FORM_ARRAY_RENDERED]['data']       = $output['data'];
                    $form_array[self::FORM_ARRAY_FIELDS][$key][self::FORM_ARRAY_RENDERED]['validation'] = $output['validation'];
                }
            }

            self::$form_array = $form_array;


        }


        public static function build_form_field($field) {

            $conditions      = '';
            $conditional     = '';
            $placeholder     = '';
            $data_attributes = '';

            //- Ensure the field doesn't get displayed if it's admin only
            if (!is_admin() && isset($field['field_admin_only']) && $field['field_admin_only'] == TRUE) {
                return NULL;
            }

            $html       = array();
            $validate   = array();
            $multiple   = FALSE;
            $no_wrapper = array('submit', 'hidden', 'nonce', 'html');

            $field       = self::merge_default_field_array($field);
            $form_data   = self::$form_data;
            $field_value = ((isset($form_data[$field['field_name']]) && $form_data[$field['field_name']]) ?
                    $form_data[$field['field_name']] : ((isset($field['field_value'])) ?
                        $field['field_value'] : $field['field_default']));

            $required = ((isset($field['required']) && $field['required'] == TRUE) ||
                (isset($field['field_validation']) && is_array($field['field_validation']) && isset($field['field_validation']['required']))) ? "required" : '';

            if (is_admin()) {
                $required = (isset($field['field_required_admin']) && $field['field_required_admin'] == TRUE) ? 'required' : $required;
            }

            $disabled = (isset($field['field_disabled']) && $field['field_disabled'] == TRUE) ? 'disabled' : NULL;

            $prefix = (isset($field['field_prefix']) && $field['field_prefix']) ? $field['field_prefix'] : NULL;

            $suffix = (isset($field['field_suffix']) && $field['field_suffix']) ? $field['field_suffix'] : NULL;

            $public = (is_admin()) ? 'admin' : 'public';

            if ($field['field_type'] == 'checkbox') {
                $field['field_wrapper_element'] = 'div';
            }

            $field_wrapper_element = isset($field['field_wrapper_element']) ? $field['field_wrapper_element'] : 'li';

            $field_wrapper_class = isset($field['field_wrapper_class']) ? $field['field_wrapper_class'] :
                (isset(self::$form_field_wrapper_class) ? self::$form_field_wrapper_class : '');

            //- Process optional data attributes
            if (array_key_exists('field_data_attributes', $field)) {
                foreach ($field['field_data_attributes'] as $key => $value) {
                    $data_attributes .= "data-{$key}='{$value}'";
                }
            }

            //- BEGIN BUILDING HTML
            //-----------------------------------------
            //
            //- Start FIELD WRAPPER ELEMENT
            $html[] = (!in_array($field['field_type'], $no_wrapper)) ? "<{$field_wrapper_element} class='{$field_wrapper_class} {$conditional} {$public} {$required}' {$conditions}>" : '';


            //- Show FIELD LABEL
            $html[] = ($field['field_label']) ? "<label for='{$field['field_name']}' class='{$field['field_label_class']} {$required}'>{$field['field_label']}</label>" : '';


            switch ($field['field_type']) {

                case 'legend':
                    $html[] = "<legend>{$field_value}</legend>";
                    break;

                case 'html':
                    $html[] = "{$prefix}<{$field['field_container']} id='{$field['field_id']}' class='{$field['field_class']}'>{$field['field_content']}</{$field['field_container']}>{$suffix}";
                    break;

                case 'iframe':
                    $html[] = "{$prefix}<{$field['field_type']} width='100%' id='{$field['field_id']}'  class='{$field['field_class']}' srcdoc='{$field['field_content']}'></{$field['field_type']}>{$suffix}";
                    break;

                case 'date':
                    $size      = isset($field['field_size']) && $field['field_size'] ? "size='{$field['field_size']}'" : '';
                    $maxlength = isset($field['field_size']) && $field['field_size'] ? "maxlength='{$field['field_size']}'" : '';
                    $html[]    = "{$prefix}<input data-provide='datepicker' type='text' name='{$field['field_name']}' id='{$field['field_id']}' class='{$field['field_class']} {$public} {$required}' {$placeholder} {$size} {$maxlength} {$disabled} tabindex='{$field['tabindex']}' value='{$field_value}' {$data_attributes}/>{$suffix}";

                    break;
                case 'email':
                case 'tel':
                case 'text':
                    $size        = isset($field['field_size']) && $field['field_size'] ? "size='{$field['field_size']}'" : '';
                    $maxlength   = isset($field['field_maxlength']) && $field['field_maxlength'] ? "maxlength='{$field['field_maxlength']}'" : '';
                    $field_value = esc_html($field_value);
                    $html[]      = "{$prefix}<input type='{$field['field_type']}' name='{$field['field_name']}' id='{$field['field_id']}' class='{$field['field_class']} {$public} {$required}' placeholder='{$field['field_placeholder']}' {$size} {$maxlength} {$disabled} tabindex='{$field['tabindex']}' value='{$field_value}' {$data_attributes}/>{$suffix}";
                    break;

                case 'display':
                    $html[] = "<div id='{$field['field_id']}' class='{$field['field_class']} {$public} {$data_attributes}'>{$field_value}</div>";
                    break;

                case 'password':
                    $html[] = "{$prefix}<input type='password' name='{$field['field_name']}' id='{$field['field_name']}' class='{$field['field_class']} {$public} {$required}' {$placeholder} {$disabled} tabindex='{$field['tabindex']}' value='{$field_value}' {$data_attributes}/>{$suffix}";
                    break;

                case 'hidden':
                    $html[] = "{$prefix}<input type='hidden' name='{$field['field_name']}' id='{$field['field_name']}' {$disabled} value='{$field_value}' {$data_attributes}/>{$suffix}";
                    break;

                case 'link':
                    $html[] = "{$prefix}<a href='{$field['field_url']}' class='{$field['field_class']} {$public}' rel='{$field['field_rel']}' title='{$field['field_link_title']}' target='{$field['field_link_target']}' {$data_attributes}/>{$field['field_value']}</a>{$suffix}";
                    break;

                case 'time':
                    $html[] = "{$prefix}<input type='text' name='{$field['field_name']}' id='{$field['field_id']}' {$disabled} value='{$field_value}' size='20' tabindex='{$field['tabindex']}' style='' {$data_attributes}/>{$suffix}";
                    $script = '$("#' . $field["field_id"] . '").timeEntry({ampmPrefix: " ", ampmNames: ["am", "pm"],spinnerImage: "timeEntry2.png", spinnerSize: [20, 20, 0]});';
                    $html[] = I_Tools::format_inline_javascript($script);
                    break;

                case 'textarea':
                    $html[] = "{$prefix}<textarea name='{$field['field_name']}' id='{$field['field_id']}' cols='60' rows='4' style='width:97%' tabindex='{$field['tabindex']}' class='{$required}' {$disabled} {$data_attributes}>{$field_value}</textarea>{$suffix}";
                    break;

                case 'multi-select':
                    $multiple = TRUE;
                case 'select':
                    $multiple = ($multiple) ? 'multiple' : '';
                    $html[]   = "{$prefix}<select {$multiple} size='{$field['field_size']}' tabindex='{$field['tabindex']}' class='{$field['field_class']} {$public} {$required} {$multiple}' {$disabled} name='{$field['field_name']}[]' id='{$field['field_id']}' {$data_attributes}>";
                    //- check if we were passed any select options
                    if (is_array($field['field_options'])) {
                        $options = array();
                        $html[]  = join("\n", self::_map_select_options($field['field_options'], $options, $field_value, $multiple));
                    }
                    $html[] = "</select>{$suffix}";

                    $editor_attributes             = array();
                    ($multiple) ? $editor_attributes['multiple'] = $multiple : NULL;

                    $editor_attributes = (!empty($editor_attributes)) ? $editor_attributes : NULL;
                    break;

                case 'radio':
                    foreach ($field['field_options'] as $option_key => $option_value) {
                        $checked = ($field_value == $option_key) ? "checked='checked'" : '';
                        $html[]  = "<input type='radio' name='{$field['field_name']}' tabindex='{$field['tabindex']}' value='{$option_key}' {$checked} {$data_attributes} /> {$option_value}";
                    }
                    break;

                case 'checkbox':
                    $format = '<%8$s class="%9$s"><input type="checkbox" class="checkbox %1$s" id="%2$s" name="%3$s" tabindex="%9$s" value="%6$s" %4$s %5$s> <label for="%2$s">  %7$s</label></%8$s>';
                    if (count($field['field_options'])) {
                        $i = 0;
                        foreach ($field['field_options'] as $value => $label) {
                            $checked = in_array($value, (array) $field['field_value']) ? 'checked' : '';
                            $html[]  = sprintf($format, $field['field_class'], $field['field_id'] . '_' . $i, $field['field_name'] . '[]', $checked, $data_attributes, $value, $label, $field['field_option_wrapper_element']? : 'li', $field['field_option_wrapper_class'], $field['tabindex']);
                            $i++;
                        }
                    } else {
                        $html[] = sprintf($format, $field['field_class'], $field['field_id'], $field['field_name'], checked($field_value, 1, false), $data_attributes, 1, '', $field['field_option_wrapper_element']? : 'li', $field['field_option_wrapper_class'], $field['tabindex']);
                    }
                    break;

                case 'map':
                    $latitude  = $field['field_map_latitude'];
                    $longitude = $field['field_map_longitude'];
                    if ($latitude == '' || $longitude == '') {
                        $html[] = "Map not currently available.";
                    } else {
                        wp_enqueue_script('google-maps', 'http://maps.google.com/maps/api/js?sensor=false', array(), FALSE, TRUE);
                        $html[] = "<div id='gmap' class='{$field['field_class']}'></div>";
                        $script = "gmapV3({$latitude}, {$longitude});addMarker({$latitude}, {$longitude});";
                        $html[] = I_Tools::format_inline_javascript($script);
                    }
                    break;

                case 'button':
                    $html[] = "{$prefix}<button id='{$field['field_id']}' name='{$field['field_name']}' class='{$field['field_class']} {$public}' {$disabled} tabindex='{$field['tabindex']}' value='{$field_value}' {$data_attributes}>{$field_value}</button>{$suffix}";
                    break;

                case 'nonce':
                    $nonce_field = wp_nonce_field($field_value, I_Conf::FORM_NONCE, true, false);
                    $html[]      = $nonce_field;
                    break;

                case 'submit':
                    $html[] = "{$prefix}<p class='submit'><input type='submit' id='{$field['field_id']}' name='{$field['field_name']}' class='{$field['field_class']} {$public}' {$disabled} tabindex='{$field['tabindex']}' value='{$field_value}' /></p>{$suffix}";
                    return join("\n", $html);
                    break;
            }

            //- Show FIELD DESCRIPTION
            $html[] = ($field['field_description']) ? "<p class='help-block'>{$field['field_description']}</p>" : '';

            //- End FIELD WRAPPER ELEMENT
            $html[] = (!in_array($field['field_type'], $no_wrapper)) ? "</{$field_wrapper_element}>" : '';


            //- Parse Validation
            $validation = array();

            if (isset($field['field_validation']) && is_array($field['field_validation']) && !empty($field['field_validation'])) {
                $rules        = array();
                $imc_messages = array();

                $field_name = in_array($field['field_type'], array('select', 'multi-select')) ? $field['field_name'] . '[]' : $field['field_name'];

                $validate = self::parse_jquery_validation($field['field_validation']);

                $rules[$field_name]        = (object) $validate['rules'];
                $imc_messages[$field_name] = (object) $validate['messages'];

                $validation['rules']    = (object) $rules;
                $validation['messages'] = (object) $imc_messages;
            }

            //- Assemble Output array
            $output['html']       = join("\n", $html);
            $output['data']       = $field_value;
            $output['validation'] = $validation;

            return $output;


        }


        private static function _map_select_options($options_array, $options, $field_value, $multiple) {
            foreach ($options_array as $option_key => $option_value) {
                //- check for opt group
                if (is_array($option_value)) {
                    if (!empty($option_value)) {
                        $options[]     = "<optgroup label='{$option_key}'>";
                        $group_options = array();
                        $options[]     = join("\n", self::_map_select_options($option_value, $group_options, $field_value, $multiple));
                    }
                } else {

                    if ($multiple) {
                        $selected = (in_array($option_key, (array) $field_value)) ? 'selected' : '';
                    } else {
                        $selected = ($field_value == $option_key) ? 'selected' : '';
                    }
                    $options[] = "<option value='{$option_key}' {$selected}>{$option_value}</option>";
                }
            }

            return $options;


        }


        /**
         * Converts the incoming validation syntax to match what the JQuery Validation plugin needs
         * 
         * Reference:
         *  http://jqueryvalidation.org/documentation
         * 
         * Here are the the supported validation methods:
         * - required
         * - remote             http://jqueryvalidation.org/remote-method
         * - minlength
         * - maxlength
         * - rangelength
         * - min
         * - max
         * - range
         * - email              http://jqueryvalidation.org/email-method
         * - url
         * - date
         * - dateISO
         * - number
         * - digits
         * - creditcard
         * - equalTo
         * 
         * 
         * @param array $input_validation_array
         * @return array
         */
        public static function parse_jquery_validation($input_validation_array) {
            $output_validation_array             = array();
            $output_validation_array['rules']    = array();
            $output_validation_array['messages'] = array();

            if ($input_validation_array && is_array($input_validation_array) && !empty($input_validation_array)) {

                foreach ($input_validation_array as $validator => $specs_array) {
                    switch ($validator) {
                        case 'required':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This field is required';
                            $rule = true;

                            break;
                        case 'remote':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This field is invalid';
                            //- FIXME - complete this implementation according to http://jqueryvalidation.org/remote-method
                            $rule = true;

                            break;
                        case 'minLength':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This value is too short';
                            $rule = $specs_array['length'];

                            break;
                        case 'maxLength':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This value is too long';
                            $rule = $specs_array['length'];

                            break;
                        case 'acceptance':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This field must be accepted';
                            $rule = true;

                            break;
                        case 'rangeLength':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This value is not the correct character length';
                            $rule = array($specs_array['min_length'], $specs_array['max_length']);

                            break;
                        case 'min':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This value is too high';
                            $rule = $specs_array['value'];

                            break;
                        case 'max':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This value is too low';
                            $rule = $specs_array['value'];

                            break;
                        case 'range':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This value is out of range';
                            $rule = array($specs_array['min_value'], $specs_array['max_value']);

                            break;
                        case 'email':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This email is invalid';
                            $rule = true;

                            break;
                        case 'url':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This url is invalid';
                            $rule = true;

                            break;
                        case 'date':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This date is invalid';
                            $rule = true;

                            break;
                        case 'dateISO':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This date is invalid';
                            $rule = true;

                            break;
                        case 'number':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This number is invalid';
                            $rule = true;

                            break;
                        case 'digits':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This number is invalid';
                            $rule = true;

                            break;
                        case 'creditcard':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This credit card number is invalid';
                            $rule = true;

                            break;
                        case 'equalTo':
                            $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This value does not match';
                            $rule = $specs_array['field_selector'];

                            break;

                        /* The JQuery Valdiation does not have these
                          case 'length':
                          $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This value is not the correct character length';
                          $rule = $specs_array['length'];

                          break;

                          case 'oneOf':
                          $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This is an invalid value';
                          $rule = (array) $specs_array['items'];

                          break;
                          case 'pattern':
                          $msg  = (isset($specs_array['message']) && $specs_array['message']) ? $specs_array['message'] : 'This is an invalid value';
                          $rule = (in_array($specs_array['pattern_name'], array('email', 'number', 'url', 'digits'))) ? "'{$specs_array['pattern_name']}'" : $specs_array['pattern_name'];

                          break; */
                    }

                    $output_validation_array['rules'][strtolower($validator)]    = $rule;
                    $output_validation_array['messages'][strtolower($validator)] = $msg;
                }
            }

            return $output_validation_array;


        }


        public static function merge_default_field_array($field) {
            $merged = wp_parse_args($field, self::build_default_field_array());
            return $merged;


        }


        public static function build_default_field_array() {
            $default = array(
                'required' => NULL
                , 'field_order' => NULL
                , 'field_prefix' => NULL
                , 'field_suffix' => NULL
                , 'field_name' => NULL
                , 'field_id' => NULL
                , 'field_type' => NULL
                , 'field_fieldset' => NULL
                , 'field_label' => NULL
                , 'field_label_class' => NULL
                , 'field_default' => NULL
                , 'field_disabled' => NULL
                , 'field_content' => NULL
                , 'field_class' => NULL
                , 'field_editor_class' => NULL
                , 'field_required' => NULL
                , 'field_required_admin' => NULL
                , 'field_conditional' => NULL
                , 'field_condition_type' => NULL
                , 'field_condition_action' => NULL
                , 'field_condition_required' => NULL
                , 'field_conditions' => NULL
                , 'field_description' => NULL
                , 'field_options' => NULL
                , 'field_container' => NULL
                , 'field_value' => NULL
                , 'field_size' => NULL
                , 'field_cols' => NULL
                , 'field_rows' => NULL
                , 'field_maxlength' => NULL
                , 'field_admin_only' => NULL
                , 'field_placeholder' => NULL
                , 'field_url' => NULL
                , 'field_rel' => NULL
                , 'field_link_title' => NULL
                , 'field_link_target' => NULL
                , 'field_map_latitude' => NULL
                , 'field_map_longitude' => NULL
                , 'field_validation' => NULL
                , 'field_option_wrapper_class' => NULL
                , 'field_option_wrapper_element' => NULL
                , 'tabindex' => NULL
            );

            return $default;


        }


        /**
         * Generate a json object with the Type, Action & Rules for this field
         * to be conditionally displayed.
         *
         * @param array $field
         * @param array $form_data
         * @return json object
         */
        public static function evaluate_conditions($field) {
            $conditions['type']     = $field['field_condition_type'];
            $conditions['action']   = $field['field_condition_action'];
            $conditions['required'] = $field['field_condition_required'];
            $i                      = 0;

            foreach ($field['field_conditions'] as $condition) {
                $rules[$i] = $condition;
                $i++;
            }

            $conditions['rules'] = $rules;
            return json_encode($conditions);


        }


        /**
         * Allow HTML form fields to display dynamic data by
         * putting variable names in braces, ie: {acct_id}
         * @param type $content
         */
        public static function parse_placeholders($content) {
            $form_data = self::$form_data;
            $pattern   = "/\{([a-zA-Z_]+)\}/i";

            $content = preg_replace_callback(
                $pattern, function($matches) use ($form_data) {
                return $form_data[$matches[1]];
            }, $content);

            return $content;


        }


        public static function set_form_data($form_data) {
            if (is_array($form_data)) {
                self::$form_data = $form_data;
            }


        }


        public static function render_jquery_validate_script($form_selector, $message_selector, $rules, $imc_messages, $full_selector = FALSE) {

            if (!$full_selector) {
                $form_selector    = "$('{$form_selector}')";
                $message_selector = "'{$message_selector}'";
            }

            $script = <<<SCRIPT
                {$form_selector}.validate({
                    debug: false,
                    errorLabelContainer: {$message_selector},
                    wrapper: 'li',
                    rules: {$rules},
                    messages: {$imc_messages},
                    invalidHandler: function(event, validator) {
                        {$message_selector}.removeClass('updated')
                            .addClass('error')
                            .css('opacity', 1);
                    }
                });
                
SCRIPT;

            return $script;


        }


        /**
         * Adds a new item to the front of an array
         * - Mainly used for adding a default empty item for a non-required form select
         * 
         * @param array $options
         * @param array $empty_option
         */
        public static function add_empty_option(&$options, $empty_option) {
            if (is_array($options) && is_array($empty_option)) {
                $options = $empty_option + $options;
            }


        }


        public static function return_with_empty_option($options, $empty_option) {
            if (is_array($options) && is_array($empty_option)) {
                $options = $empty_option + $options;
                return $options;
            }


        }


    }


}


