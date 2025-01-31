<?php

declare(strict_types=1);

class Judoka_Ranking_Shortcode
{
    private $judoka_model;
    private $competition_model;
    private $default_picture;
    private $default_flag;

    public function __construct()
    {
        $this->judoka_model = new Judoka_Model();
        $this->competition_model = new Competition_Model();
        $this->default_picture = JUDOKA_PLUGIN_URL . 'assets/images/default-judoka.png';
        $this->default_flag = JUDOKA_PLUGIN_URL . 'assets/images/cmr-flag.png';

        add_shortcode('judoka_ranking', array($this, 'render_ranking'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets()
    {
        wp_enqueue_style(
            'judoka-ranking-style',
            JUDOKA_PLUGIN_URL . 'assets/css/judoka-shortcodes.css',
            array(),
            JUDOKA_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'judoka-ranking-script',
            JUDOKA_PLUGIN_URL . 'assets/js/judoka-shortcodes.js',
            array('jquery'),
            JUDOKA_PLUGIN_VERSION,
            true
        );

        wp_localize_script('judoka-ranking-script', 'judokaRankingAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('judoka_ranking_nonce')
        ));
    }

    public function render_ranking($atts)
    {
        $atts = shortcode_atts(array(
            'category' => 'seniors',
            'gender' => 'all',
            'weight' => 'all'
        ), $atts);

        ob_start();
?>
        <div class="judoka-ranking-container">
            <?php $this->render_filters($atts); ?>
            <div class="ranking-table">
                <?php
                $this->render_table_header();
                $this->render_table_body($atts);
                ?>
            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    public function render_filters($atts)
    {
    ?>
        <div class="ranking-filters">
            <select id="category-filter">
                <option value="seniors" <?php selected($atts['category'], 'seniors'); ?>>Seniors</option>
                <option value="juniors" <?php selected($atts['category'], 'juniors'); ?>>Juniors</option>
            </select>

            <div class="view-toggle">
                <button class="view-btn active" data-view="simple">Simple</button>
                <button class="view-btn" data-view="expanded">Expanded</button>
            </div>

            <select id="nation-filter">
                <option value="all">All</option>
                <?php
                $nations = $this->get_all_nations();
                foreach ($nations as $nation) {
                    echo '<option value="' . esc_attr($nation) . '">' . esc_html($nation) . '</option>';
                }
                ?>
            </select>

            <input type="text" id="search-name" placeholder="Search by name">
        </div>
    <?php
    }

    private function render_table_header()
    {
    ?>
        <div class="ranking-header">
            <div class="col-place">Place</div>
            <div class="col-change">Ch.</div>
            <div class="col-competitor">Competitor</div>
            <div class="col-nation">Nation</div>
            <div class="col-points">Points</div>
        </div>
    <?php
    }

    private function render_table_body($atts)
    {
        $judokas = $this->get_ranked_judokas($atts);

        if (empty($judokas)) {
            echo '<div class="no-results">No judokas found matching the criteria.</div>';
            return;
        }

        echo '<div class="ranking-body">';
        foreach ($judokas as $rank => $judoka) {
            $this->render_judoka_row($judoka, $rank + 1);
        }
        echo '</div>';
    }

    private function render_judoka_row($judoka, $rank)
    {
        $photo_url = !empty($judoka->photo_profile) ? $judoka->photo_profile : $this->default_picture;
        $previous_rank = $this->get_previous_rank($judoka->id);
        $rank_change = $previous_rank ? $previous_rank - $rank : 0;
    ?>
        <div class="ranking-row">
            <div class="col-place">#<?php echo esc_html($rank); ?></div>
            <div class="col-change">
                <?php $this->render_rank_change($rank_change); ?>
            </div>
            <div class="col-competitor">
                <img src="<?php echo esc_url($photo_url); ?>"
                    alt="<?php echo esc_attr($judoka->full_name); ?>"
                    class="judoka-photo">
                <span class="judoka-name"><?php echo esc_html($judoka->full_name); ?></span>
            </div>
            <div class="col-nation">
                <?php if (!empty($judoka->club)): ?>
                    <span><?php echo esc_html($judoka->club); ?></span>
                <?php endif; ?>
            </div>
            <div class="col-points">
                <?php echo number_format((float)$judoka->total_points); ?> points
            </div>
        </div>
<?php
    }

    private function render_rank_change($change)
    {
        if ($change > 0) {
            echo '<span class="rank-up">↑' . absint($change) . '</span>';
        } elseif ($change < 0) {
            echo '<span class="rank-down">↓' . absint($change) . '</span>';
        } else {
            echo '<span class="rank-same">--</span>';
        }
    }

    private function get_ranked_judokas($filters)
    {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT j.*, 
                    COALESCE(SUM(c.points), 0) as total_points
             FROM {$wpdb->prefix}judokas j
             LEFT JOIN {$wpdb->prefix}competitions_judoka c ON j.id = c.judoka_id
             WHERE j.category = %s
             AND (j.gender = %s OR %s = 'all')
             AND (j.weight = %s OR %s = 'all')
             GROUP BY j.id
             ORDER BY total_points DESC",
            $filters['category'],
            $filters['gender'],
            $filters['gender'],
            $filters['weight'],
            $filters['weight']
        );

        $results = $wpdb->get_results($query);

        if ($wpdb->last_error) {
            error_log('Judoka ranking query error: ' . $wpdb->last_error);
            return array();
        }

        return $results;
    }

    private function get_previous_rank($judoka_id)
    {
        //TODO : get previous rank
        return null;
    }

    private function get_all_nations()
    {
        global $wpdb;
        $query = "SELECT DISTINCT club FROM {$wpdb->prefix}judokas WHERE club != '' ORDER BY club";
        return $wpdb->get_col($query);
    }
}

new Judoka_Ranking_Shortcode();
