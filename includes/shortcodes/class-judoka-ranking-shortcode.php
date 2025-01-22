<?php

declare(strict_types=1);

class Judoka_Ranking_Shortcode {

    /**
     * Summary of judoka_model
     * @var judoka_model
     */
    private $judoka_model;

    /**
     * Summary of competition_model
     * @var competition_model
     */
    private $competition_model;

    /**
     * 
     * @var string
     */
    private $default_picture;

    /**
     * 
     * @var string
     */
    private $default_flag;

    public function __construct()
    {
        $this->judoka_model = new Judoka_Model();
        $this->competition_model = new Competition_Model();
        $this->default_picture = JUDOKA_PLUGIN_URL . 'assets/images/default_judoka.png';
        $this->default_flag = JUDOKA_PLUGIN_URL . 'assets/images/default_flag.png';

        add_shortcode('judoka_ranking', array($this, 'render_ranking'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * enqueue_assets
     *
     * @return void
     */
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
    
    /**
     * render_ranking
     *
     * @param  mixed $atts
     * @return void
     */
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
            <!-- Filters section -->
            <?php $this->render_filters($atts); ?>

            <!-- Ranking table section -->
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
    
    /**
     * render_filters
     *
     * @param  mixed $atts
     * @return void
     */
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
            </select>

            <input type="text" id="search-name" placeholder="Search by name">
        </div>
        <?php
    }
    
    /**
     * render_table_header
     *
     * @return void
     */
    private function render_table_header(): void {
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
    
    /**
     * render_table_body
     *
     * @param  mixed $atts
     * @return void
     */
    private function render_table_body($atts) {
        $judokas = $this->get_ranked_judokas($atts);
        
        echo '<div class="ranking-body">';
        foreach ($judokas as $rank => $judoka) {
            $this->render_judoka_row($judoka, $rank + 1);
        }
        echo '</div>';
    }
    
    /**
     * render_judoka_row
     *
     * @param  mixed $judoka
     * @param  mixed $rank
     * @return void
     */
    private function render_judoka_row($judoka, $rank) {
        $photo_url = !empty($judoka->photo_profile) ? $judoka->photo_profile : $this->default_photo;
        $previous_rank = $this->get_previous_rank($judoka->id);
        $rank_change = $previous_rank ? $previous_rank - $rank : 0;
        ?>
        <div class="ranking-row">
            <div class="col-place">#<?php echo $rank; ?></div>
            <div class="col-change">
                <?php $this->render_rank_change($rank_change); ?>
            </div>
            <div class="col-competitor">
                <img src="<?php echo esc_url($photo_url); ?>" 
                     alt="<?php echo esc_attr($judoka->name); ?>" 
                     class="judoka-photo">
                <span class="judoka-name"><?php echo esc_html($judoka->name); ?></span>
            </div>
            <div class="col-nation">
                <img src="<?php echo esc_url($this->default_flag); ?>" 
                     alt="Cameroon" 
                     class="nation-flag">
                <span>Cameroon</span>
            </div>
            <div class="col-points">
                <?php echo number_format($judoka->total_points); ?> points
            </div>
        </div>
        <?php
    }
    
    /**
     * render_rank_change
     *
     * @param  mixed $change
     * @return void
     */
    private function render_rank_change($change) {
        if ($change > 0) {
            echo '<span class="rank-up">↑' . $change . '</span>';
        } elseif ($change < 0) {
            echo '<span class="rank-down">↓' . abs($change) . '</span>';
        } else {
            echo '<span class="rank-same">--</span>';
        }
    }
    
    /**
     * get_ranked_judokas
     *
     * @param  mixed $filters
     * @return void
     */
    private function get_ranked_judokas($filters) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT j.*, j.photo_url as photo_profile, 
                    SUM(c.points) as total_points
             FROM {$wpdb->prefix}judokas j
             LEFT JOIN {$wpdb->prefix}competitions_judoka c ON j.id = c.judoka_id
             WHERE j.category = %s
             AND (j.gender = %s OR %s = 'all')
             AND (j.weight_class = %s OR %s = 'all')
             GROUP BY j.id
             ORDER BY total_points DESC",
            $filters['category'],
            $filters['gender'],
            $filters['gender'],
            $filters['weight'],
            $filters['weight']
        );

        return $wpdb->get_results($query);
    }

    private function get_previous_rank($judoka_id) {
        return null;
    }
}

new Judoka_Ranking_Shortcode();