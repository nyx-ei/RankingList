<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php
    if (isset($_GET['import']) && $_GET['import'] === 'success') {
        $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
        echo '<div class="notice notice-success"><p>' . sprintf(__('Import successful. %d judokas imported.', 'judoka-management'), $count) . '</p></div>';
    } elseif (isset($_GET['error'])) {
        $error_message = '';
        switch ($_GET['error']) {
            case 'no_file':
                $error_message = 'No file has been selected.';
                break;
            case 'invalid_format':
                $error_message = 'Invalid file format. Please use a CSV or Excel (.xlsx) file.';
                break;
            default:
                $error_message = 'An error occurred during import.';
        }
        echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
    }
    ?>

    <div class="import-section">
        <h2>Import data</h2>
        <form id="form-import-judoka" method="post" enctype="multipart/form-data">
            <!-- <input type="hidden" name="action" value="import_judokas"> -->
            <?php wp_nonce_field('import_judoka_nonce', 'judoka_import_nonce'); ?>
            <p>
                <label for="judoka_import_file">Select a CSV or Excel file:</label>
                <input type="file" name="judoka_import_file" id="judoka_import_file" accept=".csv,.xlsx" required>
            </p>
            <p>
                <input type="submit" class="button button-primary" value="Importer">
            </p>
        </form>
    </div>

    <div class="import-instructions">
        <h3>Import Instructions</h3>
        <p>The import file must be in CSV or Excel (.xlsx) format with the following columns:</p>
        <ol>
            <li>full_name</li>
            <li>birth_date (format YYYY-MM-DD)</li>
            <li>category</li>
            <li>weight (in kg)</li>
            <li>club</li>
            <li>grade</li>
            <li>gender</li>
        </ol>
        <p>Ensure your file respects this format for a successful import.</p>
    </div>
</div>
