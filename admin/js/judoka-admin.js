jQuery(document).ready(function ($) {
    let competitionCount = 0;

    $('#add-competition').on('click', function () {
        competitionCount++;
        const template = `
            <table class="form-table competition-entry">
                <tr>
                    <th colspan="2">
                        <h4>Competition ${competitionCount + 1}</h4>
                        <button type="button" class="button remove-competition">Delete this competition</button>
                    </th>
                </tr>
                <tr>
                    <th><label>Competition Name</label></th>
                    <td><input type="text" name="competitions[${competitionCount}][competition_name]" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label>Competition Date</label></th>
                    <td><input type="date" name="competitions[${competitionCount}][date_competition]"></td>
                </tr>
                <tr>
                    <th><label>Points Earned</label></th>
                    <td><input type="number" name="competitions[${competitionCount}][points]" min="0"></td>
                </tr>
                <tr>
                    <th><label>Rang</label></th>
                    <td><input type="number" name="competitions[${competitionCount}][rang]" min="1"></td>
                </tr>
                <tr>
                    <th><label>Medals</label></th>
                    <td>
                        <select name="competitions[${competitionCount}][medals]">
                            <option value="">None</option>
                            <option value="Gold">Gold</option>
                            <option value="Silver">Silver</option>
                            <option value="Bronze">Bronze</option>
                        </select>
                    </td>
                </tr>
            </table>
        `;
        $('#competitions-container').append(template);
    });

    $(document).on('click', '.remove-competition', function () {
        $(this).closest('.competition-entry').remove();
    });

    //add new judoka
    $('#form-judoka').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);

        formData.append('action', 'add_judoka');
        formData.append('judoka_nonce', judokaAjax.judoka_nonce);

        form.find('input[type="submit"]').prop('disabled', false);
        form.append('<div class="notice notice-info"><p>Sending...</p></div>');

        $.ajax({
            url: judokaAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                form.find('.notice').remove();
                if (response.success) {
                    form.append('<div class="notice notice-success"><p>Judoka successfully added!</p></div>');
                    setTimeout(function () {
                        window.location.href = 'admin.php?page=judokas-management';
                    }, 2000);
                } else {
                    form.append(`<div class="notice notice-error"><p>Error: ${response.data}</p></div>`);
                    form.find('input[type="submit"]').prop('disabled', false);
                }
            },
            error: function () {
                form.find('.notice').remove();
                form.append('<div class="notice notice-error"><p>Server connection error</p></div>');
                form.find('input[type="submit"]').prop('disabled', true);
            }
        });
    });

    //edit judoka
    $('#form-edit-judoka').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);

        formData.append('action', 'edit_judoka');
        formData.append('judoka_edit_nonce', judokaAjax.judoka_edit_nonce);

        form.find('input[type="submit"]').prop('disabled', false);
        form.append('<div class="notice notice-info"><p>Sending...</p></div>');

        $.ajax({
            url: judokaAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                form.find('.notice').remove();
                if (response.success) {
                    form.append('<div class="notice notice-success"><p>Judoka successfully updated!</p></div>');
                    setTimeout(function () {
                        window.location.href = 'admin.php?page=judokas-management';
                    }, 2000);
                } else {
                    form.append(`<div class="notice notice-error"><p>Error: ${response.data}</p></div>`);
                    form.find('input[type="submit"]').prop('disabled', false);
                }
            },
            error: function () {
                form.find('.notice').remove();
                form.append('<div class="notice notice-error"><p>Server connection error</p></div>');
                form.find('input[type="submit"]').prop('disabled', true);
            }
        });
    });

    //delete judoka
    $('.delete-judoka').on('click', function(e) {
        e.preventDefault();

        const button = $(this);
        const id = button.data('id');
        const name = button.data('name');
        const row = button.closest('tr');

        if (confirm(`Are you sure you want to delete ${name}?`)) {
            $.ajax({
                url: judokaAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_judoka',
                    judoka_id: id,
                    judoka_delete_nonce: judokaAjax.judoka_delete_nonce
                },
                beforeSend: function() {
                    button.prop('disabled', true).text('Deleting...');
                },
                success: function(response) {
                    console.log(response);
                    window.location.href = 'admin.php?page=judokas-management';
                    if (response.success) {
                        row.fadeOut(500, function() {
                            $(this).remove();
                        });
                        $('<div class="notice notice-success"><p>Judoka successfully deleted!</p></div>')
                            .insertBefore('.wp-list-table')
                            .delay(3000)
                            .fadeOut(500, function() {
                                $(this).remove();
                            });
                    } else {
                        alert('Error: ' + response.data);
                        button.prop('disabled', false).text('Delete');
                    }
                },
                error: function() {
                    alert('Server connection error');
                    button.prop('disabled', false).text('Delete');
                }
            });
        }
    });

    $('#photo_profile').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = $('<img>').attr({
                    src: e.target.result,
                    class: 'photo-preview',
                    style: 'max-width: 200px; margin-top: 10px;'
                });
                $('.photo-preview').remove();
                $('#photo_profile').after(preview);
            };
            reader.readAsDataURL(file);
        }
    });
});
