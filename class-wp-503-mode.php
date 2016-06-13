<?php

class Wp503Mode {

    protected static $settings;
    protected static $adminScreen;

    public function __construct() {}

    public static function init() {
        self::$settings  = get_option(WP_503_OPTION);
        self::definePublicHooks();

        if (is_admin()) {
            self::defineAdminHooks();
        }
    }

    /**
     * Run mode
     */

    public static function run() {

        if (
            !self::checkUserRole() &&
            !strstr($_SERVER['PHP_SELF'], 'wp-cron.php') &&
            !strstr($_SERVER['PHP_SELF'], 'wp-login.php') &&
            !strstr($_SERVER['PHP_SELF'], 'wp-admin/') &&
            !strstr($_SERVER['PHP_SELF'], 'async-upload.php') &&
            !(strstr($_SERVER['PHP_SELF'], 'upgrade.php') && self::checkUserRole()) &&
            !strstr($_SERVER['PHP_SELF'], '/plugins/') &&
            !strstr($_SERVER['PHP_SELF'], '/xmlrpc.php') &&
            !self::checkExclude()
        ) {

            $protocol = !empty($_SERVER['SERVER_PROTOCOL']) && in_array($_SERVER['SERVER_PROTOCOL'], array('HTTP/1.1', 'HTTP/1.0'))
                ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
            $charset = get_bloginfo('charset') ? get_bloginfo('charset') : 'UTF-8';
            $status_code = 503;
            $backtime = 3600;

            nocache_headers();
            ob_start();
            header("Content-type: text/html; charset=$charset");
            header("$protocol $status_code Service Unavailable", TRUE, $status_code);
            header("Retry-After: $backtime");

            if (file_exists(get_stylesheet_directory().'/maintenance.php')) {
                include_once(get_stylesheet_directory().'/maintenance.php');
            } else {
                include_once WP_503_PATH  .'templates/maintenance.php';
            }
            ob_flush();
            exit();
        }
    }


    /**
     * Register all of the hooks related to the admin area functionality
     */

    private static function defineAdminHooks() {
        add_action('admin_menu', array(__CLASS__, 'createAdminPage'));
        add_action('admin_notices', array(__CLASS__, 'addAdminNotices'));
    }


    /**
     * Register all of the hooks related to the public area functionality
     */

    private static function definePublicHooks() {

        if(self::$settings['status'] && self::$settings['status'] == 1 ) {
            add_action('init', array(__CLASS__, 'run'));
        }

    }

    /**
     * Check if user has access in 503 mode
     */

    private static function checkUserRole() {

        if ( !is_user_logged_in() ) return false;

        if ( is_super_admin() ) return true;

        $accessRoles = self::$settings['access'];
        $user = wp_get_current_user();

        if( !$user || empty($accessRoles) ) return false;

        foreach ($user->roles as $userRole) {
            if (in_array($userRole, (array) $accessRoles)) return true;
        }

        return false;

    }

    /**
     * Check excluded URLs
     */

    private static function checkExclude() {
        $is_excluded = false;

        if (!empty(self::$settings['exclude']) && is_array(self::$settings['exclude'])) {
            foreach (self::$settings['exclude'] as $item) {
                if ((!empty($_SERVER['REQUEST_URI']) && strstr($_SERVER['REQUEST_URI'], $item))) {
                    $is_excluded = true;
                    break;
                }
            }
        }

        return $is_excluded;
    }


    /**
     * Create and setup settings page
     */

    public static function createAdminPage() {
        self::$adminScreen = add_options_page( 'Режим обслуживания', 'Режим обслуживания', 'edit_others_posts', WP_503_PAGE_SLUG, array(__CLASS__, 'adminPageTemplate'));
    }


    /**
     * Template for settings page
     */

    public static function adminPageTemplate(){
        self::saveSettings();
        $settings = self::$settings;
        include_once WP_503_PATH .'templates/admin.php';
    }


    /**
     * Show notice in admin area
     */

    public static function addAdminNotices() {

        if (self::$adminScreen !== get_current_screen()->id) {
            if (self::$settings['status'] == 1) {
                $notice = array(
                    'class' => 'error',
                    'msg' => sprintf('Maintenance mode activated. <a href="%s">Deactivate</a>', admin_url('options-general.php?page=' . WP_503_PAGE_SLUG))
                );
                echo '<div id="message" class="'.$notice['class'].' fade"><p>'.$notice['msg'].'</p></div>';
            }
        }
    }


    /**
     * Saving settings
     */

    private static function saveSettings(){
        if (!empty($_POST)) {

            if (!wp_verify_nonce($_POST['_wpnonce'], WP_503_NONCE)) {
                die('Security check!');
            }

            $_POST['options']['status'] = (int) $_POST['options']['status'];

            if (!empty($_POST['options']['exclude'])) {
                $exclude_array = explode("\n", $_POST['options']['exclude']);
                $_POST['options']['exclude'] = array_map('trim', $exclude_array);
            } else {
                $_POST['options']['exclude'] = array();
            }
            if (
                isset(self::$settings['status']) && isset($_POST['options']['status']) &&
                (
                    (self::$settings['status'] == 1 && in_array($_POST['options']['status'], array(0, 1))) ||
                    (self::$settings['status'] == 0 && $_POST['options']['status'] == 1)
                )
            ) {
                self::clearCache();
            }

            self::$settings = $_POST['options'];

            update_option(WP_503_OPTION, self::$settings);
        }
    }


    /**
     * Clear cache
     */

    private static function clearCache(){
        // Super Cache Plugin
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        // W3 Total Cache Plugin
        if (function_exists('w3tc_pgcache_flush')) {
            w3tc_pgcache_flush();
        }
    }
}

