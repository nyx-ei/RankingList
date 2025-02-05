<?php

return array(
    'menu' => array(
        'page_title' => 'Judokas Management',
        'menu_title' => 'RankingList',
        'capability' => 'manage_options',
        'menu_slug' => 'judokas-management',
        'icon' => 'dashicons-groups',
    ),

    'submenu' => array(
        'add_judoka' => array(
            'page_title' => 'Add a Judoka',
            'menu_title' => 'Add a Judoka',
            'capability' => 'manage_options',
            'menu_slug' => 'add-judoka'
        ),
        'edit_judoka' => array(
            'page_title' => 'Edit a Judoka',
            'menu_title' => '',
            'capability' => 'manage_options',
            'menu_slug' => 'edit-judoka'
        ),
        'import_judokas' => array(
            'page_title' => 'Import Judokas',
            'menu_title' => 'Import Judokas',
            'capability' => 'manage_options',
            'menu_slug' => 'import-judokas',
        ),
        'reports' => array(
            'page_title' => 'Reports',
            'menu_title' => 'Reports',
            'capability' => 'manage_options',
            'menu_slug' => 'judoka-reports',
        ),
    ),
);
