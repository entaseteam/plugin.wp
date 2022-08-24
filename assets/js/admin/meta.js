var EntaseMeta = new function() {

    this.$ = jQuery;
    this.Init = function() {
        this.$(document).ready(function() {
            EntaseMeta.PageLoad();
        });
    };

    this.PageLoad = function() {
        var $ = this.$;

        $('._btnEntase_CopyValue').click(function() {
            var value = $(this).data('value');
            if (value)
            {
                var isShortcode = $(this).data('type') == 'shortcode';
                EntaseMeta.CopyToClipboard(isShortcode ? '[' + value + ']' : value);
            }
        });
    };

    this.CopyToClipboard = function (value) {
        // create hidden text element, if it doesn't already exist
        var targetId = "_hiddenCopyText_";
        var target = document.getElementById(targetId);
        if (!target) {
            var target = document.createElement("textarea");
            target.style.position = "absolute";
            target.style.left = "-9999px";
            target.style.top = "0";
            target.id = targetId;
            document.body.appendChild(target);
        }
        target.textContent = value;

        // select the content
        var currentFocus = document.activeElement;
        target.focus();
        target.setSelectionRange(0, target.value.length);

        // copy the selection
        var succeed;
        try {
            succeed = document.execCommand("copy");
        } catch (e) {
            succeed = false;
        }
        // restore original focus
        if (currentFocus && typeof currentFocus.focus === "function") {
            currentFocus.focus();
        }

        target.textContent = "";
        target.remove();

        return succeed;
    };

};
EntaseMeta.Init();