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
            'menu_title' => 'Edit a Judoka',
            'capability' => 'manage_options',
            'menu_slug' => 'edit-judoka'
        ),
        'delete_judoka' => array(
            'page_title' => 'Delete a Judoka',
            'menu_title' => 'Delete a Judoka',
            'capability' => 'manage_options',
            'menu_slug' => 'delete-judoka'
        ),
    ),
);
