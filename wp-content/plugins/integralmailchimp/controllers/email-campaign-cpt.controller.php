<?php

namespace IMC\Controllers;

use IMC\Library\Framework\Integral_Plugin_CPT;
use IMC\Library\Utility\Integral_MailChimp_Helper;
use IMC\Library\Utility\Integral_MailChimp_Lists;
use IMC\Library\Utility\Integral_Form_API;
use IMC\Library\Utility\I_Tools;
use IMC\Library\Utility\Integral_MailChimp_Templates;
use IMC\I_Conf;
use IMC\Library\Utility\Integral_MailChimp_Reports;
use IMC\Library\Utility\Integral_MailChimp_Campaigns;
use IMC\Library\Framework\Logger;

/**
 * Handles the viewing and processing of the Email Campaign CPT meta boxes
 * 
 * 
 */
//- Add the TinyMCE Code Plugin
//add_filter('mce_external_plugins', array('IMC\Controllers\Email_Campaigns_CPT_Controller', 'load_custom_tinymce_plugins'));

if (!class_exists('Email_Campaigns_CPT_Controller')) {

    class Email_Campaigns_CPT_Controller extends Integral_Plugin_CPT {


        function __construct() {
            I_Tools::load_utilities(array('integral-mailchimp-base', 'integral-mailchimp-api', 'integral-mailchimp-lists', 'integral-mailchimp-reports', 'integral-mailchimp-campaigns', 'integral-mailchimp-templates', 'integral-form-api'));

            //- Get the CPT info -- post type, meta_boxes & fields, taxonomies, etc
            //- do it in plugins loaded so the standard admin menu is never created
            add_action('plugins_loaded', array($this, 'get_post_info'));
        }
        
        public function get_post_info(){
            $this->post_info = $this->load_post_info();
            parent::__construct();
        }


        /**
         * Filter hook callback for adding in our own custom tinymce plugins
         * 
         * @param array $plugins_array
         * @return array
         */
        public static function load_custom_tinymce_plugins($plugins_array) {
            $plugins         = array();
            $plugins['code'] = ''; //- Loads the html viewer editor

            foreach ($plugins as $plugin_name => $plugin_path) {
                $plugin_path                 = $plugin_path ? : I_Conf::JS_PATH . 'tinymce/plugins/' . $plugin_name . '/plugin.min.js';
                $plugin_url                  = plugins_url($plugin_path, IMC_PLUGIN_FILE);
                $plugins_array[$plugin_name] = $plugin_url;
            }
            return $plugins_array;


        }


        /**
         * Handles ajax updating on the add/edit campaign page
         * 
         * - expects an $email_action post var that routes the request
         */
        public static function ajax_build_campaign() {

            $email_action = '';
            $post_id      = null;
            $list_id      = null;
            extract($_GET);

            switch ($email_action):
                case 'load_segments_and_defaults':

                    //- Get the list info
                    $lists = Integral_MailChimp_Lists::load_mailchimp_lists(array('all_data' => true));
                    $groups[$list_id] = array();
                    //- Check if there are groups
                   /** $groupings = Integral_MailChimp_Lists::load_mailchimp_groupings($list_id, $lists[$list_id]['name']);
                    Logger::log_error('groupings', array($list_id => $groupings));  
                    if(!empty($groupings)){
                        foreach($groupings as $grouping){
                     Logger::log_error('groupings', array($groupings));   
                    
                    $groups[$list_id] = Integral_Form_API::build_form_field(
                            array('field_name' => 'imc_list_interest_grouping',
                                'field_id' => 'list_interest_grouping',
                                'field_label' => 'Choose List Group(s)',
                                'field_label_class' => '',
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta',
                                'field_container' => 'div',
                                'field_description' => 'To send to specific list groups select them above.',
                                'field_type' => 'checkbox',
                                'tabindex'=> 102,
                                'field_options' => Integral_Form_API::return_with_empty_option($grouping, array('0' => 'All')),
                                'field_option_wrapper_element' => 'div',
                                'field_option_wrapper_class' => '',
                                'field_value' => get_post_meta($post_id, 'imc_existing_email_segment', TRUE)
                            )
                    );
                        }
                    }//- end of groupings check
                    */

                    //- Check if there are segments
                    $segments[$list_id] = Integral_Form_API::build_form_field(
                            array('field_name' => 'imc_existing_email_segment',
                                'field_id' => 'existing_email_segment',
                                'field_label' => __('Choose a List Segment', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta',
                                'field_container' => 'div',
                                'field_description' => __('To send to an existing saved list segment, select it from the choices above', 'integral-mailchimp') .'.',
                                'field_type' => 'select',
                                'tabindex'=> 103,
                                'field_options' => Integral_Form_API::return_with_empty_option(Integral_MailChimp_Lists::load_mailchimp_list_segments($list_id), array('0' => '----')),
                                'field_value' => get_post_meta($post_id, 'imc_existing_email_segment', TRUE)
                            )
                    );

                    //- Send back a json array
                    wp_send_json(array('lists' => $lists[$list_id], 'segments' => $segments, 'groups' => $groups));

                    break;

                default;
                    die();
                    break;

            endswitch;


        }


        /**
         * Handles ajax saving of the add/edit campaign page
         * 
         */
        public static function ajax_save_campaign() {
            $ajax_data = I_Tools::fetch_ajax_post();

            $form_errors    = array();
            $status_message = array();
            $form_data      = array();
            parse_str($ajax_data['form_data'], $form_data);

            $cpt_data         = array();
            $cpt_data['meta'] = array();


            //- EXTRACT AND SANITIZE FORM FIELDS
            //----------------------------------------------------------------------------------------

            $cpt_data['meta']['imc_email_subject']           = isset($form_data['imc_email_subject']) ? sanitize_text_field($form_data['imc_email_subject']) : NULL;
            $cpt_data['meta']['imc_email_list']              = isset($form_data['imc_email_list']) && is_array($form_data['imc_email_list']) ? reset($form_data['imc_email_list']) : NULL;
            $cpt_data['meta']['imc_user_email_templates']    = isset($form_data['imc_user_email_templates']) && is_array($form_data['imc_user_email_templates']) ? reset($form_data['imc_user_email_templates']) : NULL;
            $cpt_data['meta']['imc_gallery_email_templates'] = isset($form_data['imc_gallery_email_templates']) && is_array($form_data['imc_gallery_email_templates']) ? reset($form_data['imc_gallery_email_templates']) : NULL;
            $cpt_data['meta']['imc_from_name']               = isset($form_data['imc_from_name']) ? sanitize_text_field($form_data['imc_from_name']) : NULL;
            $cpt_data['meta']['imc_from_email']              = isset($form_data['imc_from_email']) ? sanitize_email($form_data['imc_from_email']) : NULL;

            $cpt_data['meta']['imc_to_name']                = isset($form_data['imc_to_name']) ? sanitize_text_field($form_data['imc_to_name']) : NULL;
            $cpt_data['meta']['imc_existing_email_segment'] = isset($form_data['imc_existing_email_segment']) && is_array($form_data['imc_existing_email_segment']) && reset($form_data['imc_existing_email_segment']) > 0 ? reset($form_data['imc_existing_email_segment']) : NULL;
            $cpt_data['meta']['imc_email_send_test']        = isset($form_data['imc_email_send_test']) ? sanitize_email($form_data['imc_email_send_test']) : NULL;
            $cpt_data['meta']['imc_campaign_id']            = isset($form_data['imc_campaign_id']) ? sanitize_text_field($form_data['imc_campaign_id']) : NULL;
            $cpt_data['meta']['imc_content']                = isset($form_data['imc_content']) ? $form_data['imc_content'] : NULL;
            
            $cpt_data['post_title'] = isset($form_data['post_title']) ? sanitize_text_field($form_data['post_title']) : NULL;
            $cpt_data['post_id']    = isset($form_data['post_ID']) ? sanitize_text_field($form_data['post_ID']) : NULL;
            $cpt_data['tax_input']  = isset($form_data['tax_input']) ? $form_data['tax_input'] : NULL;

            //- Since WordPress doesn't currently have a sanitize function for processing the type of html/styles content that
            //- will get passed here, we rely on MailChimp to sanitize it for us and then we can load the sanitized content back
            //- into the post on success
            //$cpt_data['content'] = isset($form_data['imc_content']) ? $form_data['imc_content'] : NULL;

            $current_action = isset($ajax_data['current_action']) ? sanitize_text_field($ajax_data['current_action']) : NULL;

            if ($current_action == 'email_send_test' && !$cpt_data['meta']['imc_email_send_test']) {
                $form_errors['imc_email_send_test'] = __('Please provide a valid Test Email', 'integral-mailchimp');
            }


            //- RUN VALIDATION
            //----------------------------------------------------------------------------------------
            //
            //- Run validation on the main fields
            if (!$cpt_data['post_title']) {
                $form_errors['title-prompt-text'] = __('Please provide a valid Campaign Name', 'integral-mailchimp');
            }

            if (!$cpt_data['meta']['imc_content']) {
                $form_errors['imc_content'] = __('The message portion of your email is empty', 'integral-mailchimp');
            }

            //- Run validation on the meta fields
            $required_meta_fields = array(
                'imc_email_subject' => __('Please provide a valid Email Subject', 'integral-mailchimp'),
                'imc_email_list' => __('Please select a Subscriber List', 'integral-mailchimp'),
                'imc_from_email' => __('Please provide a valid From Email', 'integral-mailchimp'),
                'imc_from_name' => __('Please provide a From Name', 'integral-mailchimp'),
            );
            foreach ($required_meta_fields as $field_name => $required_message) {
                if (!$cpt_data['meta'][$field_name]) {
                    $form_errors[$field_name] = $required_message;
                }
            }

            $cpt_to_api_field_names = array(
                'imc_email_list' => 'list_id',
                'imc_email_subject' => 'subject',
                'imc_from_email' => 'from_email',
                'imc_from_name' => 'from_name',
                'imc_to_name' => 'to_name',
                'imc_existing_email_segment' => 'saved_segment_id'
            );



            if (empty($form_errors)) {
                $campaign_id = NULL;


                //- SWITCH 1 OF 3 - PROCESS FORM / SAVE MC CAMPAIGN / SAVE LOCAL CPT
                //----------------------------------------------------------------------------------------

                switch ($current_action) {

                    //- Each of these actions runs the standard saving process
                    case 'email_save_draft':
                    case 'email_send_test':
                    case 'email_send_blast':

                        //- Create/Update MC Campaign
                        $type    = 'regular';
                        $options = array();
                        $content = array();
                        $segment = array();

                        $options['title']         = $cpt_data['post_title'];
                        $options['list_id']       = $cpt_data['meta']['imc_email_list'];
                        $options['subject']       = $cpt_data['meta']['imc_email_subject'];
                        $options['from_email']    = $cpt_data['meta']['imc_from_email'];
                        $options['from_name']     = $cpt_data['meta']['imc_from_name'];
                        $options['to_name']       = $cpt_data['meta']['imc_to_name'];
                        $options['generate_text'] = TRUE;

                        $content['html'] = $cpt_data['meta']['imc_content'];

                        ($cpt_data['meta']['imc_existing_email_segment']) ? $segment['saved_segment_id'] = $cpt_data['meta']['imc_existing_email_segment'] : $segment = NULL;



                        //- CREATE OR UPDATE THE REMOTE MAILCHIMP CAMPAIGN
                        //----------------------------------------------------------------------------------------

                        if (isset($cpt_data['meta']['imc_campaign_id']) && $cpt_data['meta']['imc_campaign_id']) {
                            //- If the post has an existing Campaign Id then update it field-by-field
                            $campaign_id = $cpt_data['meta']['imc_campaign_id'];

                            $original_post_meta = get_post_meta($cpt_data['post_id']);
                            $original_post_meta = is_array($original_post_meta) ? array_map(function($item) {
                                    return is_array($item) ? reset($item) : $item;
                                }, $original_post_meta) : NULL;

                            $response = array();
                            if (is_array($original_post_meta) && !empty($original_post_meta)) {

                                //- Figure out which fields have changed and update each item in the remote MC Campaign
                                //- Extract only the changed items
                                $changed_elements = array_intersect_key(array_diff_assoc($cpt_data['meta'], $original_post_meta), $cpt_data['meta']);

                                $campaign_segments_array = array();
                                //- Extract the segment for use in a separate call
                                if (isset($changed_elements['imc_existing_email_segment'])) {
                                    $campaign_segments_array[$cpt_to_api_field_names['imc_existing_email_segment']] = $changed_elements['imc_existing_email_segment'];
                                    unset($changed_elements['imc_existing_email_segment']);
                                }

                                //- Remove the imc_content for comparison
                                unset($changed_elements['imc_content']);

                                $campaign_options_array = array();
                                foreach ($changed_elements as $meta_key => $meta_value) {
                                    if (isset($cpt_to_api_field_names[$meta_key])) {
                                        $meta_key = $cpt_to_api_field_names[$meta_key];

                                        $campaign_options_array[$meta_key] = $meta_value;
                                    }
                                }

                                //- If we have options to update
                                if (!empty($campaign_options_array)) {
                                    Integral_MailChimp_Campaigns::update_mailchimp_campaign_field($campaign_id, 'options', $campaign_options_array);
                                }

                                //- If we have segments to update
                                if (!empty($campaign_segments_array)) {
                                    Integral_MailChimp_Campaigns::update_mailchimp_campaign_field($campaign_id, 'segment_ops', $campaign_segments_array);
                                }

                                //- Inline the CSS in our content
                                $content['html'] = self::inline_content_css($content['html']);

                                //- Always update the content since doing a compare is just ridiculous
                                $response = Integral_MailChimp_Campaigns::update_mailchimp_campaign_field($campaign_id, 'content', $content);
                            }

                            //- Reload the Campaign for use below
                            $response = Integral_MailChimp_Campaigns::load_mailchimp_campaign($campaign_id);
                        } else {

                            //- Inline the CSS in our content
                            $content['html'] = self::inline_content_css($content['html']);
                            
                            $filters = array();

                            //- Otherwise create a new Campaign (returns the campaign)
                            $response = Integral_MailChimp_Campaigns::create_mailchimp_campaign($type, $options, $content, $filters, $segment);
                        }



                        //- SAVE THE LOCAL EMAIL CAMPAIGN CPT
                        //----------------------------------------------------------------------------------------

                        if (isset($response['success']) && $response['success']) {
                            //- Confirm a valid $response and save the Campaign ID into the post's meta
                            if (isset($response['response']['id'])) {
                                //$create_update_msg = $campaign_id ? 'updated' : 'created';
                                //$status_message[]  = "The Email Campaign ({$options['title']}) was successfully {$create_update_msg}";
                                $campaign_id = $campaign_id ? : $response['response']['id'];

                                //- Add the campaign_id to the meta for saving in to the campaign cpt
                                $cpt_data['meta']['imc_campaign_id'] = $campaign_id;

                                //- Create/Update post
                                $saved_post_id = self::save_cpt($cpt_data);

                                if ($cpt_data['post_id'] != $saved_post_id) {
                                    //- These 2 should always be the same
                                    $logger_message = 'The post_id supplied to and received from saving the Post did NOT match in ' . __FUNCTION__ . '()';
                                    $logger_items   = array('supplied_post_id' => $cpt_data['post_id'], 'received_post_id' => $saved_post_id);
                                    Logger::log_error($logger_message, $logger_items);
                                }

                                //- If we get to here then everything pretty much went smoothly
                                $status_message[] = __('The Email Campaign was successfully saved!', 'integral-mailchimp');
                            } else {
                                $logger_message = 'Failed loading the MC Campaign in ' . __FUNCTION__ . '()';
                                $logger_items   = array('load_campaign_response' => $response, 'campaign_save_data' => $cpt_data);
                                Logger::log_error($logger_message, $logger_items);
                            }
                        } else {
                            if (isset($response['response']) && isset($response['response']['message'])) {
                                $error_type = isset($response['response']['type']) ? isset($response['response']['type']) : 'MailChimp_Error';
                                $error_msg  = $response['response']['message'];
                            } else {
                                $error_type = 'MailChimp_Error';
                                $error_msg  = sprintf(__('There was an error creating the Email Campaign (%1$s)', 'integral-mailchimp'), $options['title']);
                            }
                            $form_errors[$error_type] = $error_msg;
                            $logger_items             = array('load_campaign_response' => $response, 'campaign_save_data' => $cpt_data, 'mc_api_error_type' => $error_type);
                            Logger::log_error($error_msg, $logger_items);
                        }

                        break;
                }



                //- SWITCH 2 OF 3 - RUN READY CHECK ON MC CAMPAIGN
                //----------------------------------------------------------------------------------------

                $is_ready = FALSE;

                //- Run the Campaign Ready Test before Send or Send Test
                if (empty($form_errors)) {

                    switch ($current_action) {

                        case 'email_send_test':
                        case 'email_send_blast':

                            if ($campaign_id) {
                                $ready_response = Integral_MailChimp_Campaigns::ready_check_mailchimp_campaign($campaign_id);

                                if (isset($ready_response['success']) && $ready_response['success']) {
                                    if (isset($ready_response['response']) && isset($ready_response['response']['is_ready'])) {
                                        if ($ready_response['response']['is_ready']) {
                                            $is_ready = TRUE;
                                        } else {
                                            $form_errors['is_ready'] = self::_format_ready_response($ready_response['response']['items']);
                                        }
                                    }
                                } else if (isset($ready_response['response']) && isset($ready_response['response']['message']) && isset($ready_response['response']['type'])) {
                                    $form_errors[$ready_response['response']['type']] = $ready_response['response']['message'];
                                }
                            } else {
                                $logger_message = 'No Campaign ID provided for running the MC Campaign Ready Test in ' . __FUNCTION__ . '()';
                                $logger_items   = array('campaign_save_data' => $cpt_data);
                                Logger::log_error($logger_message, $logger_items);
                            }


                            break;
                    }
                }


                if (empty($form_errors)) {


                    //- SWITCH 3 OF 3 - SEND TEST / SEND CAMPAIGN
                    //----------------------------------------------------------------------------------------

                    switch ($current_action) {

                        case 'email_send_test':

                            if ($is_ready && $campaign_id && $cpt_data['meta']['imc_email_send_test']) {

                                $emails = array($cpt_data['meta']['imc_email_send_test']);

                                //- Send a test email to the saved Campaign
                                $send_test_response = Integral_MailChimp_Campaigns::send_test_mailchimp_campaign($campaign_id, $emails);

                                if (isset($send_test_response['success']) && $send_test_response['success']) {
                                    $status_message[] = __('The Test Email was successfully sent!', 'integral-mailchimp');
                                } else if (isset($send_test_response['response']) && isset($send_test_response['response']['message']) && isset($send_test_response['response']['type'])) {
                                    $form_errors[$send_test_response['response']['type']] = $send_test_response['response']['message'];
                                }
                            } else {
                                $logger_message = 'There was an error trying to send a test email for the Campaign';
                                $logger_items   = array('campaign_save_data' => $cpt_data);
                                Logger::log_error($logger_message, $logger_items);

                                $form_errors['imc_campaign_id'] = $logger_message;
                            }

                            break;

                        case 'email_send_blast':

                            if ($is_ready && $campaign_id) {

                                //- Send the Campaign!!! =)
                                $send_response = Integral_MailChimp_Campaigns::send_mailchimp_campaign($campaign_id);

                                if (isset($send_response['success']) && $send_response['success']) {
                                    $status_message[] = __('The Email Campaign was successfully sent!', 'integral-mailchimp');
                                    if (!empty($saved_post_id)) {
                                        $reload_edit_page = admin_url('edit.php?post_type=imc_email_campaign');// get_edit_post_link($saved_post_id, 'link');
                                        wp_publish_post($saved_post_id);
                                    }
                                } else if (isset($send_response['response']) && isset($send_response['response']['message']) && isset($send_response['response']['type'])) {
                                    $form_errors[$send_response['response']['type']] = $send_response['response']['message'];
                                }
                            } else {
                                $logger_message = 'There was an error trying to send the Campaign';
                                $logger_items   = array('campaign_save_data' => $cpt_data);
                                Logger::log_error($logger_message, $logger_items);

                                $form_errors['imc_campaign_id'] = $logger_message;
                            }

                            break;
                    }
                }
            }


            if (empty($form_errors)) {
                if (empty($status_message)) {
                    $logger_message   = 'There was a problem parsing the form';
                    Logger::log_error($logger_message, array('campaign_save_data' => $cpt_data));
                    $status_message[] = $logger_message;
                }

                $status_message = join('</div><div>', $status_message);

                $success_response           = array();
                $success_response['msg']    = "<div>{$status_message}</div>";
                !empty($campaign_id) ? $success_response['cid']    = $campaign_id : NULL;
                !empty($reload_edit_page) ? $success_response['reload'] = $reload_edit_page : NULL;

                wp_send_json($success_response);
            } else {
                wp_send_json($form_errors);
            }


        }


        public static function inline_content_css($html_content) {

            //- Inline the CSS in our content
            $inline_css_response = Integral_MailChimp_Helper::inline_email_css($html_content);

            if (isset($inline_css_response['success']) && $inline_css_response['success']) {
                $html_content = $inline_css_response['response'];
                return $html_content['html'];
            } else {
                $logger_message = 'Failed Inlining the CSS for the MC Campaign Content in ' . __FUNCTION__ . '()';
                $logger_items   = array('inline_css_response' => $inline_css_response, 'html_content' => $html_content);
                Logger::log_error($logger_message, $logger_items);
            }

            return $html_content;


        }


        public static function save_cpt($cpt_data) {
            $args = array(
                //'post_content' => $cpt_data['content'],
                'post_title' => $cpt_data['post_title'],
                'tax_input' => $cpt_data['tax_input']
            );

            $post = get_post($cpt_data['post_id']);

            if (is_a($post, 'WP_Post')) {
                $args['ID']  = $post->ID;
                $new_post_id = wp_update_post($args, TRUE);
            } else {
                $new_post_id = wp_insert_post($args, TRUE);
            }

            if (is_wp_error($new_post_id)) {
                $creating_updating = isset($args['ID']) ? 'Updating' : 'Creating';
                $logger_message    = "There was an error {$creating_updating} the Email Campaign in " . __FUNCTION__ . "()";
                $logger_items      = array('WP_Error' => $new_post_id->get_error_message(), 'campaign_save_data' => $cpt_data);
                Logger::log_error($logger_message, $logger_items);
            } else {
                foreach ($cpt_data['meta'] as $meta_key => $meta_value) {
                    update_post_meta($new_post_id, $meta_key, $meta_value);
                }
            }


            return $new_post_id;


        }


        private static function _format_ready_response($ready_array) {
            if (is_array($ready_array) && !empty($ready_array)) {

                $items = array();

                foreach ($ready_array as $item) {
                    if ($item['type'] != 'tick-large') {
                        $items[] = "<li class='{$item['type']}'>{$item['heading']} - {$item['details']}</li>";
                    }
                }

                if ($items) {
                    $items  = join("\n", $items);
                    $output = "<ul class='campaign-ready-report'>{$items}</ul>";
                } else {
                    $output         = __('The Campaign appears to be ready!', 'integral-mailchimp');
                    $logger_message = 'Ready check fails but no problem items found in ' . __FUNCTION__ . '()';
                    $logger_items   = array('ready_check_response' => $ready_array);
                    Logger::log_error($logger_message, $logger_items);
                }

                return $output;
            }


        }


        public function load_post_info() {
            $post_info = array(
                'post_type' => 'imc_email_campaign',
                'singular' => __('MailChimp Email Campaign', 'integral-mailchimp'),
                'plural' => __('MailChimp Email Campaigns', 'integral-mailchimp'),
                //- setting this to false hides the permalink box
                'public' => false,
                'show_ui' => true,
                //- we're listing acct notes under the Members menu so hide it here 
                'show_in_menu' => false,
                'menu_position' => '',
                'hierarchical' => false,
                'rewrite' => array('slug' => 'email'),
                'can_export' => true,
                'supports' => array('title', 'editor'),
                //- change the default "Enter title here" placeholder text in the CPT subject field
                'title_field_placeholder' => __('Campaign Name', 'integral-mailchimp'),
                //- enable Duplicate link
                'duplicate_post_link' => true,
                //- register any taxonomies for this CPT
                'taxonomy' => array(
                    'taxonomy_type' => 'imc_email_category',
                    'taxonomy_info' => array(
                        'label' => __('Email Categories', 'integral-mailchimp'),
                        'labels' => array(
                            'singular_name' => __('Email Category', 'integral-mailchimp'),
                            'add_new_item' => __('Add New Email Category', 'integral-mailchimp')
                        ),
                        'sort' => true,
                        'hierarchical' => true,
                        'args' => array('orderby' => 'term_order'),
                        'rewrite' => array('slug' => 'email_category'),
                        'show_ui' => true,
                        'show_admin_column' => true
                    )
                ),
                //'use_scripts' => array('imc-new-email'),
                'remove_scripts' => array('editor-expand'),
                'meta_boxes' => array(
                    array(
                        'id' => 'subject',
                        'title' => __('Email Subject', 'integral-mailchimp'),
                        'context' => 'normal',
                        'priority' => 'high',
                        'fields' => array(
                            array(
                                'field_name' => 'imc_email_subject',
                                'field_id' => 'imc_email_subject',
                                'field_label_class' => '',
                                'field_type' => 'text',
                                'field_class' => 'large-text',
                                'field_placeholder' => __('Email Subject Line', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta',
                                'tabindex'=> 100
                            )
                        )
                    ),
                    array(
                        'id' => 'email_recipients',
                        'title' => __('Email Recipients', 'integral-mailchimp'),
                        'context' => 'normal',
                        'priority' => 'high',
                        'fields' => array(
                            array(
                                'field_name' => 'imc_email_list',
                                'field_id' => 'imc_email_list',
                                'field_label' => __('Choose a List', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta',
                                'field_type' => 'select',
                                'field_class' => 'list-select',
                                'tabindex'=> 101,
                                'field_options' => Integral_MailChimp_Lists::load_mailchimp_lists(),
                                'field_default' => get_option(I_Conf::OPT_DEFAULT_USER_LIST),
                                'field_data_attributes' => array('ajax-action' => 'ajax_build_campaign', 'email-action' => 'load_segments_and_defaults'),
                            ),
                            array(
                                'field_name' => 'imc_existing_email_segment',
                                'field_type' => 'hidden',
                                'field_value' => ''
                            ),
                            array(
                                'field_container' => 'div',
                                'field_id' => 'groups_holder',
                                'field_class' => 'hide',
                                'field_type' => 'html'
                            ),
                            array(
                                'field_container' => 'div',
                                'field_id' => 'segments_holder',
                                'field_class' => 'hide',
                                'field_type' => 'html'
                            ),
                            array(
                                'field_container' => 'div',
                                'field_class' => 'clearfix',
                                'field_type' => 'html'
                            )
                        )
                    ),
                    array(
                        'id' => 'email_sending_options',
                        'title' => __('Email Sending Options', 'integral-mailchimp'),
                        'context' => 'normal',
                        'priority' => 'high',
                        'fields' => array(
                            array(
                                'field_name' => 'imc_from_name',
                                'field_id' => 'imc_from_name',
                                'field_label' => __('From Name', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta pull-left',
                                'field_type' => 'text',
                                'tabindex'=> 103
                            ),
                            array(
                                'field_name' => 'imc_from_email',
                                'field_id' => 'imc_from_email',
                                'field_label' => __('From Email', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta pull-left',
                                'field_type' => 'text',
                                'tabindex'=> 104
                            ),
                            array(
                                'field_name' => 'imc_to_name',
                                'field_id' => 'imc_to_name',
                                'field_label' => __('To Name', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta pull-left',
                                'field_type' => 'text',
                                'field_default' => '*|FNAME|* *|LNAME|*',
                                'tabindex'=> 105
                            ),
                            array(
                                'field_container' => 'div',
                                'field_class' => 'clearfix',
                                'field_type' => 'html'
                            )
                        )
                    ),
                    array(
                        'id' => 'email_template',
                        'title' => __('Email Template', 'integral-mailchimp'),
                        'context' => 'normal',
                        'priority' => 'high',
                        'fields' => array(
                            array(
                                'field_name' => 'imc_user_email_templates',
                                'field_id' => 'imc_user_email_templates',
                                'field_label' => __('Your Saved Templates', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta pull-left',
                                'field_type' => 'select',
                                'tabindex'=> 106,
                                'field_class' => 'template-select form-control',
                                'field_data_attributes' => array('ajax-action' => 'load_mailchimp_template_info_ajax', 'ajax-target' => 'template_holder'),
                                'field_options' => Integral_Form_API::return_with_empty_option(Integral_MailChimp_Templates::load_mailchimp_templates('user'), array('none' => '----')),
                                'std' => ''
                            ),
                            array(
                                'field_name' => 'imc_gallery_email_templates',
                                'field_id' => 'imc_gallery_email_templates',
                                'field_label' => __('MailChimp Templates', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta pull-left',
                                'field_type' => 'select',
                                'tabindex'=> 107,
                                'field_class' => 'template-select form-control',
                                'field_data_attributes' => array('ajax-action' => 'load_mailchimp_template_info_ajax', 'ajax-target' => 'template_holder'),
                                'field_options' => Integral_Form_API::return_with_empty_option(Integral_MailChimp_Templates::load_mailchimp_templates('gallery'), array('none' => '----')),
                                'std' => ''
                            ),
                            array(
                                'field_name' => 'template_holder',
                                'field_id' => 'template_holder',
                                'field_type' => 'iframe',
                                'field_class' => 'hide',
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'template clearfix'
                            ),
                            array(
                                'field_name' => 'imc_content',
                                'field_id' => 'imc_content',
                                'field_type' => 'textarea',
                                'field_class' => '',
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'hide'
                            )
                        )
                    ),
                    array(
                        'id' => 'email_save_send',
                        'title' => __('Email Test and Send', 'integral-mailchimp'),
                        'context' => 'normal',
                        'priority' => 'high',
                        'fields' => array(
                            array(
                                'field_container' => 'div',
                                'field_class' => 'imc-message well',
                                'field_id' => 'imc-email-campaign-message',
                                'field_type' => 'html'
                            ),
                            array(
                                'field_name' => 'email_save_draft',
                                'field_id' => 'email_save_draft',
                                'field_label' => __('Save as Draft', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta pull-left',
                                'field_type' => 'button',
                                'tabindex'=> 108,
                                'field_class' => 'btn btn-primary form-control imc-email-process',
                                'field_data_attributes' => array('ajax-action' => 'email_save_draft'),
                                'field_default' => __('Save Draft', 'integral-mailchimp')
                            ),
                            array(
                                'field_name' => 'imc_email_send_test',
                                'field_id' => 'imc_email_send_test',
                                'field_label' => __('Enter an Email Address to Send a Test To', 'integral-mailchimp'),
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta clearfix pull-left',
                                'field_type' => 'email',
                                'tabindex'=> 109,
                                'field_class' => 'form-control',
                                'field_default' => ''
                            ),
                            array(
                                'field_name' => 'email_send_test_button',
                                'field_id' => 'email_send_test_button',
                                'field_label' => __('Send Test Email', 'integral-mailchimp'),
                                'field_type' => 'button',
                                'tabindex'=> 110,
                                'field_class' => 'btn btn-primary form-control imc-email-process',
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta pull-left',
                                'field_data_attributes' => array('ajax-action' => 'email_send_test'),
                                'field_default' => __('Send Test', 'integral-mailchimp')
                            ),
                            array(
                                'field_name' => 'email_send_blast',
                                'field_id' => 'email_send_blast',
                                'field_label' => __('Send Email to All Selected Subscribers', 'integral-mailchimp'),
                                'field_label_class' => 'center-block',
                                'field_type' => 'button',
                                'tabindex'=> 111,
                                'field_class' => 'btn btn-success btn-lg center-block imc-email-process',
                                'field_wrapper_element' => 'div',
                                'field_wrapper_class' => 'imc-meta clearfix well',
                                'field_data_attributes' => array('ajax-action' => 'email_send_blast'),
                                'field_default' => __('Send Email Now!', 'integral-mailchimp')
                            ),
                            array(
                                'field_name' => 'imc_campaign_id',
                                'field_type' => 'hidden',
                            //'field_value' => ''
                            ),
                            array(
                                'field_container' => 'div',
                                'field_class' => 'clearfix',
                                'field_type' => 'html'
                            )
                        )
                    )
                ),
                'remove_meta_boxes' => array(
                    'side' => array('submitdiv')
                ),
                //- Each column is an array in column id => col_title format
                //- 'category' is auto-handled and grabs any custom taxonomy tags for the post
                'custom_columns' => array(
                    'title' => __('Email Subject', 'integral-mailchimp'),
                    'email_date' => __('Date Sent', 'integral-mailchimp'),
                    'category' => __('Email Category', 'integral-mailchimp'),
                    'emails_sent' => __('# Subscribers', 'integral-mailchimp'),
                    'unique_opens' => __('Opens', 'integral-mailchimp'),
                    'hard_bounces' => __('Bounces', 'integral-mailchimp'),
                    'unique_clicks' => __('Clicks', 'integral-mailchimp'),
                ),
                'sortable_columns' => array('title', 'email_date', 'emails_sent', 'unique_opens', 'hard_bounces', 'unique_clicks')
            );

            return apply_filters('imc_modify_cpt', $post_info);


        }


        /**
         * Allows you to populate any custom columns you might have defined with postmeta values
         * @param type $column -- the name of the current column
         * @param type $post_id -- the id of the current post (iterates through each row in the CPT admin table view)
         * Note: It returns a single value so if your postmeta key isn't unique for this post then the results may not be what you expect
         */
        public function get_custom_column_values($column, $post_id) {
            $value = NULL;
            switch ($column) {
                case 'category':
                    $types = wp_get_post_terms($post_id, $this->post_info['taxonomy']['taxonomy_type']);
                    if ($types) {
                        foreach ($types as $type) {
                            $value[] = $type->name;
                        }
                        $value = implode(", ", $value);
                    }
                    break;

                case 'emails_sent':
                case 'unique_opens':
                case 'hard_bounces':
                case 'unique_clicks':
                    $campaign_stats = Integral_MailChimp_Reports::load_campaign_stats_by_post($post_id);
                    $value          = (!empty($campaign_stats[$column])) ? $campaign_stats[$column] : '-';

                    break;

                case 'email_date':
                    $value = '';
                    $post  = get_post($post_id);

                    if ('0000-00-00 00:00:00' == $post->post_date) {
                        $t_time    = $h_time    = __('Unpublished');
                        $time_diff = 0;
                    } else {
                        $t_time = get_the_time(__('Y/m/d g:i:s A'));
                        $m_time = $post->post_date;
                        $time   = get_post_time('G', true, $post);

                        $time_diff = time() - $time;

                        if ($time_diff > 0 && $time_diff < DAY_IN_SECONDS) {
                            $h_time = sprintf(__('%1$s ago'), human_time_diff($time));
                        } else {
                            $h_time = mysql2date(__('m/d/Y'), $m_time);
                        }
                    }

                    //- This filter is documented in wp-admin/includes/class-wp-posts-list-table.php
                    $value .= '<abbr title="' . $t_time . '">' . apply_filters('post_date_column_time', $h_time, $post, $column, 'list') . '</abbr>';
                    $value .= '<br />';

                    if ('publish' == $post->post_status) {
                        $value .= __('Sent');
                    } elseif ('future' == $post->post_status) {
                        if ($time_diff > 0) {
                            $value .= '<strong class="attention">' . __('Missed schedule', 'integral-mailchimp') . '</strong>';
                        } else {
                            $value .= __('Scheduled', 'integral-mailchimp');
                        }
                    } else {
                        $value .= __('Last Modified', 'integral-mailchimp');
                    }

                    break;

                default:
                    break;
            }

            if ($value) {
                echo $value;
            }


        }


        public function duplicate_post_meta($post_meta) {
            //- Remove the corresponding MailChimp Campaign, so it creates a new one
            unset($post_meta['imc_campaign_id']);

            return $post_meta;


        }


    }


    new Email_Campaigns_CPT_Controller();
}

