<?php

namespace IMC\Models;

use IMC\Library\Framework\Logger;

if (!class_exists('IMC_Users_Model')) {

    class IMC_Users_Model {


        /**
         * WORDPRESS USER WRAPPERS
         * ************************************************************************ */


        /**
         * Load a user by ID
         * 
         * @param int $user_id
         * @return object
         */
        public static function load_user_by_id($user_id) {
            if ($user_id && is_numeric($user_id)) {
                return get_userdata($user_id);
            }


        }


        /**
         * Load a user by email
         * 
         * @param int $user_email
         * @return object
         */
        public static function load_user_by_email($user_email) {
            if ($user_email && is_email($user_email)) {
                return get_user_by('email', $user_email);
            }


        }


        /**
         * Loads all the user meta keys/values for a given user
         * 
         * @global object $wpdb
         * @param boolean $include_hidden
         * @param boolean $current_blog_only
         * @param int $limit
         * @return array/object
         */
        public static function load_user_meta_keys($include_hidden = FALSE, $current_blog_only = TRUE, $limit = 0) {
            global $wpdb;
            $having   = array();
            $where    = array();
            $user_ids = array();

            $having[] = "meta_key NOT LIKE 'field_%'";
            $include_hidden ? NULL : $having[] = "meta_key NOT LIKE '\_%'";

            $limit = (is_numeric($limit) && $limit) ? "LIMIT {$limit}" : NULL;

            if ($current_blog_only) {
                $users = get_users(array('exclude' => array(1)));

                if (is_array($users) && !empty($users)) {
                    foreach ($users as $user) {
                        $user_ids[] = $user->ID;
                    }

                    $user_ids = join(',', $user_ids);
                }

                $user_ids ? $where[] = "user_id IN ({$user_ids})" : NULL;
            }

            $having = (!empty($having)) ? "HAVING " . join(' AND ', $having) : NULL;
            $where  = (!empty($where)) ? "WHERE " . join(' AND ', $where) : NULL;

            $query = "SELECT meta_key FROM $wpdb->usermeta
                {$where}
                GROUP BY meta_key
                {$having}
                ORDER BY meta_key
                {$limit}";

            $user_meta_keys = $wpdb->get_col($query);

            return $user_meta_keys;


        }


    }


}