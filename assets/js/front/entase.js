var EntaseWP = new function() {
    
    var $ = jQuery;

    this.client = null;

    this.Init = function() {
        $(document).ready(function() {
            EntaseWP.PageLoad();
        });
    };

    this.PageLoad = function() {
        this.client = new EntaseClient();

        $('body').on('click', '.entase_book', [], function() {
            var eventID = $(this).data('event') || $(this).attr('rel');
            console.log(eventID);
            EntaseWP.BookEvent(eventID);
        });
    };

    this.BookEvent = function(eventID) {
        this.client.BookEvent(eventID);
    };
};
EntaseWP.Init();