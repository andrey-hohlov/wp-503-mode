<?php

/**
 * Plugin Name:         WP Maintenance mode
 * Plugin URI:          http://hohlov.pro
 * Description:         Maintenance mode for WP
 * Version:             0.0.3
 * Author:              Andrey Hohlov
 * Author URI:          http://hohlov.pro/
 * License:             MIT
 * License URI:         http://opensource.org/licenses/MIT
 * Text Domain:         wp-maintenance-mode
 * Domain Path:         /languages
 */

// If this file is called directly, abort
if (!defined('WPINC')) {
    die;
}

define('WP_MAINTENANCE_PATH', plugin_dir_path( __FILE__ ));
define('WP_MAINTENANCE_OPTION', 'wp_maintenance');


/**
 * Activation and deactivation
 */

register_activation_hook( __FILE__, 'maintenanceModeActivate');
function maintenanceModeActivate() {
    $options = array();
    $options['status'] = 0;
    $options['access'] = 'administrator';
    update_option(WPMNT_OPTION, $options, false);
}

register_deactivation_hook( __FILE__, 'maintenanceModeDeactivate');
function maintenanceModeDeactivate() {
    delete_option(WPMNT_OPTION);
}


/**
 * Init plugin
 */

require WPMNT_PATH . 'class-wp-maintenance-mode.php';

MaintenanceMode::getInstance();
