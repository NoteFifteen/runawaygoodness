<?php

namespace IMC\Controllers;

use IMC\I_Conf;
use IMC\Library\Utility\I_Tools;
use IMC\Views\Admin_View;
use IMC\Library\Utility\Integral_MailChimp_Lists;
use IMC\Library\Utility\Integral_MailChimp_Helper;
use IMC\Models\User_Lists_Model;
use IMC\Library\Utility\I_Cache;
use IMC\Library\Framework\Logger;

/**
 * Handles the viewing and processing of options for this plugin
 * 
 * 
 */
if (!class_exists('List_Groups_Controller')) {

    class List_Groups_Controller {


        private static $list_array  = NULL;
        private static $tab_content = NULL;


        private static function _initialize_controller($with_view = TRUE) {

            I_Tools::initialize_controller(__FILE__);

            I_Tools::load_utilities(array('integral-form-api'));

            if ($with_view) {
                I_Tools::load_view_master('admin');
                Admin_View::initialize_view();
                Admin_View::set_layout('admin');
                Admin_View::set_view(I_Tools::get_file_slug());
                Admin_View::set_title(ucwords(sprintf(__('%s Lists', 'integral-mailchimp'), 'Integral MailChimp')));
            }


        }


        /**
         * LIST GROUP MANAGEMENT FORM
         * ************************************************************************ */
        public static function group_management_form() {

            self::_initialize_controller();
            I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists'));

            self::_build_tag_info_boxes();
            Admin_View::$view->tab_content = self::$tab_content;

            Admin_View::render_view();


        }


        /**
         * Steps:
         * - Fetch groups from MailChimp
         * - For each list, build a group management "form" (we're not using the full form builder, just the field builder)
         *      - 1 x text field for a NEW INTEREST GROUPING
         *      - A section for each GROUPING created
         *          - listing of the existing GROUPS created
         *          - 1 x text field for a NEW INTEREST GROUP
         * 
         */


        /**
         * Assembles the tabs and tab content for this page
         * 
         */
        private static function _build_tag_info_boxes() {

            //- Builds the form array
            self::$list_array = Integral_MailChimp_Lists::load_mailchimp_lists(array('with_counts' => FALSE));

            //- Build form tabs
            $tabs        = self::build_form_tabs();
            $tab_content = NULL;
            $i           = 0;

            //- Start looping through form tab panes
            foreach (self::$list_array as $list_id => $list_name) {
                $active        = ($i++ == 0) ? 'active' : '';
                $format_string = '<h4>%1$s</h4><div class="imc-tag-group %2$s">%3$s</div>';
                $tab_content .= "<div class='tab-pane {$active}' id='{$list_id}'>";
                //- grab the MC Merge Tags
                $groups        = array();// self::_build_groups_array($list_id, $list_name);
                $fields        = self::_render_tab_content($groups, $list_id);
                $tab_content .= sprintf($format_string, 'Active', 'imc-tag-group-active', join("\n", $fields['active']));
                $tab_content .= "</div>";
            }

            self::$tab_content = $tabs . $tab_content;


        }


        /**
         * Builds the Bootstrap clickable tabs
         * 
         * @return string
         */
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
         * Builds an array of groups from MailChimp
         * 
         */
        private static function _build_groups_array($list_id, $list_name) {
            $options = array();

            I_Tools::load_utilities(array('integral-mailchimp-api', 'integral-mailchimp-lists'));

            //- Grab any merge tags being created by other plugins (runs the apply_filters() for fetching merge tags from other plugins)
            $filters   = array('with_counts' => TRUE, 'all_data' => TRUE);
            $groupings = Integral_MailChimp_Lists::load_mailchimp_groupings($list_id, $list_name, $filters);

            die('<pre>$groupings [' . __CLASS__ . ' - ' . __FUNCTION__ . '() - ' . __LINE__ . ']:<br/>' . print_r($groupings, true) . '</pre>');

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
            $format_string      = '<div id="%1s" class="img-tag-info-box well %2$s %8$s" data-merge-tag="%4$s" data-list-id="%7$s"><h4>%3$s</h4><p class="imc-plugin-tag">'. __('Merge Tag', 'integral-mailchimp') .': |%4$s|</p><p class="img-plugin-tag">'. __('Field Type', 'integral-mailchimp') .': (%5$s)</p><p class="imc-plugin-name">'. __('Source', 'integral-mailchimp') .': %6$s</p></div>';
            $fields             = array();
            $fields['unused']   = array();
            $fields['active']   = array();
            $fields['inactive'] = array();

            foreach ($merge_tags as $merge_tag => $merge_tag_info) {

                $plugin_name  = $merge_tag_info['plugin_name'] ? $merge_tag_info['plugin_name'] : '&nbsp;';
                $in_mailchimp = isset($merge_tag_info['in_mailchimp']) ? 'exists_in_mailchimp' : '';

                $fields[$merge_tag_info['status']][$merge_tag] = sprintf($format_string, $merge_tag_info['id'], $merge_tag_info['status'], $merge_tag_info['name'], $merge_tag, $merge_tag_info['field_type'], $plugin_name, $list_id, $in_mailchimp);
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

            $errors   = array();
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

                    $imc_messages[] = __('The Merge Tag was Deactivated', 'integral-mailchimp') .'.';

                    //- Else we need to activate it and if it's not already added to MailChimp then add it via the API
                } else {
                    $syncd_merge_tags[] = $merge_tag;
                    update_option('imc_list_sync_tags_' . $list_id, $syncd_merge_tags);

                    $imc_messages[] = __('The Merge Tag was Activated', 'integral-mailchimp') .'.';

                    //- Fetch the syncable merge tags from the plugins
                    $plugin_merge_tags = (array) Integral_MailChimp_Lists::load_mailchimp_plugin_sync_merge_tags(FALSE);

                    if (is_array($plugin_merge_tags) && isset($plugin_merge_tags[$merge_tag])) {
                        $mergetag_field_types = array('text', 'number', 'radio', 'dropdown', 'date', 'address', 'phone', 'url', 'imageurl', 'zip', 'birthday');

                        $tag_options               = array();
                        $tag_options['field_type'] = (isset($plugin_merge_tags[$merge_tag]['field_type']) && in_array($plugin_merge_tags[$merge_tag]['field_type'], $mergetag_field_types)) ? $plugin_merge_tags[$merge_tag]['field_type'] : 'text';
                        $tag_options['public']     = (isset($plugin_merge_tags[$merge_tag]['public'])) ? $plugin_merge_tags[$merge_tag]['public'] : TRUE;
                        $tag_options['show']       = (isset($plugin_merge_tags[$merge_tag]['show'])) ? $plugin_merge_tags[$merge_tag]['show'] : TRUE;
                        //- FIXME - Take advantage of the other Merge Tag options (public, show, etc)

                        $tag_name = isset($plugin_merge_tags[$merge_tag]['name']) ? $plugin_merge_tags[$merge_tag]['name'] : '';

                        //- Load the existing merge tags from MailChimp to determine if we save or update below
                        $existing_merge_tags = Integral_MailChimp_Lists::load_mailchimp_merge_tags($list_id);

                        if (isset($existing_merge_tags[$merge_tag])) {
                            //- Update merge tag
                            $tag_options['name'] = $tag_name;

                            $response   = Integral_MailChimp_Lists::update_mailchimp_merge_tags($list_id, $merge_tag, $tag_options);
                            $imc_messages[] = __('The Merge Tag was updated in MailChimp', 'integral-mailchimp');
                        } else {
                            //- Create merge tag
                            $response   = Integral_MailChimp_Lists::save_mailchimp_merge_tags($list_id, $merge_tag, $tag_name, $tag_options);
                            $imc_messages[] = __('The Merge Tag was added to MailChimp', 'integral-mailchimp');
                        }

                        //- Else the Merge Tag provided did not match any provided by the plugin filter
                    } else {

                        $errors[] = __('Error Processing Merge Tag', 'integral-mailchimp');
                    }
                }
            } else {
                $errors[] = __('Error Processing Merge Tag', 'integral-mailchimp');
            }


            if (empty($errors)) {

                $status_message = join('<br>', $imc_messages);
                wp_send_json(array('msg' => $status_message));
            } else {

                wp_send_json($errors);
            }


        }


    }


}

