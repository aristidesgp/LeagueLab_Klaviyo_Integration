<?php
/*
*
* @package aristidesgp


Plugin Name:  League Lab Klaviyo Integration
Plugin URI:   https://thomasgbennett.com/
Description:  Tool to synchronize registered players by leagues from League Lab to Klaviyo
Version:      1.0.0
Author:       Bennet Group (Aristides Gutierrez)
Author URI:   https://thomasgbennett.com/
*/


defined('ABSPATH') or die('You do not have access, sally human!!!');

define('LLKI_PLUGIN_VERSION', '1.0.0');

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once  dirname(__FILE__) . '/vendor/autoload.php';
}

//Change WRPL for plugin's initials
define('LLKI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LLKI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LLKI_ADMIN_URL', get_admin_url());
define('LLKI_PLUGIN_DIR_BASENAME', dirname(plugin_basename(__FILE__)));
define('LLKI_THEME_DOMAIN', get_site_url());


//include the helpers
include 'inc/util/helper.php';

if (class_exists('LLKI\\Inc\\Init')) {
    register_activation_hook(__FILE__, array('LLKI\\Inc\\Base\\Activate', 'activate'));
    LLKI\Inc\Init::register_services();
}
