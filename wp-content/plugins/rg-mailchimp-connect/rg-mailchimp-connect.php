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

// setup mailchimp shortcode
function rg_mailchimp_connect() {
    $html = "<p>Hello, World!</p>";
 
    return $html;
}

add_shortcode( 'rgmcconnect' , 'rg_mailchimp_connect' );