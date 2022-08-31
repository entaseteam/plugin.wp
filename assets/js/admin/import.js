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

        $('#btnEntase_Import').click(function() {
            if (!EntaseImport.importing) {
                var html = $(this).html();
                $(this).html(html + '...');
                EntaseImport.DoImport($(this).data('role'));
            }
        });
    };

    this.DoImport = function(role) {
        this.importing = true;

        var data = {
            action: 'entase_import',
            role: role
        };

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
                        EntaseImport.DoImport(this.data.role);
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