<table style="width:100%">
    <tr>
        <th>Property</th>
        <th>Current Value</th>
        <th>Shortcode</th>
        <th>&nbsp;</th>
    </tr>
    {#each $proprties as $prop}
        <tr>
            <td>{$prop.name}</td>
            <td>{$prop.value}</td>
            <td>{$prop.shortcode}</td>
            <td>
                <a href="javascript:void(0);" class="_btnEntase_CopyShortcode page-title-action" data-shortcode="{$prop.value}">Copy Value</a>
                <a href="javascript:void(0);" class="_btnEntase_CopyShortcode page-title-action" data-shortcode="{$prop.shortcode}">Copy Shortcode</a>
            </td>
        </tr>
    {#end}
</table>