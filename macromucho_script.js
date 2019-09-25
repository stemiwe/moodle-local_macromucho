function copyToClipboard() {
    var textAreas = document.getElementsByTagName("textarea");

    for (var i = 0; i < textAreas.length; i++) {
        if (textAreas[i].offsetParent) {
            copyText = textAreas[i];
        }
    }

    copyText.select();
    copyText.setSelectionRange(0, 99999); /*For mobile devices*/
    document.execCommand("copy");    
}
