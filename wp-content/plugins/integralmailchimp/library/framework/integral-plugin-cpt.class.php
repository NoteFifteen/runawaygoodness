<?php

namespace IMC\Library\Framework;

use IMC\Library\Utility\Integral_Form_API;
use IMC\Library\Utility\I_Tools;
use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/*
 */
/* * *
 * This is the core Integral Plugin Custom Post Type class 
 * 
 * !!!!! This class should only be extended !!!!! 
 * 
 * This plugin is responsible for:
 * -- providing custom post type functionality
 * -- Framework for adding custom post types with methods for adding/editing/saving meta fields
 * 
 */
if (!class_exists('Integral_Plugin_CPT')) {

    abstract class Integral_Plugin_CPT {


        protected $post_info = NULL;


        function __construct() {
            //- Create the custom post type
            add_action('init', array($this, 'create_post_type'));
            //- Create the custom taxonomy 
            add_action('init', array($this, 'create_taxonomy'));
            //- Enqueue needed admin scripts
            add_action('admin_enqueue_scripts', array($this, 'cpt_add_scripts'));
            //- Dequeue unneeded admin scripts
            add_action('admin_enqueue_scripts', array($this, 'cpt_remove_scripts'), 9999);
            //- Create the metaboxes 
            add_action("add_meta_boxes_{$this->post_info['post_type']}", array($this, "add_meta_boxes"), 10, 1);
            //- Remove specified metaboxes
            add_action('admin_menu', array($this, "remove_meta_boxes"));
            //- Hook to save data from custom post custom meta boxes 
            //add_action("save_post", array($this, "update_meta_data"), 10, 2);

            //- Customize the columns on the CPT admin table view 
            add_filter("manage_{$this->post_info['post_type']}_posts_columns", array($this, 'customize_columns'));
            add_action("manage_{$this->post_info['post_type']}_posts_custom_column", array($this, 'get_custom_column_values'), 10, 2);
            //- Make the custom columns sortable 
            add_filter("manage_edit-{$this->post_info['post_type']}_sortable_columns", array($this, 'sortable_columns'));

            //-  Add a 'Duplicate' link to allow CPTs to be cloned. Use $_GET to make sure it only applies to the proper CPT
            if ($this->post_info['duplicate_post_link'] == true) {
                if (isset($_GET['post_type']) && $_GET['post_type'] == $this->post_info['post_type']) {
                    add_filter('post_row_actions', array($this, 'duplicate_post_link'), 10, 2);
                    //- Same as above but for hierarchical post types
                    add_filter('page_row_actions', array($this, 'duplicate_post_link'), 10, 2);
                }
                add_action('admin_action_duplicate_post_as_draft', array($this, 'duplicate_post_as_draft'));
                add_filter('integral_duplicate_post_meta', array($this, 'duplicate_post_meta'));
            }

            //-  Filter to change the default Enter title here subject placeholder
            if (isset($this->post_info['title_field_placeholder']) && $this->post_info['title_field_placeholder']) {
                add_filter('enter_title_here', array($this, 'modify_cpt_title'), 10, 2);
            }

            //- Include Form API
            I_Tools::load_utilities(array('integral-form-api'));


        }


        //- Each CPT has to define a load_post_info() method which returns an array of post info 
        public abstract function load_post_info();


        //- Each CPT has to define a duplicate_post_meta() method which modifies the post_meta array 
        public abstract function duplicate_post_meta($post_meta);


        function create_post_type() {
            //- This was causing a crap ton of error log notices
            !isset($this->post_info['query_var']) ? $this->post_info['query_var'] = NULL : NULL;

            $labels = array(
                'name' => $this->post_info['plural'],
                'singular_label' => $this->post_info['singular'],
                'add_new_item' => 'Add ' . $this->post_info['singular'],
                'new_item' => 'New ' . $this->post_info['singular'],
                'edit_item' => 'Edit ' . $this->post_info['singular'],
                'view_item' => 'View ' . $this->post_info['singular'],
                'search_item' => 'Search ' . $this->post_info['singular'],
                'not_found' => "No {$this->post_info['plural']} Found"
            );

            $args = array(
                'labels' => $labels,
                'public' => $this->post_info['public'],
                'show_ui' => $this->post_info['show_ui'],
                'show_in_menu' => $this->post_info['show_in_menu'],
                'menu_position' => $this->post_info['menu_position'],
                'hierarchical' => $this->post_info['hierarchical'],
                'rewrite' => $this->post_info['rewrite'],
                'query_var' => $this->post_info['query_var'],
                'can_export' => $this->post_info['can_export'],
                'supports' => $this->post_info['supports']
            );

            register_post_type($this->post_info['post_type'], $args);


        }


        /**
         * Register any custom taxonomies for this CPT
         * 
         */
        function create_taxonomy() {
            if (isset($this->post_info['taxonomy']) && $this->post_info['taxonomy']) {
                register_taxonomy(
                    $this->post_info['taxonomy']['taxonomy_type'], $this->post_info['post_type'], array(
                    'label' => $this->post_info['taxonomy']['taxonomy_info']['label'],
                    'labels' => $this->post_info['taxonomy']['taxonomy_info']['labels'],
                    'sort' => $this->post_info['taxonomy']['taxonomy_info']['sort'],
                    'hierarchical' => $this->post_info['taxonomy']['taxonomy_info']['hierarchical'],
                    'args' => $this->post_info['taxonomy']['taxonomy_info']['args'],
                    'rewrite' => $this->post_info['taxonomy']['taxonomy_info']['rewrite'],
                    'show_ui' => $this->post_info['taxonomy']['taxonomy_info']['show_ui'],
                    'show_admin_column' => $this->post_info['taxonomy']['taxonomy_info']['show_admin_column']
                    )
                );
            }


        }


        public function cpt_add_scripts($hook) {
            global $post;
            
            //- make the media uploader available for any custom image uploads
            if (!did_action('wp_enqueue_media')) {
                wp_enqueue_media();
            }

            if (is_object($post) && $post->post_type == $this->post_info['post_type']) {
                if (isset($this->post_info['use_scripts']) && $this->post_info['use_scripts']) {
                    foreach ($this->post_info['use_scripts'] as $handle) {
                        wp_enqueue_script($handle);
                    }
                }
            }


        }


        public function cpt_remove_scripts() {
            global $post;
            if (is_object($post) && $post->post_type == $this->post_info['post_type']) {
                if (isset($this->post_info['remove_scripts']) && $this->post_info['remove_scripts']) {
                    foreach ($this->post_info['remove_scripts'] as $handle) {
                        wp_dequeue_script($handle);
                    }
                }
            }


        }


        /**
         * Change the default columns on the CPT admin table screen
         * @param array $columns
         * @return array $columns
         */
        public function customize_columns($columns) {
            //- manually add the checkbox at the start of each row
            $cb      = array('cb' => "<input type=\"checkbox\" />");
            $columns = ($this->post_info['custom_columns'] && is_array($this->post_info['custom_columns'])) ? array_merge($cb, $this->post_info['custom_columns']) : $columns;

            return apply_filters('integral_cpt_custom_columns', $columns);


        }


        /**
         * Allows you to specify which columns should be sortable on the CPT admin table screen
         * By default it merges the columns you specify with the defaults. If you want to over ride
         * the defaults add a filter and manually remove them
         * 
         * @param array $columns
         * @return array $columns
         */
        public function sortable_columns($columns) {
            if ($this->post_info['sortable_columns'] && is_array($this->post_info['sortable_columns'])) {
                foreach ($this->post_info['sortable_columns'] as $index => $key) {
                    $columns[$key] = true;
                }
            }

            return apply_filters('integral_cpt_sortable_columns', $columns);


        }


        /**
         * Allows you to populate any custom columns you might have defined with postmeta values
         * @param type $column -- the name of the current column
         * @param type $post_id -- the id of the current post (iterates through each row in the CPT admin table view)
         * Note: It returns a single value so if your postmeta key isn't unique for this post then the results may not be what you expect
         */
        public function get_custom_column_values($column, $post_id) {
            $value = get_metadata('post', $post_id, $column, true);
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
            echo $value;


        }


        /**
         * Display the custom meta boxes
         *
         * Uses show_meta_boxes as a callback function to handle the actual
         * output of each box.
         *
         * @param object $post -- the current post being viewed
         *
         */
        public function add_meta_boxes($post) {
            if(get_current_screen()->post_type == $this->post_info['post_type']){
            if ($this->post_info['meta_boxes'] && is_array($this->post_info['meta_boxes'])) {
                foreach ($this->post_info['meta_boxes'] as $meta_box) {
                    /* WP Doesn't provide an option to place metaboxes btwn the post title and editor
                     * To work around that, we hook into the action 'edit_form_after_title' found in
                     * wp-admin/edit-form-advanced.php on line 498. Using 'use' requires PHP >= 5.3
                     */
                    if ($meta_box['context'] == 'after_title') {
                        add_action('edit_form_after_title', function($post) use ($meta_box) {
                            //- add_meta_box wraps any extra params you pass it in an array with key 'args'
                            $extra['args'] = $meta_box;

                            //-  wrap it in the WP metabox dom elements & styles
                            //- TODO: Add show/hide & saved state functionality: https://plugins.trac.wordpress.org/browser/scb-framework/trunk/scb/BoxesPage.php?rev=339808
                            $output = "<br><div id='{$meta_box['id']}' class='postbox'><h3 class='hndle'><span>{$meta_box['title']}</span></h3><div class='inside'>";
                            $output .= $this->show_meta_boxes($post, $extra, true);
                            $output .= "</div></div>";
                            echo $output;
                        });
                    }
                    /*
                     * @param string $id -- id attribute of edit screen section
                     * @param string $title -- title of the edit screen section
                     * @param function $callback -- callback function that outputs the box
                     * @param string $post_type -- the type of screen to show the box on
                     * @param string $context -- the part of the page where it should show
                     * @param string $priority -- optional controls the display priority within the section of the page
                     * @param array $callback_args -- extra parameters to pass along with $post object
                     */

                    add_meta_box($meta_box['id'], $meta_box['title'], array(__CLASS__, 'show_meta_boxes'), $this->post_info['post_type'], $meta_box['context'], $meta_box['priority'], $meta_box);
                }
            }

            }
        }


        /**
         * Creates the meta boxes and fields
         *
         * @param object $post -- the current post
         * @param array $meta_box -- has the box details as well as an array of fields to display in the box
         * @param boolean $return -- default is false which means echo the output. Passing true means return it as a string.
         */
        public static function show_meta_boxes($post, $meta_box, $return = false) {
            //- add a nonce field to each metabox
            $output = wp_nonce_field('add-edit-post', I_Conf::FORM_NONCE, true, false);
            foreach ($meta_box['args']['fields'] as $field) {
                $field_id = isset($field['field_name']) ? $field['field_name'] : (isset($field['field_id']) ? $field['field_id'] : NULL);
                if ($field_id) {
                    //- get the postmeta value. Returns an empty string if no value set so set to NULL
                    $field['field_value'] = get_post_meta($post->ID, $field_id, true) ? : NULL;
                }
                $field_html = Integral_Form_API::build_form_field($field);
                $output .= $field_html['html'];
            }
            if ($return) {
                return $output;
            } else {
                echo $output;
            }


        }


        /**
         * Remove metaboxes from CPTs
         * Gets the boxes and context from the CPT controller
         */
        public function remove_meta_boxes() {
            if ($this->post_info['remove_meta_boxes'] && is_array($this->post_info['remove_meta_boxes'])) {
                foreach ($this->post_info['remove_meta_boxes'] as $context => $ids) {
                    foreach ($ids as $id) {
                        remove_meta_box($id, $this->post_info['post_type'], $context);
                    }
                }
            }


        }


        /**
         * save data from custom meta fields 
         * 
         * @param type $post_id
         * @param object $post -- WP post object
         * @param array $meta_boxes
         * @return type
         */
        public function save_meta_data($post_id, $post, $meta_boxes) {
            //- verify nonce
            if (isset($_POST[I_Conf::FORM_NONCE]) && !wp_verify_nonce($_POST[I_Conf::FORM_NONCE], 'add-edit-post')) {
                return $post_id;
            }

            //- check autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            //- check permissions
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
            foreach ($meta_boxes as $meta_box) {

                foreach ($meta_box['fields'] as $field) {
                    $field_name = isset($field['field_name']) ? $field['field_name'] : NULL;
                    if ($field_name) {
                        $old = get_post_meta($post_id, $field_name, true);
                        $new = isset($_POST[$field_name]) ? $_POST[$field_name] : NULL;

                        if ($new && $new != $old) {
                            update_post_meta($post_id, $field_name, $new);
                        } elseif ('' == $new && $old) {
                            delete_post_meta($post_id, $field_name, $old);
                        }
                    }
                }
            }


        }


        public function update_meta_data($post_id, $post) {
            if (is_object($post) && $post->post_type == $this->post_info['post_type']) {
                $this->save_meta_data($post_id, $post, $this->post_info['meta_boxes']);
            }


        }


        /**
         * Creates a new Duplicate action for each CPT in the admin listings
         * 
         * @param array $actions
         * @param object $post
         * @return array
         */
        public static function duplicate_post_link($actions, $post) {
            if (current_user_can('edit_posts')) {
                
                $duplicate_url = admin_url('admin.php?action=duplicate_post_as_draft&amp;post='. $post->ID);
                
                $actions['duplicate'] = '<a href="'. $duplicate_url .'" title="'. __('Duplicate this item', 'integral-mailchimp') .'" rel="permalink">'. __('Duplicate', 'integral-mailchimp') .'</a>';
                unset($actions['view']);
                
                if ($post->post_status == 'publish') {
                    unset($actions['edit']);
                    unset($actions['inline hide-if-no-js']);
                }
            }
            return $actions;


        }


        /**
         * Allows you to modify the default "Enter title here" text in the subject line of a CPT
         * 
         * @param string $title -- the text to display, default is Enter title here
         * @param object $post -- CPT post object
         * @return string
         */
        public function modify_cpt_title($title, $post) {
            if ($post->post_type == $this->post_info['post_type']) {
                $title = (isset($this->post_info['title_field_placeholder']) && $this->post_info['title_field_placeholder']) ? $this->post_info['title_field_placeholder'] : $title;
            }
            return $title;
        }


        /**
         * Duplicates a post with draft status
         * 
         * @global object $wpdb
         */
        public static function duplicate_post_as_draft() {
            global $wpdb;

            if (!(isset($_GET['post']) || isset($_POST['post']) || ( isset($_REQUEST['action']) && 'duplicate_post_as_draft' == $_REQUEST['action'] ) )) {
                wp_die(__('Missing Post ID', 'integral-mailchimp'));
            }

            $post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);

            $post = get_post($post_id);

            if (is_object($post) && is_a($post, 'WP_Post')) {

                $current_user    = wp_get_current_user();
                $new_post_author = $current_user->ID;

                $args = array(
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'post_author' => $new_post_author,
                    'post_content' => $post->post_content,
                    'post_excerpt' => $post->post_excerpt,
                    'post_name' => $post->post_name,
                    'post_parent' => $post->post_parent,
                    'post_password' => $post->post_password,
                    'post_status' => 'draft',
                    'post_title' => $post->post_title,
                    'post_type' => $post->post_type,
                    'to_ping' => $post->to_ping,
                    'menu_order' => $post->menu_order
                );

                $new_post_id = wp_insert_post($args);

                $taxonomies = get_object_taxonomies($post->post_type);
                foreach ($taxonomies as $taxonomy) {
                    $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                    wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                }

                $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");

                if (is_array($post_meta_infos) && !empty($post_meta_infos)) {

                    $new_post_meta = array();
                    foreach ($post_meta_infos as $meta_info) {
                        if (is_object($meta_info) && isset($meta_info->meta_key) && isset($meta_info->meta_value)) {
                            $new_post_meta[$meta_info->meta_key] = $meta_info->meta_value;
                        }
                    }

                    $new_post_meta = apply_filters('integral_duplicate_post_meta', $new_post_meta);

                    if (count($new_post_meta) != 0) {
                        $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                        foreach ($new_post_meta as $meta_key => $meta_value) {
                            $meta_value      = addslashes($meta_value);
                            $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                        }
                        $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                        $wpdb->query($sql_query);
                    }
                }

                wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
                exit;
            } else {
                wp_die(__('Could not create new post, original post not found: ', 'integral-mailchimp') . $post_id);
            }


        }


    }


}

