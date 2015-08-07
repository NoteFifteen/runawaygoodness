<?php
/**
 * Plugin Name:       RunAwayGoodness Core Functionality
 * Description:       Custom functionality for RAG
 * Version:           1.0
 * Author:            John Hawkins
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       runawaygoodness
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Set some paths
define( 'CCD_PATH', plugin_dir_path(__FILE__) );

// grab some files
require_once( CCD_PATH . 'includes/functions.php' );
