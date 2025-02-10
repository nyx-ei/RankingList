<?php

class Database_Manager {
    
    private $wpdb;
    private $charset_collate;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $wpdb->get_charset_collate();
    }

    public function createJudokasTable() {

        try {
             $table_name = $this->wpdb->prefix . 'judokas';
             $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
             updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
             PRIMARY KEY  (id)
            ) $this->charset_collate;";
            
            return $this->executeQuery($sql);
        } catch (\Throwable $e) {
            return new WP_Error('database_error', $e->getMessage());
        }

       
    }

    public function createCompetitionsTable() {
        try {
            $table_name = $this->wpdb->prefix . 'competitions_judoka';
            $judokas_table = $this->wpdb->prefix . 'judokas';
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            judoka_id mediumint(9) NOT NULL,
            competition_name varchar(100) NOT NULL,
            date_competition date NOT NULL,
            points int NOT NULL,
            rang int NOT NULL,
            medals varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            FOREIGN KEY (judoka_id) REFERENCES $judokas_table(id)
            ) $this->charset_collate;";
        
            return $this->executeQuery($sql);
        } catch (\Throwable $e) {
            return new WP_Error('database_error', $e->getMessage());
        }
    }

    public function createRankingsHistoryTable()
    {
        try {
        $table_name = $this->wpdb->prefix. 'rankings_history';
        $judokas_table = $this->wpdb->prefix. 'judokas';

            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                judoka_id mediumint(9) NOT NULL,
                rank int NOT NULL,
                total_points int NOT NULL,
                snapshot_date date NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                FOREIGN KEY (judoka_id) REFERENCES $judokas_table(id),
                KEY idx_snapshot_date (snapshot_date)
            ) $this->charset_collate;";

            return $this->executeQuery($sql);
        } catch (\Throwable $e) {
            return new WP_Error('database_error', $e->getMessage());
        }
    }


    private function executeQuery($sql) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}