<?php

declare(strict_types=1);

class Judoka_Performance_Shortcode
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

        add_shortcode('judoka_performance', array($this, 'render_performance'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_get_judoka_details', array($this, 'ajax_get_judoka_details'));
        add_action('wp_ajax_nopriv_get_judoka_details', array($this, 'ajax_get_judoka_details'));
    }

    public function enqueue_assets()
    {
        wp_enqueue_style(
            'judoka-performance-style',
            JUDOKA_PLUGIN_URL . 'assets/css/judoka-performance.css',
            array(),
            JUDOKA_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'judoka-performance-script',
            JUDOKA_PLUGIN_URL . 'assets/js/judoka-performance.js',
            array('jquery'),
            JUDOKA_PLUGIN_VERSION,
            true
        );

        wp_localize_script('judoka-performance-script', 'judokaPerformanceAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('judoka_performance_nonce')
        ));
    }

    public function render_performance($atts)
    {
        $atts = shortcode_atts(array(
            'judoka_id' => 0,
            'per_page' => 8,
        ), $atts);

        $atts = array_map('sanitize_text_field', $atts);

        $judoka_id = intval($atts['judoka_id']);
        $per_page = intval($atts['per_page']);

        if ($judoka_id === 0) {
            return $this->render_judokas_list($per_page);
        }

        return $this->render_judoka_details($judoka_id);
    }

    private function render_judokas_list($per_page = 12)
    {
        $current_page = isset($_GET['judo_page']) ? max(1, intval($_GET['judo_page'])) : 1;
        
        $filters = [];
        if (!empty($_GET['category']) && $_GET['category'] !== 'all') {
            $filters['category'] = sanitize_text_field($_GET['category']);
        }
        if (!empty($_GET['club']) && $_GET['club'] !== 'all') {
            $filters['club'] = sanitize_text_field($_GET['club']);
        }
        if (!empty($_GET['gender']) && $_GET['gender'] !== 'all') {
            $filters['gender'] = sanitize_text_field($_GET['gender']);
        }
        
        $result = $this->judoka_model->get_judokas_paginated($current_page, $per_page, $filters);
        $judokas = $result['items'];
        $total_judokas = $result['total'];
        $total_pages = ceil($total_judokas / $per_page);

        $current_page_url = get_permalink();

        ob_start();
        ?>
        <div class="judoka-list-container">
            <h2>Judoka List</h2>

            <div class="judoka-filters">
                <select id="category-filter">
                    <option value="all">All categories</option>
                    <?php
                    $categories = $this->judoka_model->get_distinct_categories();
                    foreach ($categories as $category) {
                        $selected = (isset($_GET['category']) && $_GET['category'] === $category) ? ' selected' : '';
                        echo '<option value="' . esc_attr($category) . '"' . $selected . '>' . esc_html($category) . '</option>';
                    }
                    ?>
                </select>

                <select id="club-filter">
                    <option value="all">All clubs</option>
                    <?php
                    $clubs = $this->judoka_model->get_distinct_clubs();
                    foreach ($clubs as $club) {
                        $selected = (isset($_GET['club']) && $_GET['club'] === $club) ? ' selected' : '';
                        echo '<option value="' . esc_attr($club) . '"' . $selected . '>' . esc_html($club) . '</option>';
                    }
                    ?>
                </select>

                <div class="gender-filter">
                    <button class="gender-btn <?php echo (!isset($_GET['gender']) || $_GET['gender'] === 'all') ? 'active' : ''; ?>" data-gender="all">All</button>
                    <button class="gender-btn <?php echo (isset($_GET['gender']) && $_GET['gender'] === 'M') ? 'active' : ''; ?>" data-gender="M">Men</button>
                    <button class="gender-btn <?php echo (isset($_GET['gender']) && $_GET['gender'] === 'F') ? 'active' : ''; ?>" data-gender="F">Women</button>
                </div>

                <input type="text" id="search-judoka" placeholder="Search a judoka">
                <button id="apply-filters" class="filter-button">Apply Filters</button>
                <?php if (!empty($filters)): ?>
                    <a href="<?php echo esc_url($current_page_url); ?>" class="clear-filters-button">Clear Filters</a>
                <?php endif; ?>
            </div>

            <div class="judoka-grid">
                <?php
                if (empty($judokas)) {
                    echo '<p>No judokas found.</p>';
                } else {
                    foreach ($judokas as $judoka) {
                        $photo_url = !empty($judoka->photo_profile) ? $judoka->photo_profile : $this->default_picture;
                        $total_points = $this->competition_model->get_total_points($judoka->id) ?: 0;

                        $profile_url = add_query_arg('judoka_id', $judoka->id, $current_page_url);
                        ?>
                        <a href="<?php echo esc_url($profile_url); ?>" class="judoka-card-link">
                            <div class="judoka-card"
                                data-id="<?php echo esc_attr($judoka->id); ?>"
                                data-category="<?php echo esc_attr($judoka->category); ?>"
                                data-gender="<?php echo esc_attr($judoka->gender); ?>"
                                data-club="<?php echo esc_attr($judoka->club); ?>">
                                <div class="judoka-card-photo">
                                    <img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($judoka->full_name); ?>">
                                </div>
                                <div class="judoka-card-info">
                                    <h3><?php echo esc_html($judoka->full_name); ?></h3>
                                    <p>
                                        <span class="weight-badge"><?php echo esc_html($judoka->weight); ?></span>
                                        <span class="category-badge"><?php echo esc_html($judoka->category); ?></span>
                                    </p>
                                    <p>Club: <?php echo esc_html($judoka->club); ?></p>
                                    <p>Points: <?php echo number_format((float)$total_points); ?></p>
                                    <span class="view-details-btn">
                                        View details
                                    </span>
                                </div>
                            </div>
                        </a>
                        <?php
                    }
                }
                ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="judoka-pagination">
                <div class="pagination-container">
                    <?php
                    $disable_first = $current_page == 1 ? 'disabled' : '';
                    $disable_prev = $current_page == 1 ? 'disabled' : '';
                    $disable_next = $current_page == $total_pages ? 'disabled' : '';
                    $disable_last = $current_page == $total_pages ? 'disabled' : '';

                    $pagination_args = ['judo_page' => 1];
                    if (!empty($filters['category'])) {
                        $pagination_args['category'] = $filters['category'];
                    }
                    if (!empty($filters['club'])) {
                        $pagination_args['club'] = $filters['club'];
                    }
                    if (!empty($filters['gender'])) {
                        $pagination_args['gender'] = $filters['gender'];
                    }
                    
                    $first_page_url = add_query_arg($pagination_args, $current_page_url);
                    
                    $pagination_args['judo_page'] = max(1, $current_page - 1);
                    $prev_page_url = add_query_arg($pagination_args, $current_page_url);
                    
                    $pagination_args['judo_page'] = min($total_pages, $current_page + 1);
                    $next_page_url = add_query_arg($pagination_args, $current_page_url);
                    
                    $pagination_args['judo_page'] = $total_pages;
                    $last_page_url = add_query_arg($pagination_args, $current_page_url);
                    ?>
                    
                    <a class="pagination-link <?php echo $disable_first; ?>" href="<?php echo esc_url($first_page_url); ?>">
                        <span class="screen-reader-text">First page</span>
                        <span aria-hidden="true">«</span>
                    </a>
                    <a class="pagination-link <?php echo $disable_prev; ?>" href="<?php echo esc_url($prev_page_url); ?>">
                        <span class="screen-reader-text">Previous page</span>
                        <span aria-hidden="true">‹</span>
                    </a>
                    
                    <span class="pagination-current">
                        <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                    </span>
                    
                    <a class="pagination-link <?php echo $disable_next; ?>" href="<?php echo esc_url($next_page_url); ?>">
                        <span class="screen-reader-text">Next page</span>
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="pagination-link <?php echo $disable_last; ?>" href="<?php echo esc_url($last_page_url); ?>">
                        <span class="screen-reader-text">Last page</span>
                        <span aria-hidden="true">»</span>
                    </a>
                </div>
                <div class="pagination-info">
                    <span class="total-items">
                        <?php echo $total_judokas; ?> judokas found
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#apply-filters').on('click', function() {
                applyFilters();
            });

            function applyFilters() {
                const category = $('#category-filter').val();
                const club = $('#club-filter').val();
                const gender = $('.gender-btn.active').data('gender');
                const search = $('#search-judoka').val();

                let url = '<?php echo esc_js($current_page_url); ?>';
                let params = [];
                
                if (category && category !== 'all') {
                    params.push('category=' + encodeURIComponent(category));
                }
                
                if (club && club !== 'all') {
                    params.push('club=' + encodeURIComponent(club));
                }
                
                if (gender && gender !== 'all') {
                    params.push('gender=' + encodeURIComponent(gender));
                }
                
                if (search) {
                    params.push('search=' + encodeURIComponent(search));
                }
                
                if (params.length > 0) {
                    url += (url.indexOf('?') !== -1 ? '&' : '?') + params.join('&');
                }

                window.location.href = url;
            }
            
            $('.gender-btn').on('click', function() {
                $('.gender-btn').removeClass('active');
                $(this).addClass('active');
            });
            
            $('#search-judoka').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    applyFilters();
                }
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function render_judoka_details($judoka_id)
    {
        $judoka = $this->judoka_model->get_judoka($judoka_id);
        
        if (!$judoka) {
            return '<p>Judoka not found.</p>';
        }

        $competitions = $this->competition_model->get_by_judoka($judoka_id);
        $totalPoints = $this->competition_model->get_total_points($judoka_id) ?: 0;
        $medalsCount = $this->competition_model->get_medals_count($judoka_id);

        $medalStats = array(
            'Gold' => 0,
            'Silver' => 0,
            'Bronze' => 0
        );

        foreach ($medalsCount as $medal) {
            if (isset($medalStats[$medal->medals])) {
                $medalStats[$medal->medals] = $medal->count;
            }
        }

        $birthdate = new DateTime($judoka->birth_date);
        $today = new DateTime();
        $age = $birthdate->diff($today)->y;

        $photo_url = !empty($judoka->photo_profile) ? $judoka->photo_profile : $this->default_picture;
        
        ob_start();
        ?>
        <div class="judoka-profile-header" style="background-color: #3a3f78; background-image: linear-gradient(to right, #3a3f78, #2d305e);">
            <div class="profile-info">
                <div class="profile-photo">
                    <img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($judoka->full_name); ?>">
                </div>
                <div class="profile-details">
                    <h1 class="judoka-name"><?php echo esc_html(strtoupper($judoka->full_name)); ?></h1>
                    <div class="country-info">
                        <img src="<?php echo esc_url($this->default_flag); ?>" alt="Drapeau" class="flag-icon">
                        <span><?php echo esc_html($judoka->club); ?></span>
                    </div>
                    <p class="age-info">Age: <?php echo esc_html($age); ?> ans</p>
                </div>
                <div class="weight-display">
                    -<?php echo esc_html($judoka->weight); ?>
                    <span class="weight-unit">kg</span>
                </div>
            </div>
            
            <div class="profile-tabs">
                <a href="#overview" class="tab active">Overview</a>
                <a href="#photos" class="tab">Photos</a>
                <a href="#contests" class="tab">Compétitions</a>
                <a href="#videos" class="tab">Vidéos</a>
                <a href="#results" class="tab">Résultats</a>
                <a href="#wrl" class="tab">WRL</a>
            </div>
        </div>
        
        <div class="profile-content">
            <div id="overview" class="tab-content active">
                <div class="section-title">
                    <h2>Under the spotlight</h2>
                </div>
                
                <div class="spotlight-gallery">
                    <?php
                    $images = !empty($judoka->images) ? json_decode($judoka->images, true) : [];
                    if (!empty($images)) {
                        foreach ($images as $image) {
                            echo '<div class="gallery-item"><img src="' . esc_url($image) . '" alt="Photo de ' . esc_attr($judoka->full_name) . '"></div>';
                        }
                    } else {
                    
                        echo '<div class="gallery-item"><img src="' . esc_url($photo_url) . '" alt="Photo de ' . esc_attr($judoka->full_name) . '"></div>';
                    }
                    ?>
                </div>
                
                <div class="performance-stats">
                    <div class="stat-container">
                        <div class="stat-box">
                            <div class="stat-value"><?php echo count($competitions); ?></div>
                            <div class="stat-label">Competitions</div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="stat-value"><?php echo $medalStats['Gold']; ?></div>
                            <div class="stat-label">Gold</div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="stat-value"><?php echo $medalStats['Silver']; ?></div>
                            <div class="stat-label">Silver</div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="stat-value"><?php echo $medalStats['Bronze']; ?></div>
                            <div class="stat-label">Bronze</div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="stat-value"><?php echo number_format((float)$totalPoints); ?></div>
                            <div class="stat-label">Points</div>
                        </div>
                    </div>
                </div>
                
                <div class="recent-competitions">
                    <h3>Recent Competitions</h3>
                    <table class="competitions-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Competition</th>
                                <th>Rank</th>
                                <th>Medals</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($competitions)) {
                                echo '<tr><td colspan="5">Aucune compétition enregistrée.</td></tr>';
                            } else {
                                foreach (array_slice($competitions, 0, 5) as $comp) {
                                    $medal_class = strtolower($comp->medals);
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html(date('d/m/Y', strtotime($comp->date_competition))); ?></td>
                                        <td><?php echo esc_html($comp->competition_name); ?></td>
                                        <td>#<?php echo esc_html($comp->rang); ?></td>
                                        <td><span class="medal <?php echo esc_attr($medal_class); ?>"><?php echo esc_html($comp->medals); ?></span></td>
                                        <td><?php echo esc_html($comp->points); ?> pts</td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div id="contests" class="tab-content">
                <h2>Competition history</h2>
                <div class="competitions-filters">
                    <select id="competition-year-filter">
                        <option value="all">All years</option>
                        <?php
                        $years = [];
                        foreach ($competitions as $comp) {
                            $year = date('Y', strtotime($comp->date_competition));
                            if (!in_array($year, $years)) {
                                $years[] = $year;
                                echo '<option value="' . esc_attr($year) . '">' . esc_html($year) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    
                    <select id="medal-filter">
                        <option value="all">All medals</option>
                        <option value="Gold">Gold</option>
                        <option value="Silver">Silver</option>
                        <option value="Bronze">Bronze</option>
                        <option value="none">No medal</option>
                    </select>
                </div>
                
                <table class="competitions-table full-width">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Competition</th>
                            <th>Rank</th>
                            <th>Medals</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($competitions)) {
                            echo '<tr><td colspan="5">No competition recorded.</td></tr>';
                        } else {
                            foreach ($competitions as $comp) {
                                $medal_class = strtolower($comp->medals);
                                $year = date('Y', strtotime($comp->date_competition));
                                ?>
                                <tr data-year="<?php echo esc_attr($year); ?>" data-medal="<?php echo esc_attr($comp->medals); ?>">
                                    <td><?php echo esc_html(date('d/m/Y', strtotime($comp->date_competition))); ?></td>
                                    <td><?php echo esc_html($comp->competition_name); ?></td>
                                    <td>#<?php echo esc_html($comp->rang); ?></td>
                                    <td><span class="medal <?php echo esc_attr($medal_class); ?>"><?php echo esc_html($comp->medals); ?></span></td>
                                    <td><?php echo esc_html($comp->points); ?> pts</td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div id="photos" class="tab-content">
                <h2>Photos gallery</h2>
                <div class="photo-gallery">
                    <?php
                    $images = !empty($judoka->images) ? json_decode($judoka->images, true) : [];
                    if (!empty($images)) {
                        foreach ($images as $image) {
                            echo '<div class="gallery-item large"><img src="' . esc_url($image) . '" alt="Photo de ' . esc_attr($judoka->full_name) . '"></div>';
                        }
                    } else {
                        echo '<p>No photos available.</p>';
                    }
                    ?>
                </div>
            </div>
            
            <div id="videos" class="tab-content">
                <h2>Vidéos</h2>
                <p>No videos available.</p>
            </div>
            
            <div id="results" class="tab-content">
                <h2>Detailed results</h2>
                <p>Detailed result data coming soon.</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function ajax_get_judoka_details()
    {
        check_ajax_referer('judoka_performance_nonce', 'nonce');

        $judoka_id = isset($_POST['judoka_id']) ? intval($_POST['judoka_id']) : 0;
        
        if ($judoka_id) {
            $html = $this->render_judoka_details($judoka_id);
            wp_send_json_success(['html' => $html]);
        } else {
            wp_send_json_error(['message' => 'Invalid judoka ID.']);
        }
    }
}

new Judoka_Performance_Shortcode();