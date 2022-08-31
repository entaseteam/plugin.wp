<style>
    ._optgroup ._btnChange {
        vertical-align: bottom;
    }

    ._optgroup ._btnSave, 
    ._optgroup ._btnCancel {
        display: none;
    }
    ._optgroup ._btnCancel {
        background-color:#5c6f7e;
    }
    ._optgroup ._btnCancel:hover {
        background-color:#4a667c;
    }
</style>
<div class="wrap">
    <h1>Entase General Settings</h1>
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><label for="blogname">API Secret Key</label></th>
                <td>
                    <div class="_optgroup">
                        <input type="text" data-field="api_secret_key" data-type="hidden" value="{$api_sk}" disabled="disabled" class="_input regular-text">
                        <a href="javascript:void(0);" class="_btnChange page-title-action">Change</a>
                        <input type="button" class="_btnSave button button-primary button-large" value="Save" />
                        <input type="button" class="_btnCancel button button-primary button-large" value="Cancel" />
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="blogname">API Public Key</label></th>
                <td>
                    <div class="_optgroup">
                        <input type="text" data-field="api_public_key" data-type="hidden" value="{$api_pk}" disabled="disabled" class="_input regular-text">
                        <a href="javascript:void(0);" class="_btnChange page-title-action">Change</a>
                        <input type="button" class="_btnSave button button-primary button-large" value="Save" />
                        <input type="button" class="_btnCancel button button-primary button-large" value="Cancel" />
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="blogname">Productions link</label></th>
                <td>
                    <div class="_optgroup">
                        <input type="text" data-field="productions_slug" value="{$productionPosts.slug}" disabled="disabled" class="_input regular-text">
                        <a href="javascript:void(0);" class="_btnChange page-title-action">Change</a>
                        <input type="button" class="_btnSave button button-primary button-large" value="Save" />
                        <input type="button" class="_btnCancel button button-primary button-large" value="Cancel" />
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="blogname">Auto sync periodically (cron)</label></th>
                <td>
                    <div class="_optgroup">
                        <select data-field="enable_cron" class="_input regular-text" disabled="disabled">
                            <option value="disable" {#if !$enable_cron}selected{#end}>Disable</option>
                            <option value="enable" {#if $enable_cron}selected{#end}>Enable</option>
                        </select>
                        <a href="javascript:void(0);" class="_btnChange page-title-action">Change</a>
                        <input type="button" class="_btnSave button button-primary button-large" value="Save" />
                        <input type="button" class="_btnCancel button button-primary button-large" value="Cancel" />
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>