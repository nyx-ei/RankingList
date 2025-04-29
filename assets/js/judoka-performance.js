jQuery(document).ready(function($) {
    $('.gender-btn').on('click', function() {
        $('.gender-btn').removeClass('active');
        $(this).addClass('active');

        if (!$('#apply-filters').length) {
            filterJudokas();
        }
    });
    
    $('#category-filter, #club-filter').on('change', function() {
        if (!$('#apply-filters').length) {
            filterJudokas();
        }
    });
    
    $('#search-judoka').on('input', function() {
        if (!$('#apply-filters').length) {
            filterJudokas();
        }
    });
    
    function filterJudokas() {
        const selectedGender = $('.gender-btn.active').data('gender');
        const selectedCategory = $('#category-filter').val();
        const selectedClub = $('#club-filter').val();
        const searchText = $('#search-judoka').val().toLowerCase();
        
        $('.judoka-card').each(function() {
            const card = $(this);
            const gender = card.data('gender');
            const category = card.data('category');
            const club = card.data('club');
            const name = card.find('.judoka-card-info h3').text().toLowerCase();
            
            let isVisible = true;
            
            if (selectedGender !== 'all' && gender !== selectedGender) {
                isVisible = false;
            }
            
            if (selectedCategory !== 'all' && category !== selectedCategory) {
                isVisible = false;
            }
            
            if (selectedClub !== 'all' && club !== selectedClub) {
                isVisible = false;
            }
            
            if (searchText && !name.includes(searchText)) {
                isVisible = false;
            }
            
            card.closest('.judoka-card-link').toggle(isVisible);
        });
    }
    
    if ($('.profile-tabs').length) {
        setupProfileTabs();
        setupCompetitionFilters();
    }
    
    function setupProfileTabs() {
        $('.profile-tabs .tab').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).attr('href');
            
            $('.profile-tabs .tab').removeClass('active');
            $(this).addClass('active');
            
            $('.tab-content').removeClass('active');
            $(target).addClass('active');
        });
    }

    function setupCompetitionFilters() {
        $('#competition-year-filter, #medal-filter').on('change', function() {
            filterCompetitions();
        });
    }
    
    function filterCompetitions() {
        const selectedYear = $('#competition-year-filter').val();
        const selectedMedal = $('#medal-filter').val();
        
        $('.competitions-table tbody tr').each(function() {
            const row = $(this);
            const year = row.data('year');
            const medal = row.data('medal');
            
            let isVisible = true;
            
            if (selectedYear !== 'all' && year !== selectedYear) {
                isVisible = false;
            }
            
            if (selectedMedal !== 'all') {
                if (selectedMedal === 'none' && medal !== '') {
                    isVisible = false;
                } else if (selectedMedal !== 'none' && medal !== selectedMedal) {
                    isVisible = false;
                }
            }
            
            row.toggle(isVisible);
        });
    }
    
    $('.view-btn').on('click', function() {
        const view = $(this).data('view');
        
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.ranking-table').attr('data-view', view);
        
        if (view === 'expanded') {
            $('.expanded-only').show();
        } else {
            $('.expanded-only').hide();
        }
    });

    $('#apply-filters').on('click', function() {
        applyFilters();
    });
    
    function applyFilters() {
        const category = $('#category-filter').val();
        const club = $('#club-filter').val();
        const gender = $('.gender-btn.active').data('gender');
        const search = $('#search-judoka').val();

        let url = window.location.href.split('?')[0];
        let params = [];

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('judoka_id')) {
            params.push('judoka_id=' + urlParams.get('judoka_id'));
        }

        if (category && category !== 'all') {
            params.push('category=' + encodeURIComponent(category));
        }
        
        if (club && club !== 'all') {
            params.push('club=' + encodeURIComponent(club));
        }
        
        if (gender && gender !== 'all') {
            params.push('gender=' + encodeURIComponent(gender));
        }
        
        if (search) {
            params.push('search=' + encodeURIComponent(search));
        }
        
        params.push('judo_page=1');

        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    }
    
    $('#search-judoka').on('keypress', function(e) {
        if (e.which === 13 && $('#apply-filters').length) {
            e.preventDefault();
            applyFilters();
        }
    });
});