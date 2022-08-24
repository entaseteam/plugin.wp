var EntaseClient = function(pk) {

    var $ = jQuery;

    this.dataTable = null;
    this.args = {
        parentScreenCenter: {
            top: window.screenTop + (window.innerHeight / 2),
            left: window.screenLeft + (window.innerWidth / 2)
        }
    };
    this.allowedCallables = ['refocus'];
    this.IsAppBrowser = function () {
        var ua = navigator.userAgent || navigator.vendor || window.opera;
        var identifiers = ['fban', 'fbav', 'instagram'];
        var usrAgent = ua.toLowerCase();
        for (var i in identifiers) {
            if (usrAgent.indexOf(identifiers[i]) >= 0) return true;
        }

        return false;
    };

    this.booking = {
        Open: function (settings) {

            this.client.ShowBGWrap();
            //Program.PostMessage('sbgwrap');

            if (typeof settings != 'object') settings = {};

            var width = 800;
            var height = 1000;

            var left = this.client.args.parentScreenCenter.left - (width / 2);
            var top = this.client.args.parentScreenCenter.top - (height / 2);

            // Fix height and vertical positioning on smaller resolutions
            if (screen.height < height + 140) {
                height = screen.height - 140;
                top = 0;
            }

            var url = 'https://www.entase.bg/book?eid=' + settings.eventID;
            if (this.client.IsAppBrowser()) url += '&ref=' + settings.ref

            if (navigator.userAgent.indexOf('Opera Mini') > -1) {
                window.open(url, '_blank');
                return;
            }

            this.window = window.open(url, 'entasebooking', 'toolbar=no,location=no,status=no,menubar=no,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',screenX=' + left + ',screenY=' + top);
            this.closeHandler = setInterval(function (self, settings) {
                if (self.window.closed) {
                    self.client.HideBGWrap();
                    clearInterval(self.closeHandler);
                }
                /*else if (window.origin == self.window.origin) {
                    if (typeof self.window.settings == 'undefined')
                        self.window.settings = settings;
                    else if (self.window.settings.ref == '')
                        self.window.settings.ref = settings.ref
                }*/
            }, 500, this, settings);
        },
        client: this,
        window: null,
        closeHandler: 9
    };

    this.Dispose = function() {
        if (window.addEventListener) window.removeEventListener("message", this.OnMessage, false);
        else if (window.attachEvent) window.detachEvent("onmessage", this.OnMessage, false);
    };

    this.BookEvent = function(eventID) {
        this.booking.Open({
            eventID: eventID,
            ref: btoa(document.location.href).replace(/=/ig, '')
        });
    };

    this.CloseBooking = function () {
        this.booking.window.close();
    };

    this.refocus = function(args) {
        if (args.close) {
            this.CloseBooking();
        }
        else this.booking.window.focus();
    };

    this.OnMessage = function (e) {
        var data = e.data;
        if (this.allowedCallables.includes(data.call)) {
            if (typeof (this[data.call]) == "function") {
                this[data.call].call(this, data.message);
            }
        }
    };

    if (window.addEventListener) window.addEventListener("message", this.OnMessage.bind(this), false);
    else if (window.attachEvent) window.attachEvent("onmessage", this.OnMessage.bind(this), false);
};




EntaseClient.prototype.HideBGWrap = function() {
    document.getElementById('entasentfcw').remove();
    document.getElementById('entasewrap').remove();
};
EntaseClient.prototype.ShowBGWrap = function() {
    var wrap = document.createElement('div');
    wrap.id = 'entasewrap';
    wrap.setAttribute('style', 'position:fixed;top:0;left:0;right:0;bottom:0; background-color: rgb(0 0 0 / 83%);z-index:9999;');

    var innerwrap = document.createElement('div');
    innerwrap.id = 'entasentfcw';
    innerwrap.setAttribute('style', 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;');

        var isFirefox = typeof InstallTrigger !== 'undefined';
        var msg = isFirefox ?
            ''
            + 'Резервацията се осъществява в нов прозорец от entase.<br />'
            + 'Можете да прекратите процеса като <a href="javascript:void(0);" onclick="rdrct(true)" style="color:#ee552b">кликнете тук</a>.' 
            :
            ''
            + 'Резервацията се осъществява в нов прозорец от entase.<br />'
            + 'Ако не виждате прозореца, моля <a href="javascript:void(0);" onclick="rdrct(false)" style="color:#ee552b">кликнете тук</a>.';
        
            var htmlsrc = ''
            + '<html lang="bg">'
            + '<head>'
            + '<meta charset="UTF-8" />'
            + '<link rel="preconnect" href="https://fonts.gstatic.com">'
            + '<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">'
            + '<style>'
            + ' body {'
            + '     font-family: Montserrat, sans-serif;'
            + '     font-size: 15px;'
            + '     line-height: 22px;'
            + '     margin: 0;'
            + '     padding: 0;'
            + '     color: white;'
            + '     overflow: hidden;'
            + '     height: 100%;'
            + '     min-height: 100%;'
            + '     box-sizing: border-box;'
            + ' }'
            + ' .section {'
            + '     margin: 0;'
            + '     display: flex;'
            + '     flex-direction: column;'
            + '     height: 100vh;'
            + '     justify-content: center;'
            + '     align-items: center;'
            + '     text-align: center;'
            + '}'
            + ' .logo {'
            + '     max-width: 150px;'
            + '     margin: auto;'
            + '     margin-bottom: 30px;'
            + ' }'
            + ' .msg {'
            + '     padding: 0 25px;'
            + ' }'
            + ' .closelinkbox {'
            + ' width: 25px;'
            + ' height: 25px;'
            + ' position: absolute;'
            + ' right: 30px;'
            + ' top: 30px;'
            + ' cursor: pointer;'
            + '}'
            + ' .closelink {'
            + ' display:block;'
            + ' transition: transform .25s, opacity .25s;'
            + '}'
            + ' .closelink:hover {'
            + '     transform: rotate(45deg);'
            + '}'
            + ' .closeicon_1,'
            + ' .closeicon_2  {'
            + '     height: 25px;'
            + '     width: 2px;'
            + '     background-color: white;'
            + '     display: block;'
            + '     margin: auto;'
            + ' }'
            + ' .closeicon_1 {'
            + '     transform: rotate(45deg);'
            + ' }'
            + ' .closeicon_2 {'
            + '     transform: rotate(90deg);'
            + ' }'
            + '</style>'
            + '</head>'
            + '<body onclick="rdrct(false)">'
            + ' <div class="closelinkbox">'
            + '     <a href="javascript:void(0);" onclick="rdrct(true)" class="closelink">'
            + '         <i class="closeicon_1">'
            + '             <i class="closeicon_2"></i>'
            + '         </i>'
            + '     </a>'
            + ' </div>'
            + ' <div class="section">'
            + '     <div>'
            + '         <div class="logo">'
            + '             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 315.64 425.15"><defs><style>.cls-1{fill:#ec4d24;}.cls-2{fill:#fff;}</style></defs><title>headline</title><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><g id="Artwork_9" data-name="Artwork 9"><g id="Group_89" data-name="Group 89"><g id="Group_87" data-name="Group 87"><path id="Path_272" data-name="Path 272" class="cls-1" d="M0,266.45H0l227.65-131.4a64.47,64.47,0,0,1,88,23.54L88,290A64.46,64.46,0,0,1,0,266.45Z"/><path id="Path_273" data-name="Path 273" class="cls-1" d="M268.22,0h0a64.46,64.46,0,0,1-23.53,88L105.14,168.52a64.44,64.44,0,0,1,23.53-88Z"/><path id="Path_274" data-name="Path 274" class="cls-1" d="M210.27,256.63h0a64.46,64.46,0,0,1-23.53,88L47.19,425.15a64.44,64.44,0,0,1,23.53-88Z"/></g><g id="Group_88" data-name="Group 88"><path id="Path_275" data-name="Path 275" class="cls-2" d="M107,107.15h12.3v53.1H107Z"/><path id="Path_276" data-name="Path 276" class="cls-2" d="M157.29,158.23a12.2,12.2,0,0,1-4.26,1.9,22.14,22.14,0,0,1-5.32.59q-7.34,0-11.24-3.67t-4-10.88V129.5h-6.26v-9.11h6.26v-9.93h11.83v9.93h10.17v9.11H144.28v16.56a5.55,5.55,0,0,0,1.3,4,5,5,0,0,0,3.78,1.42,7.58,7.58,0,0,0,4.73-1.54Z"/><path id="Path_277" data-name="Path 277" class="cls-2" d="M173.73,105.37a7.12,7.12,0,0,1,2,5.32,12.3,12.3,0,0,1-.36,3.08,38.91,38.91,0,0,1-1.53,4.49l-4.38,10.88H162l3.31-11.94a6.53,6.53,0,0,1-3-2.49,7.35,7.35,0,0,1-1.06-4,7,7,0,0,1,2.13-5.32,7.34,7.34,0,0,1,5.32-2,7.26,7.26,0,0,1,5,2Z"/><path id="Path_278" data-name="Path 278" class="cls-2" d="M186.26,159.65a25.87,25.87,0,0,1-7.68-3.19l3.9-8.52a24.13,24.13,0,0,0,6.62,2.84,27.31,27.31,0,0,0,7.57,1.07c5,0,7.45-1.23,7.45-3.67a2.59,2.59,0,0,0-2-2.48,29.87,29.87,0,0,0-6.27-1.3,60.13,60.13,0,0,1-8.28-1.78,12.63,12.63,0,0,1-5.67-3.43,9.88,9.88,0,0,1-2.37-7.1,11.18,11.18,0,0,1,2.25-6.86,14.48,14.48,0,0,1,6.5-4.61A27.76,27.76,0,0,1,198.44,119a39.14,39.14,0,0,1,8.63.94,24.24,24.24,0,0,1,7.1,2.6l-3.9,8.4a23.44,23.44,0,0,0-11.71-3.07,11.89,11.89,0,0,0-5.67,1.06,3.05,3.05,0,0,0-1.89,2.72,2.72,2.72,0,0,0,2,2.6,35.88,35.88,0,0,0,6.5,1.42,71.47,71.47,0,0,1,8.16,1.77,12.42,12.42,0,0,1,5.55,3.43,9.8,9.8,0,0,1,2.37,7,10.8,10.8,0,0,1-2.25,6.74,14.53,14.53,0,0,1-6.62,4.61,29.56,29.56,0,0,1-10.29,1.66A39.62,39.62,0,0,1,186.26,159.65Z"/><path id="Path_279" data-name="Path 279" class="cls-2" d="M28.74,237.23a20.07,20.07,0,0,1-8.16-7.45,21.62,21.62,0,0,1,0-21.64,20.07,20.07,0,0,1,8.16-7.45A25,25,0,0,1,40.45,198a22.7,22.7,0,0,1,11.23,2.72,16,16,0,0,1,7,7.69l-9.23,5a10,10,0,0,0-9.22-5.55,10.4,10.4,0,0,0-7.69,3.07,12.78,12.78,0,0,0,0,16.56,10.4,10.4,0,0,0,7.69,3.07,9.93,9.93,0,0,0,9.22-5.55l9.22,5a16.25,16.25,0,0,1-7,7.57,22.5,22.5,0,0,1-11.24,2.72,25.09,25.09,0,0,1-11.71-3Z"/><path id="Path_280" data-name="Path 280" class="cls-2" d="M106.55,198.56v40.8H95.32v-4.85a15.6,15.6,0,0,1-5.56,4,17.49,17.49,0,0,1-7.1,1.42q-8,0-12.77-4.61t-4.73-13.72V198.56H77v21.29q0,9.81,8.27,9.81a8.88,8.88,0,0,0,6.86-2.72q2.59-2.72,2.6-8.28v-20.1Z"/><path id="Path_281" data-name="Path 281" class="cls-2" d="M117.55,183.07h11.83v56.29H117.55Z"/><path id="Path_282" data-name="Path 282" class="cls-2" d="M169.58,237.36a12.23,12.23,0,0,1-4.25,1.89,22.14,22.14,0,0,1-5.32.59q-7.33,0-11.24-3.67t-4-10.88V208.5h-6.27v-9.11h6.27v-9.93h11.83v9.93h10.17v9.11H156.58v16.56a5.55,5.55,0,0,0,1.3,4,5,5,0,0,0,3.78,1.42,7.58,7.58,0,0,0,4.73-1.54Z"/><path id="Path_283" data-name="Path 283" class="cls-2" d="M217.37,198.56v40.8H206.13v-4.85a15.51,15.51,0,0,1-5.56,4,17.49,17.49,0,0,1-7.1,1.42q-8,0-12.77-4.61T176,221.62V198.56H187.8v21.29q0,9.81,8.28,9.81a8.82,8.82,0,0,0,6.85-2.72c1.74-1.81,2.6-4.57,2.6-8.27V198.56Z"/><path id="Path_284" data-name="Path 284" class="cls-2" d="M245.39,199.39a21.36,21.36,0,0,1,8.28-1.54v10.88h-2.6a10.9,10.9,0,0,0-7.92,2.84q-2.84,2.84-2.84,8.52v19.27H228.48v-40.8h11.36V204a13.18,13.18,0,0,1,5.55-4.61Z"/><path id="Path_285" data-name="Path 285" class="cls-2" d="M300.15,222.22H269.28a9.6,9.6,0,0,0,3.9,6,13.09,13.09,0,0,0,7.69,2.24,15.73,15.73,0,0,0,5.68-.94,13.7,13.7,0,0,0,4.61-3l6.27,6.86q-5.81,6.62-16.8,6.62a26.66,26.66,0,0,1-12.18-2.72,19.75,19.75,0,0,1-8.16-7.45A20.67,20.67,0,0,1,257.45,219a20.91,20.91,0,0,1,2.72-10.88,19.91,19.91,0,0,1,7.81-7.45,24.12,24.12,0,0,1,22-.12,18.81,18.81,0,0,1,7.57,7.33,22.06,22.06,0,0,1,2.72,11.24C300.27,219.22,300.23,220.24,300.15,222.22Zm-27.67-13.13a9.63,9.63,0,0,0-3.31,6.27h20.1a9.83,9.83,0,0,0-3.31-6.15,10,10,0,0,0-6.63-2.37,10.16,10.16,0,0,0-6.86,2.37Z"/><path id="Path_286" data-name="Path 286" class="cls-2" d="M89.05,316.47a12.3,12.3,0,0,1-4.26,1.9,22.14,22.14,0,0,1-5.32.59q-7.34,0-11.23-3.67t-4-10.88v-16.8H57.83v-9.1H64.1v-9.94H75.92v9.94H86.1v9.1H75.92v16.56a5.58,5.58,0,0,0,1.3,4A5,5,0,0,0,81,309.61a7.55,7.55,0,0,0,4.73-1.53Z"/><path id="Path_287" data-name="Path 287" class="cls-2" d="M96.27,270a6.37,6.37,0,0,1-.32-9l.32-.32a7.45,7.45,0,0,1,5.32-1.89,7.69,7.69,0,0,1,5.32,1.77,5.82,5.82,0,0,1,2,4.5,6.4,6.4,0,0,1-2,4.85A8.65,8.65,0,0,1,96.27,270Zm-.6,7.57H107.5v40.8H95.67Z"/><path id="Path_288" data-name="Path 288" class="cls-2" d="M183,281.46q4.5,4.5,4.5,13.49v23.3H175.62V296.84q0-4.84-2-7.22a7.08,7.08,0,0,0-5.68-2.36,8.38,8.38,0,0,0-6.62,2.72c-1.66,1.81-2.48,4.49-2.48,8v20.46H147V296.84q0-9.58-7.69-9.58a8.39,8.39,0,0,0-6.5,2.72q-2.48,2.72-2.48,8v20.46H118.5V277.56h11.35v4.73a15,15,0,0,1,5.56-3.9,19,19,0,0,1,7.21-1.42,17.54,17.54,0,0,1,7.81,1.66,13.88,13.88,0,0,1,5.56,5,16.69,16.69,0,0,1,6.38-5,20.12,20.12,0,0,1,8.4-1.78Q178.34,277,183,281.46Z"/><path id="Path_289" data-name="Path 289" class="cls-2" d="M238.18,301.33H207.31a9.6,9.6,0,0,0,3.9,6,13,13,0,0,0,7.69,2.25,15.49,15.49,0,0,0,5.68-.95,13.67,13.67,0,0,0,4.61-3l6.27,6.86q-5.81,6.62-16.8,6.62a26.66,26.66,0,0,1-12.18-2.72,19.81,19.81,0,0,1-8.16-7.45,20.56,20.56,0,0,1-2.83-10.88,20.82,20.82,0,0,1,2.83-10.76,19.84,19.84,0,0,1,7.81-7.45,24.12,24.12,0,0,1,22-.12,18.74,18.74,0,0,1,7.57,7.33,22,22,0,0,1,2.72,11.23Zm-27.67-13.12a9.63,9.63,0,0,0-3.31,6.27h20.1a9.83,9.83,0,0,0-3.31-6.15,10,10,0,0,0-6.63-2.37,10.23,10.23,0,0,0-6.86,2.25Z"/><path id="Path_290" data-name="Path 290" class="cls-2" d="M245.51,316.83a7.29,7.29,0,0,1-2.13-5.33,7,7,0,0,1,2.13-5.32,7.81,7.81,0,0,1,10.41,0,7,7,0,0,1,2.13,5.32,7.29,7.29,0,0,1-2.13,5.33A7.46,7.46,0,0,1,245.51,316.83Z"/></g></g></g></g></g></svg>'
            + '         </div>'
            + '         <div class="msg">'+msg+'</div>'
            + '     </div>'
            + ' </div>'
            + ' <script>function rdrct(close) { window.parent.postMessage({ call: "refocus", message: { close: close } }, "*"); }</script>'
            + '</body>'
            + '</html>';

        var notification = document.createElement('iframe');
        notification.id = 'entasentfcf';
        notification.setAttribute('style', 'width:100%;height:100%;border:0;');
        notification.src = 'data:text/html,' + encodeURIComponent(htmlsrc);

        document.body.appendChild(wrap);
        document.body.appendChild(innerwrap);
        innerwrap.appendChild(notification);
};