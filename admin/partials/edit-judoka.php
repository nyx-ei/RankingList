<?php
if (!defined('ABSPATH')) { exit; }

require_once JUDOKA_PLUGIN_DIR . 'admin/partials/judoka-form.php';

$judoka_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$judoka_model = new Judoka_Model();
$competition_model = new Competition_Model();

$judoka = $judoka_model->get_judoka($judoka_id);
if (!$judoka) {
    wp_die('Judoka not found');
}

$competitions = $competition_model->get_by_judoka($judoka_id);

render_judoka_form($judoka, $competitions);
