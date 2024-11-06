<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}competitions_judoka");

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}judokas");

delete_option('judoka_plugin_version');

$upload_dir = wp_upload_dir();
$judoka_upload_dir = $upload_dir['basedir'] . '/judokas';
if (is_dir($judoka_upload_dir)) {
    $files = glob($judoka_upload_dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    rmdir($judoka_upload_dir);
}