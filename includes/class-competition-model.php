<?php
class Competition_Model {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'competitions_judoka';
    }

    /**
     * Insert a new competition record for a judoka in the database.
     *
     * @param array $data An associative array containing competition data.
     *                    - 'judoka_id'        (int)    The ID of the judoka.
     *                    - 'competition_name' (string) The name of the competition.
     *                    - 'date_competition' (string) The date of the competition.
     *                    - 'points'           (int)    The number of points earned.
     *                    - 'rang'             (int)    The rank achieved in the competition.
     *                    - 'medals'           (string) The type of medal earned (e.g., Gold, Silver, Bronze).
     *
     * @return int|false The number of rows inserted, or false on error.
     */
    public function create($data) {
        return $this->wpdb->insert(
            $this->table_name,
            array(
                'judoka_id' => intval($data['judoka_id']),
                'competition_name' => sanitize_text_field($data['competition_name']),
                'date_competition' => sanitize_text_field($data['date_competition']),
                'points' => intval($data['points']),
                'rang' => intval($data['rang']),
                'medals' => sanitize_text_field($data['medals'])
            )
        );
    }

    /**
     * Retrieve all competition records for a given judoka.
     *
     * @param int $judoka_id The ID of the judoka.
     *
     * @return array An array of competition records, or an empty array if none are found.
     */
    public function get_by_judoka($judoka_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE judoka_id = %d ORDER BY date_competition DESC",
                $judoka_id
            )
        );
    }

    /**
     * Update a competition record for a judoka in the database.
     *
     * @param int $id The ID of the competition record to update.
     * @param array $data An associative array containing updated competition data.
     *                    - 'competition_name' (string) The name of the competition.
     *                    - 'date_competition' (string) The date of the competition.
     *                    - 'points'           (int)    The number of points earned.
     *                    - 'rang'             (int)    The rank achieved in the competition.
     *                    - 'medals'           (string) The type of medal earned (e.g., Gold, Silver, Bronze).
     *
     * @return int|false The number of rows updated, or false on error.
     */
    public function update($id, $data) {
        return $this->wpdb->update(
            $this->table_name,
            array(
                'competition_name' => sanitize_text_field($data['competition_name']),
                'date_competition' => sanitize_text_field($data['date_competition']),
                'points' => intval($data['points']),
                'rang' => intval($data['rang']),
                'medals' => sanitize_text_field($data['medals'])
            ),
            array('id' => $id)
        );
    }

    /**
     * Delete a competition record from the database based on the provided ID.
     *
     * @param int $id The ID of the competition record to delete.
     *
     * @return int|false The number of rows deleted, or false on error.
     */
    public function delete($id) {
        return $this->wpdb->delete($this->table_name, array('id' => $id));
    }

    /**
     * Delete all competition records for a judoka from the database based on the provided judoka ID.
     *
     * @param int $judoka_id The ID of the judoka.
     *
     * @return int|false The number of rows deleted, or false on error.
     */
    public function delete_by_judoka($judoka_id) {
        return $this->wpdb->delete($this->table_name, array('judoka_id' => $judoka_id));
    }

    /**
     * Get the total points earned by a judoka in competitions.
     *
     * @param int $judoka_id The ID of the judoka.
     *
     * @return int The total points earned.
     */
    public function get_total_points($judoka_id) {
        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT SUM(points) FROM {$this->table_name} WHERE judoka_id = %d",
                $judoka_id
            )
        );
    }

    /**
     * Get the count of each type of medal earned by a judoka.
     *
     * @param int $judoka_id The ID of the judoka.
     *
     * @return array An array of objects containing the type of medal and the count,
     *               or an empty array if no medals are found.
     */
    public function get_medals_count($judoka_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT medals, COUNT(*) as count
                FROM {$this->table_name}
                WHERE judoka_id = %d AND medals != ''
                GROUP BY medals",
                $judoka_id
            )
        );
    }
}
