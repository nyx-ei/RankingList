jQuery(document).ready(function($) {
    
    $('#category-filter').on('change', function() {
        const category = $(this).val();
        
        window.location.href = updateQueryStringParameter(window.location.href, 'category', category);
    });

    $('.view-btn').on('click', function() {
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        const view = $(this).data('view');
        $('.ranking-table').attr('data-view', view);
    });

    $('#nation-filter').on('change', function() {
        const nation = $(this).val();
        if (nation === 'all') {
            $('.ranking-row').show();
        } else {
            $('.ranking-row').each(function() {
                const rowNation = $(this).find('.col-nation span').text();
                $(this).toggle(rowNation === nation);
            });
        }
    });
    
    $('#search-name').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.ranking-row').each(function() {
            const name = $(this).find('.judoka-name').text().toLowerCase();
            $(this).toggle(name.includes(searchTerm));
        });
    });

    function updateQueryStringParameter(uri, key, value) {
        const re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        const separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return uri + separator + key + "=" + value;
        }
    }
});