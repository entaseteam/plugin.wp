var EntaseImport = new function() {
    this.$ = jQuery;
    this.importing = false;

    this.Init = function() {
        this.$(document).ready(function() {
            EntaseImport.PageLoad();
        });
    };

    this.PageLoad = function() {
        var $ = this.$;

        $('#btnEntase_Import, #btnEntase_Sync').click(function() {
            if (!EntaseImport.importing) {
                var html = $(this).html();
                $(this).html(html + '...');
                EntaseImport.DoImport($(this).data('role'), $(this).data('procedure'));
            }
        });
    };

    this.DoImport = function(role, procedure, fromID) {
        this.importing = true;

        if (typeof fromID == 'undefined') 
            fromID = false;

        var data = {
            action: 'entase_import',
            role: role,
            procedure: procedure
        };

        if (fromID) 
            data.fromID = fromID;

        this.$.ajax({
            url: ajaxurl,
            method: 'post',
            dataType: "json",
            data: data,
            context: data,
            success: function(response) {
                if (response.status == 'ok') {
                    EntaseStatusMsg(response.imported + ' records were imported.');
                    if (response.hasMore)
                    {
                        EntaseStatusMsg('Stay on this page! Import continues...', 'info');
                        EntaseImport.DoImport(this.role, this.procedure, response.lastID ?? false);
                    }
                    else window.location.reload();
                }
                else if (response.msg) EntaseStatusMsg(response.msg, 'error');
                else EntaseStatusMsg('Service unavailable.', 'error');
            },
            error: function(err) {
                EntaseStatusMsg('Import failed.', 'error');
            }
        });
    }

};
EntaseImport.Init();