<?php

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap">
    <h1>Add a new judoka</h1>
    <form method="post" action="" enctype="multipart/form-data" id="form-judoka">
        <?php wp_nonce_field('add_judoka_nonce', 'judoka_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="first_name">First name</label></th>
                <td><input type="text" id="first_name" name="first_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="last_name">Last name</label></th>
                <td><input type="text" id="last_name" name="last_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="full_name">Full Name</label></th>
                <td><input type="text" id="full_name" name="full_name" class="regular-text" required readonly></td>
            </tr>
            <tr>
                <th><label for="birth_date">Birthdate</label></th>
                <td><input type="date" id="birth_date" name="birth_date" required></td>
            </tr>
            <tr>
            <th><label for="category">Category</label></th>
                <td>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="Senior">Senior</option>
                        <option value="Junior">Junior</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="weight">Weight (kg)</label></th>
                <td><input type="number" step="0.1" id="weight" name="weight" required></td>
            </tr>
            <tr>
                <th><label for="club">Club</label></th>
                <td><input type="text" id="club" name="club" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="grade">Grade</label></th>
                <td>
                    <select id="grade" name="grade" required>
                        <option value="">Select a grade</option>
                        <option value="White belt">White belt</option>
                        <option value="Yellow belt">Yellow belt</option>
                        <option value="Orange belt">Orange belt</option>
                        <option value="Green belt">Green belt</option>
                        <option value="Blue belt">Blue belt</option>
                        <option value="Brown belt">Brown belt</option>
                        <option value="Black belt 1st dan">Black belt 1st dan</option>
                        <option value="Black belt 2nd dan">Black belt 2nd dan</option>
                        <option value="Black belt 3rd dan">Black belt 3rd dan</option>
                        <option value="Black belt 4th dan">Black belt 4th dan</option>
                        <option value="Black belt 5th dan">Black belt 5th dan</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="gender">Gender</label></th>
                <td>
                    <select id="gender" name="gender" required>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="photo_profile">Profile photo</label></th>
                <td>
                    <input type="file" id="photo_profile" name="photo_profile" accept="image/*">
                    <p class="description">Recommended format: JPEG or PNG, max 2MB</p>
                </td>
            </tr>
            <tr>
                <th><label for="images">Additional images</label></th>
                <td>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    <p class="description">You can select multiple images</p>
                </td>
            </tr>
        </table>

        <h3>Competition informations</h3>
        <div id="competitions-container">
            <table class="form-table competition-entry">
                <tr>
                    <th><label for="competition_name">Competition name</label></th>
                    <td><input type="text" name="competitions[0][competition_name]" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="date_competition">Competition date</label></th>
                    <td><input type="date" name="competitions[0][date_competition]"></td>
                </tr>
                <tr>
                    <th><label for="points">Points earned</label></th>
                    <td><input type="number" name="competitions[0][points]" min="0"></td>
                </tr>
                <tr>
                    <th><label for="rang">Rank</label></th>
                    <td><input type="number" name="competitions[0][rang]" min="1"></td>
                </tr>
                <tr>
                    <th><label for="medals">Medals</label></th>
                    <td>
                        <select name="competitions[0][medals]">
                            <option value="">None</option>
                            <option value="Gold">Gold</option>
                            <option value="Silver">Silver</option>
                            <option value="Bronze">Bronze</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <button type="button" id="add-competition" class="button">Add another competition</button>

        <?php submit_button('Add the judoka'); ?>
    </form>
</div>

<script>
    function normalizeText(text) {
        return text.trim().toUpperCase();
    }

    function updateFullName() {
        const firstName = document.getElementById("first_name").value;
        const lastName = document.getElementById("last_name").value;

        const fullName = `${normalizeText(firstName)} ${normalizeText(lastName)}`;
        document.getElementById("full_name").value = fullName;
    }

    document.getElementById("first_name").addEventListener("input", updateFullName);
    document.getElementById("last_name").addEventListener("input", updateFullName);
</script>
