var EntaseStatusMsg = function(msg, style='success') {

    var $ = jQuery;
    var topValue = 50;
    for(var box of $('.entase_status_msg').get()) {
        topValue += $(box).outerHeight() + 10;
    }

    var $box = $('<div class="entase_status_msg"></div>');
    $box
        .html(msg)
        .addClass(style)
        .css('top', topValue)
        .prependTo('body');

    setTimeout(function($box) {
        $box.fadeOut(1000, function() { $(this).remove();});
    }, 2000, $box);

};