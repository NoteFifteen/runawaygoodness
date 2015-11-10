<?php

namespace IMC\Controllers;

use IMC\Library\Framework\Integral_Plugin_CPT;
use IMC\Library\Utility\Integral_Form_API;
use IMC\Library\Utility\I_Tools;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/**
 * Handles the viewing and processing of the History Log CPT meta boxes
 * 
 * 
 */
//- Add the TinyMCE Code Plugin
//add_filter('mce_external_plugins', array('IMC\Controllers\History_Log_CPT_Controller', 'load_custom_tinymce_plugins'));

if (!class_exists('History_Log_CPT_Controller')) {

    class History_Log_CPT_Controller extends Integral_Plugin_CPT {


        function __construct() {
            I_Tools::load_utilities(array('integral-form-api'));

            //- Get the CPT info -- post type, meta_boxes & fields, taxonomies, etc
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
                $logger_message    = "There was an error {$creating_updating} the History Log in " . __FUNCTION__ . "()";
                $logger_items      = array('WP_Error' => $new_post_id->get_error_message(), 'cpt_save_data' => $cpt_data);
                Logger::log_error($logger_message, $logger_items);
            } else {
                foreach ($cpt_data['meta'] as $meta_key => $meta_value) {
                    update_post_meta($new_post_id, $meta_key, $meta_value);
                }
            }


            return $new_post_id;


        }


        public function load_post_info() {
            $post_info = array(
                'post_type' => 'imc_history_log',
                'singular' => __('History Log', 'integral-mailchimp'),
                'plural' => __('History Logs', 'integral-mailchimp'),
                'public' => false,
                'show_ui' => false,
                'menu_position' => 15,
                'hierarchical' => false,
                'rewrite' => array('slug' => 'history-log'),
                'can_export' => true,
                'supports' => array('title', 'editor'),
                //- change the default "Enter title here" placeholder text in the CPT subject field
                'title_field_placeholder' => __('Log Title', 'integral-mailchimp'),
                //- enable Duplicate link
                'duplicate_post_link' => true,
                //- register any taxonomies for this CPT
                'taxonomy' => array(
                    'taxonomy_type' => 'imc_history_log_category',
                    'taxonomy_info' => array(
                        'label' => __('History Log Categories', 'integral-mailchimp'),
                        'labels' => array(
                            'singular_name' => __('History Log Category', 'integral-mailchimp'),
                            'add_new_item' => __('Add New History Log Category', 'integral-mailchimp')
                        ),
                        'sort' => true,
                        'hierarchical' => true,
                        'args' => array('orderby' => 'term_order'),
                        'rewrite' => array('slug' => 'history-log-category'),
                        'show_ui' => true,
                        'show_admin_column' => true
                    )
                ),
                'use_scripts' => array(),
                'remove_scripts' => array(),
                'meta_boxes' => array(),
                'remove_meta_boxes' => array(),
                //- Each column is an array in column id => col_title format
                //- 'category' is auto-handled and grabs any custom taxonomy tags for the post
                'custom_columns' => array(
                    'title' => __('Log Title', 'integral-mailchimp'),
                    'category' => __('Log Category', 'integral-mailchimp')
                ),
                'sortable_columns' => array('title')
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

                default:
                    break;
            }

            if ($value) {
                echo $value;
            }


        }


        public function duplicate_post_meta($post_meta) {
           
            return $post_meta;


        }


    }


    new History_Log_CPT_Controller();
}

