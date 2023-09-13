var Skins = new function () {

    var $ = jQuery;
    var skinBuilder = null;

    this.Init = function() {
        $(document).ready(() => { this.PageLoad(); });
    };

    this.PageLoad = function() {
        this.$pnlSkinEdit = $('._pnlSkinEdit').clone();
        $('#btnAddSkin').click(() => { this.AddSkin(); });
        $('._btnEditSkin').click((e) => { 
            var $sender = $(e.target);
            var data = JSON.parse(atob($sender.data('skin')));
            this.AddSkin(data);
        });
        $('._btnDeleteSkin').click((e) => { 
            var $sender = $(e.target);            
            this.DeleteSkin($sender.data('id'));
        });
    };

    this.EmptyPlot = function() {
        $('._pnlSkinPlot').html('');
        
        var $pnlSkin = this.$pnlSkinEdit.clone().show();
        $('._pnlSkinPlot').append($pnlSkin);        
    };

    this.AddSkin = function(data={}) {

        var skinData = {
            id: new Date().getTime(),
            name: 'New skin',
            widget: 'events',
            template: []
        };
        $.extend(skinData, data);

        this.EmptyPlot();
        
        $('._txtSkinName').val(skinData.name);
        $('._ddlWidget').val(skinData.widget);
        this.skinBuilder = new SkinsBuilder('._pnlSkinEdit', skinData.template, {
            onChange: (builder) => {
                var template = builder.ExportTemplate();
                var $form = $('<form action="/entase/previewskin" method="post" target="skinpreview"><input name="template" type="hidden"><input name="widget" type="hidden"></form>');
                $form.find('[name=template]').val(JSON.stringify(template));
                $form.find('[name=widget]').val($('._ddlWidget').val());
                $form.appendTo('body').get(0).submit();
                $form.remove();
                
            }
        });

        $('._ddlWidget').change((e) => { this.skinBuilder.SetFieldOptions(e.target.value) });      
        

        var $btnSaveSkin = $('._btnSaveSkin');
        $btnSaveSkin.data('id', skinData.id).click((e) => {
            
            var $sender = $(e.target);
            var data = {
                action: 'entase_updateskin',
                id: $sender.data('id'),
                name: $('._txtSkinName').val(),
                widget: $('._ddlWidget').val(),
                template: this.skinBuilder.ExportTemplate()
            };
            
            $.ajax({
                url: ajaxurl,
                method: 'post',
                dataType: "json",
                data: data,
                context: data,
                success: function(response) {
                    if (response.status == 'ok') {
                        EntaseStatusMsg('Skins were updated.');
                        setTimeout(() => { location.reload(); }, 1000);
                    }
                    else if (response.msg) EntaseStatusMsg(response.msg, 'error');
                    else EntaseStatusMsg('Service unavailable.', 'error');
                },
                error: function(err) {
                    EntaseStatusMsg('Service unavailable.', 'error');
                }
            });
        });
    };

    this.DeleteSkin = function (id) {

        if (!confirm('Are you sure you want to delete this skin?'))
            return;

        var data = {
            action: 'entase_deleteskin',
            id : id
        };

        $.ajax({
            url: ajaxurl,
            method: 'post',
            dataType: "json",
            data: data,
            context: data,
            success: function(response) {
                if (response.status == 'ok') {
                    EntaseStatusMsg('Skin was deleted.');
                    setTimeout(() => { location.reload(); }, 1000);
                }
                else if (response.msg) EntaseStatusMsg(response.msg, 'error');
                else EntaseStatusMsg('Service unavailable.', 'error');
            },
            error: function(err) {
                EntaseStatusMsg('Service unavailable.', 'error');
            }
        });
    };
    

};
Skins.Init();