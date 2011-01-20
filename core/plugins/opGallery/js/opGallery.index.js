$(document).ready(function() {
    $('.opProdImageThumb').each(function(index) {
        $(this).wrap('<a href="?i=' + index + '"></a>');
    });

    $('#albumCategoryList').categoryCollapse();
});