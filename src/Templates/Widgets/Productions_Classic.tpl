<div class="production_items">
    {#each $items as $item}
        <div class="production_item">
            <a href="{$item.url}">
                <div>
                    {#each $item.fields as $field}
                        <div class="production_{$field.key}">{$field.val}</div>
                    {#end}
                </div>
            </a>
        </div>
    {#end}
</div>