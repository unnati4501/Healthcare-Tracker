$(document).ready(function() {
    let theme = 'red-theme';
    let popover = [];
    var calendarEl = document.getElementById('calendar');
 
    var calendar = new FullCalendar.Calendar(calendarEl, {
    //   plugins: [ 'dayGrid' ],
    headerToolbar: {
        start: 'prev,next today',
        center: 'title',
        end: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      navLinks: true, // can click day/week names to navigate views
      businessHours: true, // display business hours
      displayEventTime: true,
      editable: false,
      slotLabelInterval: '00:30', 
      events : url.calendarReport,
        eventContent: function(arg) {
            let arrayOfDomNodes = []
                let titleEvent = document.createElement('div');
                if(arg.event.extendedProps.image_url) {
                  titleEvent.innerHTML = '<img src="'+arg.event.extendedProps.image_url+'" style="width: 24px; height: 24px;margin-right:5px;">' + arg.event._def.title
                  titleEvent.classList = "fc-event-title fc-sticky"
                }else{
                    titleEvent.innerHTML = arg.event._def.title
                    titleEvent.classList = "fc-event-title fc-sticky"
                }
                arrayOfDomNodes = [ titleEvent]
                return { domNodes: arrayOfDomNodes }
        },
        eventDidMount: function(info)
        {   
            info.el.setAttribute("data-bs-html", true);
            info.el.setAttribute("data-bs-trigger", "hover focus click");
            info.el.setAttribute("data-bs-container", "body");
            info.el.setAttribute("data-bs-placement", "top");
            info.el.setAttribute("data-bs-content", info.event.extendedProps.toolTipHtml);
            if (info.event.extendedProps.status == 4) {
                info.el.setAttribute("data-bs-custom-class", 'orange-theme popover-cal-event');
            } else if(info.event.extendedProps.status == 5) { // Completed
                info.el.setAttribute("data-bs-custom-class", 'green-theme popover-cal-event');
            } else if(info.event.extendedProps.status == 3) { // Cancelled
                info.el.setAttribute("data-bs-custom-class", 'new-grey-theme popover-cal-event');
            } else if(info.event.extendedProps.status == 6) { // Pending
                info.el.setAttribute("data-bs-custom-class", 'blue-theme popover-cal-event');
            } else if(info.event.extendedProps.status == 7) { // Elapsed
                info.el.setAttribute("data-bs-custom-class", 'yellow-theme popover-cal-event');
            } else { // Rejected
                info.el.setAttribute("data-bs-custom-class", 'red-theme popover-cal-event');
            }
            // console.log(info.el);
            // console.log(info.event.extendedProps.eventName);
            info.el.setAttribute('id', info.event._instance.defId);
            popover[info.event._instance.defId] = new bootstrap.Popover(info.el, {
                title: info.event.extendedProps.eventName,
            });

            var hiddenEvents = calendar.getEvents().filter(event => event.extendedProps.hidden);
            var visibleEvents = calendar.getEvents().filter(event => !event.extendedProps.hidden);

            // Check if all events are hidden
            if (hiddenEvents.length > 0 && visibleEvents.length === 0) {
                // Hide the "View More" button
                var viewMoreButton = document.querySelector('.fc-more');
                if (viewMoreButton) {
                    viewMoreButton.style.display = 'none';
                }
            } else {
                // Show the "View More" button if previously hidden
                var viewMoreButton = document.querySelector('.fc-more');
                if (viewMoreButton) {
                    viewMoreButton.style.display = '';
                }
            }
            // {description: "Lecture", department: "BioChemistry"}
        },
        eventClassNames: function(info)
        {
            $('.popover').each(function () {
              var id = $(this).attr('id');
              $('.fc-daygrid-event[aria-describedby='+id+']').attr('id', 'test_'+id);
              $('.fc-daygrid-event[aria-describedby='+id+']').removeAttr('aria-describedby');
              $('#'+id).remove();
            });
            var themeColor          = '';   
            var result              = true;
            var presentersArray     = [];
            var coachStates         = [];
            var expertiseArray      = [];

            if (info.event.extendedProps.status == 4) {
                themeColor = 'orange-event';
            } else if(info.event.extendedProps.status == 5) { // Completed
                themeColor = 'green-event';
            } else if(info.event.extendedProps.status == 3) { // Cancelled
                themeColor = 'new-grey-event';
            } else if(info.event.extendedProps.status == 6) { // Pending
                themeColor = 'blue-event';
            } else if(info.event.extendedProps.status == 7) { // Elapsed
                themeColor = 'yellow-event';
            } else { // Rejected
                themeColor = 'red-event';
            }

            const presentersInputs = document.querySelectorAll('input.presenters:checked');
            const couchInputs = document.querySelectorAll('input.couchstatus:checked');
            const expertiseInputs = document.querySelectorAll('input.expertise:checked');

            couchInputs.forEach(input => {
                if (!coachStates.includes(input.value)) {
                    coachStates.push(input.value); // Add to array if not already present
                }
            });
            
            presentersInputs.forEach(input => {
                if (!presentersArray.includes(input.dataset.value)) {
                    presentersArray.push(input.dataset.value); // Add to array if not already present
                }
            });
            expertiseInputs.forEach(input => {
                if (!expertiseArray.includes(input.dataset.status)) {
                    expertiseArray.push(input.dataset.status); // Add to array if not already present
                }
            });
            
            if (coachStates.length > 0) {
                result = result && coachStates.indexOf(info.event.extendedProps.status) >= 0;
            }
            if (presentersArray.length > 0) {
                result = result && presentersArray.indexOf(info.event.extendedProps.presenterName) >= 0;
            }
            if (expertiseArray.length > 0) {
                result = result && expertiseArray.indexOf(info.event.extendedProps.expertiseName) >= 0;
            }
            if (!result) {
                result = "hidden";
            }
            
            return [result, themeColor];
        },
        dateClick: function() {
            $('.popover').each(function () {
                var id = $(this).attr('id');
                var eventHtml = $('.fc-daygrid-event[aria-describedby='+id+']');
                var eventId = $('.fc-daygrid-event[aria-describedby='+id+']').attr('id');
                var eventName = $('.fc-daygrid-event[aria-describedby='+id+']').find('small').text();
                $('#'+id).remove();
                popover[eventId].dispose();
                popover[eventId] = new bootstrap.Popover(eventHtml, {
                    title: eventName,
                });
            });
        },
        viewDidMount: function() {
            $('.popover').each(function () {
                var id = $(this).attr('id');
                $('.fc-daygrid-event[aria-describedby='+id+']').attr('id', 'test_'+id);
                $('.fc-daygrid-event[aria-describedby='+id+']').removeAttr('aria-describedby');
                $('#'+id).remove();
            });
        },
        views: { // set the view button names
            month:    {buttonText: 'Monthly'},
            agendaWeek :{buttonText: 'Weekly'},
            agendaDay : {buttonText:'Daily'},
            today : {buttonText:'Today'}
        },
        selectable: false,
        selectHelper: false,
        dayMaxEventRows:4,
        moreLinkContent: function (numEvents) {
            return numEvents.text;
        },
    });

    const tabEl = document.querySelector('a[href="#calender-view-tab"]')
        tabEl.addEventListener('shown.bs.tab', event => {
            calendar.render();
    })
    calendar.render();
    $(document).on('click','.fc-daygrid-event',function() {
        var currentId = $(this).attr('id');
        $(this).attr("testId");
        $('.popover').each(function () {
            var id = $(this).attr('id');
            var eventHtml = $('.fc-daygrid-event[aria-describedby='+id+']');
            var eventId = $('.fc-daygrid-event[aria-describedby='+id+']').attr('id');
            if (eventId != currentId) {
                var eventName = $('.fc-daygrid-event[aria-describedby='+id+']').find('small').text();
                $('#'+id).remove();
                popover[eventId].dispose();
                popover[eventId] = new bootstrap.Popover(eventHtml, {
                    title: eventName,
                });
            }
        });
    });
    $(document).on('click', 'input:checkbox.calFilter', function(){
         calendar.render();
         calendar.changeView('dayGridMonth');
    });
    if ($(".search-wrap").length) {
        $(".noresults").css("display", "none");
        $(document).delegate(".search-wrap .form-control", "keyup", function() {
            var value = $(this).val().toLowerCase();
            var thisSearch = $(this).closest(".searchable-block").find("li");
            if ($(".searchable-block").length) {
                if ($.trim(value) !== "") {
                    thisSearch.filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf($.trim(value)) > -1)
                    });
                } else {
                    thisSearch.show();
                }
                var $noresults = $(this).closest(".searchable-block").find('.noresults');
                if ($(".searchable-block li:visible").length === 0) {
                    $noresults.css("display", "block");
                } else {
                    $noresults.css("display", "none");
                }
            }
        });
    }
    $(".calendar-filter-btn").click(function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        $(".calender-filter").show();
        $("html,body").css("overflow-y","hidden");
    })
    $(".close-calendar-filter").click(function(e){
        $("html,body").css("overflow-y","auto");
        $(".calender-filter").hide();
    })
    $(document).on('click', 'input:checkbox.presenter_selectall', function(){
        if(this.checked) {
            $('.presenters').prop('checked', true);
        } else {
            $('.presenters').prop('checked', false);
        }
        calendar.render();
        calendar.changeView('dayGridMonth');
    });
    $(document).on('click', 'input:checkbox.presenters', function(){
        if($('.presenters:checked').length == $('.presenters').length){
            $('.presenter_selectall').prop('checked',true);
        }else{
            $('.presenter_selectall').prop('checked',false);
        }
    });
    $(document).on('click', 'input:checkbox.status_selectall', function(){
        if(this.checked) {
            $('.couchstatus').prop('checked', true);
        } else {
            $('.couchstatus').prop('checked', false);
        }
    });
    $(document).on('click', 'input:checkbox.couchstatus', function(){
        if($('.couchstatus:checked').length == $('.couchstatus').length){
            $('.status_selectall').prop('checked',true);
        }else{
            $('.status_selectall').prop('checked',false);
        }
    });
    $(document).on('click', 'input:checkbox.expertise_selectall', function(){
        if(this.checked) {
            $('.expertise').prop('checked', true);
        } else {
            $('.expertise').prop('checked', false);
        }
    });
    $(document).on('click', 'input:checkbox.expertise', function(){
        if($('.expertise:checked').length == $('.expertise').length){
            $('.expertise_selectall').prop('checked',true);
        }else{
            $('.expertise_selectall').prop('checked',false);
        }
    });
});