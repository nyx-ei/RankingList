<?php

if (!defined('ABSPATH')) exit;

class Judoka_Asset_Handler {
    public function enqueue_admin_scripts() {
        wp_enqueue_style('judoka-admin-css', JUDOKA_PLUGIN_URL . 'admin/css/judoka-admin.css');
        wp_enqueue_script('judoka-admin-js', JUDOKA_PLUGIN_URL . 'admin/js/judoka-admin.js', ['jquery']);

        wp_localize_script('judoka-admin-js', 'judokaAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'judoka_nonce' => wp_create_nonce('add_judoka_nonce'),
            'judoka_edit_nonce' => wp_create_nonce('edit_judoka_nonce'),
            'judoka_delete_nonce' => wp_create_nonce('delete_judoka_nonce'),
            'judoka_import_nonce' => wp_create_nonce('import_judoka_nonce'),
        ]);
    }
}
