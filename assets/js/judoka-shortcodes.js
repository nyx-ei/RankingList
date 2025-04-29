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
            
            checkNoResults();
        }, 300);
    });
    
    function checkNoResults() {
        const visibleRows = $('.ranking-row:visible').length;
        
        if (visibleRows === 0) {
            if ($('.no-visible-results').length === 0) {
                $('.ranking-body').append('<div class="no-visible-results">No judokas found matching your search criteria.</div>');
            }
        } else {
            $('.no-visible-results').remove();
        }
    }

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
                        
                        checkNoResults();
                    }
                    
                    $('.ranking-table').attr('data-view', currentFilters.view);
                    
                    $('.ranking-pagination').hide();
                }
            },
            complete: function() {
                $('.ranking-body').removeClass('loading');
            }
        });
    }
    
    $(document).on('click', '.pagination-link:not(.disabled)', function(e) {
        // Permettre la navigation par défaut (sans AJAX) pour maintenir l'état
        // La pagination est gérée côté serveur
    });

    $('.ranking-table').attr('data-view', 'simple');
    
    if ($('.gender-btn.active').data('gender') !== 'all' || 
        $('.weight-btn.active').length > 0 || 
        $('#category-filter').val() !== 'all' || 
        $('#club-filter').val() !== 'all' || 
        $('#search-name').val().length > 0) {
        $('.ranking-pagination').hide();
    }
});