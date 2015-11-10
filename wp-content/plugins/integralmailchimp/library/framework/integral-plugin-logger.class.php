<?php

namespace IMC\Library\Framework;

use IMC\I_Conf;
use Monolog\Logger as mLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Formatter\HtmlFormatter;

/**
 * Wrapper class for monolog: https://github.com/Seldaek/monolog
 * Allows for static method calls throughout the rest of the plugin
 */
if (!class_exists('Logger')) {

    class Logger {


        //-Log file name and location
        const LOG_CHANNEL = 'imc_logger';
        const LOG_FOLDER  = 'integral_log_files';
        //- Log file processors
        const TRACE       = 'IntrospectionProcessor';
        const GIT         = 'GitProcessor';


        private static $_this          = NULL;
        private static $_logger;
        private static $log_folder;
        private static $log_file;
        private static $logging_active = FALSE;


        public function __construct() {
            //- Enforce Singleton
            if (isset(self::$_this)) {
                wp_die(sprintf(__('%1$s is a singleton class. Creating a second instance is prohibited.', 'integral-mailchimp'), get_class($this)));
            }

            self::$logging_active = get_option(I_Conf::OPT_ENABLE_DEBUG_MODE);

            self::$_this = $this;

            self::$log_folder = self::get_log_folder();

            self::$log_file = self::get_log_file();

            self::check_log_folder();

            self::$_logger = new mLogger(self::LOG_CHANNEL);

            $stream = new StreamHandler(self::$log_file, mLogger::ERROR);

            $stream_formatter = new LineFormatter();

            $html_formatter = new HtmlFormatter();

            $stream->setFormatter($stream_formatter);

            $chrome = new ChromePHPHandler();

            //- Don't need to call it since this is the default> Here in case we want to change or extend it
            //- $chrome_formatter = new ChromePHPFormatter();
            //$chrome->setFormatter($chrome_formatter);

            self::$_logger->pushHandler($stream);

            self::$_logger->pushHandler($chrome);


        }


        public static function get_log_folder() {
            //- PHPV >= 5.4 define((IMG_LOG_PATH', trailingslashit(wp_upload_dir()['basedir'].'/integral_logs'));
            $upload_dir = wp_upload_dir();
            return trailingslashit(trailingslashit($upload_dir['basedir']) . self::LOG_FOLDER);


        }


        public static function get_log_file() {
            return self::$log_folder . I_Conf::PLUGIN_TEXT_DOMAIN . '.log';


        }


        /**
         * Checks for a folder for storing the log files and creates it if it doesn't exist.
         * For a single-site install the folder will be created in uploads, for multi-site
         * it will be in uploads/sites/site_#/, ie: uploads/sites/4/
         */
        public static function check_log_folder() {
            if (!is_dir(self::$log_folder)) {
                mkdir(self::$log_folder);
            }


        }


        /**
         * General wrapper for all log messages
         * 
         * @param string $message   -- the message to be logged
         * @param array $context    -- addition values to be logged, ex: username => $username
         * @param string $level     -- one of the PSR-3 logging levels (default = error): emergency, alert, critical, error, warning, notice, info, debug
         * @param string $processor -- name of a processor class used to format the output
         * @return logs the message
         */
        public static function log($message, $context = array(), $level = 'error', $processor = NULL) {
            if (self::$logging_active) {
                if ($processor != NULL) {
                    $processor = "Monolog\Processor\\" . constant('self::' . strtoupper($processor));
                    if (class_exists($processor)) {
                        /**
                         * the array is partial namespaces that get skipped when performing the backtrace. Since we're wrapping
                         * the actual log method in another class we need to exclude it or we'd just show this class / method
                         * not the location where it was actually called
                         */
                        $processor = new $processor('', array('Monolog\\', 'Logger'), $processor);
                        self::$_logger->pushProcessor($processor);
                    }
                }
                return self::$_logger->log($level, $message, $context);
            }


        }


        /**
         * Debug type wrapper for the main log() function
         * 
         * @param string $message
         * @param array $context
         * @param string $processor
         */
        public static function log_debug($message, $context = array(), $processor = NULL) {
            return self::log($message, $context, 'debug', $processor);


        }


        /**
         * Info type wrapper for the main log() function
         * 
         * @param string $message
         * @param array $context
         * @param string $processor
         */
        public static function log_info($message, $context = array(), $processor = NULL) {
            return self::log($message, $context, 'info', $processor);


        }


        /**
         * Notice type wrapper for the main log() function
         * 
         * @param string $message
         * @param array $context
         * @param string $processor
         */
        public static function log_notice($message, $context = array(), $processor = NULL) {
            return self::log($message, $context, 'notice', $processor);


        }


        /**
         * Warning type wrapper for the main log() function
         * 
         * @param string $message
         * @param array $context
         * @param string $processor
         */
        public static function log_warning($message, $context = array(), $processor = NULL) {
            return self::log($message, $context, 'warning', $processor);


        }


        /**
         * Error type wrapper for the main log() function
         * 
         * @param string $message
         * @param array $context
         * @param string $processor
         */
        public static function log_error($message, $context = array(), $processor = NULL) {
            return self::log($message, $context, 'error', $processor);


        }


        /**
         * Critical type wrapper for the main log() function
         * 
         * @param string $message
         * @param array $context
         * @param string $processor
         */
        public static function log_critical($message, $context = array(), $processor = NULL) {
            return self::log($message, $context, 'critical', $processor);


        }


        /**
         * Alert type wrapper for the main log() function
         * 
         * @param string $message
         * @param array $context
         * @param string $processor
         */
        public static function log_alert($message, $context = array(), $processor = NULL) {
            return self::log($message, $context, 'alert', $processor);


        }


        /**
         * Emergency type wrapper for the main log() function
         * 
         * @param string $message
         * @param array $context
         * @param string $processor
         */
        public static function log_emergency($message, $context = array(), $processor = NULL) {
            return self::log($message, $context, 'emergency', $processor);


        }


    }


}

new Logger();
