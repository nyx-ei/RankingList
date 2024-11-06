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
    ),
);
