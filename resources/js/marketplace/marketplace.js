// Function for set equalize heights of each element
function equalizeHeights(parent, child) {
    return false;
    $list = $(parent), ($items = $list.find(child)).css("height", "auto");
    var e = Math.floor($list.width() / $items.width());
    if (null == e || e < 2) return !0;
    for (var t = 0, n = $items.length; t < n; t += e) {
        var i = 0,
            o = $items.slice(t, t + e);
        o.each(function() {
                var e = parseInt($(this).outerHeight());
                i < e && (i = e)
            }),
            o.css("height", i)
    }
}
// function to load events by defualt
function preapreEvents(selectedSubcategory) {
    if (selectedSubcategory > 0) {
        loadEvents({
            subcategory: selectedSubcategory
        });
    } else {
        $('[data-no-events-block]').show();
        $('[data-events-block]').addClass('d-none').html('');
        $('[data-events-loading-block], [data-loadmore-block]').hide();
    }
}
// function for load events
function loadEvents(extraParams) {
    if (extraParams == undefined) {
        extraParams = {};
    }
    var params = {
            name: $('#bookingTabEventName').val(),
            presenter: $('#bookingTabEventPresenter').val(),
            company: $('#bookingTabEventCompany').val(),
        },
        params = $.extend(params, extraParams);
    $.ajax({
        url: urls.eventsByCategory,
        type: 'POST',
        dataType: 'json',
        data: params,
    }).done(function(data) {
        if (data.data) {
            if (params.page) {
                // check if page param is exist means load more button is clicked so append new records to existing block
                $('[data-events-block]').append(data.data);
            } else {
                // check if page param is not exist means first data is loading so cleared previous data and set newer
                $('[data-no-events-block]').hide();
                $('[data-events-block]').html(data.data).removeClass('d-none');
            }
            // set equal height of each event block
            setTimeout(function() {
                equalizeHeights('[data-events-block]', '[data-event]');
            }, 100);
            // set load more button visibility according to hasMore flag
            if (data.hasMore) {
                $('[data-loadmore-block]').show();
                $('[data-loadmore-control]').data('page', data.nextPage);
            } else {
                $('[data-loadmore-block]').hide();
                $('[data-loadmore-control]').data('page', 2);
            }
        } else {
            // set visibility of blocks
            $('[data-no-events-block]').show();
            $('[data-events-block]').addClass('d-none').html('');
            $('[data-loadmore-block]').hide();
        }
    }).fail(function(error) {
        // set visibility of blocks
        $('[data-no-events-block]').show();
        $('[data-events-block]').addClass('d-none').html('');
        $('[data-loadmore-block]').hide();
        $('.toast').remove();
        toastr.error((error.message || 'Failed to load evetns, Please try again!'))
    }).always(function() {
        // set visibility of blocks
        $('[data-loadmore-control]').html(labels.loadMore).removeClass('processing');
        $('[data-events-loading-block]').hide();
    });
}
// function for load booked tab data
function loadBookedTab() {
    $("#booked-tab-result-block").hide();
    $("#booked-tab-process-block").show();
    $('#bookedEvents').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            type: 'POST',
            url: urls.getBookedEvents,
            data: {
                name: $('#bookeTabdEventName').val(),
                presenter: $('#bookedTabEventPresenter').val(),
                company: $('#bookedTabEventCompany').val(),
                category: $('#bookedTabEventCategory').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'logo',
            name: 'logo'
        }, {
            data: 'event_name',
            name: 'event_name'
        }, {
            data: 'company_name',
            name: 'company_name',
        }, {
            data: 'subcategory_name',
            name: 'subcategory_name',
        }, {
            data: 'presenter',
            name: 'presenter',
        }, {
            data: 'duration',
            name: 'duration',
            class: 'text-center',
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format("MMM DD, YYYY") + '<br />' + moment.utc(data).tz(timezone).format("hh:mm A") + " - " + moment.utc(row.end_time).tz(timezone).format("hh:mm A");
            }
        }, {
            data: 'users_count',
            name: 'users_count',
            class: 'text-center',
            visible: visibleCompany
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        paging: true,
        pageLength: dataTableConf.pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        language: {
            paginate: {
                previous: dataTableConf.pagination.previous,
                next: dataTableConf.pagination.next,
            }
        },
        drawCallback: function(settings) {
            $("#booked-tab-result-block").show();
            $("#booked-tab-process-block").hide();
        }
    });
}
// On window resize set equalize heights of each element
$(window).resize(function() {
    // set equal height of each event block
    if ($('[data-events-block] [data-event]').length > 0) {
        var iv;
        if (iv !== null) {
            window.clearTimeout(iv);
        }
        iv = setTimeout(function() {
            equalizeHeights('[data-events-block]', '[data-event]');
        }, 120);
    }
});
$(document).ready(function() {
    // set CSRF token default in each ajax request
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // initialize bookings category carousel
    $('#bookings-category-carousel').owlCarousel({
        navText: ["<i class='far fa-long-arrow-left'></i>", "<i class='far fa-long-arrow-right'></i>"],
        loop: false,
        margin: 0,
        nav: true,
        dots: false,
        pullDrag: false,
        mouseDrag: false,
        responsive: {
            0: {
                items: 2
            },
            500: {
                items: 3
            },
            767: {
                items: 3
            },
            1000: {
                items: 5
            },
            1700: {
                items: 8
            }
        }
    });
    // currented selected subcategory
    var selectedSubcategory = ($('#bookings-category-carousel .owl-item .item.selected').data('id') || 0);
    // check if hash exist in URL then show tab accordingly
    if (window.location.hash) {
        var hash = window.location.hash;
        if ($('.nav-tabs a[href="' + hash + '"]').length > 0) {
            $('.nav-tabs a[href="' + hash + '"]').tab('show');
            if (hash == "#booked-tab") {
                // load booked tab data
                loadBookedTab();
            } else {
                // load evens of selected category
                preapreEvents(selectedSubcategory);
            }
        } else {
            // load evens of selected category
            preapreEvents(selectedSubcategory);
        }
    } else {
        // load evens of selected category
        preapreEvents(selectedSubcategory);
    }
    // change URL hash on tab switch
    $(document).on('show.bs.tab', '#marketPlaceTabList.nav-tabs a', function(e) {
        var target = $(e.target).attr("href");
        if (target) {
            window.location.hash = target;
            if (target == "#booked-tab") {
                // load booked tab data
                loadBookedTab();
            } else {
                // load evens of selected category
                preapreEvents(selectedSubcategory);
            }
        }
    });
    // Load category wise events
    $(document).on('click', "#bookings-category-carousel .owl-item .item", function() {
        $("#bookings-category-carousel .owl-item .item").removeClass('selected');
        $(this).addClass("selected");
        selectedSubcategory = ($(this).data('id') || 0);
        $('[data-events-loading-block]').show();
        $('[data-no-events-block], [data-loadmore-block]').hide();
        $('[data-events-block]').addClass('d-none').html('');
        loadEvents({
            subcategory: selectedSubcategory
        });
    });
    // load more button click event
    $(document).on('click', '[data-loadmore-control]', function(e) {
        var hasProcessingClass = $(this).hasClass('processing');
        if (!hasProcessingClass) {
            $(this).addClass('processing');
            $(this).html(labels.loadingText);
            var page = ($(this).data('page') || 2);
            loadEvents({
                subcategory: selectedSubcategory,
                page: page
            });
        }
    });
    // search button click
    $(document).on('submit', '#marketplaceSearch', function(e) {
        e.preventDefault();
        var activeTab = ($("ul#marketPlaceTabList li a.active").attr('href') || "#bookings-tab");
        if (activeTab == "#booked-tab") {
            // load booked tab data
            loadBookedTab();
        } else {
            // load evens of selected category
            preapreEvents(selectedSubcategory);
        }
    });
    // reset button click
    $(document).on('click', '#resetSearch', function(e) {
        e.preventDefault();
        $('#bookingTabEventName, #bookingTabEventCompany, #bookingTabEventPresenter').val('').trigger('change');
        $('[data-events-loading-block]').show();
        $('[data-no-events-block], [data-loadmore-block]').hide();
        $('[data-events-block]').addClass('d-none').html('');
        loadEvents({
            subcategory: selectedSubcategory
        });
    });
    // booked tab search button click
    $(document).on('submit', '#bookedEventSearch', function(e) {
        e.preventDefault();
        $("#booked-tab-result-block").hide();
        $("#booked-tab-process-block").show();
        loadBookedTab();
    });
    // booked tab reset search button click
    $(document).on('click', '#resetBookedEventSearchBtn', function(e) {
        $("#bookeTabdEventName, #bookedTabEventPresenter, #bookedTabEventCompany, #bookedTabEventCategory").val('').trigger('change');
        $("#booked-tab-result-block").hide();
        $("#booked-tab-process-block").show();
        loadBookedTab();
    });
});