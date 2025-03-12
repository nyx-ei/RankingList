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
        add_action('wp_ajax_filter_judokas', array($this, 'ajax_filter_judokas'));
        add_action('wp_ajax_nopriv_filter_judokas', array($this, 'ajax_filter_judokas'));
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
            'category' => 'all',
            'gender' => 'all',
            'weight' => 'all',
            'club' => 'all'
        ), $atts);

        ob_start();
        ?>
        <div class="judoka-ranking-container">
            <div class="ranking-sidebar">
                <?php $this->render_sidebar_filters(); ?>
            </div>
            <div class="ranking-main">
                <?php $this->render_top_filters($atts); ?>
                <div class="ranking-table" data-view="simple">
                    <?php
                    $this->render_table_header();
                    $this->render_table_body($atts);
                    ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_sidebar_filters()
    {
        ?>
        <div class="weight-gender-filters">
            <div class="gender-section">
                <h3>Gender</h3>
                <div class="gender-buttons">
                    <button class="gender-btn active" data-gender="all">All</button>
                    <button class="gender-btn" data-gender="M">M</button>
                    <button class="gender-btn" data-gender="F">F</button>
                </div>
            </div>
            
            <div class="weight-section">
                <h3>Weight Categories</h3>
                <div class="weight-buttons">
                    <?php
                    $weights = [
                        'M' => ['-60', '-66', '-73', '-81', '-90', '-100', '+100'],
                        'F' => ['-48', '-52', '-57', '-63', '-70', '-78', '+78']
                    ];
                    
                    foreach ($weights as $gender => $categories) {
                        echo '<div class="weight-group" data-gender="' . esc_attr($gender) . '">';
                        foreach ($categories as $weight) {
                            echo '<button class="weight-btn" data-weight="' . esc_attr($weight) . '">' 
                                . esc_html($weight) . '</button>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_top_filters($atts)
    {
        ?>
        <div class="ranking-filters">
            <select id="category-filter">
                <option value="all" <?php selected($atts['category'], 'all'); ?>>All Categories</option>
                <option value="senior" <?php selected($atts['category'], 'senior'); ?>>Senior</option>
                <option value="junior" <?php selected($atts['category'], 'junior'); ?>>Junior</option>
            </select>

            <div class="view-toggle">
                <button class="view-btn active" data-view="simple">Simple</button>
                <button class="view-btn" data-view="expanded">Expanded</button>
            </div>

            <select id="club-filter">
                <option value="all">All Clubs</option>
                <?php
                $clubs = $this->get_all_clubs();
                foreach ($clubs as $club) {
                    echo '<option value="' . esc_attr($club) . '">' . esc_html($club) . '</option>';
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
            <div class="col-details expanded-only">Details</div>
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
        <div class="ranking-row" 
             data-category="<?php echo esc_attr($judoka->category); ?>"
             data-gender="<?php echo esc_attr($judoka->gender); ?>"
             data-weight="<?php echo esc_attr($judoka->weight); ?>"
             data-club="<?php echo esc_attr($judoka->club); ?>">
            
            <div class="col-place">#<?php echo esc_html($rank); ?></div>
            <div class="col-change"><?php $this->render_rank_change($rank_change); ?></div>
            <div class="col-competitor">
                <img src="<?php echo esc_url($photo_url); ?>" 
                     alt="<?php echo esc_attr($judoka->full_name); ?>" 
                     class="judoka-photo">
                <span class="judoka-name"><?php echo esc_html($judoka->full_name); ?></span>
            </div>
            <div class="col-nation">
                <img src="<?php echo esc_url($this->default_flag); ?>" 
                     alt="Cameroon flag" 
                     class="nation-flag">
                <span><?php echo esc_html($judoka->club); ?></span>
            </div>
            <div class="col-points">
                <?php echo number_format((float)$judoka->total_points); ?> points
            </div>
            <div class="col-details expanded-only">
                <div class="details-content">
                    <p>Weight: <?php echo esc_html($judoka->weight); ?></p>
                    <p>Category: <?php echo esc_html($judoka->category); ?></p>
                    <?php
                    $competitions = $this->competition_model->get_by_judoka($judoka->id);
                    if (!empty($competitions)) {
                        echo '<p>Recent competitions:</p><ul>';
                        foreach (array_slice($competitions, 0, 3) as $comp) {
                            echo '<li>' . esc_html($comp->competition_name) . ' - ' 
                                . esc_html($comp->points) . ' points</li>';
                        }
                        echo '</ul>';
                    }
                    ?>
                </div>
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

        $where_clauses = array('1=1');
        $where_values = array();

        if ($filters['category'] !== 'all') {
            $where_clauses[] = 'j.category = %s';
            $where_values[] = $filters['category'];
        }

        if ($filters['gender'] !== 'all') {
            $where_clauses[] = 'j.gender = %s';
            $where_values[] = $filters['gender'];
        }

        if ($filters['weight'] !== 'all') {
            $where_clauses[] = 'j.weight = %s';
            $where_values[] = $filters['weight'];
        }

        if ($filters['club'] !== 'all') {
            $where_clauses[] = 'j.club = %s';
            $where_values[] = $filters['club'];
        }

        $query = $wpdb->prepare(
            "SELECT j.*, COALESCE(SUM(c.points), 0) as total_points
             FROM {$wpdb->prefix}judokas j
             LEFT JOIN {$wpdb->prefix}competitions_judoka c ON j.id = c.judoka_id
             WHERE " . implode(' AND ', $where_clauses) . "
             GROUP BY j.id
             ORDER BY total_points DESC",
            ...$where_values
        );

        $results = $wpdb->get_results($query);

        if ($wpdb->last_error) {
            error_log('Judoka ranking query error: ' . $wpdb->last_error);
            return array();
        }

        return $results;
    }

    private function get_all_clubs()
    {
        global $wpdb;
        $query = "SELECT DISTINCT club FROM {$wpdb->prefix}judokas WHERE club != '' ORDER BY club";
        return $wpdb->get_col($query);
    }

    private function get_previous_rank($judoka_id)
    {
        global $wpdb;

        $latest_snapshot = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(snapshot_date) 
            FROM {$wpdb->prefix}rankings_history 
            WHERE snapshot_date < CURDATE()"
        ));

        if (!$latest_snapshot) {
            return null;
        }

        $query = $wpdb->get_var($wpdb->prepare(
            "SELECT rank 
            FROM {$wpdb->prefix}rankings_history 
            WHERE judoka_id = %d 
            AND snapshot_date = %s",
            $judoka_id,
            $latest_snapshot
        ));
    }

    public function store_current_rankings()
    {
        global $wpdb;

        $date = 'Y-m-d ';

        $current_date = current_time($date);

        $existing_snapshot = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
            FROM {$wpdb->prefix}rankings_history 
            WHERE snapshot_date = %s",
            $current_date
        ));

        if ($existing_snapshot > 0)
        {
            return;
        }

        $current_rankings = $this->get_ranked_judokas([
            'category' => 'all',
            'gender' => 'all',
            'weight' => 'all',
            'club' => 'all'
        ]);

        foreach ($current_rankings as $rank => $judoka) {
            $wpdb->insert(
                $wpdb->prefix . 'rankings_history',
                [
                    'judoka_id' => $judoka->id,
                    'rank' => $rank + 1,
                    'total_points' => $judoka->total_points,
                    'snapshot_date' => $current_date
                ],
                ['%d', '%d', '%d', '%s']
            );
        }
    }

    public function ajax_filter_judokas()
    {
        check_ajax_referer('judoka_ranking_nonce', 'nonce');

        $filters = array(
            'category' => sanitize_text_field($_POST['category'] ?? 'all'),
            'gender' => sanitize_text_field($_POST['gender'] ?? 'all'),
            'weight' => sanitize_text_field($_POST['weight'] ?? 'all'),
            'club' => sanitize_text_field($_POST['club'] ?? 'all')
        );

        $judokas = $this->get_ranked_judokas($filters);
        
        ob_start();
        foreach ($judokas as $rank => $judoka) {
            $this->render_judoka_row($judoka, $rank + 1);
        }
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }
}

new Judoka_Ranking_Shortcode();