<?php

if (!defined('ABSPATH')) {
    exit;
}

$judoka_model = new Judoka_Model();
$competition_model = new Competition_Model();
$judokas = $judoka_model->get_all();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">List of Judokas</h1>
    <a href="?page=ajouter-judoka" class="page-title-action">Add a new Judoka</a>

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
                    echo sprintf(
                        '<option value="%s">%s</option>',
                        esc_attr($category),
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
                    echo sprintf(
                        '<option value="%s">%s</option>',
                        esc_attr($club),
                        esc_html($club)
                    );
                }
                ?>
            </select>
            <button class="button" id="filter-submit">Filtrer</button>
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
                                 alt="Photo de <?php echo esc_attr($judoka->nom_complet); ?>"
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
                        <a href="?page=view-judoka&id=<?php echo $judoka->id; ?>" 
                           class="button button-small">Details</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    $('#filter-submit').on('click', function() {
        const category = $('#filter-category').val();
        const club = $('#filter-club').val();
        
        $('table tbody tr').each(function() {
            const $row = $(this);
            const showCategory = !category || $row.find('td:eq(3)').text() === category;
            const showClub = !club || $row.find('td:eq(4)').text() === club;
            
            $row.toggle(showCategory && showClub);
        });
    });

    $('.delete-judoka').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        if (confirm(`Are you sure you want to delete ${name}?`)) {
            $.post(ajaxurl, {
                action: 'delete_judoka',
                id: id,
                nonce: '<?php echo wp_create_nonce('delete_judoka_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error during deletion');
                }
            });
        }
    });
});
</script>

