$(document).ready(function() {
    $('#opPlugin').bind('change', function() {
        window.location = '/admin/opTranslation/translate/' + $('#tID').attr('value') + '/' + $(this).attr('value');
    });
});