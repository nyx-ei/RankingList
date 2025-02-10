<?php

/**
 * Plugin Name: RankingList
 * Description: Plugin to manage ranking list
 * Version: 1.0.0
 * Author: NYX-EI <contact@nxy-ei.com>
 * License: GPLv3
 * Author URI: https://nxy-ei.com
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('JUDOKA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JUDOKA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JUDOKA_PLUGIN_VERSION', '1.0.0');

define('JUDOKA_ADMIN_MENU_CONFIG', include JUDOKA_PLUGIN_DIR . 'admin/config/admin-menu.php');

require_once __DIR__ . '/vendor/autoload.php';

require_once JUDOKA_PLUGIN_DIR . 'includes/db/class-database-manager.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/db/class-database-access.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/models/class-base-model.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/models/class-judoka-model.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/models/class-competition-model.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/class-judoka-activator.php';
require_once  JUDOKA_PLUGIN_DIR . 'includes/class-judoka-cron-manager.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/shortcodes/class-judoka-ranking-shortcode.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-admin.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-asset-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-menu-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-file-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-import-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-crud-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-report-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/config/admin-menu.php';

// Plugin activation
register_activation_hook(__FILE__, function() {
    Judoka_Activator::activate();

    $cron_manager = Judoka_Cron_Manager::get_instance();
    $cron_manager->setup_cron();
});

// Plugin deactivation
register_deactivation_hook(__FILE__, function() {
    Judoka_Activator::deactivate();

    $cron_manager = Judoka_Cron_Manager::get_instance();
    $cron_manager->remove_cron();
});

if (is_admin()) {
    $judoka_admin = new Judoka_Admin();
}
