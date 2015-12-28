<?php

/**
 * Plugin Name:         WP Mnt Mode
 * Plugin URI:          http://hohlov.pro
 * Description:         Maintenance mode for WP
 * Version:             0.0.2
 * Author:              Andrey Hohlov
 * Author URI:          http://hohlov.pro/
 * License:             MIT
 * License URI:         http://opensource.org/licenses/MIT
 * Text Domain:         wp-mnt-mode
 * Domain Path:         /languages
 */

// If this file is called directly, abort
if (!defined('WPINC')) {
    die;
}

define('WPMNT_PATH', plugin_dir_path( __FILE__ ));
define('WPMNT_OPTION', 'wpMnt');


/**
 * Activation and deactivation
 */

register_activation_hook( __FILE__, 'wpMntActivate');
register_deactivation_hook( __FILE__, 'wpMntDeactivate');

function wpMntActivate() {
    $options = array();
    $options['status'] = 0;
    $options['access'] = 'administrator';
    update_option(WPMNT_OPTION, $options, false);
}

function wpMntDeactivate() {
    delete_option(WPMNT_OPTION);
}

/**
 * Init plugin
 */

require WPMNT_PATH . 'class-wp-mnt-mode.php';

MntMode::getInstance();
