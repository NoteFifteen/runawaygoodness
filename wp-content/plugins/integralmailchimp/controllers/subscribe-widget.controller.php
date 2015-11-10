<?php

namespace IMC\Controllers;

use IMC\I_Conf;
use IMC\Library\Utility\Integral_Form_API;
use IMC\Library\Utility\I_Tools;
use IMC\Library\Utility\I_Cache;
use IMC\Library\Utility\Integral_MailChimp_Lists;
use IMC\Library\Utility\Integral_MailChimp_Helper;
use IMC\Library\Framework\Logger;
use IMC\Models\IMC_Users_Model;

/**
 * Handles the viewing and processing of the subscribe widget for this plugin
 * 
 * 
 */
if (!class_exists('Subscribe_Widget_Controller')) {

    class Subscribe_Widget_Controller extends \WP_Widget {


        protected $widget_info       = NULL;
        protected $widget_form_array = NULL;
        public static $widget_id     = NULL;

        function __construct() {

            $api_key = get_option(I_Conf::OPT_APIKEY);
            if (!$api_key) {
                return;
            }


            add_action('widgets_init', array($this, 'register_widget'));

            I_Tools::load_utilities(array('integral-mailchimp-base', 'integral-mailchimp-api', 'integral-mailchimp-lists', 'integral-mailchimp-helper', 'integral-form-api'));

            //- Get the Widget info
            $this->widget_info       = $this->_load_widget_info();

            $this->widget_form_array = isset($this->widget_info['form']) ? $this->widget_info['form'] : array();

            $widget_defaults = array(
                'classname' => 'integral-widget-class',
                'description' => ucwords(sprintf(__('%1$s Widget Description', 'integral-mailchimp'), 'Integral'))
            );

            $control_defaults = array(
                'id_base' => 'integral_widget'
            );

            $widget_ops  = wp_parse_args($this->widget_info['widget_ops'], $widget_defaults);
            $control_ops = wp_parse_args($this->widget_info['control_ops'], $control_defaults);

            //- Create the widget
            parent::__construct($control_ops['id_base'], $this->widget_info['name'], $widget_ops, $control_ops);
            self::$widget_id = $this->id_base;

        }


        static function register_widget() {
            register_widget(__CLASS__);


        }
       
        
        /** 
         * Loads any widget stylesheets. This is what we'll use to support signup templates
         * in the future
         */
        static function load_front_end_styles(){
               //- Conditionally load the CSS and JS if the plugin is active on a page
               //- This is where we can load in signup form themes
                if ( is_active_widget(false, false, self::$widget_id)){
                   // echo "active instance is {$id}";
                 }

           }



        /**
         * Displays the widget settings controls on the widget panel.
         * Make use of the get_field_id() and get_field_name() function
         * when creating your form elements. This handles the confusing stuff.
         * 
         */
        public function form($instance) {
            $form_output   = array();
            $form_rules    = array();
            $form_messages = array();
            I_Tools::load_utilities(array('integral-form-api'));
            
            $form_array = $this->widget_form_array;

            $id = explode("-", $this->get_field_id("widget_id"));
            $instance_id = $id[1] . "-" . $id[2];
            //- display the widget instance id
            $instance_field = array(
                array(
                'field_name' => 'instance_id',
                'field_id' => 'instance_id',
                'field_type' => 'display',
                'field_label' => 'Widget ID',
                'field_default' => $instance_id,
                'field_wrapper_element' => 'li',
                'field_wrapper_class' => 'imc-widget-form-item well'
                    ));
            $form_array['fields'] = array_merge($instance_field, $form_array['fields']);
            
            if (!(isset($this->widget_info['custom_update']) && $this->widget_info['custom_update'])) {
                Integral_Form_API::set_form_data($instance);
            }

            if (isset($form_array['fields']) && !empty($form_array['fields'])) {
                foreach ($form_array['fields'] as $key => $field) {

                    if (isset($instance[$field['field_name']])) {
                        $field['field_value'] = $instance[$field['field_name']];
                    }

                    isset($field['field_name']) ? $field['field_name'] = $this->get_field_name($field['field_name']) : NULL;
                    isset($field['field_id']) ? $field['field_id']   = $this->get_field_id($field['field_id']) : NULL;

                    $output = Integral_Form_API::build_form_field($field);

                    $form_output[] = $output['html'];

                    if (isset($output['validation']['rules']) && is_object($output['validation']['rules'])) {
                        foreach ($output['validation']['rules'] as $key => $rule) {
                            $form_rules[$key] = $rule;
                        }
                    }

                    if (isset($output['validation']['messages']) && is_object($output['validation']['messages'])) {
                        foreach ($output['validation']['messages'] as $key => $message) {
                            $form_messages[$key] = $message;
                        }
                    }
                }
            }


            $fields_wrapper_class = '';
            $script               = '';
            $message_box          = '';


            //- TODO - Need to enqueue the jquery validation script on the widgets page to make this code work
            //-       - Assuming we want to offer validation in the widget config form
            /*
              $fields_wrapper_class = $this->widget_options['classname'] . '-config-form-'. $this->number;

              $form_selector     = "$('.imc-widget-config-wrapper.{$fields_wrapper_class}').closest(\"form\")";
              $imc_messages_selector = "$('.imc-widget-config-wrapper.{$fields_wrapper_class} div.messages')";

              $form_rules    = json_encode((object) $form_rules);
              $form_messages = json_encode((object) $form_messages);

              $script = Integral_Form_API::render_jquery_validate_script($form_selector, $imc_messages_selector, $form_rules, $form_messages, TRUE);

              $script = I_Tools::format_inline_javascript($script);

              $message_box = '<div class="messages"></div>';
             */

            isset($form_array['script']) ? $script .= I_Tools::format_inline_javascript($form_array['script']) : NULL;

            echo "<div class='imc-widget-config-wrapper {$fields_wrapper_class}'>";
            echo $script;
            echo $message_box;
            echo join("\n", $form_output);
            echo '</div>';


        }


        private function _load_widget_info() {
           
            $widget_info = array(
                'name' => ucwords(sprintf(__('%1$s Subscribe', 'integral-mailchimp'), 'Integral MailChimp')),
                'custom_update' => TRUE,
                'widget_ops' => array(
                    'classname' => 'imc_subscribe_widget',
                    'description' => ucwords(__('Email List Subscribe Form', 'integral-mailchimp')),
                ),
                'control_ops' => array(
                    'id_base' => 'imc_subscribe_widget'
                ),
                'view' => array(
                    'before_title' => '<h3 style="color: green">',
                    'after_title' => '</h3>',
                    'before_widget' => '<div class="imc-test-class">',
                    'after_widget' => '</div>',
                ),
                'form' => $this->_load_widget_control_form()
            );

            return apply_filters('imc_modify_widget', $widget_info);


        }


        /**
         * 
         */
        private function _load_widget_control_form() {

            //- Load available MailChimp list
            $list_options = Integral_MailChimp_Lists::load_mailchimp_lists();

      
            if (is_array($list_options) && !empty($list_options)) {

                $fields = array();

                $fields[] = array(
                    'field_name' => 'imc-widget-title',
                    'field_id' => 'imc-widget-title',
                    'field_label' => ucwords(__('Title', 'integral-mailchimp')) .':',
                    'field_type' => 'text',
                    'field_class' => 'imc-widget-title',
                    'field_wrapper_class' => 'imc-widget-form-item well',
                );

                $fields[] = array(
                    'field_name' => 'imc-widget-description',
                    'field_id' => 'imc-widget-description',
                    'field_label' => ucwords(__('Description', 'integral-mailchimp')) .':',
                    'field_type' => 'text',
                    'field_class' => 'imc-widget-description',
                    'field_wrapper_class' => 'imc-widget-form-item well',
                );

                $register_option = array('register_as_users' => __('Register new Subscribers as new WordPress Users', 'integral-mailchimp'));
                $fields[]        = array(
                    'field_name' => 'imc-widget-create-user',
                    'field_id' => 'imc-widget-create-user',
                    'field_label' => '',
                    'field_type' => 'checkbox',
                    'field_class' => 'imc-widget-create-user',
                    'field_wrapper_class' => 'imc-widget-form-item well',
                    'field_options' => $register_option
                );
                
                $register_option = array('display_in_modal' => 'Display in a Modal Window');
                $fields[]        = array(
                    'field_name' => 'imc-widget-display-in-modal',
                    'field_id' => 'imc-widget-display-in-modal',
                    'field_label' => '',
                    'field_type' => 'checkbox',
                    'field_class' => 'imc-widget-display-in-modal',
                    'field_description' => 'Add a link or button in an HTML widget on on your page or template with the attributes data-toggle="modal" and data-target="<b>#widget_id</b>" to trigger the modal window.',
                    'field_wrapper_class' => 'imc-widget-form-item well',
                    'field_options' => $register_option
                );

                $fields[] = array(
                    'field_name' => 'imc-widget-select-list',
                    'field_id' => 'imc-widget-select-list',
                    'field_label' => __('Select a List', 'integral-mailchimp') .':',
                    'field_type' => 'select',
                    'field_class' => 'imc-widget-select-list',
                    'field_wrapper_class' => 'imc-widget-form-item well',
                    'field_options' => $list_options
                );


                //- Load all available Groupings for each list
                foreach ($list_options as $list_id => $list_name) {
                    $filters   = array('with_counts' => TRUE, 'all_data' => TRUE);
                    $groupings = Integral_MailChimp_Lists::load_mailchimp_groupings($list_id, $list_name, $filters);

                    $grouping_options = array();
                    if (is_array($groupings) && !empty($groupings)) {

                        $grouping_options[0] = '-- '. __('No Interest Group', 'integral-mailchimp') .' --';
                        foreach ($groupings as $grouping_id => $grouping) {
                            $grouping_options[$grouping_id] = $grouping['name'];
                        }

                        $field_id = 'imc-widget-select-grouping-' . $list_id;

                        $fields[] = array(
                            'field_name' => $field_id,
                            'field_id' => $field_id,
                            'field_label' => __('Select an Interest Grouping', 'integral-mailchimp') .':',
                            'field_type' => 'select',
                            'field_wrapper_element' => 'div',
                            'field_wrapper_class' => 'imc-widget-form-item well imc-widget-select-grouping ' . $field_id,
                            'field_options' => $grouping_options
                        );
                    }


                    //- Load the existing merge tags from MailChimp
                    $remote_list_merge_tags = Integral_MailChimp_Lists::load_mailchimp_merge_tags($list_id);

                    //- Check which merge tags are currently actively sync'd
                    $syncd_merge_tags = maybe_unserialize(get_option('imc_list_sync_tags_' . $list_id));

                    if (!is_array($syncd_merge_tags)) {
                        $syncd_merge_tags = array();
                    } else {
                        $syncd_merge_tags = array_flip($syncd_merge_tags);
                    }

                    if (!empty($syncd_merge_tags)) {

                        $syncd_merge_tags = is_array($remote_list_merge_tags) && !empty($remote_list_merge_tags) ? array_intersect_key($remote_list_merge_tags, $syncd_merge_tags) : $syncd_merge_tags;

                        if (is_array($syncd_merge_tags) && !empty($syncd_merge_tags)) {

                            $mergetag_options = array();
                            foreach ($syncd_merge_tags as $merge_tag => $merge_tag_info) {
                                if (isset($merge_tag_info['public']) && isset($merge_tag_info['show']) && $merge_tag_info['public'] && $merge_tag_info['show']) {
                                    $mergetag_options[$merge_tag] = $merge_tag_info['name'];
                                }
                            }

                            $field_id = 'imc-widget-select-mergetags-' . $list_id;

                            $fields[] = array(
                                'field_name' => $field_id,
                                'field_id' => $field_id,
                                'field_class' => 'imc-widget-checkboxes',
                                'field_label' => __('Select Merge Tags', 'integral-mailchimp') .':',
                                'field_type' => 'checkbox',
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-widget-form-item well imc-widget-select-mergetags ' . $field_id,
                                'field_options' => $mergetag_options,
                                'field_option_wrapper_element' => 'li',
                                'field_option_wrapper_class' => ''
                            );
                        }
                    }
                }

                $form           = array();
                $form['fields'] = $fields;
                $form['script'] = $this->_load_widget_config_inline_script();

                return $form;
            } else {
                return 'No Lists Found';
            }


        }


        private function _load_widget_config_inline_script() {

            $script = <<<SCRIPT
                $('.imc-widget-select-list').change(function () {
                    var list_id = $(this).val();
                    var parent_form = $(this).closest('form'); 
                
                    $(parent_form).find('.imc-widget-select-grouping, .imc-widget-select-mergetags').hide();
                    $(parent_form).find('.imc-widget-select-grouping-'+ list_id +', .imc-widget-select-mergetags-'+ list_id).show();
                    

                }).change();
                
                
SCRIPT;

            return $script;


        }


        /**
         * Update the widget settings
         * 
         */
        function update($new_instance, $old_instance) {
            $instance = array();

            //- Get title
            $title_field = 'imc-widget-title';
            $title       = (isset($new_instance[$title_field])) ? strip_tags($new_instance[$title_field]) : NULL;

            //- Get description
            $description_field = 'imc-widget-description';
            $description       = (isset($new_instance[$description_field])) ? strip_tags($new_instance[$description_field]) : NULL;

            //- Get user reg option
            $reg_user_field = 'imc-widget-create-user';
            $reg_user       = (isset($new_instance[$reg_user_field]) && isset($new_instance[$reg_user_field][0])) ? strip_tags($new_instance[$reg_user_field][0]) : NULL;
            
            //- Get modal window option
            $display_in_modal = 'imc-widget-display-in-modal';
            $use_modal       = (isset($new_instance[$display_in_modal]) && isset($new_instance[$display_in_modal][0])) ? strip_tags($new_instance[$display_in_modal][0]) : NULL;

            //- Extract the list_id
            $list_field = 'imc-widget-select-list';
            $list_id    = strip_tags($new_instance[$list_field][0]);

            //- Extract the grouping_id based on the list_id
            $group_field = 'imc-widget-select-grouping-' . $list_id;
            $grouping_id = (isset($new_instance[$group_field]) && isset($new_instance[$group_field][0])) ? strip_tags($new_instance[$group_field][0]) : NULL;

            //- Extract the mergetag_ids based on the list_id
            $mergetag_field = 'imc-widget-select-mergetags-' . $list_id;
            $mergetag_ids   = (isset($new_instance[$mergetag_field]) && is_array($new_instance[$mergetag_field])) ? $new_instance[$mergetag_field] : NULL;

            $instance[$title_field]       = $title;
            $instance[$description_field] = $description;
            $instance[$reg_user_field]    = $reg_user;
            $instance[$display_in_modal]  = $use_modal;
            $instance[$list_field]        = $list_id;
            $instance[$group_field]       = $grouping_id;
            $instance[$mergetag_field]    = $mergetag_ids;

            return $instance;


        }
        
        /**
         * Allow sign-up form to be displayed in Bootstrap 3 modal window
         * by adding before_widget and after_widget elements
         */
        public static function display_in_modal($widget_id){
           $modal_markup = array();
           $modal_markup['before'] = '<div class="modal fade" id="'.$widget_id.'"><div class="modal-dialog"><div class="modal-content"><div class="modal-body">';
           $modal_markup['after'] = '</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div></div></div></div>';
           $modal_markup['before'] = apply_filters('imc_widget_modal_before', $modal_markup['before']);
           $modal_markup['after'] = apply_filters('imc_widget_modal_after', $modal_markup['after']);
           return $modal_markup;
        }
        
        /**
         * Hacky fix to handle modal forms being placed in relatively positioned parent
         * elements which breaks the z-index. Need to append the form to the body element
         */
        private function _modal_fix_index($widget_id){
            $script = <<<SCRIPT
                    $(function(){
                        $('#{$widget_id}').appendTo("body");
                    });
SCRIPT;
            return $script;
        }


        /**
         * Display the final widget
         * 
         */
        function widget($args, $instance) {

            wp_enqueue_script('jquery-form');
            wp_enqueue_script('jquery-validate');

            $before_widget = '';
            $before_widget_modal = '';
            $after_widget  = '';
            $after_widget_modal = '';
            $before_title  = '';
            $after_title   = '';
            extract($args);

            $output = '';
            
            //- check if it should be displayed in a modal window
            if(isset($instance['imc-widget-display-in-modal']) && $instance['imc-widget-display-in-modal']!=''){
                wp_enqueue_script('imc-bootstrap-modal');
                wp_enqueue_style('imc-bootstrap-modal');
                $modal_markup = self::display_in_modal($widget_id);
                $before_widget_modal = $modal_markup['before'];
                $after_widget_modal = $modal_markup['after'];
                $zindex_fix = I_Tools::format_inline_javascript($this->_modal_fix_index($widget_id));
            }

            //- Build the registration form
            //- - title
            //- - description
            //- - action (hidden)
            //- - list_id (hidden)
            //- - reg_user (hidden)
            //- - grouping_id (hidden)
            //- - groups (checkboxes)
            //- - merge-tag fields (mixed)
            //
            //
            //- Our variables from the widget settings
            $title       = isset($instance['imc-widget-title']) ? apply_filters('widget_title', $instance['imc-widget-title']) : NULL;
            $description = isset($instance['imc-widget-description']) ? $instance['imc-widget-description'] : NULL;

            $list_id = isset($instance['imc-widget-select-list']) ? $instance['imc-widget-select-list'] : NULL;

            if ($list_id) {
                $fields = array();

                //- Extract the mergetag_ids based on the list_id
                $mergetag_field = 'imc-widget-select-mergetags-' . $list_id;
                $mergetag_ids   = (isset($instance[$mergetag_field]) && is_array($instance[$mergetag_field])) ? $instance[$mergetag_field] : NULL;

                $used_merge_tags   = array();
                $merge_tag         = 'EMAIL';
                $used_merge_tags[] = $merge_tag;
                $fields[]          = array(
                    'field_name' => 'imc-widget-reg-form-mergetag-' . $merge_tag,
                    'field_id' => 'imc-widget-reg-form-mergetag-' . $merge_tag,
                    'field_type' => 'text',
                    'field_label' => __('Email', 'integral-mailchimp'),
                    'field_validation' => array(
                        'email' => array('message' => __('The email is invalid, please provide a valid email.', 'integral-mailchimp')),
                        'required' => array('message' => __('Please provide a valid email.', 'integral-mailchimp')),
                    )
                );

                if ($mergetag_ids && is_array($mergetag_ids) && !empty($mergetag_ids)) {


                    //- Load the existing merge tags from MailChimp
                    $remote_list_merge_tags = Integral_MailChimp_Lists::load_mailchimp_merge_tags($list_id);

                    foreach ($mergetag_ids as $merge_tag) {
                        if (isset($remote_list_merge_tags[$merge_tag])) {
                            $merge_tag_info = $remote_list_merge_tags[$merge_tag];
                            if (isset($merge_tag_info['public']) && isset($merge_tag_info['show']) && $merge_tag_info['public'] && $merge_tag_info['show']) {
                                $field_type = in_array($merge_tag_info['field_type'], array('text', 'phone')) ? 'text' : $merge_tag_info['field_type'];

                                $used_merge_tags[] = $merge_tag;

                                $field = array(
                                    'field_name' => 'imc-widget-reg-form-mergetag-' . $merge_tag,
                                    'field_id' => 'imc-widget-reg-form-mergetag-' . $merge_tag,
                                    'field_type' => $field_type,
                                    'field_label' => $merge_tag_info['name']
                                );

                                //- TODO - Consider expanding these validation options depending on field
                                if (isset($merge_tag_info['req']) && $merge_tag_info['req']) {
                                    $field['field_validation'] = array(
                                        'required' => array('message' => __('Please fill in a', 'integral-mailchimp') .' '. $merge_tag_info['name']),
                                    );
                                }

                                $fields[] = $field;
                            }
                        }
                    }
                }

                //- Load Groupings
                $selected_grouping_id = NULL;
                $group_field          = 'imc-widget-select-grouping-' . $list_id;
                $grouping_id          = (isset($instance[$group_field]) && isset($instance[$group_field])) ? strip_tags($instance[$group_field]) : NULL;

                if ($grouping_id) {
                    $filters   = array('with_counts' => TRUE, 'all_data' => TRUE);
                    $groupings = Integral_MailChimp_Lists::load_mailchimp_groupings($list_id, NULL, $filters);

                    $selected_grouping = isset($groupings[$grouping_id]) ? $groupings[$grouping_id] : NULL;

                    if ($selected_grouping) {

                        $selected_grouping_id = $grouping_id;

                        if (isset($selected_grouping['groups']) && is_array($selected_grouping['groups']) && !empty($selected_grouping['groups'])) {
                            foreach ($selected_grouping['groups'] as $group_id => $group) {
                                $group_options[$group['name']] = $group['name'];
                            }

                            $fields[] = array(
                                'field_name' => 'imc-widget-reg-form-group-ids',
                                'field_id' => 'imc-widget-reg-form-group-ids',
                                'field_class' => '',
                                'field_label' => $selected_grouping['name'],
                                'field_type' => 'checkbox',
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-widget-reg-form-group-ids',
                                'field_options' => $group_options,
                                'field_option_wrapper_element' => 'li',
                                'field_option_wrapper_class' => ''
                            );
                        }
                    }
                }

                if (!empty($fields)) {
                    $form_output   = array();
                    $form_rules    = array();
                    $form_messages = array();

                    $ajax_action = I_Conf::PUBLIC_WIDGET_REG_FORM_ACTION;

                    $reg_user = isset($instance['imc-widget-create-user']) ? $instance['imc-widget-create-user'] : NULL;

                    $fields[] = array(
                        'field_name' => 'imc-widget-reg-form-used-mergetags',
                        'field_id' => 'imc-widget-reg-form-used-mergetags',
                        'field_value' => serialize($used_merge_tags),
                        'field_type' => 'hidden'
                    );

                    $fields[] = array(
                        'field_name' => 'imc-widget-reg-form-reg-user',
                        'field_id' => 'imc-widget-reg-form-reg-user',
                        'field_value' => $reg_user,
                        'field_type' => 'hidden'
                    );

                    $fields[] = array(
                        'field_name' => 'imc-widget-reg-form-list-id',
                        'field_id' => 'imc-widget-reg-form-list-id',
                        'field_value' => $list_id,
                        'field_type' => 'hidden'
                    );

                    if ($selected_grouping_id) {
                        $fields[] = array(
                            'field_name' => 'imc-widget-reg-form-grouping-id',
                            'field_id' => 'imc-widget-reg-form-grouping-id',
                            'field_value' => $selected_grouping_id,
                            'field_type' => 'hidden'
                        );
                    }

                    $fields[] = array(
                        'field_name' => 'action',
                        'field_value' => $ajax_action,
                        'field_type' => 'hidden'
                    );

                    $fields[] = array(
                        'field_name' => 'formsubmit',
                        'field_id' => 'formsubmit',
                        'field_type' => 'submit',
                        'field_value' => __('Subscribe', 'integral-mailchimp'),
                        'field_class' => 'btn button-primary clearfix'
                    );


                    foreach ($fields as $field) {

                        $field_output = Integral_Form_API::build_form_field($field);

                        $form_output[] = isset($field_output['html']) ? $field_output['html'] : $field_output;

                        if (isset($field_output['validation']['rules']) && is_object($field_output['validation']['rules'])) {
                            foreach ($field_output['validation']['rules'] as $key => $rule) {
                                $form_rules[$key] = $rule;
                            }
                        }

                        if (isset($field_output['validation']['messages']) && is_object($field_output['validation']['messages'])) {
                            foreach ($field_output['validation']['messages'] as $key => $message) {
                                $form_messages[$key] = $message;
                            }
                        }
                    }

                    $output .= join("\n", $form_output);
                } else {
                    $output .= '<p>'. __('Please configure widget', 'integral-mailchimp') .'</p>';
                }


                //- Before widget modal -- adds Bootstrap3 modal layout elements
                echo $before_widget_modal;
                
                //- Before widget (defined by themes).
                echo $before_widget;

                //- Display the widget title if one was input (before and after defined by themes).
                if ($title) {
                    $title = $before_title . $title . $after_title;
                }

                if ($description) {
                    $description = "<p class='imc-widget-reg-form-description'>{$description}</p>";
                }

                $form_id = 'imc-widget-reg-form';
                if (is_array($args) && isset($args['widget_id'])) {
                    $form_id = $args['widget_id'];
                }

                $form_action = admin_url('admin-ajax.php');

                $message_id = $form_id . '_messages';

                $script = I_Tools::format_inline_javascript($this->_load_widget_display_inline_script($form_id, $message_id, $form_rules, $form_messages));


                echo $script;
                if(isset($instance['imc-widget-display-in-modal']) && $instance['imc-widget-display-in-modal']!=''){
                    echo $zindex_fix;
                }
                echo $title;
                echo $description;
                echo "<form id='{$form_id}' action='{$form_action}'>";
                echo "<div id='{$message_id}'></div>";
                echo $output;
                echo '</form>';

                //- After widget (defined by themes)
                echo $after_widget;
                
                //- After widget modal -- closes Bootstrap3 modal elements
                echo $after_widget_modal;
            }


        }


        private function _load_widget_display_inline_script($form_id, $message_id, $form_rules, $form_messages) {

            $rules        = json_encode((object) $form_rules);
            $imc_messages = json_encode((object) $form_messages);            
            $spinner_url  = admin_url('images/wpspin_light-2x.gif');

            $script = <<<SCRIPT
                $('form#{$form_id}').ajaxForm({
                    type: 'post',
                    beforeSubmit: function() {
                        //- Clear the message html element
                        $('#{$message_id}').html('<img alt="WordPress loading spinner" class="imc-loading-spinner" src="{$spinner_url}">').css('opacity', 1);
                        
                        //- Validate the form
                        $('form#{$form_id}').validate({
                            debug: false,
                           // wrapper: "li",
						    errorClass: "error",
                            rules: {$rules},
                            messages: {$imc_messages},
                            errorPlacement: function(error, element) {
                                if(element.prop('type')=='checkbox') { 
                                    error.insertAfter(element.next());
                                } else {
                                    error.insertAfter(element);
                                }
                            }
                        });
                            
                        var is_valid = $('form#{$form_id}').valid();

                        if (!is_valid) {
                            $('#{$message_id}').html('').css('opacity', 0);
                        }

                        return is_valid;
                    },
                        
                    //- On Success
                    success: function(resp) {
                        $('#{$message_id}').html('');
                        var message_class = 'updated';
                        if (resp instanceof Array || resp instanceof Object) {
                            for (var key in resp) {
                                                        
                                //- This assumes everything went fine
                                if (key == 'msg') {
                                    var this_message = resp[key];

                                //- Otherwise there was an issue
                                } else {
                                    var this_message = resp[key];
                                    message_class = 'error';
                                    $('#'+ key).addClass('highlight-error');
                                }

                                $('#{$message_id}').append('<li>'+ this_message +'</li>');
                            }
                        }

                        $('#{$message_id}').css('opacity', 0)
                            .removeClass('error')
                            .removeClass('updated')
                            .addClass(message_class)
                            .css('opacity', 1)
                            .show();


                    }
                });
SCRIPT;

            return $script;


        }


        public static function widget_reg_form_process() {
            $form_errors = array();

            $form_data = I_Tools::fetch_ajax_post();

            //- Extract the List ID
            $list_id = (isset($form_data['imc-widget-reg-form-list-id']) && $form_data['imc-widget-reg-form-list-id']) ? sanitize_text_field($form_data['imc-widget-reg-form-list-id']) : NULL;

            if ($list_id) {

                //- Extract the register user boolean
                $reg_user = (isset($form_data['imc-widget-reg-form-reg-user']) && $form_data['imc-widget-reg-form-reg-user'] == 'register_as_users') ? TRUE : FALSE;

                //- Extract the Merge Tags
                //- This should always have at least the EMAIL tag in it
                $used_merge_tags = NULL;
                if (isset($form_data['imc-widget-reg-form-used-mergetags'])) {
                    $used_merge_tags = maybe_unserialize($form_data['imc-widget-reg-form-used-mergetags']);
                }

                $register_merge_tags = array();
                if (is_array($used_merge_tags) && !empty($used_merge_tags)) {

                    //- Load existing Merge Tags to know how to handle it's form field
                    $remote_list_merge_tags = Integral_MailChimp_Lists::load_mailchimp_merge_tags($list_id);

                    $remote_list_merge_tags = (is_array($remote_list_merge_tags)) ? $remote_list_merge_tags : array();

                    $merge_tag_prefix = 'imc-widget-reg-form-mergetag-';
                    foreach ($used_merge_tags as $merge_tag) {
                        $merge_tag     = sanitize_text_field($merge_tag);
                        $merge_tag_key = $merge_tag_prefix . $merge_tag;
                        if (isset($form_data[$merge_tag_key])) {
                            $merge_tag_value = $form_data[$merge_tag_key];

                            //- Preload the EMAIL merge tag since it doesn't get passed back by MailChimp
                            $field_type = ($merge_tag === 'EMAIL') ? 'email' : 'text';
                            if (isset($remote_list_merge_tags[$merge_tag]) && isset($remote_list_merge_tags[$merge_tag]['field_type'])) {
                                $field_type = $remote_list_merge_tags[$merge_tag]['field_type'];
                            }

                            //- Sanitize the option value based on the merge tag type
                            //- TODO - Should consider additional field_types
                            switch ($field_type) {

                                case 'email':
                                    $sanitized_email = sanitize_email($merge_tag_value);
                                    if ($merge_tag_value != $sanitized_email) {
                                        $form_errors[$merge_tag_key] = strtoupper(__('Invalid Email', 'integral-mailchimp'));
                                        $merge_tag_value             = NULL;
                                    } else {
                                        $merge_tag_value = $sanitized_email;
                                    }
                                    break;

                                case 'text':
                                default:
                                    $sanitized_text = sanitize_text_field($merge_tag_value);
                                    if ($merge_tag_value != $sanitized_text) {
                                        $form_errors[$merge_tag_key] = strtoupper(__('Invalid Value', 'integral-mailchimp'));
                                        $merge_tag_value             = NULL;
                                    } else {
                                        $merge_tag_value = $sanitized_text;
                                    }
                                    break;
                            }

                            $register_merge_tags[$merge_tag] = $merge_tag_value;
                        }
                    }
                }

                if (empty($form_errors)) {

                    //- Extract the Groups
                    $grouping_id = (isset($form_data['imc-widget-reg-form-grouping-id']) && $form_data['imc-widget-reg-form-grouping-id']) ? sanitize_text_field($form_data['imc-widget-reg-form-grouping-id']) : NULL;
                    $group_ids   = (isset($form_data['imc-widget-reg-form-group-ids']) && is_array($form_data['imc-widget-reg-form-group-ids'])) ? $form_data['imc-widget-reg-form-group-ids'] : NULL;

                    if (is_array($group_ids)) {
                        $test_group_ids = $group_ids;
                        $group_ids      = array();
                        foreach ($test_group_ids as $group_id) {
                            $group_ids[] = sanitize_text_field($group_id);
                        }
                    }

                    //- Save the new user if requested
                    if ($reg_user) {
                        I_Tools::load_models(array('users'));

                        $role          = 'subscriber'; //- TODO - Make this a selectable option
                        $user_pass     = rand(0, 99999999);
                        $user_login    = NULL;
                        $user_email    = NULL;
                        $first_name    = NULL;
                        $last_name     = NULL;
                        $nickname      = NULL;
                        $display_name  = NULL;
                        $user_nicename = NULL;

                        //- Pre-assign values for the standard Merge Tags
                        foreach ($register_merge_tags as $merge_tag => $mergetag_value) {
                            switch ($merge_tag) {
                                case 'EMAIL':
                                    $user_email = $mergetag_value;
                                    break;
                                case 'FNAME':
                                    $first_name = $mergetag_value;
                                    break;
                                case 'LNAME':
                                    $last_name  = $mergetag_value;
                                    break;
                                case 'NICKNAME':
                                    $nickname   = $mergetag_value;
                                    break;
                            }
                        }

                        //- Try to load the user by email to see if it already exists
                        $user = IMC_Users_Model::load_user_by_email($user_email);
                        if (!is_a($user, 'WP_User')) {

                            //- Create a new user
                            //-------------------
                            //- Determine Login Name
                            if (!$user_login) {
                                if ($nickname) {
                                    //- Set the user_login if we have a nickname
                                    $user_login = $nickname;
                                } else if ($first_name || $last_name) {
                                    //- Set the user_login if we have a first or last name
                                    $user_login = trim($first_name . ' ' . $last_name);
                                } else {
                                    //- Otherwise set user_login to the user_email   
                                    $user_login = $user_email;
                                }
                            }

                            //- Determine the Display Name
                            if (!$display_name) {
                                if ($nickname) {
                                    //- Set the display_name if we have a first or last name
                                    $display_name = $nickname;
                                } else if ($first_name || $last_name) {
                                    //- Set the display_name if we have a first or last name
                                    $display_name = trim($first_name . ' ' . $last_name);
                                } else {
                                    $display_name = $user_login;
                                }
                            }

                            //- Determine the User Nicename
                            if (!$user_nicename) {
                                $user_nicename = $display_name;
                            }

                            $data = compact('user_login', 'user_email', 'user_pass', 'role', 'first_name', 'last_name', 'nickname', 'display_name', 'user_nicename');

                            //- Set flag for not auto-subscribing user during creation
                            I_Cache::save_transient('skip_subs', TRUE, 2 * HOUR_IN_SECONDS);

                            //- Create the account
                            $wp_userid = wp_insert_user($data);
                            if (is_wp_error($wp_userid)) {
                                //- $wp_userid->get_error_message();
                                $logger_message = 'There was an error creating a new user during the MailChimp Widget Subscribe process in ' . __FUNCTION__ . '()';
                                $logger_items   = array('WP_Error' => $wp_userid->get_error_message(), 'user_data' => $data);
                                Logger::log_error($logger_message, $logger_items);

                                $form_errors['imc-widget-reg-form-reg-user'] = __('There was an error subscribing you. Please contact the site administrator for assistance.', 'integral-mailchimp');
                            } else {

                                //- Load the user we just created
                                $user = IMC_Users_Model::load_user_by_id($wp_userid);

                                //- If, for some crazy reason, we can't load the user
                                if (!(is_object($user) && is_a($user, 'WP_User'))) {
                                    $logger_message = 'There was an error loading a user during the MailChimp Widget Subscribe process in ' . __FUNCTION__ . '()';
                                    $logger_items   = array('WP_User' => $user, 'user_id' => $wp_userid, 'user_data' => $data);
                                    Logger::log_error($logger_message, $logger_items);

                                    $form_errors['imc-widget-reg-form-reg-user'] = __('There was an error subscribing you. Please contact the site administrator for assistance.', 'integral-mailchimp');
                                }
                            }
                        }

                        //- Update the Merge Tags for the new or existing user
                        if (is_a($user, 'WP_User')) {
                            do_action('integral_mailchimp_plugin_update_merge_tags', $register_merge_tags, $user);
                        } else {
                            //- We assume there was a serious error here since the $user is invalid
                            $form_errors['imc-widget-reg-form-reg-user'] = __('There was an error subscribing you. Please contact the site administrator for assistance.', 'integral-mailchimp');
                        }
                    }

                    //- Subscribe the email (and any merge tags) to the chosen list
                    if (empty($form_errors)) {

                        //- See if this email is already subscribed to this list
                        $subscribed_lists = Integral_MailChimp_Helper::load_mailchimp_lists_by_email($register_merge_tags['EMAIL']);

                        if ($subscribed_lists && is_array($subscribed_lists) && isset($subscribed_lists[$list_id])) {

                            $form_errors['imc-widget-reg-form-reg-user'] = __('This email is already subscribed', 'integral-mailchimp');
                        } else {

                            $options = Integral_MailChimp_Lists::build_list_subscribe_defaults();


                            if ($reg_user && is_a($user, 'WP_User')) {
                                $plugins_merge_tags = Integral_MailChimp_Lists::get_plugins_merge_tags($user);

                                $allowed_merge_tags = maybe_unserialize(get_option('imc_list_sync_tags_' . $list_id));

                                if (!is_array($allowed_merge_tags)) {
                                    $allowed_merge_tags = array();
                                }

                                $plugins_merge_tags = array_intersect_key($plugins_merge_tags, array_flip($allowed_merge_tags));
                                $merge_tags         = array_merge($register_merge_tags, $plugins_merge_tags);
                            } else {
                                $merge_tags = $register_merge_tags;
                            }
                            
                            $options['merge_tags'] = $merge_tags;

                            if ($grouping_id && !empty($group_ids)) {
                                $options['merge_tags']['groupings'] = array(array('id' => $grouping_id, 'groups' => $group_ids));
                            }

                            $response = Integral_MailChimp_Lists::add_user_to_email_list($register_merge_tags['EMAIL'], $list_id, $options);

                            if (!is_array($response)) {
                                $form_errors['imc-widget-reg-form-reg-user'] = __('There was an error subscribing you. Please contact the site administrator for assistance.', 'integral-mailchimp');
                            } else if (isset($response['response']['success']) && !$response['response']['success']) {
                                if (isset($response['response']['type'])) {
                                    switch ($response['response']['type']) {
                                        case 'MailChimp_ValidationError':
                                        default:

                                            $form_errors['imc-widget-reg-form-reg-user'] = __('There was an error subscribing you. Please contact the site administrator for assistance.', 'integral-mailchimp');

                                            break;
                                    }
                                } else if (isset($response['response']['message'])) {
                                    $form_errors['imc-widget-reg-form-reg-user'] = $response['response']['message'];
                                } else {
                                    $form_errors['imc-widget-reg-form-reg-user'] = $response['response'];
                                }
                            }
                        }
                    }
                }


                //- Handle the form_errors
                if (empty($form_errors)) {
                    $status_message = __('Thank you for subscribing!', 'integral-mailchimp');
                    wp_send_json(array('msg' => $status_message));
                } else {

                    wp_send_json($form_errors);
                }
            }


        }


    }


}


new Subscribe_Widget_Controller();

