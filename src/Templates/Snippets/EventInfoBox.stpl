<table style="width:100%;text-align:left">
    <tr>
        <th>Property</th>
        <th>Current Value</th>
        <th>Shortcode</th>
        <th>&nbsp;</th>
    </tr>
    {#each $properties as $prop}
        <tr>
            <td>{$prop.name}</td>
            <td>{$prop.value}</td>
            <td>{$prop.shortcode}</td>
            <td style="text-align:right">
                {#if $prop.value}
                    <a href="javascript:void(0);" class="_btnEntase_CopyValue page-title-action" data-value="{$prop.value}" data-type="value">Copy Value</a>
                {#end}
                {#if $prop.shortcode}
                    <a href="javascript:void(0);" class="_btnEntase_CopyValue page-title-action" data-value="{$prop.shortcode}" data-type="shortcode">Copy Shortcode</a>
                {#end}
            </td>
        </tr>
    {#end}
</table>