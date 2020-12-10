<?php
/*
Plugin Name: Imgfly
Text Domain: imgfly
Description: Optimizing images has never been easier.
Author: Imgfly
Author URI: https://imgfly.co
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Version: 1.0.0
 */

defined('ABSPATH') or exit;

/*
Livecycle hooks
 */
register_activation_hook(__FILE__, ['Imgfly', 'activate']);
register_uninstall_hook(__FILE__, ['Imgfly', 'uninstall']);

/*
Settings hooks
 */
add_action('admin_menu', ['Imgfly', 'addSettingsPage']);
add_action('admin_init', ['Imgfly', 'registerSettings']);
add_filter('plugin_action_links_' . plugin_basename(__FILE__), ['Imgfly', 'addSettingsLink']);

/*
Page render hook
 */
add_filter('template_redirect', ['Imgfly', 'rewrite'], 100);

/*
Class autoloading
 */
spl_autoload_register('imgfly_autoload');
function imgfly_autoload($class)
{
    if (in_array($class, ['Imgfly'])) {
        require_once sprintf('%s/%s.class.php', dirname(__FILE__), strtolower($class));
    }
}
