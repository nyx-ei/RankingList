<?php
if (!defined('ABSPATH')) exit;

$judoka_model = new Judoka_Model();
$categories = $judoka_model->get_distinct_categories();
$clubs = $judoka_model->get_distinct_clubs();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="notice notice-info is-dismissible hidden" id="report-notice">
        <p></p>
    </div>

    <div class="card card-form">
        <h2>Generate Report</h2>
        <form id="generate-report-form" class="report-form">
            <?php wp_nonce_field('generate_report_nonce', 'report_nonce'); ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="period_start">Period Start:</label>
                    <input type="date" id="period_start" name="period_start" class="regular-text">
                </div>
                <div class="form-group">
                    <label for="period_end">Period End:</label>
                    <input type="date" id="period_end" name="period_end" class="regular-text">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="club">Club:</label>
                    <select id="club" name="club" class="regular-text">
                        <option value="">All Clubs</option>
                        <?php foreach ($clubs as $club): ?>
                            <option value="<?php echo esc_attr($club); ?>">
                                <?php echo esc_html($club); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" class="regular-text">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category); ?>">
                                <?php echo esc_html($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="weight_min">Minimum Weight:</label>
                    <input type="number" id="weight_min" name="weight_min" step="0.1" class="regular-text">
                </div>
                <div class="form-group">
                    <label for="weight_max">Maximum Weight:</label>
                    <input type="number" id="weight_max" name="weight_max" step="0.1" class="regular-text">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sort_by">Sort By:</label>
                    <select id="sort_by" name="sort_by" class="regular-text">
                        <option value="full_name">Name</option>
                        <option value="club">Club</option>
                        <option value="category">Category</option>
                        <option value="weight">Weight</option>
                        <option value="total_points">Total Points</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sort_order">Sort Order:</label>
                    <select id="sort_order" name="sort_order" class="regular-text">
                        <option value="ASC">Ascending</option>
                        <option value="DESC">Descending</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="format">Export Format:</label>
                    <select id="format" name="format" class="regular-text">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="button button-primary" id="generate-report">
                    <span class="dashicons dashicons-media-document"></span> Generate Report
                </button>
                <button type="button" class="button" id="print-report">
                    <span class="dashicons dashicons-printer"></span> Print
                </button>
                <button type="button" class="button" id="share-report">
                    <span class="dashicons dashicons-share"></span> Share
                </button>
            </div>
        </form>
    </div>

    <div id="report-preview" class="card hidden">
        <h2>Report Preview</h2>
        <div id="report-content"></div>
    </div>

    <!-- Share Modal -->
    <div id="share-modal" class="modal hidden">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Share Report</h2>
            <form id="share-report-form">
                <?php wp_nonce_field('share_report_nonce', 'share_nonce'); ?>
                <div class="form-group">
                    <label for="share_email">Email:</label>
                    <input type="email" id="share_email" name="share_email" required class="regular-text">
                </div>
                <button type="submit" class="button button-primary">Send Report</button>
            </form>
        </div>
    </div>
</div>