$(document).ready(function() {
   $('#folderList').jCollapse();
   $('#sitemap').opAccordion();
});

function addImage(id, element) {
    var funcName = 'parent.opImageDialogAddImage_' + element + '(' + id + ');';
    eval(funcName);
}