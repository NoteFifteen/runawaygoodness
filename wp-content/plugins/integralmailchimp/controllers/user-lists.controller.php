<?php

namespace IMC\Controllers;

use IMC\I_Conf;
use IMC\Library\Utility\I_Tools;
use IMC\Views\Admin_View;
use IMC\Library\Utility\Integral_MailChimp_Lists;
use IMC\Library\Utility\Integral_MailChimp_Helper;
use IMC\Library\Utility\Integral_Form_API;
use IMC\Models\User_Lists_Model;
use IMC\Models\IMC_Users_Model;
use IMC\Library\Utility\I_Cache;
use IMC\Library\Framework\Logger;
use IMC\Library\Utility\Integral_History_Log;

/**
 * Handles the viewing and processing of options for this plugin
 * 
 * 
 */
if (!class_exists('User_Lists_Controller')) {

    class User_Lists_Controller {


        private static $list_array        = NULL;
        private static $form_options      = NULL;
        private static $form_output       = NULL;
        private static $tab_content       = NULL;
        private static $batch_api_reponse = array();


        private static function _initialize_controller($with_view = TRUE) {

            I_Tools::initialize_controller(__FILE__, FALSE);
            I_Tools::load_models(array('user-lists', 'users'));
            I_Tools::load_utilities(array('integral-form-api'));

            if ($with_view) {
                I_Tools::load_view_master('admin');
                Admin_View::initialize_view();
                Admin_View::set_layout('admin');
                Admin_View::set_view(I_Tools::get_file_slug());
                Admin_View::set_title(ucwords(sprintf(__('%1$s Lists', 'integral-mailchimp'), 'Integral MailChimp')));
            }


        }


        /**
         * LIST MANAGEMENT FORM
         * ************************************************************************ */
        public static function list_management_form_view() {

            wp_enqueue_script('jquery-form');
            wp_enqueue_script('jquery-validate');
            wp_enqueue_script('jquery-ui-progressbar');
            wp_enqueue_script('imc-bootstrap-js');
            wp_enqueue_script('imc-mergetag-sync');

            wp_enqueue_style('imc-bootstrap');
            wp_enqueue_style('jquery-ui-progressbar');
            wp_enqueue_style('integral-mailchimp-admin');

            self::_initialize_controller();
            I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists'));

            self::_build_tag_info_boxes();
            Admin_View::$view->tab_content = self::$tab_content;

            Admin_View::render_view();


        }


        /**
         * Builds and prepares the form for output
         * 
         */
        private static function _build_tag_info_boxes() {
            //- Builds the form array
            self::$list_array = Integral_MailChimp_Lists::load_mailchimp_lists(array('with_counts' => FALSE));
            self::$list_array = !empty(self::$list_array) ? self::$list_array : array();

            if (!empty(self::$list_array)) {
                //- Build form tabs
                $tabs        = self::build_form_tabs();
                $tab_content = NULL;
                $i           = 0;

                $tab_content = I_Tools::format_inline_javascript(self::_build_sync_script());

                //- Start looping through form tab panes
                foreach (self::$list_array as $list_id => $list_name) {
                    $active        = ($i++ == 0) ? 'active' : '';
                    $format_string = '<h4>%1$s</h4><div class="imc-tag-description %5$s">%4$s<div class="imc-exists-legend"></div> - ' . __('Indicates a matching Merge Tag in MailChimp', 'integral-mailchimp') . '</div><div class="imc-tag-group well %2$s">%3$s</div>';
                    $tab_content .= "<div class='tab-pane {$active}' id='{$list_id}'>";

                    //- Build the Sync box
                    $sync_box   = self::_build_sync_box($list_id);
                    //- Grab the MC Merge Tags
                    $merge_tags = self::_build_merge_tags_array($list_id);
                    $fields     = self::_render_tab_content($merge_tags, $list_id);

                    $tab_content .= $sync_box;
                    $tab_content .= sprintf($format_string, __('Active', 'integral-mailchimp'), 'imc-tag-group-active', join("\n", $fields['active']), '(' . __('Merge Tags that will be synced with MailChimp', 'integral-mailchimp') . ')', 'imc-desc-active');
                    $tab_content .= sprintf($format_string, __('Inactive', 'integral-mailchimp'), 'imc-tag-group-inactive', join("\n", $fields['inactive']), '(' . __('Merge Tags that will not be synced with MailChimp', 'integral-mailchimp') . ')', 'imc-desc-inactive');
                    $tab_content .= sprintf($format_string, __('Unused', 'integral-mailchimp'), 'imc-tag-group-unused', join("\n", $fields['unused']), '(' . __('Merge Tags that are currently unavailable for syncing to MailChimp', 'integral-mailchimp') . ')', 'imc-desc-unused');
                    $tab_content .= "</div>";
                }
            } else {
                $tabs        = '';
                $tab_content = '<h3>' . __('No MailChimp Email Lists found!', 'integral-mailchimp') . '</h3>';
            }

            self::$tab_content = $tabs . $tab_content;


        }


        private static function _build_sync_box($list_id) {
            global $wp_roles;
            $role_options = $wp_roles->get_names();
            //unset($role_options['administrator']);

            Integral_Form_API::add_empty_option($role_options, array(0 => __('Select User Role', 'integral-mailchimp')));

            $field_id = 'imc_sync_role_' . $list_id;

            $sync_role_field_array = array(
                'field_name' => $field_id,
                'field_id' => $field_id,
                'field_label' => '',
                'field_type' => 'select',
                'field_wrapper_element' => 'div',
                'field_wrapper_class' => 'imc-sync-role-wrapper',
                'field_class' => 'imc-sync-role',
                'field_default' => 'subscriber',
                'field_data_attributes' => array('listid' => $list_id),
                'field_options' => $role_options
            );

            $field_id = 'imc_sync_button_' . $list_id;

            $sync_button_array = array(
                'field_name' => $field_id,
                'field_id' => $field_id,
                'field_label' => '',
                'field_type' => 'button',
                'field_wrapper_element' => 'div',
                'field_wrapper_class' => 'imc-sync-button-wrapper',
                'field_class' => 'imc-sync-button btn button-primary',
                'field_data_attributes' => array('listid' => $list_id),
                'field_value' => strtoupper(__('SYNC NOW', 'integral-mailchimp'))
            );

            $role_field = Integral_Form_API::build_form_field($sync_role_field_array);
            $role_field = isset($role_field['html']) ? $role_field['html'] : __('Error Loading User Roles', 'integral-mailchimp');

            $sync_button = Integral_Form_API::build_form_field($sync_button_array);
            $sync_button = isset($sync_button['html']) ? $sync_button['html'] : __('Error Loading Sync Button', 'integral-mailchimp');

            $progress_bar = "<div class='imc-progressbar'></div>";

            $sync_box = "<div class='imc_sync_wrapper well' data-listid='{$list_id}'><h4>" . __('Batch Update', 'integral-mailchimp') . "</h4><div class='imc_sync_form'>{$role_field} {$sync_button} <div class='imc_loading'>&nbsp;</div><div class='imc_sync_count'></div></div>{$progress_bar}</div>";

            return $sync_box;


        }


        private static function _build_sync_script() {

            $action = I_Conf::ADMIN_LIST_SYNC_USERS_ACTION;

            $role_select_error = __('Please select a User Role', 'integral-mailchimp');

            $script = <<<SCRIPT
                function getRandomInt(min, max) {
                    return Math.floor(Math.random() * (max - min + 1)) + min;
                }
                
                var currentList = false;
                var currentRole = false;
                
                var group_size  = 100;
				var total_users = -1;
                var group_start = -1;
                
                var currentUniqueID = 0;
                var syncReady       = 1;
                var sync_completed  = false;
                
                var processedUsersThisRound = 0;
                var processedUsersTotal = 0;
                
                $('.imc-sync-button').click(function() {
                    if (syncReady) {
                        
                        reset_process_variables();
                
                        $('.imc_sync_wrapper .imc_sync_form div.imc_loading').css('visibility', 'visible');
                
                        sync_completed  = false;
                        syncReady       = 0;
                        currentList     = $(this).data('listid');
                        currentRole     = $('.imc_sync_wrapper .imc-sync-role[data-listid='+ currentList +']').val();
                        currentUniqueID = getRandomInt(10000000, 99999999);
                
                        if (!currentRole) {
                            alert('{$role_select_error}');
                        } else {
                            setTimeout(process_next_group, 100);
                        }
                    }
                });
                
                function reset_process_variables() {
                    currentList = false;
                    currentRole = false;
                    group_start = 0;
                }

                function process_next_group() {
                    if (sync_completed !== true) {
                
                        //- If this is the first group, then send 0 as the group_start
                        if (group_start == -1) {
                            group_start = 0;
                        }
						
						//- If this is the first group, then send 0 as the total_users
                        if (total_users == -1) {
                            total_users = 0;
                        }

                        $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                dataType: "json",
                                data: {group_start: group_start, group_size: group_size, list_id: currentList, role: currentRole, unique_id: currentUniqueID, total_users: total_users, action: '{$action}'}
                                })
                                
                                //- Successful response
                                .done( function(response, status, jqXHR) {
                                    total_users             = response.totalUsers;
                                    processedUsersThisRound = response.currentProcessed;
                                    processedUsersTotal     = response.totalProcessed;
                                    sync_completed          = response.completed;
                                    group_start             = processedUsersTotal;
                                
                                    //- Uncomment this to abort long lists early for testing 
                                    /*if (processedUsersTotal >= 30) {
                                        sync_completed = true;
                                    }*/
                                
                                    if (total_users == 0) {
                                
                                        var sync_message = 'There are no users to sync at this time';
                                        $('.imc_sync_wrapper .imc_sync_form div.imc_loading').css('visibility', 'hidden');
                                        
                                        if ($('#imc_user_sync').length) {
                                            $('#imc_user_sync').slideUp('slow');
                                            setTimeout(function() {location.reload(true);}, 500);
                                        }

                                    } else {

                                        var sync_progress = Math.abs((processedUsersTotal / total_users) * 100);

                                        $('.imc_sync_wrapper[data-listid='+ currentList +'] .imc-progressbar').progressbar({
                                            value: sync_progress
                                        });

                                        if (sync_completed == true) {
                                            var completed = 'Sync Complete!';
                                            $('.imc_sync_wrapper .imc_sync_form div.imc_loading').css('visibility', 'hidden');
                                        
                                            if ($('#imc_user_sync').length) {
                                                $('#imc_user_sync').slideUp('slow');
                                                setTimeout(function() {location.reload(true);}, 500);
                                            }
                                
                                        } else {
                                            var completed = '';
                                        }
                                
                                        var sync_message = '('+ processedUsersTotal +' of '+ total_users +' Users Synced) '+ completed;
                                
                                        setTimeout(process_next_group, 1);
                                    }

                                    $('.imc_sync_wrapper[data-listid='+ currentList +'] .imc_sync_form div.imc_sync_count').text(sync_message);
                
                                })
                                
                                //- Error catching
                                .fail(function(jqhr, textStatus, error){
                                    var err = textStatus + ", " + error;
                                    console.log("Request Failed: " + err);
                                });
                        }
                }//- end process next group
SCRIPT;

            return $script;


        }


        public static function list_sync_users() {
            self::_initialize_controller();

            I_Tools::load_utilities(array('integral-history-log'));

            $run_batched = TRUE;

            //- get the $_POST or $_GET data, sanitized
            $data = I_Tools::fetch_post_all();

            $list_id     = 0;
            $role        = NULL;
            $unique_id   = 0;
            $group_start = 0;
            $group_size  = 0;
            $total_users = 0;
            $total       = 0;
            extract($data);
            //$group_size  = ($group_start == 0) ? '' : $group_size;
            $count       = 0;

            //- Setup the History Logging

            if ($list_id && $role) {

                //- If this is the first run, then get the total number of matching users
                $count_total = $total_users;

                //$users = array_values(self::filter_with_emails(get_users(array('role' => $role))));
                $users = self::get_users_with_email($count_total, array('role' => $role, 'offset' => $group_start, 'number' => $group_size, 'count_total' => ($total_users == 0)));

                if (is_array($users) && !empty($users)) {

                    if ($run_batched == TRUE) {

                        $option_log_name = 'imc_batch_sync-' . $list_id . '-' . $unique_id;
                        $log_entry       = get_option($option_log_name, array());
                        array_push($log_entry, $data);

                        //- Grab the total users if we requested it
                        if ($total_users == 0) {
                            $total_users = $count_total;
                        }

                        $api_response = self::add_batch_users_to_default_list($users, $list_id);

                        $success_count = self::_get_sync_success_count();
                        $error_count   = self::_get_sync_error_count();
                        
                        $count = $success_count + $error_count;
                        
                        array_push($log_entry, $api_response);

                        update_option($option_log_name, $log_entry);

                        $success = isset($api_response['list_ids']) && isset($api_response['list_ids'][$list_id]) && isset($api_response['list_ids'][$list_id]['success']) ? $api_response['list_ids'][$list_id]['success'] : FALSE;

                        if (!$success) {

                            $logger_message = 'There was an error adding a subscriber(s) to the default list during the Batch Update sync process in ' . __FUNCTION__ . '()';
                            $logger_items   = array('api_response' => $api_response, 'request_data' => $data);
                            Logger::log_error($logger_message, $logger_items);

                            $response = isset($api_response['lists']) && isset($api_response['lists'][$list_id]) && isset($api_response['lists'][$list_id]['response']) ? $api_response['lists'][$list_id]['response'] : FALSE;
                        }
                    } else {
                        //- Get the total number of users on the first loop through
                        if ($total_users == 0) {
                            $total_users = count($users);
                        }

                        $group_size = ($group_start == 0) ? 10 : $group_size;
                        $limit      = (($group_start + $group_size) < $total_users) ? $group_start + $group_size : $total_users;
                        for ($i = $group_start; $i < $limit; $i++) {
                            $user = $users[$count];
                            $count++;

                            //- Try to add them first
                            //- TODO - Consider allowing users to be added to all available or selected lists vs just the default
                            $api_response = self::add_user_to_default_list($user, $list_id, FALSE);

                            $success = isset($api_response['list_ids']) && isset($api_response['list_ids'][$list_id]) && isset($api_response['list_ids'][$list_id]['success']) ? $api_response['list_ids'][$list_id]['success'] : FALSE;

                            if (!$success) {

                                $logger_message = 'There was an error adding a subscriber to the default list during the Batch Update sync process in ' . __FUNCTION__ . '()';
                                $logger_items   = array('api_response' => $api_response, 'user_data' => $user);
                                Logger::log_error($logger_message, $logger_items);

                                $response = isset($api_response['lists']) && isset($api_response['lists'][$list_id]) && isset($api_response['lists'][$list_id]['response']) ? $api_response['lists'][$list_id]['response'] : FALSE;
                                if (is_array($response) && isset($response['type'])) {
                                    switch ($response['type']) {
                                        case 'MailChimp_List_AlreadySubscribed':

                                            //- Update if unable to add
                                            $api_response = self::update_user_in_all_lists($user, $user, $list_id, FALSE);
                                            $success      = isset($api_response['lists']) && isset($api_response['lists'][$list_id]) && isset($api_response['lists'][$list_id]['success']) ? $api_response['lists'][$list_id]['success'] : FALSE;

                                            if (!$success) {
                                                $logger_message = 'There was an error updating a subscriber in all lists during the Batch Update sync process in ' . __FUNCTION__ . '()';
                                                $logger_items   = array('api_response' => $api_response, 'user_data' => $user);
                                                Logger::log_error($logger_message, $logger_items);
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!get_option(I_Conf::OPT_USERS_SYNCED)) {
                update_option(I_Conf::OPT_USERS_SYNCED, TRUE);
            }

            if ($count < $group_size) {
                $response['completed'] = true;
            } else {
                $response['completed'] = false;
            }

            $response['totalProcessed']   = $group_start + $count;
            $response['currentProcessed'] = $count;
            $response['totalUsers']       = $total_users;
            
            wp_send_json($response);


        }


        private static function _get_sync_success_count() {
            $add_count     = 0;
            $update_count  = 0;
            $success_count = 0;
            
            if (isset(self::$batch_api_reponse['list_ids']) && is_array(self::$batch_api_reponse['list_ids'])) {
                foreach (self::$batch_api_reponse['list_ids'] as $list_id => $api_response) {
                    $add_count += isset($api_response['response']['add_count']) ? $api_response['response']['add_count'] : 0;
                    $update_count += isset($api_response['response']['update_count']) ? $api_response['response']['update_count'] : 0;
                }
                $success_count = $add_count + $update_count;
            }
            
            return $success_count;


        }


        private static function _get_sync_error_count() {
            $error_count = 0;

            if (isset(self::$batch_api_reponse['list_ids']) && is_array(self::$batch_api_reponse['list_ids'])) {
                foreach (self::$batch_api_reponse['list_ids'] as $list_id => $api_response) {
                    $error_count += isset($api_response['response']['error_count']) ? $api_response['response']['error_count'] : 0;
                }
            }
            
            return $error_count;


        }


        public static function get_users_with_email(&$count_total, $args = array()) {

            $args        = wp_parse_args($args);
            //$args['count_total'] = false;
            $user_query  = new \WP_User_Query(array(
                'role' => $args['role'],
                'number' => $args['number'],
                'offset' => $args['offset'],
                'count_total' => $args['count_total'],
                'search' => '*@*'
                , 'search_columns' => array('user_email')
            ));
            $count_total = $args['count_total'] ? $user_query->get_total() : 0;
            return (array) $user_query->results;


        }


        private static function filter_with_emails($users) {

            foreach ($users as $key => $user) {
                if ($user->data->user_email == '') {
                    unset($users[$key]);
                }
            }

            return $users;


        }


        public static function build_form_tabs() {
            $list_array = self::$list_array;
            $tabs       = '<ul class="nav nav-tabs" role="tablist">';
            $i          = 0;
            foreach ($list_array as $list_id => $list_name) {
                $active = ($i++ == 0) ? 'active' : NULL;
                $tabs.="<li class='{$active}'><a href='#{$list_id}' role='tab' data-toggle='tab'>{$list_name}</a></li>";
            }
            $tabs.="</ul>";
            return $tabs;


        }


        /**
         * Builds an array of all possible items that can be sync'd, but doesn't indicate whether they are 
         * currently being sync'd or not
         * 
         */
        private static function _build_merge_tags_array($list_id) {

            I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists'));

            //- Grab any merge tags being created by other plugins (runs the apply_filters() for fetching merge tags from other plugins)
            $plugin_merge_tags = Integral_MailChimp_Lists::load_mailchimp_plugin_sync_merge_tags(FALSE);

            //- Load the existing merge tags from MailChimp
            $remote_list_merge_tags = Integral_MailChimp_Lists::load_mailchimp_merge_tags($list_id);

            //- Check which merge tags are currently actively sync'd
            $syncd_merge_tags = maybe_unserialize(get_option('imc_list_sync_tags_' . $list_id));

            if (!is_array($syncd_merge_tags)) {
                $syncd_merge_tags = array();
            }

            //- If there are any remote Merge Tags in MailChimp
            if (is_array($remote_list_merge_tags) && !empty($remote_list_merge_tags)) {

                $combined_merge_tags = array_merge($remote_list_merge_tags, $plugin_merge_tags);


                foreach ($combined_merge_tags as $merge_tag => $merge_tag_info) {

                    //- If the Merge Tag exists in MailChimp add a flag
                    if (isset($remote_list_merge_tags[$merge_tag])) {
                        $combined_merge_tags[$merge_tag]['in_mailchimp'] = TRUE;
                    }

                    if (in_array($merge_tag, $syncd_merge_tags)) {
                        $combined_merge_tags[$merge_tag]['status'] = 'active';
                    } elseif (!array_key_exists($merge_tag, $plugin_merge_tags)) {
                        $combined_merge_tags[$merge_tag]['status'] = 'unused';
                    } else {
                        $combined_merge_tags[$merge_tag]['status'] = 'inactive';
                    }
                }

                //- Otherwise just show the Merge Tags provided by the plugins
            } else {
                $combined_merge_tags = $plugin_merge_tags;
            }

            return $combined_merge_tags;


        }


        private static function _render_tab_content($merge_tags, $list_id) {
            $format_string      = '<div id="%1s" class="imc-tag-info-box well %2$s %8$s" data-merge-tag="%4$s" data-list-id="%7$s"><div class="imc-drag-handle"></div><h4>%3$s</h4><p class="imc-plugin-tag">' . __('Merge Tag', 'integral-mailchimp') . ': |%4$s|</p><p class="imc-plugin-tag">' . __('Field Type', 'integral-mailchimp') . ': (%5$s)</p><p class="imc-plugin-name">' . __('Source', 'integral-mailchimp') . ': %6$s</p></div>';
            $fields             = array();
            $fields['unused']   = array();
            $fields['active']   = array();
            $fields['inactive'] = array();

            foreach ($merge_tags as $merge_tag => $merge_tag_info) {

                $plugin_name  = isset($merge_tag_info['plugin_name']) ? $merge_tag_info['plugin_name'] : '&nbsp;';
                $in_mailchimp = isset($merge_tag_info['in_mailchimp']) ? 'exists_in_mailchimp' : '';

                $element_id = isset($merge_tag_info['id']) ? $merge_tag_info['id'] : '';

                $fields[$merge_tag_info['status']][$merge_tag] = sprintf($format_string, $element_id, $merge_tag_info['status'], $merge_tag_info['name'], $merge_tag, $merge_tag_info['field_type'], $plugin_name, $list_id, $in_mailchimp);
            }

            return $fields;


        }


        /**
         * Processes and saves the form submission
         * 
         */
        public static function list_sync_merge_tag() {

            self::_initialize_controller();
            I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists'));

            //User_Lists_Forms::initialize_forms();

            $form_data = I_Tools::fetch_ajax_post();

            $errors       = array();
            $imc_messages = array();

            if (isset($form_data['list_id']) && isset($form_data['merge_tag'])) {

                $list_id   = $form_data['list_id'];
                $merge_tag = $form_data['merge_tag'];

                //- Check which merge tags are currently sync'd
                $syncd_merge_tags = maybe_unserialize(get_option('imc_list_sync_tags_' . $list_id));

                if (!is_array($syncd_merge_tags)) {
                    $syncd_merge_tags = array();
                }

                //- If this merge tag is already active, then we deactivate it ... and no need to do anything with the API
                if (in_array($merge_tag, $syncd_merge_tags)) {
                    if (($key = array_search($merge_tag, $syncd_merge_tags)) !== false) {
                        unset($syncd_merge_tags[$key]);
                    }
                    update_option('imc_list_sync_tags_' . $list_id, $syncd_merge_tags);

                    $imc_messages[] = __('The Merge Tag was Deactivated', 'integral-mailchimp');

                    //- Else we need to activate it and if it's not already added to MailChimp then add it via the API
                } else {
                    $syncd_merge_tags[] = $merge_tag;
                    update_option('imc_list_sync_tags_' . $list_id, $syncd_merge_tags);

                    $imc_messages[] = __('The Merge Tag was Activated', 'integral-mailchimp');

                    //- Fetch the syncable merge tags from the plugins
                    $plugin_merge_tags = (array) Integral_MailChimp_Lists::load_mailchimp_plugin_sync_merge_tags(FALSE);

                    if (is_array($plugin_merge_tags) && isset($plugin_merge_tags[$merge_tag])) {
                        $mergetag_field_types = array('text', 'number', 'radio', 'dropdown', 'date', 'address', 'phone', 'url', 'imageurl', 'zip', 'birthday');

                        $tag_options               = array();
                        $tag_options['field_type'] = (isset($plugin_merge_tags[$merge_tag]['field_type']) && in_array($plugin_merge_tags[$merge_tag]['field_type'], $mergetag_field_types)) ? $plugin_merge_tags[$merge_tag]['field_type'] : 'text';
                        $tag_options['public']     = (isset($plugin_merge_tags[$merge_tag]['public'])) ? $plugin_merge_tags[$merge_tag]['public'] : TRUE;
                        $tag_options['show']       = (isset($plugin_merge_tags[$merge_tag]['show'])) ? $plugin_merge_tags[$merge_tag]['show'] : TRUE;
                        //- TODO - Take advantage of the other Merge Tag options (public, show, etc)

                        $tag_name = isset($plugin_merge_tags[$merge_tag]['name']) ? $plugin_merge_tags[$merge_tag]['name'] : '';

                        //- Load the existing merge tags from MailChimp to determine if we save or update below
                        $existing_merge_tags = Integral_MailChimp_Lists::load_mailchimp_merge_tags($list_id);

                        if (isset($existing_merge_tags[$merge_tag])) {
                            //- Update merge tag
                            $tag_options['name'] = $tag_name;
                            unset($tag_options['field_type']);

                            $response       = Integral_MailChimp_Lists::update_mailchimp_merge_tags($list_id, $merge_tag, $tag_options);
                            $imc_messages[] = __('The Merge Tag was Updated in MailChimp', 'integral-mailchimp');
                        } else {
                            //- Create merge tag
                            $response       = Integral_MailChimp_Lists::save_mailchimp_merge_tags($list_id, $merge_tag, $tag_name, $tag_options);
                            $imc_messages[] = __('The Merge Tag was Added to MailChimp', 'integral-mailchimp');
                        }

                        //- Else the Merge Tag provided did not match any provided by the plugin filter
                    } else {
                        $errors[] = __('Error Processing Merge Tag', 'integral-mailchimp');

                        $logger_message = 'Loading the Merge Tags from the plugins failed in ' . __FUNCTION__ . '()';
                        $logger_items   = array('plugin_merge_tags' => $plugin_merge_tags, 'form_data' => $form_data);
                        Logger::log_error($logger_message, $logger_items);
                    }
                }
            } else {
                $errors[] = __('Error Processing Merge Tag', 'integral-mailchimp');

                $logger_message = 'Invalid form values in ' . __FUNCTION__ . '()';
                $logger_items   = array('form_data' => $form_data);
                Logger::log_error($logger_message, $logger_items);
            }


            if (empty($errors)) {

                $status_message = join('<br>', $imc_messages);
                wp_send_json(array('msg' => $status_message));
            } else {

                wp_send_json($errors);
            }


        }


        /**
         * FILTER CALLBACKS
         * ************************************************************************ */
        public static function get_syncable_mergetags($tags) {
            $tags['FNAME']     = array(
                'name' => __('First Name', 'integral-mailchimp'),
                'field_type' => 'text',
                'plugin_name' => 'WordPress'
            );
            $tags['LNAME']     = array(
                'name' => __('Last Name', 'integral-mailchimp'),
                'field_type' => 'text',
                'plugin_name' => 'WordPress'
            );
            $tags['NICKNAME']  = array(
                'name' => __('Nickname', 'integral-mailchimp'),
                'field_type' => 'text',
                'plugin_name' => 'WordPress'
            );
            $tags['USER_ROLE'] = array(
                'name' => __('User Role', 'integral-mailchimp'),
                'field_type' => 'text',
                'plugin_name' => 'WordPress',
                'public' => FALSE,
                'show' => FALSE
            );

            return $tags;


        }


        public static function get_mergetag_values_for_sync($tags, $user) {
            $user_meta         = get_user_meta($user->ID);
            global $wpdb;
            $tags['FNAME']     = reset($user_meta['first_name']);
            $tags['LNAME']     = reset($user_meta['last_name']);
            $tags['NICKNAME']  = reset($user_meta['nickname']);
            $tags['USER_ROLE'] = key(unserialize(reset($user_meta[$wpdb->prefix . 'capabilities'])));
            return $tags;


        }


        public static function update_mergetag_values_from_sync($tags, $user) {

            if (isset($tags['FNAME'])) {
                update_user_meta($user->ID, 'first_name', $tags['FNAME']);
            }

            if (isset($tags['LNAME'])) {
                update_user_meta($user->ID, 'last_name', $tags['LNAME']);
            }

            if (isset($tags['NICKNAME'])) {
                update_user_meta($user->ID, 'nickname', $tags['NICKNAME']);
            }

            //- We don't update USER_ROLE for obvious security reasons and
            //- it is a hidden field so they shouldn't be able to modify via MailChimp anyway


        }


        /**
         * ADDING USERS TO LISTS
         * ************************************************************************ */


        /**
         * Adds a user in a specific site in a network to the default remote MailChimp list
         * 
         * @param int $user_id
         */
        public static function add_blog_user_to_default_list($user_id, $role, $blog_id) {

            switch_to_blog($blog_id);

            self::add_user_to_default_list($user_id);

            restore_current_blog();


        }


        /**
         * Adds a user to the default remote MailChimp list
         * - Also prepares a response for showing on the next page load
         * 
         * @param int $user_id
         */
        public static function add_user_to_default_list($user_id, $list_id = NULL, $show_updated = TRUE) {

            self::_initialize_controller();

            if (!I_Cache::load_transient('skip_subs')) {

                //- If we were passed a WP_User then use it or load one based on the user_id
                $user = (is_a($user_id, 'WP_User')) ? $user_id : IMC_Users_Model::load_user_by_id($user_id);

                if (isset($user->user_email) && sanitize_email($user->user_email)) {
                    I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists'));

                    $response = array();

                    $default_list_id = $list_id ? : User_Lists_Model::load_user_lists_database_values(I_Conf::OPT_DEFAULT_USER_LIST);
                    $all_lists       = Integral_MailChimp_Lists::load_mailchimp_lists(array('with_counts' => FALSE));

                    $options                 = Integral_MailChimp_Lists::build_list_subscribe_defaults();
                    $options['send_welcome'] = FALSE;

                    //- We make this an array since we may have multiple default lists in the future
                    $lists_array = array($default_list_id => $all_lists[$default_list_id]);

                    $plugins_merge_tags = Integral_MailChimp_Lists::get_plugins_merge_tags($user);

                    $response['list_ids']   = array();
                    $response['list_names'] = array();

                    foreach ($lists_array as $list_id => $list_name) {

                        $allowed_merge_tags = maybe_unserialize(get_option('imc_list_sync_tags_' . $list_id));

                        if (!is_array($allowed_merge_tags)) {
                            $allowed_merge_tags = array();
                        }

                        $final_merge_tags = array_intersect_key($plugins_merge_tags, array_flip($allowed_merge_tags));

                        $options['merge_tags'] = $final_merge_tags;

                        $api_response = Integral_MailChimp_Lists::add_user_to_email_list($user->user_email, $list_id, $options);

                        $response['list_ids'][$list_id]     = $api_response;
                        $response['list_names'][$list_name] = $api_response;
                    }

                    $response['user_id']    = $user_id;
                    $response['user_email'] = $user->user_email;

                    if ($show_updated) {
                        I_Cache::save_transient('user_saved', $response, 2 * HOUR_IN_SECONDS);
                    } else {
                        return $response;
                    }
                } else {
                    $logger_message = 'User missing email address during subscribe in ' . __FUNCTION__ . '()';
                    $logger_items   = array('user_data' => $user);
                    Logger::log_warning($logger_message, $logger_items);
                }

                return FALSE;
            }

            I_Cache::delete_transient('skip_subs');


        }


        /**
         * Adds a list of users to the default remote MailChimp list
         * - Also prepares a response for showing on the next page load
         * 
         * @param array - (int) $user_ids
         */
        public static function add_batch_users_to_default_list($user_ids, $list_id = NULL) {

            self::_initialize_controller();

            $users = array();

            if (!I_Cache::load_transient('skip_subs')) {
                I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists'));

                //- 1) Load all the users from the passed array of user_id's
                //- 2) Load all the default lists
                //- 3) Iterate through each list
                //-     4) Grab the merge tags for each user for the current list
                //-     5) Batch send all users to the current list
                //
                //
                //
                //- 1) Load all the users from the passed array of user_id's
                foreach ($user_ids as $user_id) {
                    //- If we were passed a WP_User then use it or load one based on the user_id
                    $user = (is_a($user_id, 'WP_User')) ? $user_id : IMC_Users_Model::load_user_by_id($user_id);

                    if (isset($user->user_email) && sanitize_email($user->user_email)) {

                        $plugins_merge_tags = Integral_MailChimp_Lists::get_plugins_merge_tags($user);

                        $users_preliminary[] = array('user_id' => $user->user_id, 'email' => $user->user_email, 'merge_tags' => $plugins_merge_tags);
                    } else {
                        $logger_message = 'User missing email address during subscribe in ' . __FUNCTION__ . '()';
                        $logger_items   = array('user_data' => $user);
                        Logger::log_warning($logger_message, $logger_items);
                    }
                }

                $num_users = count($users_preliminary);

                $response = array();

                //- 2) Load all default lists
                $default_list_id = $list_id ? : User_Lists_Model::load_user_lists_database_values(I_Conf::OPT_DEFAULT_USER_LIST);
                $all_lists       = Integral_MailChimp_Lists::load_mailchimp_lists(array('with_counts' => FALSE));

                $options                      = Integral_MailChimp_Lists::build_list_subscribe_defaults();
                $options['send_welcome']      = FALSE;
                $options['update_existing']   = TRUE;
                $options['double_optin']      = FALSE;
                $options['replace_interests'] = FALSE;

                //- We make this an array since we may have multiple default lists in the future
                $lists_array = array($default_list_id => $all_lists[$default_list_id]);

                $response['list_ids']   = array();
                $response['list_names'] = array();

                //- 3) Iterate through each of the lists
                foreach ($lists_array as $list_id => $list_name) {

                    $allowed_merge_tags = maybe_unserialize(get_option('imc_list_sync_tags_' . $list_id));

                    if (!is_array($allowed_merge_tags)) {
                        $allowed_merge_tags = array();
                    }

                    //- 4) Grab the merge tags for each user in this list
                    foreach ($users_preliminary as $user_preliminary) {

                        $final_merge_tags = array_intersect_key($user_preliminary['merge_tags'], array_flip($allowed_merge_tags));

                        $users[] = array('email' => array('email' => $user_preliminary['email']), 'email_type' => 'html', 'merge_vars' => $final_merge_tags);

                        $response['users'][] = array('user_id' => $user_preliminary['user_id'], 'user_email' => $user->user_email);
                    }

                    //- 5) Batch send all users to this list
                    $api_response = Integral_MailChimp_Lists::add_batch_users_to_email_list($users, $list_id, $options);

                    //- If something went wrong log an error
                    if (isset($api_response['error_count']) && $api_response['error_count'] > 0) {
                        
                    } else if (isset($api_response['add_count']) && isset($api_response['update_count']) && ($api_response['add_count'] + $api_response['update_count']) != $num_users) {
                        
                    }


                    $response['list_ids'][$list_id]     = $api_response;
                    $response['list_names'][$list_name] = $api_response;
                }

                self::$batch_api_reponse = $response;

                return $response;

                //return FALSE;
            }

            I_Cache::delete_transient('skip_subs');


        }


        /**
         * Displays the generated response from add_user_to_default_list()
         *
         */
        public static function user_added_to_list() {

            self::_initialize_controller(I_Conf::CONTROLLER_WITHOUT_VIEW);

            //- TODO: This filtering needs to be redone at some point, we may not be on the users.php page for all situations

            $in_filename = strstr($_SERVER['PHP_SELF'], 'users.php');

            if ($in_filename) {

                $get_values = I_Tools::fetch_get_all();

                if (isset($get_values['id']) && is_numeric($get_values['id'])) {

                    $trans_response = I_Cache::load_transient('user_saved');

                    if (is_array($trans_response) && !empty($trans_response)) {

                        I_Cache::delete_transient('user_saved');

                        //- $lists, $user_id, $user_email
                        $list_names = array();
                        $user_id    = FALSE;
                        $user_email = '';
                        extract($trans_response);

                        foreach ($list_names as $list_name => $response) {

                            if (isset($response['success']) && $response['success']) {

                                echo "<div class='updated'><p>" . sprintf(__("The user's " . 'email (%1$s) was successfully subscribed to the \'%2$s\' MailChimp list.', 'integral-mailchimp'), $user_email, $list_name) . "</p></div>";
                            } else {

                                $error_type = self::_parse_response_exception_type($response);

                                switch ($error_type) {
                                    case 'Mailchimp_List_AlreadySubscribed':
                                        $message = sprintf(__('%1$s is already subscribed to the \'%2$s\' MailChimp list.', 'integral-mailchimp'), $user_email, $list_name);
                                        break;
                                    default:
                                        $message = sprintf(__("There was an error subscribing the user's " . 'email (%1$s) to the \'%2$s\' MailChimp list.', 'integral-mailchimp'), $user_email, $list_name);
                                        break;
                                }

                                echo "<div class='error'><p>{$message}</p></div>";
                            }
                        }
                    } else {
                        return;
                    }
                }
            }


        }


        /**
         * REMOVING USERS FROM LISTS
         * ************************************************************************ */


        /**
         * Removes a user in a specific site in a network from all remote MailChimp lists
         *
         * @param int $user_id
         * @param int $blog_id
         */
        public static function remove_blog_user_from_all_lists($user_id, $blog_id) {

            switch_to_blog($blog_id);

            self::remove_user_from_all_lists($user_id);

            restore_current_blog();


        }


        /**
         * Removes a user from all remote MailChimp lists
         * - Also prepares a response for showing on the next page load
         *
         * @param int $user_id
         */
        public static function remove_user_from_all_lists($user_id) {

            self::_initialize_controller(I_Conf::CONTROLLER_WITHOUT_VIEW);
            I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists', 'integral-mailchimp-helper'));

            $user                   = IMC_Users_Model::load_user_by_id($user_id);
            $options                = apply_filters('imc_list_unsubscribe_defaults', Integral_MailChimp_Lists::build_list_unsubscribe_defaults());
            $subscribed_lists       = Integral_MailChimp_Helper::load_mailchimp_lists_by_email($user->user_email);
            $response               = Integral_MailChimp_Lists::remove_user_from_email_lists($user->user_email, $subscribed_lists, $options);
            $response['user_id']    = $user_id;
            $response['user_email'] = $user->user_email;
            I_Cache::save_transient('user_deleted', $response, 2 * HOUR_IN_SECONDS);


        }


        /**
         * Displays the generated response from remove_user_from_all_lists()
         *
         */
        public static function user_removed_from_lists() {

            self::_initialize_controller(I_Conf::CONTROLLER_WITHOUT_VIEW);

            //- TODO: This filtering needs to be redone at some point, we may not be on the users.php page for all situations

            $in_filename = strstr($_SERVER['PHP_SELF'], 'users.php');

            if ($in_filename) {

                $get_values = I_Tools::fetch_get_all();

                if (isset($get_values['delete_count']) && is_numeric($get_values['delete_count']) && isset($get_values['update']) && 'del' == $get_values['update']) {

                    $trans_response = I_Cache::load_transient('user_deleted');

                    if (is_array($trans_response) && !empty($trans_response)) {

                        I_Cache::delete_transient('user_deleted');

                        $user_email = '';
                        extract($trans_response);

                        if (isset($success) && is_array($success)) {

                            foreach ($success as $list_name => $status) {

                                if ($status) {

                                    echo "<div class='updated'><p>" . sprintf(__("The user's " . 'email (%1$s) was successfully unsubscribed from the \'%2$s\' MailChimp list.', 'integral-mailchimp'), $user_email, $list_name) . "</p></div>";
                                } else {
                                    
                                }
                            }
                        } else {
                            $message = __('There was a generic error during the unsubscribe process', 'integral-mailchimp');
                            echo "<div class='error'><p>{$message}</p></div>";
                        }
                    } else {
                        return;
                    }
                }
            }


        }


        /**
         * UPDATING USERS IN LISTS
         * ************************************************************************ */


        /**
         * Updates merge tags, etc. in all MailChimp lists that the user is subscribed to
         * 
         * @param int $user_id
         * @param object $old_user_data
         */
        public static function update_user_in_all_lists($user_id, $old_user_data, $list_id = NULL, $show_response = TRUE) {

            self::_initialize_controller(I_Conf::CONTROLLER_WITHOUT_VIEW);
            I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists', 'integral-mailchimp-helper'));

            $user = (is_a($user_id, 'WP_User')) ? $user_id : IMC_Users_Model::load_user_by_id($user_id);

            if (isset($user->user_email) && sanitize_email($user->user_email)) {
                $new_email = $user->user_email;
                $old_email = $old_user_data->user_email;

                if ($list_id) {
                    $all_lists        = Integral_MailChimp_Lists::load_mailchimp_lists(array('with_counts' => FALSE));
                    $subscribed_lists = array($list_id => $all_lists[$list_id]);
                } else {
                    $subscribed_lists = Integral_MailChimp_Helper::load_mailchimp_lists_by_email($old_email);
                }

                if ($subscribed_lists && is_array($subscribed_lists)) {

                    $options = Integral_MailChimp_Lists::build_list_subscribe_defaults();

                    $options['update_existing'] = TRUE;

                    $plugins_merge_tags = Integral_MailChimp_Lists::get_plugins_merge_tags($user);

                    foreach ($subscribed_lists as $list_id => $list_name) {

                        $allowed_merge_tags = maybe_unserialize(get_option('imc_list_sync_tags_' . $list_id));

                        if (!is_array($allowed_merge_tags)) {
                            $allowed_merge_tags = array();
                        }

                        $final_merge_tags              = array_intersect_key($plugins_merge_tags, array_flip($allowed_merge_tags));
                        ($new_email != $old_email) ? $final_merge_tags['new-email'] = $new_email : NULL;

                        $options['merge_tags'] = $final_merge_tags;

                        $response['lists'][$list_name] = Integral_MailChimp_Lists::update_user_in_email_list($old_email, $list_id, $options);
                    }

                    $response['user_id']   = $user_id;
                    $response['old_email'] = $old_email;
                    $response['new_email'] = $new_email;

                    if ($show_response) {
                        I_Cache::save_transient('user_updated', $response, 2 * HOUR_IN_SECONDS);
                    } else {
                        return $response;
                    }
                }
            } else {
                $logger_message = 'User missing email address during update in ' . __FUNCTION__ . '()';
                $logger_items   = array('user_data' => $user);
                Logger::log_warning($logger_message, $logger_items);
            }

            return FALSE;


        }


        /**
         * Displays the generated response from update_user_in_all_lists()
         *
         */
        public static function user_updated_in_all_lists() {
            self::_initialize_controller(I_Conf::CONTROLLER_WITHOUT_VIEW);

            $in_filename = strstr($_SERVER['PHP_SELF'], 'user-edit.php');

            if ($in_filename) {

                $get_values = I_Tools::fetch_get_all();

                if (isset($get_values['user_id']) && is_numeric($get_values['user_id']) && isset($get_values['updated']) && $get_values['updated']) {

                    $trans_response = I_Cache::load_transient('user_updated');

                    if (is_array($trans_response) && !empty($trans_response)) {

                        I_Cache::delete_transient('user_updated');

                        $new_email = '';
                        extract($trans_response);

                        if (isset($lists) && is_array($lists)) {
                            foreach ($lists as $list_name => $response) {

                                if (isset($response['success']) && $response['success']) {

                                    echo "<div class='updated'><p>" . sprintf(__('The user (%1$s) was successfully updated in the \'%2$s\' MailChimp list.', 'integral-mailchimp'), $new_email, $list_name) . "</p></div>";
                                } else {

                                    $error_type = self::_parse_response_exception_type($response);

                                    switch ($error_type) {
                                        case 'Mailchimp_List_AlreadySubscribed':
                                            $message = sprintf(__('%1$s is already subscribed to the \'%2$s\' MailChimp list.', 'integral-mailchimp'), $new_email, $list_name);
                                            break;
                                        default:
                                            $message = sprintf(__('There was an error updating the user (%1$s) in the \'%2$s\' MailChimp list.', 'integral-mailchimp'), $new_email, $list_name);
                                            break;
                                    }

                                    echo "<div class='error'><p>{$message}</p></div>";
                                }
                            }
                        }
                    } else {
                        return;
                    }
                }
            }


        }


        /**
         * Extracts the Exception type from a response
         * 
         * @param array $trans_response
         * @return boolean or string
         */
        private static function _parse_response_exception_type($trans_response) {
            $type = NULL;

            extract($trans_response);
            extract($response);

            if ($type) {
                return $type;
            }

            return FALSE;


        }


    }


}

