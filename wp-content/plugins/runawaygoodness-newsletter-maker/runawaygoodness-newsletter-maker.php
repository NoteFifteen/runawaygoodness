<?php
/**
 * Plugin Name:       RG - Newsletter Maker
 * Plugin URI:        http://runawaygoodness.com/
 * Description:       Allows for easy creation of daily newsletter code
 * Version:           0.4.1
 * Author:            Booktrope
 * Author URI:        http://booktrope.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       booktrope
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Set some paths
define( 'BTNM_PATH', plugin_dir_path(__FILE__) );
define( 'BTNM_URL', plugins_url( '/', __FILE__ ) );

// grab some files
require_once( BTNM_PATH . 'includes/functions.php' );
require_once( BTNM_PATH . 'includes/acf-fieldlist.php' );

add_image_size( 'newslettercover', 120, 184, false );

