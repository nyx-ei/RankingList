<?php

if (!defined('ABSPATH')) {
    exit;
}

$judoka_model = new Judoka_Model();
$competition_model = new Competition_Model();

$per_page = 10; 
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

$filters = [];
if (!empty($_GET['category'])) {
    $filters['category'] = sanitize_text_field($_GET['category']);
}
if (!empty($_GET['club'])) {
    $filters['club'] = sanitize_text_field($_GET['club']);
}

$result = $judoka_model->get_judokas_paginated($current_page, $per_page, $filters);
$judokas = $result['items'];
$total_judokas = $result['total'];
$total_pages = ceil($total_judokas / $per_page);

?>

<div class="wrap">
    <h1 class="wp-heading-inline">List of Judokas</h1> &nbsp;
    <a href="?page=add-judoka" class="page-title-action">Add a new Judoka</a> &nbsp; &nbsp;
    <a href="?page=import-judokas" class="page-title-action">Import data</a>

    <?php if (!empty($_GET['message'])): ?>
        <div class="notice notice-success">
            <p><?php echo esc_html($_GET['message']); ?></p>
        </div>
    <?php endif; ?>

    <div class="tablenav top">
        <div class="alignleft actions">
            <select id="filter-category">
                <option value="">All categories</option>
                <?php
                $categories = $judoka_model->get_distinct_categories();
                foreach ($categories as $category) {
                    $selected = isset($_GET['category']) && $_GET['category'] === $category ? ' selected' : '';
                    echo sprintf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($category),
                        $selected,
                        esc_html($category)
                    );
                }
                ?>
            </select>
            <select id="filter-club">
                <option value="">All clubs</option>
                <?php
                $clubs = $judoka_model->get_distinct_clubs();
                foreach ($clubs as $club) {
                    $selected = isset($_GET['club']) && $_GET['club'] === $club ? ' selected' : '';
                    echo sprintf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($club),
                        $selected,
                        esc_html($club)
                    );
                }
                ?>
            </select>
            <button class="button" id="filter-submit">Filtrer</button>
            <?php if (!empty($filters)): ?>
                <a href="?page=list-judokas" class="button clear-filters">Effacer les filtres</a>
            <?php endif; ?>
        </div>

        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo $total_judokas; ?> items</span>
            <?php if ($total_pages > 1): ?>
                <span class="pagination-links">
                    <?php
                    $disable_first = $current_page == 1 ? 'disabled' : '';
                    $disable_prev = $current_page == 1 ? 'disabled' : '';
                    $disable_next = $current_page == $total_pages ? 'disabled' : '';
                    $disable_last = $current_page == $total_pages ? 'disabled' : '';

                    // Préserver les filtres dans les liens de pagination
                    $pagination_args = ['paged' => 1];
                    if (!empty($filters['category'])) {
                        $pagination_args['category'] = $filters['category'];
                    }
                    if (!empty($filters['club'])) {
                        $pagination_args['club'] = $filters['club'];
                    }

                    $first_page_url = add_query_arg($pagination_args);

                    $pagination_args['paged'] = max(1, $current_page - 1);
                    $prev_page_url = add_query_arg($pagination_args);

                    $pagination_args['paged'] = min($total_pages, $current_page + 1);
                    $next_page_url = add_query_arg($pagination_args);

                    $pagination_args['paged'] = $total_pages;
                    $last_page_url = add_query_arg($pagination_args);
                    ?>

                    <a class="first-page button <?php echo $disable_first; ?>" href="<?php echo esc_url($first_page_url); ?>">
                        <span class="screen-reader-text">First page</span>
                        <span aria-hidden="true">«</span>
                    </a>
                    <a class="prev-page button <?php echo $disable_prev; ?>" href="<?php echo esc_url($prev_page_url); ?>">
                        <span class="screen-reader-text">Previous page</span>
                        <span aria-hidden="true">‹</span>
                    </a>

                    <span class="paging-input">
                        <span class="current-page"><?php echo $current_page; ?></span>
                        <span class="tablenav-paging-text"> of
                            <span class="total-pages"><?php echo $total_pages; ?></span>
                        </span>
                    </span>

                    <a class="next-page button <?php echo $disable_next; ?>" href="<?php echo esc_url($next_page_url); ?>">
                        <span class="screen-reader-text">Next page</span>
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="last-page button <?php echo $disable_last; ?>" href="<?php echo esc_url($last_page_url); ?>">
                        <span class="screen-reader-text">Last page</span>
                        <span aria-hidden="true">»</span>
                    </a>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Full Name</th>
                <th>Age</th>
                <th>Category</th>
                <th>Club</th>
                <th>Grade</th>
                <th>Total Points</th>
                <th>Medals</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($judokas as $judoka):
                $age = date_diff(date_create($judoka->birth_date), date_create('today'))->y;
                $points = $competition_model->get_total_points($judoka->id);
                $medals = $competition_model->get_medals_count($judoka->id);
            ?>
                <tr>
                    <td>
                        <?php if (!empty($judoka->photo_profile)): ?>
                            <img src="<?php echo esc_url($judoka->photo_profile); ?>"
                                alt="Photo de <?php echo esc_attr($judoka->full_name); ?>"
                                style="width: 50px; height: 50px; object-fit: cover;">
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($judoka->full_name); ?></td>
                    <td><?php echo esc_html($age); ?> ans</td>
                    <td><?php echo esc_html($judoka->category); ?></td>
                    <td><?php echo esc_html($judoka->club); ?></td>
                    <td><?php echo esc_html($judoka->grade); ?></td>
                    <td><?php echo esc_html($points); ?></td>
                    <td>
                        <?php foreach ($medals as $medal): ?>
                            <span class="medal-count <?php echo strtolower($medal->medals); ?>">
                                <?php echo esc_html($medal->medals); ?>: <?php echo esc_html($medal->count); ?>
                            </span>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <a href="?page=edit-judoka&id=<?php echo $judoka->id; ?>"
                            class="button button-small">Edit</a>
                        <button class="button button-small delete-judoka"
                            data-id="<?php echo $judoka->id; ?>"
                            data-name="<?php echo esc_attr($judoka->full_name); ?>">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo $total_judokas; ?> items</span>
            <?php if ($total_pages > 1): ?>
                <span class="pagination-links">
                    <?php
                    $disable_first = $current_page == 1 ? 'disabled' : '';
                    $disable_prev = $current_page == 1 ? 'disabled' : '';
                    $disable_next = $current_page == $total_pages ? 'disabled' : '';
                    $disable_last = $current_page == $total_pages ? 'disabled' : '';

                    $pagination_args = ['paged' => 1];
                    if (!empty($filters['category'])) {
                        $pagination_args['category'] = $filters['category'];
                    }
                    if (!empty($filters['club'])) {
                        $pagination_args['club'] = $filters['club'];
                    }

                    $first_page_url = add_query_arg($pagination_args);

                    $pagination_args['paged'] = max(1, $current_page - 1);
                    $prev_page_url = add_query_arg($pagination_args);

                    $pagination_args['paged'] = min($total_pages, $current_page + 1);
                    $next_page_url = add_query_arg($pagination_args);

                    $pagination_args['paged'] = $total_pages;
                    $last_page_url = add_query_arg($pagination_args);
                    ?>

                    <a class="first-page button <?php echo $disable_first; ?>" href="<?php echo esc_url($first_page_url); ?>">
                        <span class="screen-reader-text">First page</span>
                        <span aria-hidden="true">«</span>
                    </a>
                    <a class="prev-page button <?php echo $disable_prev; ?>" href="<?php echo esc_url($prev_page_url); ?>">
                        <span class="screen-reader-text">Previous page</span>
                        <span aria-hidden="true">‹</span>
                    </a>

                    <span class="paging-input">
                        <span class="current-page"><?php echo $current_page; ?></span>
                        <span class="tablenav-paging-text"> of
                            <span class="total-pages"><?php echo $total_pages; ?></span>
                        </span>
                    </span>

                    <a class="next-page button <?php echo $disable_next; ?>" href="<?php echo esc_url($next_page_url); ?>">
                        <span class="screen-reader-text">Next page</span>
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="last-page button <?php echo $disable_last; ?>" href="<?php echo esc_url($last_page_url); ?>">
                        <span class="screen-reader-text">Last page</span>
                        <span aria-hidden="true">»</span>
                    </a>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $('#filter-submit').on('click', function() {
            const category = $('#filter-category').val();
            const club = $('#filter-club').val();
            
            let url = window.location.href;

            let baseUrl = '';
            const match = url.match(/([^\?]*\?page=list-judokas)/);
            if (match) {
                baseUrl = match[1];
            } else {
                baseUrl = '?page=list-judokas';
            }

            let newUrl = baseUrl;

            if (category) {
                newUrl += '&category=' + encodeURIComponent(category);
            }
            if (club) {
                newUrl += '&club=' + encodeURIComponent(club);
            }

            window.location.href = newUrl;
        });

        $('.clear-filters').on('click', function(e) {
            e.preventDefault();
            window.location.href = '?page=list-judokas';
        });
    });
</script>