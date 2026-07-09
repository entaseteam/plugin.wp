var EntaseWP = new function() {
    
    var $ = jQuery;
    var config = (typeof window.entaseWPSettings === 'object' && window.entaseWPSettings !== null) ? window.entaseWPSettings : {};

    this.client = null;

    this.Init = function() {
        $(document).ready(function() {
            EntaseWP.PageLoad();
        });
    };

    this.PageLoad = function() {

        // Init Entase client
        var initClient = () => { this.client = new Entase({ pk: config.pk || null }); };
        if (typeof Entase != 'undefined') initClient();
        else window.addEventListener('entase:ready', initClient);
        

        $('body').on('click', '.entase_book', [], function() {
            var eventID = $(this).data('event') || $(this).attr('rel');
            EntaseWP.BookEvent(eventID);
        });
    };

    this.BookEvent = function(eventID) {
        if (!eventID || this.client === null || typeof this.client.book !== 'function') return;
        this.client.book(eventID, {});
    };
};
EntaseWP.Init();