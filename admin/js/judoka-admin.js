jQuery(document).ready(function ($) {
    let competitionCount = $('.competition-entry').length;

    function updateFullName() {
        const firstName = $('#first_name').val().trim().toUpperCase();
        const lastName = $('#last_name').val().trim().toUpperCase();
        $('#full_name').val(`${firstName} ${lastName}`);
    }

    updateFullName();

    $('#first_name, #last_name').on('input', function() {
        updateFullName();
    });

    $('.judoka-form').on('submit', function (e) {
        e.preventDefault();

        updateFullName();

        const form = $(this);
        const formData = new FormData(this);
        const isEdit = form.attr('id') === 'form-edit-judoka';

        formData.append('action', isEdit ? 'edit_judoka' : 'add_judoka');
        formData.append(
            isEdit ? 'judoka_edit_nonce' : 'judoka_nonce',
            isEdit ? judokaAjax.judoka_edit_nonce : judokaAjax.judoka_nonce
        );

        form.find('input[type="submit"]').prop('disabled', true);
        form.find('.notice').remove();
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
                    form.append(`<div class="notice notice-success"><p>Judoka successfully ${isEdit ? 'updated' : 'added'}!</p></div>`);
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
                form.find('input[type="submit"]').prop('disabled', false);
            }
        });
    });

    //Competitions
    $('#add-competition').on('click', function () {
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
                    <th><label>Rank</label></th>
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
        competitionCount++;
    });

    // delete of competition
    $(document).on('click', '.remove-competition', function () {
        $(this).closest('.competition-entry').remove();
    });

    // $('#first_name, #last_name').on('input', updateFullName);

    // Preview profile image
    function handleImagePreview(input, previewClass) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = $('<img>').attr({
                    src: e.target.result,
                    class: previewClass,
                    style: 'max-width: 150px; margin: 5px;'
                });

                $(`.${previewClass}`).remove();
                $(input).after(preview);
            };
            reader.readAsDataURL(file);
        }
    }

    $('#photo_profile').on('change', function() {
        handleImagePreview(this, 'photo-preview');
    });

    // Preview images
    $('#images').on('change', function() {
        const files = this.files;
        const previewContainer = $('<div>').addClass('images-preview');

        $(this).siblings('.images-preview').remove();

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = $('<img>').attr({
                        src: e.target.result,
                        style: 'max-width: 150px; margin: 5px;'
                    });
                    previewContainer.append(preview);
                };
                reader.readAsDataURL(files[i]);
            }
            $(this).after(previewContainer);
        }
    });

    // Delete judoka
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

    updateFullName();
});