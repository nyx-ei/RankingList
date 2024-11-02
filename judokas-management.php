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

require_once JUDOKA_PLUGIN_DIR . 'includes/class-judoka-activator.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/class-judoka-model.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/class-competition-model.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-admin.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-asset-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-menu-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-file-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-import-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-crud-handler.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/config/admin-menu.php';

//enable and disable
register_activation_hook(__FILE__, array('Judoka_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Judoka_Activator', 'deactivate'));


if (is_admin()) {
    $judoka_admin = new Judoka_Admin();
}
