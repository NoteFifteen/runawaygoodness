<?php
/**
 * Plugin Name:       RunawayGoodness - Mailchimp Connect
 * Plugin URI:        http://runawaygoodness.com/
 * Description:       Connections for RG to Mailchimp
 * Version:           0.1
 * Author:            Runaway Goodness
 * Author URI:        http://runawaygoodness.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       runawaygoodness
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( RG_INCLUDE_PATH, plugin_dir_path( __FILE__ ) . 'includes' );

// point to where you downloaded the REST libarary phar
include( RG_INCLUDE_PATH . '/httpful.phar' );

// Include function file
include( RG_INCLUDE_PATH . '/functions.php' );

// set plugin options
/* 
 * Runaway Goodness Prod Main List
 */
// "5e63b9bb07"; // RG main
// "af1b834d6a"; // genres

// "159f7318a4"; // after dark

/* 
 * Internal Testing List
 */
define( 'RGTESTMODE', false );
if( RGTESTMODE ) {
	// Internal Test List
	define( 'LIST_ID', '66480cfb4f' );
	define( 'INTEREST_TYPE', '11ca3b0147' ); // genres

} else {
	// LIVE List
	define( 'LIST_ID', '5e63b9bb07' );
	define( 'INTEREST_TYPE', 'af1b834d6a' ); // genres
}

define( 'API_KEY', '19970225f9c849e9cf2b472d76604739-us11' ); // RAG 
define( 'ALMOST_DONE_LOC', '/almost-done/' );
define( 'THANK_YOU_LOC', '/thank-you/' );
define( 'ALREADY_IN_LOC', '/update-membership/' );
