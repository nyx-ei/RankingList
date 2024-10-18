<?php

/**
 * Plugin Name: RankingList
 * Description: Plugin to manage ranking list
 * Version: 1.0.0
 * Author: NYX-EI <contact@nxy-ei.com>
 * License: GPLv3
 * Author URI: https://nxy-ei.com
 */

if (!defined('ABSPATH')) {
    exit;
}

define('JUDOKA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JUDOKA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JUDOKA_PLUGIN_VERSION', '1.0.0');

require_once JUDOKA_PLUGIN_DIR . 'includes/class-judoka-activator.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/class-judoka-model.php';
require_once JUDOKA_PLUGIN_DIR . 'includes/class-competition-model.php';
require_once JUDOKA_PLUGIN_DIR . 'admin/class-judoka-admin.php';

//enable and disable
register_activation_hook(__FILE__, array('Judoka_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Judoka_Activator', 'deactivate'));


if (is_admin()) {
    $judoka_admin = new Judoka_Admin();
}

