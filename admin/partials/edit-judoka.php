<?php

if (!defined('ABSPATH')) {
    exit;
}

$judoka_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$judoka_model = new Judoka_Model();
$competition_model = new Competition_Model();

$judoka = $judoka_model->get_judoka($judoka_id);

if (!$judoka) {
    wp_die('Judoka not found');
}

$competitions = $competition_model->get_by_judoka($judoka_id);
?>

<div class="wrap">
    <h1>Edit Judoka</h1>

    <form method="post" action="" enctype="multipart/form-data" id="form-edit-judoka">
        <?php wp_nonce_field('edit_judoka_nonce', 'judoka_edit_nonce'); ?>
        <input type="hidden" name="judoka_id" value="<?php echo $judoka_id; ?>">
        
        <!-- Champs cachés pour conserver les anciennes images si non modifiées -->
        <input type="hidden" name="old_photo_profile" value="<?php echo esc_attr($judoka->photo_profile); ?>">
        <input type="hidden" name="old_images" value="<?php echo esc_attr(implode(',', (array)$judoka->images)); ?>">

        <table class="form-table">
            <tr>
                <th><label for="first_name">First Name</label></th>
                <td>
                    <input type="text" id="first_name" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th><label for="last_name">Last Name</label></th>
                <td>
                    <input type="text" id="last_name" class="regular-text" required>
                </td>
            </tr>
            <tr style="display: none;">
                <td colspan="2">
                    <input type="hidden" id="full_name" name="full_name" value="<?php echo esc_attr($judoka->full_name); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="birth_date">Birthdate</label></th>
                <td>
                    <input type="date" id="birth_date" name="birth_date" required value="<?php echo esc_attr($judoka->birth_date); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="category">Category</label></th>
                <td>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="Senior" <?php selected($judoka->category, 'Senior'); ?>>Senior</option>
                        <option value="Junior" <?php selected($judoka->category, 'Junior'); ?>>Junior</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="weight">Weight</label></th>
                <td>
                    <input type="number" step="0.1" id="weight" name="weight" class="regular-text" required value="<?php echo esc_attr($judoka->weight); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="club">Club</label></th>
                <td>
                    <input type="text" id="club" name="club" class="regular-text" required value="<?php echo esc_attr($judoka->club); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="grade">Grade</label></th>
                <td>
                    <select id="grade" name="grade" required>
                        <option value="">Select a grade</option>
                        <option value="White belt" <?php selected($judoka->grade, 'White belt'); ?>>White belt</option>
                        <option value="Yellow belt" <?php selected($judoka->grade, 'Yellow belt'); ?>>Yellow belt</option>
                        <option value="Orange belt" <?php selected($judoka->grade, 'Orange belt'); ?>>Orange belt</option>
                        <option value="Green belt" <?php selected($judoka->grade, 'Green belt'); ?>>Green belt</option>
                        <option value="Blue belt" <?php selected($judoka->grade, 'Blue belt'); ?>>Blue belt</option>
                        <option value="Brown belt" <?php selected($judoka->grade, 'Brown belt'); ?>>Brown belt</option>
                        <option value="Black belt 1st dan" <?php selected($judoka->grade, 'Black belt 1st dan'); ?>>Black belt 1st dan</option>
                        <option value="Black belt 2nd dan" <?php selected($judoka->grade, 'Black belt 2nd dan'); ?>>Black belt 2nd dan</option>
                        <option value="Black belt 3rd dan" <?php selected($judoka->grade, 'Black belt 3rd dan'); ?>>Black belt 3rd dan</option>
                        <option value="Black belt 4th dan" <?php selected($judoka->grade, 'Black belt 4th dan'); ?>>Black belt 4th dan</option>
                        <option value="Black belt 5th dan" <?php selected($judoka->grade, 'Black belt 5th dan'); ?>>Black belt 5th dan</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="gender">Gender</label></th>
                <td>
                    <select id="gender" name="gender" required>
                        <option value="">Select a gender</option>
                        <option value="M" <?php selected($judoka->gender, 'M'); ?>>Male</option>
                        <option value="F" <?php selected($judoka->gender, 'F'); ?>>Female</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="photo_profile">Photo profile</label></th>
                <td>
                    <?php if (!empty($judoka->photo_profile)): ?>
                        <div class="current-image">
                            <img src="<?php echo esc_url($judoka->photo_profile); ?>" style="max-width: 150px;">
                            <p>Current profile photo</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="photo_profile" name="photo_profile" accept="image/*">
                    <p class="description">Leave empty to keep the current photo</p>
                </td>
            </tr>
            <tr>
                <th><label for="images">Additional Images</label></th>
                <td>
                    <?php if (!empty($judoka->images)): ?>
                        <div class="current-images">
                            <?php foreach ((array)$judoka->images as $image): ?>
                                <img src="<?php echo esc_url($image); ?>" style="max-width: 150px; margin: 5px;">
                            <?php endforeach; ?>
                            <p>Current additional images</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    <p class="description">Leave empty to keep current images. Select multiple files to add new images.</p>
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
                            <button type="button" class="button remove-competition">Delete this competition</button>
                        </th>
                    </tr>
                    <tr>
                        <th><label for="competition_name">Competition name</label></th>
                        <td><input type="text" name="competitions[<?php echo $index; ?>][competition_name]" 
                                 class="regular-text" value="<?php echo esc_attr($competition->competition_name); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="date_competition">Competition date</label></th>
                        <td><input type="date" name="competitions[<?php echo $index; ?>][date_competition]" 
                                 value="<?php echo esc_attr($competition->date_competition); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="points">Points earned</label></th>
                        <td><input type="number" name="competitions[<?php echo $index; ?>][points]" min="0" 
                                 value="<?php echo esc_attr($competition->points); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="rang">Rank</label></th>
                        <td><input type="number" name="competitions[<?php echo $index; ?>][rang]" min="1" 
                                 value="<?php echo esc_attr($competition->rang); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="medals">Medals</label></th>
                        <td>
                            <select name="competitions[<?php echo $index; ?>][medals]">
                                <option value="" <?php selected($competition->medals, ''); ?>>None</option>
                                <option value="Gold" <?php selected($competition->medals, 'Gold'); ?>>Gold</option>
                                <option value="Silver" <?php selected($competition->medals, 'Silver'); ?>>Silver</option>
                                <option value="Bronze" <?php selected($competition->medals, 'Bronze'); ?>>Bronze</option>
                            </select>
                        </td>
                    </tr>
                </table>
            <?php endforeach; ?>
        </div>

        <button type="button" id="add-competition" class="button">Add another competition</button>

        <?php submit_button('Update the judoka'); ?>
    </form>
</div>

<script>
    function updateFullName() {
        const firstName = document.getElementById("first_name").value;
        const lastName = document.getElementById("last_name").value;
        const fullName = `${firstName.trim().toUpperCase()} ${lastName.trim().toUpperCase()}`;
        document.getElementById("full_name").value = fullName;
    }

    document.addEventListener("DOMContentLoaded", () => {
        const fullName = "<?php echo esc_attr($judoka->full_name); ?>";
        const [firstName, ...lastName] = fullName.split(" ");
        document.getElementById("first_name").value = firstName;
        document.getElementById("last_name").value = lastName.join(" ");

        updateFullName();
    });

    document.getElementById("first_name").addEventListener("input", updateFullName);
    document.getElementById("last_name").addEventListener("input", updateFullName);
</script>