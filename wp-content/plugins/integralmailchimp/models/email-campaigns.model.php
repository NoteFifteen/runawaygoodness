<?php

namespace IMC\Models;

use IMC\Library\Framework\Integral_Plugin_Model;
use IMC\Library\Framework\Logger;

/**
 * Handles the model functionality for viewing and processing of email campaigns for this plugin
 * 
 * 
 */
if (!class_exists('Email_Campaigns_Model')) {

    class Email_Campaigns_Model extends Integral_Plugin_Model {


        /**
         * Loads the database values for this model
         * 
         * @param object $post
         * @return array
         */
        public static function load_email_campaigns_recipient_database_values($post) {

            $meta_fields = array('list_select');

            $post_meta = get_post_meta($post->ID);

            foreach ($meta_fields as $meta_field) {
                $this_post_meta[$meta_field] = $post_meta[$meta_field][0];
            }

            return $this_post_meta;


        }


    }


}

