<?php

if (!defined('ABSPATH'))
{
    exit;
}

function judoka_full_name_submit() {
    if (isset($_POST['first_name']) && isset($_POST['last_name'])) {
        $first_name = trim(strtoupper($_POST['first_name']));
        $last_name = trim(strtoupper($_POST['last_name']));
        $_POST['full_name'] = $first_name . ' ' . $last_name;
    }
}
add_action('init', 'judoka_full_name_submit');

function render_judoka_form($judoka = null, $competitions = []) {
    $is_edit = !is_null($judoka);
    $form_id = $is_edit ? 'form-edit-judoka' : 'form-judoka';
    $nonce_action = $is_edit  ? 'edit_judoka' : 'add_judoka_nonce';
    $nonce_name = $is_edit ? 'judoka_edit_nonce' : 'judoka_nonce';

    $first_name = '';
    $last_name = '';
    if ($is_edit && isset($judoka->full_name)) {
        $name_parts = explode(' ', $judoka->full_name);
        $first_name = isset($name_parts[0]) ? $name_parts[0] : '';
        $last_name = implode(' ', array_slice($name_parts, 1));
    }
    ?>

    <div class="wrap">
        <h1><?php echo $is_edit ? 'Edit judoka' : 'Add new judoka'; ?></h1>

        <form method="post" action="" enctype="multipart/form-data" id="<?php echo $form_id;?>" class="judoka-form">
            <?php wp_nonce_field($nonce_action, $nonce_name); ?>

            <?php if($is_edit) : ?>
                <input type="hidden" name="judoka_id" value="<?php echo $judoka->id;?>">
                <input type="hidden" name="old_photo_profile" value="<?php echo esc_attr($judoka->photo_profile); ?>">
                <input type="hidden" name="old_images" value="<?php echo esc_attr(implode(',', (array)$judoka->images));?>">
            <?php endif; ?>

            <table class="form-table">
                <tr>
                    <th><label for="first_name">First Name</label></th>
                    <td>
                        <input type="text" id="first_name" name="first_name" class="regular-text" required
                               value="<?php echo esc_attr($first_name); ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="last_name">Last Name</label></th>
                    <td>
                        <input type="text" id="last_name" name="last_name" class="regular-text" required
                               value="<?php echo esc_attr($last_name); ?>">
                    </td>
                </tr>
                <tr style="display: none;">
                    <td colspan="2">
                        <input type="hidden" id="full_name" name="full_name"
                               value="<?php echo $is_edit ? esc_attr($judoka->full_name) : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="birth_date">Birthdate</label></th>
                    <td>
                        <input type="date" id="birth_date" name="birth_date" required
                               value="<?php echo $is_edit ? esc_attr($judoka->birth_date) : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="category">Category</label></th>
                    <td>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="Senior" <?php echo $is_edit ? selected($judoka->category, 'Senior', false) : ''; ?>>Senior</option>
                            <option value="Junior" <?php echo $is_edit ? selected($judoka->category, 'Junior', false) : ''; ?>>Junior</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="weight">Weight</label></th>
                    <td>
                        <input type="number" step="0.1" id="weight" name="weight" class="regular-text" required
                               value="<?php echo $is_edit ? esc_attr($judoka->weight) : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="club">Club</label></th>
                    <td>
                        <input type="text" id="club" name="club" class="regular-text" required
                               value="<?php echo $is_edit ? esc_attr($judoka->club) : ''; ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="grade">Grade</label></th>
                    <td>
                        <select id="grade" name="grade" required>
                            <option value="">Select a grade</option>
                            <?php
                            $grades = [
                                'White belt', 'Yellow belt', 'Orange belt', 'Green belt',
                                'Blue belt', 'Brown belt', 'Black belt 1st dan', 'Black belt 2nd dan',
                                'Black belt 3rd dan', 'Black belt 4th dan', 'Black belt 5th dan'
                            ];
                            foreach ($grades as $grade) {
                                $selected = $is_edit && $judoka->grade === $grade ? 'selected' : '';
                                echo "<option value=\"{$grade}\" {$selected}>{$grade}</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="gender">Gender</label></th>
                    <td>
                        <select id="gender" name="gender" required>
                            <option value="">Select a gender</option>
                            <option value="M" <?php echo $is_edit ? selected($judoka->gender, 'M', false) : ''; ?>>Male</option>
                            <option value="F" <?php echo $is_edit ? selected($judoka->gender, 'F', false) : ''; ?>>Female</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="photo_profile">Photo profile</label></th>
                    <td>
                        <?php if ($is_edit && !empty($judoka->photo_profile)): ?>
                            <div class="current-image">
                                <img src="<?php echo esc_url($judoka->photo_profile); ?>" style="max-width: 150px;">
                                <p>Current profile photo</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="photo_profile" name="photo_profile" accept="image/*">
                        <?php if ($is_edit): ?>
                            <p class="description">Leave empty to keep the current photo</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="images">Additional Images</label></th>
                    <td>
                        <?php if ($is_edit && !empty($judoka->images)): ?>
                            <div class="current-images">
                                <?php foreach ((array)$judoka->images as $image): ?>
                                    <img src="<?php echo esc_url($image); ?>" style="max-width: 150px; margin: 5px;">
                                <?php endforeach; ?>
                                <p>Current additional images</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple>
                        <?php if ($is_edit): ?>
                            <p class="description">Leave empty to keep current images. Select multiple files to add new images.</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <h3>Competitions</h3>
            <div id="competitions-container">
                <?php
                if ($is_edit && !empty($competitions)):
                    foreach ($competitions as $index => $competition): ?>
                        <table class="form-table competition-entry">
                            <tr>
                                <th colspan="2">
                                    <h4>Competition <?php echo $index + 1; ?></h4>
                                    <input type="hidden" name="competitions[<?php echo $index; ?>][id]"
                                           value="<?php echo $competition->id; ?>">
                                    <button type="button" class="button remove-competition">Delete this competition</button>
                                </th>
                            </tr>
                            <tr>
                                <th><label>Competition Name</label></th>
                                <td>
                                    <input type="text" name="competitions[<?php echo $index; ?>][competition_name]"
                                           class="regular-text" value="<?php echo esc_attr($competition->competition_name); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label>Competition Date</label></th>
                                <td>
                                    <input type="date" name="competitions[<?php echo $index; ?>][date_competition]"
                                           value="<?php echo esc_attr($competition->date_competition); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label>Points Earned</label></th>
                                <td>
                                    <input type="number" name="competitions[<?php echo $index; ?>][points]" min="0"
                                           value="<?php echo esc_attr($competition->points); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label>Rank</label></th>
                                <td>
                                    <input type="number" name="competitions[<?php echo $index; ?>][rang]" min="1"
                                           value="<?php echo esc_attr($competition->rang); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label>Medals</label></th>
                                <td>
                                    <select name="competitions[<?php echo $index; ?>][medals]">
                                        <option value="">None</option>
                                        <option value="Gold" <?php selected($competition->medals, 'Gold'); ?>>Gold</option>
                                        <option value="Silver" <?php selected($competition->medals, 'Silver'); ?>>Silver</option>
                                        <option value="Bronze" <?php selected($competition->medals, 'Bronze'); ?>>Bronze</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    <?php endforeach;
                endif; ?>
            </div>
            <button type="button" id="add-competition" class="button">Add another competition</button>

            <?php submit_button($is_edit ? 'Update changes' : 'Save changes')  ?>
        </form>
    </div>
    <?php
}
