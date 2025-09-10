function showPageLoaderWithMessage(msessage) {
    if (msessage != "") {
        $(".page-loader-wrapper p").html(msessage);
    }
    $(".page-loader-wrapper").fadeIn();
}

function hidesPageLoader() {
    $(".page-loader-wrapper").fadeOut();
    setTimeout(function() {
        $(".page-loader-wrapper p").html('Please wait...');
    }, 2000);
}

function generateBrandingUrl(subdomain) {
    if (subdomain == undefined || subdomain == '') {
        return '';
    }
    var _baseUrlInfo = new URL((_ZBASEURL || ""));
    return _baseUrlInfo.protocol + '//' + subdomain + '.' + _baseUrlInfo.host + '/';
}
(function($) {
    $(window).on("load", function() {
        // Loader
        setTimeout(function() {
            $(".page-loader-wrapper").fadeOut();
        }, 50);
        $('#sidebar .nav-item.has-treeview').each(function(){
            var $this = $(this);
            // if the current path is like this link, make it active
            if($this.hasClass('menu-open')){
                $this.find(".nav-treeview").addClass("nav-treeview-open").slideDown();
            }
        })
        //Menu 
        $("#sidebar .nav-item.has-treeview > .nav-link").click(function(e) {
            e.stopImmediatePropagation();
            e.preventDefault();
            if ($(this).closest("li").hasClass("has-treeview")) {
                var $parent = $(this).closest("li.has-treeview");
                if($parent.hasClass("menu-open")){
                    $parent.find(".nav-treeview").removeClass("nav-treeview-open").slideUp();
                    $parent.removeClass("menu-open");
                }else{
                    $parent.addClass("menu-open");
                    // $parent.siblings(".has-treeview").removeClass("menu-open").find(".nav-treeview").removeClass("nav-treeview-open").slideUp();
                    $parent.find(".nav-treeview").addClass("nav-treeview-open").slideDown();
                }
            }
        })

        // collapsed sidebar close open submenu 
        $(".hamburger").click(function() {
            if($("body").hasClass("sidebar-collapse")){
                $("body").removeClass("sidebar-collapse");
                $(".nav-treeview-open").css("display","block");
            }else{
                $("body").addClass("sidebar-collapse");
                $(".nav-treeview-open").css("display","none");
            }
            if($(window).width() < 992){
                $("body").toggleClass("sidebar-open");
                if($("body").hasClass("sidebar-open")){
                    $("body").append("<div id='sidebar-overlay'></div>");
                    $("#sidebar-overlay").click(function(e){
                        $("body").removeClass("sidebar-open");
                        $("#sidebar-overlay").remove();
                    })
                }else{
                    $("#sidebar-overlay").remove();
                }
            } 
        })
        $(".main-sidebar").hover(function() {
            $("body").addClass("sidemenu-hovered");
            if($("body").hasClass("sidebar-collapse")){
                $(".nav-treeview-open").css("display","block");
            }
           
        })
        $(".main-sidebar").mouseleave(function() {
            $("body").removeClass("sidemenu-hovered");
            if($("body").hasClass("sidebar-collapse")){
                $(".nav-treeview-open").css("display","none");
            }
        })
     

        var url = window.location;

        // // for sidebar menu entirely but not cover treeview
        $('ul.nav-sidebar a').filter(function() {
             return this.href == url;
        }).addClass('active');
        
        // // for treeview
        // $('ul.nav-treeview a').filter(function() {
        //      return this.href == url;
        // }).closest(".nav-treeview").parent().addClass('menu-open').find(".nav-treeview").slideDown();

        $("a[href='#']").click(function(e) {
            e.preventDefault();
        })

        // Edit Avatar

        // $(".edit-avatar").change(function() {
        //     readURL(this);
        // });
        // $(".edit-photo").click(function() {
        //     $(".edit-avatar").click();
        // });
        


        // Custom Scrolling
        if ($("#sidebar").length > 0) {
            $.mCustomScrollbar.defaults.scrollButtons.enable = true;
            $.mCustomScrollbar.defaults.axis = "yx";
            $("#sidebar nav").mCustomScrollbar({
                axis: "y",
                theme: "inset-dark"
            });
        }
        if ($(".custom-scrollbar").length > 0) {
            $.mCustomScrollbar.defaults.scrollButtons.enable = true;
            $.mCustomScrollbar.defaults.axis = "y";
            $(".custom-scrollbar").mCustomScrollbar({
                axis: "y",
                theme: "inset-dark"
            });
        }
        // Initialize Select2 Elements
        if ($(".select2").length > 0 && $(".no-default-select2").length == 0) {
            $('.select2').select2({
                // placeholder: "Select",
                // templateSelection: function (data) {
                //     if (data.id === '-1') { // adjust for custom placeholder values
                //      debugger;
                //     }
                
                //     return data.text;
                //   },
                allowClear: true,
                width: '100%'
            });
        }

        // custome file 
         
        // $(".custom-file-input").on("change", function() {
        //     readURL(this);
        // });
        // $(".edit-avatar").on("change", function() {
        //     logoreadURL(this);
        // });
        
        // ------------ Datepicker -------------
        // if ($('.datepicker').length > 0) {
        //     $('.datepicker').datepicker({
        //         todayHighlight: true,
        //         autoclose: true
        //     });
        // }
        // if ($('input[name="datefilter"]').length > 0) {
        //     $('input[name="datefilter"]').daterangepicker({
        //         autoUpdateInput: false,
        //         locale: {
        //             cancelLabel: 'Clear'
        //         }
        //     });
        //     $('input[name="datefilter"]').on('apply.daterangepicker', function(ev, picker) {
        //         $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        //     });
          
        //     $('input[name="datefilter"]').on('cancel.daterangepicker', function(ev, picker) {
        //         $(this).val('');
        //     });
        // }
        
        // ------------ Tooltip -------------
        if ($('[data-bs-toggle="tooltip"]').length > 0) {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
        if ($('[data-toggle="help-tooltip"]').length > 0) {
            $('[data-toggle="help-tooltip"]').tooltip({
                template: '<div class="tooltip tooltip-large" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
                html: true,
                placement: 'auto',
            });
        }
        // --------------------- form
        $('.zevo_form_submit').submit(function() {
            if ($('.zevo_form_submit').valid()) {
                $("#zevo_submit_btn").attr("disabled", true);
            } else {
                if ($(".is-invalid").first().hasClass('article-ckeditor')) {
                    $('html, body').animate({
                        scrollTop: $(".is-invalid :visible").first().offset().top - 100
                    }, 500);
                } else {
                    $('html, body').animate({
                        scrollTop: $(".is-invalid").first().offset().top - 100
                    }, 500);
                }
            }
        });
        $('#zevo_submit_btn').click(function() {
            if (!$('.zevo_form_submit').valid()) {
                if ($(".is-invalid").first().hasClass('article-ckeditor')) {
                    $('html, body').animate({
                        scrollTop: $(".is-invalid :visible").first().offset().top - 100
                    }, 500);
                } else {
                    $('html, body').animate({
                        scrollTop: $(".is-invalid").first().offset().top - 100
                    }, 500);
                }
            }
        });
        $('.nav-tabs > li a[title]').tooltip();

        //Wizard
        $('.wizard-inner a[data-toggle="tab"]').on('shown.bs.tab', function(e) {

            var target = $(e.target);

            if (target.parent().hasClass('disabled')) {
                return false;
            }
        });

        $(".next-step").click(function(e) {
            var active = $('.wizard-inner .nav-tabs li.active');
            active.next().removeClass('disabled');
            nextTab(active);

        });
        $(".prev-step").click(function(e) {

            var active = $('.wizard-inner .nav-tabs li.active');
            prevTab(active);

        });
        $('.wizard-inner .nav-tabs').on('click', 'li', function() {
            $('.wizard-inner .nav-tabs li.active').removeClass('active');
            $(this).nextAll().removeClass("completed");
            $(this).prevAll().addClass("completed");
            $(this).addClass('active');
            if($(this).index() == 0){
                $(".prev-step").attr('disabled', 'disabled');
            }else{
                $(".prev-step").removeAttr("disabled");
            }
            if($(this).index() <= ($(this).closest("ul").find("li").length - 1)){
                $(".next-step .nextstep-text").text('Next');
            }
            if($(this).index() == ($(this).closest("ul").find("li").length - 1)){
                $(".next-step .nextstep-text").text('Done');
            }
            if($(this).hasClass('completed')){
                $(this).removeClass('completed');
            }
        });


        // add slot js
        $(".add-slot").click(function() {
            $(this).closest("tbody").append('<tr><td></td><td><div class="d-flex"><div class="form-group mb-0 me-3 flex-grow-1"> <select class="form-control select2"><option value=""></option><option>option 1</option><option>option 2</option><option>option 3</option></select></div><div class="form-group mb-0 me-3 flex-grow-1"> <select class="form-control select2"><option value=""></option><option>option 1</option><option>option 2</option><option>option 3</option></select></div></div></td><td><a href="#" class="edit-slot action-icon me-3 ms-3"><i class="far fa-save"></i></a><a href="#" class="delete-slot action-icon danger"><i class="far fa-trash"></i></a></td><td></td></tr>')
            if ($(".select2").length > 0 && $(".no-default-select2").length == 0) {
                $('.select2').select2({
                    placeholder: "Select",
                    width: '100%'
                });
            }
        });

        $(document).delegate(".delete-slot", "click", function(e) {
            e.preventDefault();
            $(this).closest("tr").remove();
        });
        if ($(".filter-btn").length){
            var filterBtnHeight = $(".filter-btn").innerHeight();
            if($(window).width() < 768){
                $(".wrapper").css({"padding-bottom": filterBtnHeight});
            }            
            $(".filter-btn").click(function(){
                $("body,html,.wrapper").addClass("is-filter-open");                
                $(".search-card").fadeIn();
            })
        }
        if ($(".calendar-filter-btn").length){
            var calFilterBtnHeight = $(".calendar-filter-btn").innerHeight();
            if($(window).width() < 992){
                $(".wrapper").css({"padding-bottom": calFilterBtnHeight});
            } 
        }

        $(".filter-cancel-icon").click(function(){
            if ($("body,html,.wrapper").hasClass("is-filter-open")){
                $("body,html,.wrapper").removeClass("is-filter-open")
                $(".search-card").fadeOut();
            }
        })
        $('#recipeImages').on('change', function() {
            imagesPreview(this, 'div.recipe-gallery');
        });

        $(".presenter-item .view-more").click(function(){
            $(this).closest(".presenter-item").find(".other-timings").toggle();
            if($(this).text() == "+ View More"){
                $(this).text("- View Less")
            }else{
                $(this).text("+ View More")
            }
            
        })

        $('.digit-group').find('input').each(function() {
            $(this).attr('maxlength', 1);
            $(this).on('keyup', function(e) {
                var parent = $($(this).parent());
                
                if(e.keyCode === 8 || e.keyCode === 37) {
                    var prev = parent.find('input#' + $(this).data('previous'));
                    
                    if(prev.length) {
                        $(prev).select();
                    }
                } else if((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 65 && e.keyCode <= 90) || (e.keyCode >= 96 && e.keyCode <= 105) || e.keyCode === 39) {
                    var next = parent.find('input#' + $(this).data('next'));
                    
                    if(next.length) {
                        $(next).select();
                    } else {
                        if(parent.data('autosubmit')) {
                            parent.submit();
                        }
                    }
                }
            });
        });
        
        var bsDefaults = {
            offset: false,
            overlay: true,
            width: '330px'
         },
         bsMain = $('.bs-offset-main'),
         bsOverlay = $('.bs-canvas-overlay');
   
      $('[data-toggle="canvas"][aria-expanded="false"]').on('click', function() {
         var canvas = $(this).data('target'),
            opts = $.extend({}, bsDefaults, $(canvas).data()),
            prop = $(canvas).hasClass('bs-canvas-right') ? 'margin-right' : 'margin-left';
   
         if (opts.width === '100%')
            opts.offset = false;
         
         $(canvas).css('width', opts.width);
         if (opts.offset && bsMain.length)
            bsMain.css(prop, opts.width);
   
         $(canvas + ' .bs-canvas-close').attr('aria-expanded', "true");
         $('[data-toggle="canvas"][data-bs-target="' + canvas + '"]').attr('aria-expanded', "true");
         if (opts.overlay && bsOverlay.length)
            bsOverlay.addClass('show');
         return false;
      });
   
      $('.bs-canvas-close, .bs-canvas-overlay').on('click', function() {
         var canvas, aria;
         if ($(this).hasClass('bs-canvas-close')) {
            canvas = $(this).closest('.bs-canvas');
            aria = $(this).add($('[data-toggle="canvas"][data-bs-target="#' + canvas.attr('id') + '"]'));
            if (bsMain.length)
               bsMain.css(($(canvas).hasClass('bs-canvas-right') ? 'margin-right' : 'margin-left'), '');
         } else {
            canvas = $('.bs-canvas');
            aria = $('.bs-canvas-close, [data-toggle="canvas"]');
            if (bsMain.length)
               bsMain.css({
                  'margin-left': '',
                  'margin-right': ''
               });
         }
         canvas.css('width', '');
         aria.attr('aria-expanded', "false");
         if (bsOverlay.length)
            bsOverlay.removeClass('show');
         return false;
      });
    //   $.bsCalendar.setDefault('width', 5000);
    //   $('#calendar_offcanvas').bsCalendar({width: '80%'});
    if ($("#calendar_offcanvas").length){
        $('#calendar_offcanvas').bsCalendar({

            locale: 'en',
            url: null, // save as data-bs-target
            width: '330px',
            icons: {
                prev: 'fas fa-arrow-left fa-fw',
                next: 'fas fa-arrow-right fa-fw',
                eventEdit: 'fas fa-edit fa-fw',
                eventRemove: 'fas fa-trash fa-fw'
            },
            showEventEditButton: false,
            showEventRemoveButton: false,
            formatEvent: function (event) {
                return drawEvent(event);
            },
            formatNoEvent: function (date) {
                console.log(date);
                return ('<div class="p-2" style="font-size:.8em">' +
                    '<div class="d-flex">' +
                    '<div class="w-50 form-group pe-2 mb-2">' +
                    '<label>Start Time</label>' +
                    '<input type="text" class="form-control p-2" placeholder="Time">' +
                    '</div>' +
                    '<div class="w-50 ps-2 form-group mb-2">' +
                    '<label>End Time</label>' +
                        '<input type="text" class="form-control p-2" placeholder="Time">' +
                        '</div>' +
                        '<div class="form-group align-self-end text-nowrap mb-2 ms-2"><a href="#" class="p-2 text-primary"> <i class="fa fa-save"></i> </a><a href="#" class="p-2 text-black-50"> <i class="fa fa-times"></i> </a></div>' +
                    '</div>' +
                    '</div>' + 
                    '<div class="list-group-item p-0"><div class="d-flex p-2 justify-content-between align-items-center"><p class="mb-0">8:00 AM - 9:00 PM</p><div><a href="#" class="p-2 text-primary"> <i class="fa fa-pencil"></i> </a>' + 
                    '<a href="#" class="p-2 text-danger"> <i class="fa fa-trash"></i> </a></div></div></div>'
                    )
            },
            queryParams: function (params) {
                return params;
            },
            onClickEditEvent: function (e, event) {
            },
            onClickDeleteEvent: function (e, event) {
            },
          });
        }

        $( window ).resize(function() {
            if ($(".filter-btn").length){
                var filterBtnHeight = $(".filter-btn").innerHeight(); 
                if($(window).width() >= 768){
                    $(".wrapper").css({"padding-bottom": 0});
                }else{
                    $(".wrapper").css({"padding-bottom": filterBtnHeight});
                }   
            }
        })
    });


})(jQuery);

function nextTab(elem) {
    elem.addClass("completed");
    if(elem.next().index() != 0){
        $(".prev-step").removeAttr("disabled");
    }
    if(elem.next().index() == (elem.closest("ul").find("li").length - 1)){
        $(".next-step .nextstep-text").text('Done');
    }
    elem.next().find('a[data-toggle="tab"]').click();
}

function prevTab(elem) {
    elem.removeClass("completed");
    if(elem.prev().index() == 0){
        $(".prev-step").attr('disabled', 'disabled');
    }
    if(elem.prev().index() <= (elem.closest("ul").find("li").length - 1)){
        $(".next-step .nextstep-text").text('Next');
    }
    elem.prev().find('a[data-toggle="tab"]').click();
}


// function readURL(input) {
//     if (input.files && input.files[0]) {
//         var reader = new FileReader();
//         reader.onload = function(e) {
//             $(input).closest(".custom-file-preview").find('img').attr('src', e.target.result);
//         };
//         reader.readAsDataURL(input.files[0]);
//     }
// }

// function logoreadURL(input) {
//     if (input.files && input.files[0]) {
//         var reader = new FileReader();
//         reader.onload = function(e) {
//             $(input).closest(".edit-profile-wrapper").find('img').attr('src', e.target.result);
//         };
//         reader.readAsDataURL(input.files[0]);
//     }
// }
function imagesPreview(input, placeToInsertImagePreview) {
    if (input.files) {
        var filesAmount = input.files.length;

        for (i = 0; i < filesAmount; i++) {
            var reader = new FileReader();

            reader.onload = function(event) {
                $(placeToInsertImagePreview).append("<div class='recipe-card-img'><img src='"+ event.target.result +"'</div>");
            }

            reader.readAsDataURL(input.files[i]);
        }
    }
};


function gcdRound(width, height, newHeight, newWidth) {
    var ratioHeight = height / width * newWidth;
    return newWidth+":"+Math.round(ratioHeight);
}
function gcd(width, height, newHeight, newWidth) {
    var ratioHeight = height / width * newWidth;
    return newWidth+":"+ratioHeight;
}