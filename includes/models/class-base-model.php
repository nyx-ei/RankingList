<?php

class Base_Model {
    protected $db;

    public function __construct() {
        $this->db = Database_Access::getInstance();
    }
}