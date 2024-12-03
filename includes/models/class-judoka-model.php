<?php

class Judoka_Model extends Base_Model {

    private $table_name;

    public function __construct()
    {
        parent::__construct();
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'judokas';
    }

    /**
     * Create a new judoka in the database.
     *
     * @param array $data An array containing the judoka's information.
     *                    - 'full_name'     (string) The full name of the judoka.
     *                    - 'birth_date'    (string) The birth date of the judoka.
     *                    - 'category'      (string) The category of the judoka.
     *                    - 'weight'        (float) The weight of the judoka.
     *                    - 'club'          (string) The judoka's club.
     *                    - 'grade'         (string) The grade of the judoka.
     *                    - 'gender'        (string) The gender of the judoka.
     *                    - 'photo_profile' (string) The path to the judoka's profile photo.
     *                    - 'images'        (string) Optional. Additional images of the judoka.
     *
     * @return int|false The number of rows inserted, or false on error.
     */
    public function create_judoka($data) {

        $fields = [
            'full_name' => sanitize_text_field($data['full_name']),
            'birth_date' => sanitize_text_field($data['birth_date']),
            'category' => sanitize_text_field($data['category']),
            'weight' => floatval($data['weight']),
            'club' => sanitize_text_field($data['club']),
            'grade' => sanitize_text_field($data['grade']),
            'gender' => sanitize_text_field($data['gender']),
            'photo_profile' => sanitize_text_field($data['photo_profile']),
            'images' => !empty($data['images']) ? json_encode($data['images']) : ''
        ];

        return $this->db->insert($this->table_name, $fields);
    }


    /**
     * Retrieve a judoka based on the provided ID.
     *
     * @param int $id The ID of the judoka to retrieve.
     * @return object|false The retrieved judoka object on success, false if not found.
     */
    public function get_judoka($id) {
        return $this->db->get_row(
            "SELECT * FROM $this->table_name WHERE id = %d",
            [$id]
        );
    }

    /**
     * Retrieve all judokas from the database.
     *
     * @return array List of all judokas.
     */
    public function get_judokas() {
        return $this->db->get_results(
            "SELECT * FROM $this->table_name ORDER BY full_name ASC"
        );
    }

    /**
     * Retrieve a list of distinct categories from the database.
     *
     * @return array List of distinct categories.
     */
    public function get_distinct_categories() {
        $query = "SELECT DISTINCT category FROM $this->table_name ORDER BY category ASC";
        return $this->db->get_col($query);
    }

    /**
     * Retrieve a list of distinct clubs from the database.
     *
     * @return array List of distinct clubs.
     */
    public function get_distinct_clubs() {
        $query = "SELECT DISTINCT club FROM $this->table_name ORDER BY club ASC";
        return $this->db->get_col($query);
    }

    /**
     * Update a judoka
     *
     * @param int   $id      The ID of the judoka to update
     * @param array $data    The data to update
     *
     * @return int|false The number of rows updated, or false on error
     */
    public function update_judoka($id, $data) {
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

        return $this->db->update($this->table_name, $fields, ['id' => $id]);
    }

    /**
     * Delete a judoka from the database based on the provided ID.
     *
     * @param int $id The ID of the judoka to delete.
     * @return int|false The number of rows deleted, or false on error.
     */
    public function delete_judoka($id) {
        return $this->db->delete($this->table_name, ['id' => $id]);
    }

    /**
     * Check if a judoka already exists in the database.
     *
     * @param string $full_name The full name of the judoka.
     * @param string $birthday The birthday of the judoka in 'Y-m-d' format.
     *
     * @return int|false The ID of the judoka if it exists, false otherwise.
     */
    public function judoka_exists($full_name, $birth_date) {
        return $this->db->get_var(
            "SELECT id FROM $this->table_name WHERE full_name = %s AND birth_date = %s",
            [$full_name, $birth_date]
        );
    }
}
