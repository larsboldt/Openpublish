$(document).ready(function() {
    var usrBoxHasFocus = false;
    $('#username').focus(function() {
        usrBoxHasFocus = true;
    });
    $('#username').blur(function() {
        usrBoxHasFocus = false;
    });
    $(document).keydown(function(event) {
        switch (event.keyCode) {
            case 13:
                if (! usrBoxHasFocus) {
                    $('#adminForm').submit();
                }
                break;
        }
    });
    $("body").addClass("loginScreen");
});