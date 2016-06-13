<?php

/**
 * Plugin Name:         WP 503 mode
 * Plugin URI:          http://hohlov.pro
 * Description:         Maintenance mode for WP
 * Version:             0.0.3
 * Author:              Andrey Hohlov
 * Author URI:          http://hohlov.pro/
 * License:             MIT
 * License URI:         http://opensource.org/licenses/MIT
 * Text Domain:         wp-503-mode
 * Domain Path:         /languages
 */

// If this file is called directly, abort
if (!defined('WPINC')) {
    die;
}

define('WP_503_PATH', plugin_dir_path( __FILE__ ));
define('WP_503_OPTION', 'wp_503_mode');
define('WP_503_NONCE', 'wp_503_mode_nonce');
define('WP_503_PAGE_SLUG', 'maintenance-mode');


/**
 * Activation and deactivation
 */

register_activation_hook( __FILE__, 'maintenanceModeActivate');
function maintenanceModeActivate() {
    $options = array();
    $options['status'] = 0;
    $options['access'] = ['administrator'];
    update_option(WP_503_OPTION, $options, false);
}

register_deactivation_hook( __FILE__, 'maintenanceModeDeactivate');
function maintenanceModeDeactivate() {
    delete_option(WP_503_OPTION);
}


/**
 * Init plugin
 */

require WP_503_PATH . 'class-wp-503-mode.php';

Wp503Mode::init();
