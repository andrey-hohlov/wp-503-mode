<?php

class MntMode {

    protected static $instance = null;
    protected $settings;
    protected $adminScreen = null;
    protected $settingsPageSlug = 'maintenance-mode';

    private function __construct() {
        $this->settings = get_option(WPMNT_OPTION);

        $this->definePublicHooks();

        if (is_admin()) {
            $this->defineAdminHooks();
        }
    }

    public static function getInstance() {
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    /**
     * Register all of the hooks related to the admin area functionality
     */

    private function defineAdminHooks() {
        add_action('admin_menu', array($this, 'createAdminPage'));
        add_action('admin_notices', array($this, 'addAdminNotices'));
    }


    /**
     * Register all of the hooks related to the public area functionality
     */

    private function definePublicHooks() {

        if($this->settings['status'] && $this->settings['status'] == 1 ) {
            add_action('init', array($this, 'init'));
        }

    }

    public function init() {
        if(
            !$this->checkUserRole() &&
            !strstr($_SERVER['PHP_SELF'], 'wp-cron.php') &&
            !strstr($_SERVER['PHP_SELF'], 'wp-login.php') &&
            !strstr($_SERVER['PHP_SELF'], 'wp-admin/') &&
            !strstr($_SERVER['PHP_SELF'], 'async-upload.php') &&
            !(strstr($_SERVER['PHP_SELF'], 'upgrade.php') && $this->checkUserRole()) &&
            !strstr($_SERVER['PHP_SELF'], '/plugins/') &&
            !strstr($_SERVER['PHP_SELF'], '/xmlrpc.php') &&
            !$this->checkExclude()
        ) {

            $protocol = !empty($_SERVER['SERVER_PROTOCOL']) && in_array($_SERVER['SERVER_PROTOCOL'], array('HTTP/1.1', 'HTTP/1.0')) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
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
                include_once WPMNT_PATH  .'templates/maintenance.php';
            }
            ob_flush();
            exit();
        }
    }

    public function checkUserRole() {
        $is_allowed = false;

        if (is_super_admin()) {
            $is_allowed = true;
        }

        $role = $this->settings['access'];

        if (current_user_can($role)) {
            $is_allowed = true;
        }

        return $is_allowed;
    }

    public function checkExclude() {
        $is_excluded = false;

        if (!empty($this->settings['exclude']) && is_array($this->settings['exclude'])) {
            foreach ($this->settings['exclude'] as $item) {
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

    public function createAdminPage() {
        //$this->adminScreen = add_menu_page( 'My WP Tools', 'My WP Tools', 'edit_others_posts', 'my_tools', array(&$this, 'adminPageTemplate'), 'dashicons-admin-tools', 99);
        $this->adminScreen = add_options_page( 'Режим обслуживания', 'Режим обслуживания', 'edit_others_posts', $this->settingsPageSlug, array(&$this, 'adminPageTemplate'));
    }


    /**
     * Template for settings page
     */

    public function adminPageTemplate(){
        $this->saveSettings();
        include_once WPMNT_PATH .'templates/admin.php';
    }

    public function addAdminNotices() {

        if ($this->adminScreen !== get_current_screen()->id) {
            if ($this->settings['status'] == 1) {
                $notice = array(
                    'class' => 'error',
                    'msg' => sprintf('Maintenance mode activated. <a href="%s">Deactivate</a>', admin_url('options-general.php?page='.$this->settingsPageSlug))
                );
                echo '<div id="message" class="'.$notice['class'].' fade"><p>'.$notice['msg'].'</p></div>';
            }
        }
    }


    /**
     * Saving settings
     */

    private function saveSettings(){
        if (!empty($_POST)) {

            if (!wp_verify_nonce($_POST['_wpnonce'], 'wpMntMode')) {
                die('Security check!');
            }

            $_POST['options']['status'] = (int) $_POST['options']['status'];
            $_POST['options']['access'] = sanitize_text_field($_POST['options']['access']);

            if (!empty($_POST['options']['exclude'])) {
                $exclude_array = explode("\n", $_POST['options']['exclude']);
                $_POST['options']['exclude'] = array_map('trim', $exclude_array);
            } else {
                $_POST['options']['exclude'] = array();
            }
            if (
                isset($this->settings['status']) && isset($_POST['options']['status']) &&
                (
                    ($this->settings['status'] == 1 && in_array($_POST['options']['status'], array(0, 1))) ||
                    ($this->settings['status'] == 0 && $_POST['options']['status'] == 1)
                )
            ) {
                $this->clearCache();
            }

            $this->settings = $_POST['options'];

            update_option(WPMNT_OPTION, $this->settings);
        }
    }


    /**
     * Clear cache
     */

    private function clearCache(){
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

