var EntaseSettings = new function() {

    var $ = jQuery;

    this.Init = function() {
        $(document).ready(function() {
            EntaseSettings.PageLoad();
        });
    };

    this.PageLoad = function() {        
        for(var optgroup of $('._optgroup').get())
        {
            var $optgroup = $(optgroup);
            $optgroup.find('._btnChange').click(function() {
                EntaseSettings.ChangeSetting(this);
            });

            $optgroup.find('._btnCancel').click(function() {
                EntaseSettings.CancelChange(this);
            });

            $optgroup.find('._btnSave').click(function() {
                EntaseSettings.SaveSetting(this);
            });
        }
    };

    this.SaveSetting = function(sender) {
        var $optgroup = $(sender).closest('._optgroup');
        $optgroup.find('._input').attr('disabled', 'disabled');
        $optgroup.find('._btnChange').show();
        $optgroup.find('._btnSave').hide();
        $optgroup.find('._btnCancel').hide();

        var key = $optgroup.find('._input').data('field');
        var value = $optgroup.find('._input').val();
        
        var data = {};
        data[key] = value;

        this.Save(data);

        if ($optgroup.find('._input').data('type') == 'hidden')
        {
            var n = new String($optgroup.find('._input').val()).length;
            $optgroup.find('._input').val('*'.repeat(n));
        }

    };

    this.ChangeSetting = function(sender) {
        var $optgroup = $(sender).closest('._optgroup');
        $optgroup.find('._input').removeAttr('disabled');
        $optgroup.find('._input').data('oldval', $optgroup.find('._input').val());
        $optgroup.find('._btnChange').hide();
        $optgroup.find('._btnSave').show();
        $optgroup.find('._btnCancel').show();

        if ($optgroup.find('._input').data('type') == 'hidden')
            $optgroup.find('._input').val('');
    };

    this.CancelChange = function (sender) {
        var $optgroup = $(sender).closest('._optgroup');
        $optgroup.find('._input').attr('disabled', 'disabled');
        $optgroup.find('._input').val($optgroup.find('._input').data('oldval'));
        $optgroup.find('._btnChange').show();
        $optgroup.find('._btnSave').hide();
        $optgroup.find('._btnCancel').hide();
    };

    this.Save = function (data) {

        if (typeof data != 'object') 
            return;

        var data = $.extend({
            action: 'entase_settings'
        }, data);

        $.ajax({
            url: ajaxurl,
            method: 'post',
            dataType: "json",
            data: data,
            context: data,
            success: function(response) {
                if (response.status == 'ok') EntaseStatusMsg('Settings were updated.');
                else if (response.msg) EntaseStatusMsg(response.msg, 'error');
                else EntaseStatusMsg('Service unavailable.', 'error');
            },
            error: function(err) {
                EntaseStatusMsg('Service unavailable.', 'error');
            }
        });

    }
};
EntaseSettings.Init();