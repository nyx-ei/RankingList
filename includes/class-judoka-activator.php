<?php

class Judoka_Activator
{

    public static function activate()
    {
        global $wpdb;
        $database_manager = new Database_Manager();
        $database_manager->createJudokasTable();
        $database_manager->createCompetitionsTable();
    }

    public static function deactivate(){}
}
