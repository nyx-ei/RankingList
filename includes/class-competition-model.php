<?php
class Competition_Model {
    private $wpdb;
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'competitions_judoka';
    }

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

    public function get_by_judoka($judoka_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE judoka_id = %d ORDER BY date_competition DESC",
                $judoka_id
            )
        );
    }

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

    public function delete($id) {
        return $this->wpdb->delete($this->table_name, array('id' => $id));
    }

    public function delete_by_judoka($judoka_id) {
        return $this->wpdb->delete($this->table_name, array('judoka_id' => $judoka_id));
    }

    public function get_total_points($judoka_id) {
        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT SUM(points) FROM {$this->table_name} WHERE judoka_id = %d",
                $judoka_id
            )
        );
    }

    public function get_medals_count($judoka_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT medaille, COUNT(*) as count 
                FROM {$this->table_name} 
                WHERE judoka_id = %d AND medals != ''
                GROUP BY medals",
                $judoka_id
            )
        );
    }
}