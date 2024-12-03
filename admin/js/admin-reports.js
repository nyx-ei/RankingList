jQuery(document).ready(function($) {
    const reportForm = $('#generate-report-form');
    const shareModal = $('#share-modal');
    const reportNotice = $('#report-notice');
    
    
    reportForm.on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'generate_report');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#generate-report').prop('disabled', true).html(
                    '<span class="spinner is-active"></span> Generating...'
                );
            },
            success: function(response) {
                if (response.success) {
                    if (formData.get('format') === 'pdf' || formData.get('format') === 'excel') {
                        // Download the file
                        const blob = new Blob([response.data], { 
                            type: formData.get('format') === 'pdf' ? 
                                'application/pdf' : 
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `judoka_report.${formData.get('format')}`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        a.remove();
                    } else {
                        // Show preview
                        $('#report-content').html(response.data);
                        $('#report-preview').removeClass('hidden');
                    }
                    showNotice('Report generated successfully!', 'success');
                } else {
                    showNotice(response.data.message || 'Error generating report', 'error');
                }
            },
            error: function() {
                showNotice('Error generating report', 'error');
            },
            complete: function() {
                $('#generate-report').prop('disabled', false).html(
                    '<span class="dashicons dashicons-media-document"></span> Generate Report'
                );
            }
        });
    });

    $('#print-report').on('click', function() {
        if ($('#report-content').html()) {
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Judoka Report</title>');
            printWindow.document.write('<link rel="stylesheet" type="text/css" href="' + 
                                     adminReportsData.styleUrl + '">');
            printWindow.document.write('</head><body>');
            printWindow.document.write($('#report-content').html());
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        } else {
            showNotice('Please generate a report first', 'error');
        }
    });

    $('#share-report').on('click', function() {
        if ($('#report-content').html()) {
            shareModal.removeClass('hidden');
        } else {
            showNotice('Please generate a report first', 'error');
        }
    });

    $('.close').on('click', function() {
        shareModal.addClass('hidden');
    });

    $('#share-report-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'share_report');
        formData.append('report_data', JSON.stringify(reportForm.serializeArray()));

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $(this).find('button').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Report shared successfully!', 'success');
                    shareModal.addClass('hidden');
                } else {
                    showNotice(response.data.message || 'Error sharing report', 'error');
                }
            },
            error: function() {
                showNotice('Error sharing report', 'error');
            },
            complete: function() {
                $('#share-report-form').find('button').prop('disabled', false);
            }
        });
    });

    function showNotice(message, type) {
        reportNotice
            .removeClass('hidden notice-success notice-error')
            .addClass(`notice-${type === 'success' ? 'success' : 'error'}`)
            .find('p')
            .text(message);
    }
});