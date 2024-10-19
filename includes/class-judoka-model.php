<?php

class Judoka_Model {

    private $wpdb;
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'judokas';
    }

    public function create($data) {

        $fields = [
            'full_name' => sanitize_text_field($data['full_name']),
            'birth_date' => sanitize_text_field($data['birth_date']),
            'category' => sanitize_text_field($data['category']),
            'weight' => floatval($data['weight']),
            'club' => sanitize_text_field($data['club']),
            'grade' => sanitize_text_field($data['grade']),
            'gender' => sanitize_text_field($data['sexe']),
            'photo_profile' => sanitize_text_field($data['photo_profile']),
            'images' => isset($data['images']) ? sanitize_text_field($data['images']) : ''
        ];

        return $this->wpdb->insert($this->table_name, $fields);
        
    }

    public function get($id) {
        $query = $this->wpdb->prepare("SELECT * FROM $this->table_name WHERE id = %d", $id);
        return $this->wpdb->get_row($query);
    }

    public function get_all() {
        $query = "SELECT * FROM $this->table_name ORDER BY full_name ASC";
        return $this->wpdb->get_results($query);
    }

    public function get_distinct_categories() {
        $query = "SELECT DISTINCT category FROM $this->table_name ORDER BY category ASC";
        return $this->wpdb->get_col($query);
    }

    public function get_distinct_clubs() {
        $query = "SELECT DISTINCT club FROM $this->table_name ORDER BY club ASC";
        return $this->wpdb->get_col($query);
    }

    public function update($id, $data) {
        $fields = [
            'full_name' => sanitize_text_field($data['full_name']),
            'birth_date' => sanitize_text_field($data['birth_date']),
            'category' => sanitize_text_field($data['category']),
            'weight' => floatval($data['weight']),
            'club' => sanitize_text_field($data['club']),
            'grade' => sanitize_text_field($data['grade']),
            'gender' => sanitize_text_field($data['gender']),
            'photo_profile' => sanitize_text_field($data['photo_profile']),
            'images' => isset($data['images']) ? sanitize_text_field($data['images']) : ''
        ];

        return $this->wpdb->update($this->table_name, $fields, ['id' => $id]);
    }

    public function delete($id) {
        return $this->wpdb->delete($this->table_name, ['id' => $id]);
    }

    public function exists($full_name, $birthday) {
        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE full_name = %s AND birthday = %s",
                $full_name,
                $birthday
            )
        );
    }
}
