<?php

if (!defined('ABSPATH')) exit;

class Judoka_Admin
{
    private $menu_handler;
    private $asset_handler;
    private $judoka_handler;
    private $import_handler;

    private $report_handler;

    public function __construct()
    {
        $this->menu_handler = new Judoka_Menu_Handler();
        $this->asset_handler = new Judoka_Asset_Handler();
        $this->judoka_handler = new Judoka_CRUD_Handler();
        $this->import_handler = new Judoka_Import_Handler();
        $this->report_handler = new Judoka_Report_Handler();


        $this->init_hooks();
    }

    private function init_hooks()
    {
        add_action('admin_menu', [$this->menu_handler, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this->asset_handler, 'enqueue_admin_scripts']);
        add_action('wp_ajax_add_judoka', [$this->judoka_handler, 'handle_add']);
        add_action('wp_ajax_edit_judoka', [$this->judoka_handler, 'handle_edit']);
        add_action('wp_ajax_delete_judoka', [$this->judoka_handler, 'handle_delete']);
        add_action('wp_ajax_import_judokas', [$this->import_handler, 'handle_import']);
        add_action('wp_ajax_generate_report', [$this->report_handler, 'handle_generate']);
        add_action('wp_ajax_share_report', [$this->report_handler, 'handle_share']);
    }
}
