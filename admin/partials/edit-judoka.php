<?php

if (!defined('ABSPATH')) {
    exit;
}

$judoka_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$judoka_model = new Judoka_Model();
$competition_model = new Competition_Model();

$judoka = $judoka_model->get($judoka_id);

if (!$judoka) {
    wp_die('Judoka not found');
}

$competitions = $competition_model->get_by_judoka($judoka_id);
?>

<div class="wrap">
    <h1>Edit Judoka</h1>
    
    <form method="post" action="" enctype="multipart/form-data" id="form-edit-judoka">
        <?php wp_nonce_field('edit_judoka_nonce', 'judoka_nonce'); ?>
        <input type="hidden" name="judoka_id" value="<?php echo $judoka_id; ?>">
        
        <table class="form-table">
            <tr>
                <th><label for="full_name">Full Name</label></th>
                <td>
                    <input type="text" id="full_name" name="full_name" 
                           class="regular-text" required
                           value="<?php echo esc_attr($judoka->full_name); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="birth_date">Birth Date</label></th>
                <td>
                    <input type="date" id="birth_date" name="birth_date" 
                           required value="<?php echo esc_attr($judoka->birth_date); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="category">Category</label></th>
                <td>
                    <input type="text" id="category" name="category" 
                           class="regular-text" required
                           value="<?php echo esc_attr($judoka->category); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="weight">Weight</label></th>
                <td>
                    <input type="number" id="weight" name="weight" 
                           class="regular-text" required
                           value="<?php echo esc_attr($judoka->weight); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="club">Club</label></th>
                <td>
                    <input type="text" id="club" name="club" 
                           class="regular-text" required
                           value="<?php echo esc_attr($judoka->club); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="grade">Grade</label></th>
                <td>
                    <input type="text" id="grade" name="grade" 
                           class="regular-text" required
                           value="<?php echo esc_attr($judoka->grade); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="gender">Gender</label></th>
                <td>
                    <input type="text" id="gender" name="gender" 
                           class="regular-text" required
                           value="<?php echo esc_attr($judoka->gender); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="photo_profile">Photo Profile</label></th>
                <td>
                    <input type="file" id="photo_profile" name="photo_profile" 
                           class="regular-text" required
                           value="<?php echo esc_attr($judoka->photo_profile); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="images">Images</label></th>
                <td>
                    <input type="file" id="images" name="images" 
                           class="regular-text" required
                           value="<?php echo esc_attr($judoka->images); ?>">
                </td>
            </tr>
                
        </table>

        <h3>Competitions</h3>
        <div id="competitions-container">
            <?php foreach ($competitions as $index => $competition): ?>
                <table class="form-table competition-entry">
                    <tr>
                        <th colspan="2">
                            <h4>Competition <?php echo $index + 1; ?></h4>
                            <input type="hidden" name="competitions[<?php echo $index; ?>][id]" 
                                   value="<?php echo $competition->id; ?>">
                            <button type="button" class="button remove-competition">
                                Delete this competition
                            </button>
                        </th>
                    </tr>
                    <tr>
                        <th><label for="competition_name">Competition Name</label></th>
                        <td><input type="text" name="competitions[<?php echo $index; ?>][competition_name]" class="regular-text" value="<?php echo esc_attr($competition->competition_name); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="date_competition">Competition Date</label></th>
                        <td><input type="date" name="competitions[<?php echo $index; ?>][date_competition]" value="<?php echo esc_attr($competition->date_competition); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="points">Points gagnés</label></th>
                        <td><input type="number" name="competitions[<?php echo $index; ?>][points]" min="0" value="<?php echo esc_attr($competition->points); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="rang">Rang</label></th>
                        <td><input type="number" name="competitions[<?php echo $index; ?>][rang]" min="1" value="<?php echo esc_attr($competition->rang); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="medals">Medals</label></th>
                        <td>
                            <select name="competitions[<?php echo $index; ?>][medals]">
                                <option value="" <?php selected($competition->medals, ''); ?>>None</option>
                                <option value="Gold" <?php selected($competition->medals, 'Gold'); ?>>Or</option>
                                <option value="Silver" <?php selected($competition->medals, 'Silver'); ?>>Argent</option>
                                <option value="Bronze" <?php selected($competition->medals, 'Bronze'); ?>>Bronze</option>
                            </select>
                        </td>
                    </tr>
                
                </table>
            <?php endforeach; ?>
        </div>

        <button type="button" id="add-competition" class="button">
            Add another competition
        </button>

        <?php submit_button('Update the Judoka'); ?>
    </form>
</div>
