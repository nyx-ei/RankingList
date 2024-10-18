<?php

class Judoka_Activator
{

    public static function activate()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        //judokas table
        $table_judokas = $wpdb->prefix . 'judokas';
        $sql_judokas = "CREATE TABLE IF NOT EXISTS $table_judokas (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            full_name varchar(100) NOT NULL,
            birth_date date NOT NULL,
            category varchar(50) NOT NULL,
            weight decimal(5,2) NOT NULL,
            club varchar(100) NOT NULL,
            grade varchar(50) NOT NULL,
            gender enum('M','F') NOT NULL,
            photo_profile varchar(255),
            images text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        //competitions table
        $table_competitions = $wpdb->prefix . 'competitions_judoka';
        $sql_competitions = "CREATE TABLE IF NOT EXISTS $table_competitions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            judoka_id mediumint(9) NOT NULL,
            competition_name varchar(100) NOT NULL,
            date_competition date NOT NULL,
            points int NOT NULL,
            rang int NOT NULL,
            medals varchar(50),
            PRIMARY KEY  (id),
            FOREIGN KEY (judoka_id) REFERENCES $table_judokas(id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_judokas);
        dbDelta($sql_competitions);
    }

    public static function deactivate()
    {
        global $wpdb;
        $table_judokas = $wpdb->prefix . 'judokas';
        $table_competitions = $wpdb->prefix . 'competitions_judoka';
        $wpdb->query("DROP TABLE IF EXISTS $table_judokas, $table_competitions");
    }
}
