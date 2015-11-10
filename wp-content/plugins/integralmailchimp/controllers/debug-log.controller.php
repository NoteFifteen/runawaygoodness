<?php

namespace IMC\Controllers;

use IMC\I_Conf;
use IMC\Library\Utility\I_Tools;
use IMC\Views\Admin_View;
use IMC\Library\Utility\Integral_Form_API;
use IMC\Library\Framework\Logger;

/**
 * Handles the viewing and processing of options for this plugin
 * 
 * 
 */
if (!class_exists('Debug_Log_Controller')) {

    class Debug_Log_Controller {


        private static $log_content  = NULL;
        private static $view_content = NULL;


        private static function _initialize_controller($with_view = TRUE) {

            if (I_Conf::$debug_enabled) {
                I_Tools::initialize_controller(__FILE__, FALSE);
                I_Tools::load_utilities(array('integral-form-api'));

                if ($with_view) {
                    I_Tools::load_view_master('admin');
                    Admin_View::initialize_view();
                    Admin_View::set_layout('admin');
                    Admin_View::set_view(I_Tools::get_file_slug());
                    Admin_View::set_title(ucwords(sprintf(__('%1$s Debug Log', 'integral-mailchimp'), 'Integral MailChimp')));
                }
            } else {
                $options_url = admin_url('admin.php?page=' . I_Conf::ADMIN_GENERAL_OPTIONS_SLUG);
                wp_die(sprintf(__('DEBUGGING IS NOT CURRENTLY ENABLED. ENABLE DEBUG LOGGING %1$s HERE %2$s'), "<a href='{$options_url}'>", '</a>'));
            }


        }


        /**
         * LIST MANAGEMENT FORM
         * ************************************************************************ */
        public static function debug_log_form_view() {

            wp_enqueue_script('jquery-form');
            wp_enqueue_script('jquery-validate');
            wp_enqueue_script('imc-bootstrap-js');

            wp_enqueue_style('imc-bootstrap');
            wp_enqueue_style('integral-mailchimp-admin');

            self::_initialize_controller();

            self::_load_debug_log();

            self::_build_debug_log_form();

            Admin_View::$view->content = self::$view_content;

            Admin_View::render_view();


        }


        private static function _load_debug_log() {
            $log_file_path = Logger::get_log_file();

            if (file_exists($log_file_path)) {
                self::$log_content = file_get_contents($log_file_path);
            }


        }


        private static function _build_debug_log_form() {
            $log_content = self::$log_content;

            $content       = '';
            $form          = array();
            $form_rules    = array();
            $form_messages = array();
            $options       = array();
            $defaults      = array();
            $current_user  = wp_get_current_user();

            //- Build the actual form
            $options['imc_send_email_priority'] = array('high' => 'High', 'normal' => 'Normal', 'low' => 'Low');
            $defaults['imc_send_email_from']    = $current_user->user_email;
            $defaults['imc_send_email_name']    = $current_user->display_name;

            $debug_form = self::_load_debug_log_send_form($options, $defaults);

            foreach ($debug_form['form_fields'] as $field_array) {
                $field_output = Integral_Form_API::build_form_field($field_array);
                $form[]       = isset($field_output['html']) ? $field_output['html'] : '';

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

            $javascript = I_Tools::format_inline_javascript(self::_load_debug_log_inline_script($debug_form['form_info']['form_name'], 'message', $form_rules, $form_messages));

            $form = "{$javascript}<form name='{$debug_form['form_info']['form_name']}' id='{$debug_form['form_info']['form_name']}' method='{$debug_form['form_info']['form_method']}' action='{$debug_form['form_info']['form_action']}'>" . join(' ', $form) . '</form>';

            //- Assemble the page content
            $content .= '<h3>'. __('Submit Issue with Debug Log', 'integral-mailchimp') .'</h3>';
            $content .= '<p>'. sprintf(__('Use the following form to submit an issue along with the debug log directly to the %1$s Team', 'integral-mailchimp'), 'IntegralWP') .'. <br/><strong>'. __('View the Debug Log below', 'integral-mailchimp') .'.</strong></p>';

            $content .= $form;

            $content .= '<hr class="imc-hr">';
            $content .= '<h3>'. __('Debug Log', 'integral-mailchimp') .'</h3>';

            $clear_link = admin_url('admin-post.php?action=' . I_Conf::ADMIN_DEBUG_LOG_CLEAR_ACTION);
            $content .= "<div class='imc-send-debug-log-button-wrapper imc-primary-button  admin'><a href='{$clear_link}' id='imc_clear_debug_log_button' name='imc_clear_debug_log_button' class='imc-clear-debug-log-button btn button-primary admin' value='". __('Clear Log', 'integral-mailchimp') ."'>". __('Clear Log', 'integral-mailchimp') ."</a></div>";


            $content .= "<div class='imc-log-viewer well'><pre>{$log_content}</pre></div>";


            self::$view_content = $content;


        }


        private static function _load_debug_log_send_form($options, $defaults) {
            return array(
                'form_info' => array(
                    'form_name' => 'imc-submit-debug-log-form'
                    , 'form_method' => 'post'
                    , 'form_action' => admin_url('admin-ajax.php')
                ),
                'form_fields' => array(
                    array(
                        'field_name' => 'imc_send_debug_issue',
                        'field_id' => 'imc_send_debug_issue',
                        'field_type' => 'textarea',
                        'field_label' => __('Issue Description', 'integral-mailchimp'),
                        'field_class' => '',
                        'field_wrapper_element' => 'div',
                        'field_wrapper_class' => 'imc_debug_send well',
                        'field_description' => sprintf(__('Provide a description, as detailed as possible, about: %1$s What you were doing before the issue happened %2$s and %3$s What happened when the issue occured %4$s', 'integral-mailchimp'), '<ol><li>', '<br><strong style="text-transform: uppercase;">', '</strong></li><li>', '</li></ol>'),
                        'field_validation' => array(
                            'required' => array('message' => __('Please provide an issue description', 'integral-mailchimp')),
                            'minLength' => array('length' => 32, 'message' => __('Please provide a useful issue description', 'integral-mailchimp')),
                        )
                    ),
                    array(
                        'field_name' => 'imc_send_email_priority',
                        'field_id' => 'imc_send_email_priority',
                        'field_label' => __('Issue Priority', 'integral-mailchimp'),
                        'field_wrapper_element' => 'div',
                        'field_wrapper_class' => 'imc_debug_send well',
                        'field_container' => 'div',
                        'field_description' => __('Please be gentle with this', 'integral-mailchimp') .'.',
                        'field_type' => 'select',
                        'field_options' => $options['imc_send_email_priority'],
                        'field_default' => 'normal'
                    ),
                    array(
                        'field_name' => 'imc_send_email_name',
                        'field_id' => 'imc_send_email_name',
                        'field_type' => 'text',
                        'field_class' => '',
                        'field_label' => __('From Name', 'integral-mailchimp'),
                        'field_default' => $defaults['imc_send_email_name'],
                        'field_wrapper_element' => 'div',
                        'field_wrapper_class' => 'imc_debug_send well',
                        'field_description' => __('Your name', 'integral-mailchimp')
                    ),
                    array(
                        'field_name' => 'imc_send_email_from',
                        'field_id' => 'imc_send_email_from',
                        'field_type' => 'text',
                        'field_class' => '',
                        'field_label' => __('From Email', 'integral-mailchimp'),
                        'field_default' => $defaults['imc_send_email_from'],
                        'field_wrapper_element' => 'div',
                        'field_wrapper_class' => 'imc_debug_send well',
                        'field_description' => __('The email address for us to reply to', 'integral-mailchimp'),
                        'field_validation' => array(
                            'required' => array('message' => __('Please provide an email address for us to reply to', 'integral-mailchimp')),
                            'email' => array('message' => __('Please provide a valid email address for us to reply to', 'integral-mailchimp'))
                        )
                    ),
                    array(
                        'field_order' => NULL
                        , 'field_name' => 'action'
                        , 'field_type' => 'hidden'
                        , 'field_value' => I_Conf::ADMIN_DEBUG_LOG_SEND_ACTION
                    ),
                    array(
                        'field_order' => NULL
                        , 'field_name' => 'formsubmit'
                        , 'field_id' => 'formsubmit'
                        , 'field_type' => 'submit'
                        , 'field_value' => __('Submit Issue and Debug Log', 'integral-mailchimp')
                        , 'field_class' => 'btn button-primary clearfix'
                    ),
                    array(
                        'field_name' => 'imc_send_debug_issue_button',
                        'field_id' => 'imc_send_debug_issue_button',
                        'field_label' => '',
                        'field_type' => 'button',
                        'field_wrapper_element' => 'div',
                        'field_wrapper_class' => 'imc-send-debug-log-button-wrapper imc-primary-button',
                        'field_class' => 'imc-send-debug-log-button btn button-primary',
                        'field_value' => __('Submit Issue and Debug Log', 'integral-mailchimp')
                    )
                )
            );


        }


        public static function debug_log_clear_process() {
            $log_file_path = Logger::get_log_file();

            if (file_exists($log_file_path)) {
                file_put_contents($log_file_path, '');
            }

            $debug_log_url = I_Conf::ADMIN_DEBUG_LOG_SLUG;
            wp_redirect(admin_url("admin.php?page={$debug_log_url}"));


        }


        public static function debug_log_send_process() {
            $form_errors = array();

            $form_values = I_Tools::fetch_ajax_post();

            $clean_email = sanitize_email($form_values['imc_send_email_from']);

            if (!empty($form_values['imc_send_debug_issue']) && !empty($form_values['imc_send_email_priority']) && !empty($form_values['imc_send_email_from']) && ($clean_email == $form_values['imc_send_email_from'])) {
                $headers       = array();
                $attachments   = array();
                $log_file_path = Logger::get_log_file();


                $issue_content  = implode("\n", array_map('sanitize_text_field', explode("\n", $form_values['imc_send_debug_issue'])));
                $issue_priority = strtoupper(sanitize_text_field(reset($form_values['imc_send_email_priority'])));
                $issue_name     = sanitize_text_field($form_values['imc_send_email_name']);
                $issue_email    = $clean_email;


                if (file_exists($log_file_path)) {
                    $log_content = file_get_contents($log_file_path);
                } else {
                    $log_content = strtoupper(__('ERROR LOADING DEBUG LOG', 'integral-mailchimp'));
                }

                $params['site_name'] = get_bloginfo('name');
                $params['site_url']  = site_url();
                $params['simple_url'] = str_replace(array('http://', 'https://'), '', $params['site_url']);

                $to_email = 'support@integralwp.com';

                $subject = "[IMC-{$issue_priority}] {$params['simple_url']} Debug Issue";

                $message = <<<MESSAGE
INCOMING DEBUG ISSUE

===================================================

FROM:   {$issue_name}
               {$issue_email}

SITE:   {$params['site_name']}
            {$params['site_url']}

===================================================

{$issue_content}

===================================================

{$log_content}

===================================================
                    
MESSAGE;

                $headers[] = "From: {$issue_name} <{$issue_email}>";

                $attachments[] = $log_file_path;


                wp_mail($to_email, $subject, $message, $headers, $attachments);
            } else {
                if (empty($form_values['imc_send_debug_issue'])) {
                    $form_errors['imc_send_debug_issue'] = __('Please provide an issue description', 'integral-mailchimp');
                }

                if (empty($form_values['imc_send_email_from'])) {
                    $form_errors['imc_send_email_from'] = __('Please provide an email address for us to reply to', 'integral-mailchimp');
                } else {
                    if ($clean_email != $form_values['imc_send_email_from']) {
                        $form_errors['imc_send_email_from'] = __('Please provide a valid email address for us to reply to', 'integral-mailchimp');
                    }
                }
            }

            //- Handle the form_errors
            if (empty($form_errors)) {
                $status_message = 'Issue Submitted!';
                wp_send_json(array('msg' => $status_message));
            } else {
                wp_send_json($form_errors);
            }


        }


        private static function _load_debug_log_inline_script($form_id, $message_id, $form_rules, $form_messages) {

            $rules        = json_encode((object) $form_rules);
            $imc_messages = json_encode((object) $form_messages);
            $spinner_url  = admin_url('images/wpspin_light-2x.gif');

            $script = <<<SCRIPT
                $('form#{$form_id}').ajaxForm({
                    type: 'post',
                    beforeSubmit: function() {
                        //- Clear the message html element
                        $('#{$message_id}').html('<img alt="WordPress loading spinner" class="imc-loading-spinner" src="{$spinner_url}">').css('opacity', 1);
                        
                        $('html, body').animate({
                            scrollTop: $('body').offset().top
                        }, 500);
                        
                        //- Validate the form
                        $('form#{$form_id}').validate({
                            debug: false,
                           // wrapper: "li",
						    errorClass: "error",
                            rules: {$rules},
                            messages: {$imc_messages},
                            errorPlacement: function(error, element) {
                                if(element.prop('type')=='checkbox'){ 
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
                                
                        $('form#{$form_id}').trigger("reset");

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


    }


}

