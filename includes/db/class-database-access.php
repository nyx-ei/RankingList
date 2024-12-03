<?php

class Database_Access {
    private $wpdb;
    private static $instance = null;

    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {
        throw new Exception("Cannot clone a singleton");
    }

    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton");
    }

    public function insert($table_name, array $data) {
        $result = $this->wpdb->insert($table_name, $data);
        if ($result === false) {
            return new WP_Error('database_error', $this->wpdb->last_error);
        }
        return $this->wpdb->insert_id;
    }

    public function update($table_name, array $data, array $where) {
        $result = $this->wpdb->update($table_name, $data, $where);
        if ($result === false) {
            return new WP_Error('database_error', $this->wpdb->last_error);
        }
        return $result;
    }

    public function delete($table_name, array $where){
        $result = $this->wpdb->delete($table_name, $where);
        if ($result === false) {
            return new WP_Error('database_error', $this->wpdb->last_error);
        }
        return $result;
    }

    public function get_row($query, array $params = []) {
        if (!empty($params)) {
            $query = $this->wpdb->prepare($query, ...$params);
        }
        return $this->wpdb->get_row($query);
    }

    public function get_results($query, array $params = []) {
        if (!empty($params)) {
            $query = $this->wpdb->prepare($query, ...$params);
        }
        return $this->wpdb->get_results($query);
    }

    public function get_var($query, array $params = []) {
        if (!empty($params)) {
            $query = $this->wpdb->prepare($query, ...$params);
        }
        return $this->wpdb->get_var($query);
    }

    public function get_col($query, array $params = []) {
        if (!empty($params)) {
            $query = $this->wpdb->prepare($query, ...$params);
        }
        return $this->wpdb->get_col($query);
    }
}