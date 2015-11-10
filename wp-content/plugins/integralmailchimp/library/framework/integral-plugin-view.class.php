<?php

namespace IMC\Library\Framework;

use IMC\I_Conf;
use IMC\Library\Framework\Logger;

/* * *
 * This is the core Integral Plugin View class 
 * 
 * !!!!! This class should only be extended !!!!! 
 * 
 * This plugin is responsible for:
 * -- providing essential view level utilities for each plugin view child class
 * 
 */
if (!class_exists('Integral_Plugin_View')) {

    class Integral_Plugin_View {


        protected static $layout_path    = NULL;
        protected static $view_path      = NULL;
        protected static $layout_title   = NULL;
        protected static $view_content   = NULL;
        protected static $status_message = NULL;
        public static $view              = NULL;


        final private function __construct() {
            wp_die('THIS CLASS MAY NOT BE INSTATIATED [' . __CLASS__ . ' - ' . __FILE__ . ']');


        }


        /**
         * Initializes this view
         * 
         */
        public static function initialize_view() {
            self::$view = (object) array();


        }


        /**
         * Stores the layout filename slug for this view
         * 
         * @param string $layout_slug
         */
        public static function set_layout($layout_slug) {
            self::$layout_path = IMC_PLUGIN_PATH . I_Conf::LAYOUT_PATH . $layout_slug . '.layout.phtml';


        }


        /**
         * Stores the view filename slug for this view
         * 
         * @param string $view_slug
         */
        public static function set_view($view_slug) {
            self::$view_path = IMC_PLUGIN_PATH . I_Conf::VIEW_SCRIPT_PATH . $view_slug . '.phtml';


        }


        /**
         * Stores the title for the layout
         * 
         * @param string $layout_title
         */
        public static function set_title($layout_title) {
            self::$layout_title = $layout_title;


        }


        /**
         * Stores the status messages for the view
         * 
         * @param string $status_message
         */
        public static function set_status_message($status_message) {
            self::$status_message = $status_message;


        }


        /**
         * Renders and prints the layout and view
         * 
         */
        public static function render_view() {
            $output = self::_render_layout();

            echo $output;


        }


        /**
         * Assembles and prints the layout and view
         * 
         * @return string
         */
        private static function _render_layout() {
            $layout_title   = self::$layout_title;
            $layout_content = self::_render_view();

            ob_start();
            include(self::$layout_path);
            $output = ob_get_contents();
            ob_end_clean();

            return $output;


        }


        /**
         * Assembles and prints the view
         * 
         * @return string
         */
        private static function _render_view() {
            $status_message = self::$status_message;

            extract((array) self::$view);

            ob_start();
            include(self::$view_path);
            $output = ob_get_contents();
            ob_end_clean();

            return $output;


        }


        /**
         * Wraps the provided inline javascript with the correct wrappers
         * 
         * @param string $script
         * @param boolean $with_tag
         * @param boolean $with_ready
         * @return string
         */
        public static function format_inline_javascript($script, $with_tag = TRUE, $with_ready = TRUE) {

            $output = ($with_ready) ? "jQuery(document).ready( function($) {\n'use strict';\n {$script} \n});\n" : $script;
            $output = "//<![CDATA[ \n  {$output} \n //]]> \n";
            $output = ($with_tag) ? "<script type='text/javascript'>\n {$output} \n </script>" : $output;

            return $output;


        }


    }


}

