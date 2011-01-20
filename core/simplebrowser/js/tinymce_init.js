var FileBrowserDialogue = {
    init : function () {

    },
    mySubmit : function (URL) {
        var win = tinyMCEPopup.getWindowArg("window");
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;
        if (typeof(win.ImageDialog) != "undefined")
        {
            if (win.ImageDialog.getImageData) win.ImageDialog.getImageData();
            if (win.ImageDialog.showPreviewImage) win.ImageDialog.showPreviewImage(URL);
        }
        tinyMCEPopup.close();
    }
}
tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);