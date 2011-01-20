// JavaScript Document

$(document).ready(function() {
    $(".slides").cycle({
        fx:     'fade',
        speed:  400,
        timeout: 0,
        next: '.slideRight',
        prev: '.slideLeft'
    });
    $('#three-cols').equalHeights();
    $('#two-wide-cols').equalHeights();
    $('#two-bottom-cols').equalHeights();
});