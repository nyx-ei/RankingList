jQuery(document).ready(function($) {
    let currentFilters = {
        category: 'all',
        gender: 'all',
        weight: 'all',
        club: 'all',
        view: 'simple'
    };

    $('#category-filter').on('change', function() {
        currentFilters.category = $(this).val();
        updateRankingTable();
    });

    $('.gender-btn').on('click', function() {
        $('.gender-btn').removeClass('active');
        $(this).addClass('active');
        currentFilters.gender = $(this).data('gender');
        
        if (currentFilters.gender !== 'all') {
            $('.weight-group').hide();
            $('.weight-group[data-gender="' + currentFilters.gender + '"]').show();
        } else {
            $('.weight-group').show();
        }
        
        updateRankingTable();
    });

    $('.weight-btn').on('click', function() {
        $('.weight-btn').removeClass('active');
        $(this).addClass('active');
        currentFilters.weight = $(this).data('weight');
        updateRankingTable();
    });

    $('#club-filter').on('change', function() {
        currentFilters.club = $(this).val();
        updateRankingTable();
    });

    $('.view-btn').on('click', function() {
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        currentFilters.view = $(this).data('view');
        $('.ranking-table').attr('data-view', currentFilters.view);
    });

    let searchTimeout;
    $('#search-name').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(function() {
            $('.ranking-row').each(function() {
                const name = $(this).find('.judoka-name').text().toLowerCase();
                if (name.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }, 300);
    });

    function updateRankingTable() {
        $.ajax({
            url: judokaRankingAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'filter_judokas',
                nonce: judokaRankingAjax.nonce,
                ...currentFilters
            },
            beforeSend: function() {
                $('.ranking-body').addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    $('.ranking-body').html(response.data.html);
                    const searchTerm = $('#search-name').val().toLowerCase();
                    if (searchTerm) {
                        $('.ranking-row').each(function() {
                            const name = $(this).find('.judoka-name').text().toLowerCase();
                            $(this).toggle(name.includes(searchTerm));
                        });
                    }
                }
            },
            complete: function() {
                $('.ranking-body').removeClass('loading');
            }
        });
    }

    $('.ranking-table').attr('data-view', 'simple');
});