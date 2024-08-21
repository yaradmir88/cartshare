window.onload = function() {

    //init modal
    var modal = new RModal(document.getElementById('rmodal'), {
        afterOpen: function() {
            BX.ajax.runComponentAction("radmir:cartshare", "getLink", {
                mode: "ajax",
                data: {}
            }).then(function (response) {
                if (response.status == 'success') {
                    document.getElementById('cart-link').value = response.data.link;
                }
            });
        },
        beforeClose: function(next) {
            document.getElementById('cart-link').value = "";
            next();
        },
        dialogClass: 'rmodal-dialog',
        escapeClose: true,
        dialogOpenClass: '',
        dialogCloseClass: '',
        closeTimeout: 0
    });

    window.modal = modal;

    //add events
    document.addEventListener('keydown', function(ev) {
        modal.keydown(ev);
    }, false);

    document.getElementById('rmodal-show').addEventListener("click", function(ev) {
        ev.preventDefault();
        modal.open();
    }, false);

    document.getElementById('rmodal-close').addEventListener("click", function(ev) {
        ev.preventDefault();
        modal.close();
    }, false);

    document.getElementById('copy-button').addEventListener("click", function(event) {
        copyTextToClipboard(document.getElementById('cart-link'));
    });
}


//deprecated method for old browsers and http scheme
function fallbackCopyTextToClipboard(element) {

    element.focus();
    element.select();

    try {
        var successful = document.execCommand("copy");
        var msg = successful ? "successful" : "unsuccessful";
        console.log("Fallback: Copying text command was " + msg);
    } catch (err) {
        console.error("Fallback: Oops, unable to copy", err);
    }

    document.body.removeChild(textArea);
}

//new method for newest browsers and https scheme
function copyTextToClipboard(element) {
    
    if (!navigator.clipboard) {
        
        fallbackCopyTextToClipboard(element);
        return;
    }

    let text = element.value;

    navigator.clipboard.writeText(text).then(
        function() {
        console.log("Async: Copying to clipboard was successful!");
        },
        function(err) {
        console.error("Async: Could not copy text: ", err);
        }
    );
}