<?php
/*
 * Plugin Name: Genesis Dambuster
 * Plugin URI: http://www.genesisdambuster.com/
 * Description: A Genesis only WordPress plugin that makes it easy to set up your pages for edge to edge content. Ideal for full width Beaver Builder templates. 
 * Version: 1.4
 * Author: Russell Jamieson
 * Author URI: http://www.diywebmastery.com/about/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */ 
define('GENESIS_DAMBUSTER_VERSION','1.4');
define('GENESIS_DAMBUSTER_FRIENDLY_NAME', 'Genesis Dambuster');
define('GENESIS_DAMBUSTER_PLUGIN_NAME', plugin_basename(dirname(__FILE__))) ;
define('GENESIS_DAMBUSTER_PLUGIN_PATH', GENESIS_DAMBUSTER_PLUGIN_NAME.'/main.php');
define('GENESIS_DAMBUSTER_DOMAIN', 'GENESIS_DAMBUSTER_DOMAIN');
define('GENESIS_DAMBUSTER_HOME', 'http://www.genesisdambuster.com');
define('GENESIS_DAMBUSTER_HELP', 'help@genesisdambuster.com');
define('GENESIS_DAMBUSTER_ICON', plugins_url('images/genesisdambuster.png', __FILE__));
define('GENESIS_DAMBUSTER_NEWS', 'http://www.diywebmastery.com/tags/genesis-newsfeed/feed/?images=1&featured_only=1');
require_once(dirname(__FILE__) . '/classes/class-plugin.php');
$genesis_dambuster = Genesis_Dambuster_Plugin::get_instance();
register_activation_hook(__FILE__, array($genesis_dambuster,'activate'));
add_action('init', array($genesis_dambuster,'init'),0);
if (is_admin()) add_action('init', array($genesis_dambuster,'admin_init'),0);
?>