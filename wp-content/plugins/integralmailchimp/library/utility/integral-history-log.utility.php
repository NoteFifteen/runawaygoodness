<?php

namespace IMC\Library\Utility;

use IMC\Library\Framework\Logger;

if (!class_exists('Integral_History_Log')) {


    /**
     * Provides functionality for activating, deactivating and validating Plugin licenses
     * 
     */
    class Integral_History_Log {


        public static function add_new_log($title, $content) {

            $slug      = sanitize_title($title);
            $author_id = get_current_user_id();

            $post_data = array(
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_content' => $content,
                'post_author' => $author_id,
                'post_name' => $slug,
                'post_title' => $title,
                'post_status' => 'publish',
                'post_type' => 'imc-history-log',
            );

            $post_ID = wp_insert_post($post_data, true);

            if (!is_wp_error($post_ID)) {

                $post_meta = get_post_meta($post_ID);

                if ($post_meta) {
                    
                }
            } else {

                $logger_message = '[History Log] Error creating new log entry in ' . __FUNCTION__ . '()';
                $logger_items   = array('error' => $post_ID, 'function_args' => func_get_args());
                Logger::log_error($logger_message, $logger_items);
            }


        }


    }


}