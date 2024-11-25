<?php

if (!defined('ABSPATH')) exit;

class Judoka_Menu_Handler
{

    private $config_menu;

    public function __construct()
    {
        $this->config_menu = JUDOKA_ADMIN_MENU_CONFIG;
    }

    public function add_admin_menu()
    {
        $this->add_main_menu();
        $this->add_sub_menus();
    }

    public function display_judoka_list()
    {
        include JUDOKA_PLUGIN_DIR . 'admin/partials/list-judokas.php';
    }

    public function display_add_judoka()
    {
        include JUDOKA_PLUGIN_DIR . 'admin/partials/add-judoka.php';
    }

    public function display_edit_judoka()
    {
        include JUDOKA_PLUGIN_DIR . 'admin/partials/edit-judoka.php';
    }

    public function display_import_judokas()
    {
        include JUDOKA_PLUGIN_DIR . 'admin/partials/import-judoka.php';
    }

    private function add_main_menu()
    {
        $menu = $this->config_menu['menu'];
        add_menu_page(
            $menu['page_title'],
            $menu['menu_title'],
            $menu['capability'],
            $menu['menu_slug'],
            [$this, 'display_judoka_list'],
            $menu['icon']
        );
    }

    private function add_sub_menus()
    {
        $menu_slug = $this->config_menu['menu']['menu_slug'];
        $submenus = [
            'add_judoka' => 'display_add_judoka',
            'edit_judoka' => 'display_edit_judoka',
            'import_judokas' => 'display_import_judokas'
        ];

        foreach ($submenus as $key => $callback) {
            $submenu = $this->config_menu['submenu'][$key];
            add_submenu_page(
                $menu_slug,
                $submenu['page_title'],
                $submenu['menu_title'] ?? '',
                $submenu['capability'],
                $submenu['menu_slug'],
                [$this, $callback]
            );
        }
    }
}
