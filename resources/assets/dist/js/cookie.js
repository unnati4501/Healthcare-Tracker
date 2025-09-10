// Age restriction popuo js
$(document).ready(function() {
    createCookie("laravel_cookie_allowed", 1, 365);
    if (readCookie("laravel_cookie_consent") == "1") {
        $('#cookieModal').hide();
        $(".modal-backdrop").remove();
        $("body").removeClass("overflow-hidden modal-open agree-modal");
        $("html").css("overflow-y", 'auto');
        $(".cookieModal").removeClass("show").fadeOut();
        eraseCookie("laravel_cookie_allowed");
    } else {
        if (readCookie("laravel_cookie_allowed") != 1) {
            return;
        }
        $('<div class="modal-backdrop"></div>').appendTo(document.body);
        $(".cookieModal").addClass("show").fadeIn();
        $("body").addClass("overflow-hidden modal-open agree-modal");
        $("html").css("overflow-y", 'hidden');
        $(".cookie-accept").click(function() {
            $('#cookieModal').hide();
            $(".modal-backdrop").remove();
            $("body").removeClass("overflow-hidden modal-open agree-modal");
            $("html").css("overflow-y", 'auto');
            $(".cookieModal").removeClass("show").fadeOut();
        });
        eraseCookie("laravel_cookie_allowed");
        window.oncontextmenu = function() {
            return false;
        }
        $(document).keydown(function(event) {
            if (event.keyCode == 123) {
                return false;
            } else if ((event.ctrlKey && event.shiftKey && event.keyCode == 73) || (event.ctrlKey && event.shiftKey && event.keyCode == 74)) {
                return false;
            }
        });
    }
});

function getParameterByName(name, url) {
    if (!url) {
        url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return "";
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function createCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(";");
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == " ") c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}