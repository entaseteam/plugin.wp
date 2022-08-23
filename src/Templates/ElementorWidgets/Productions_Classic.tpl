<div class="production_items">
    {#each $items as $item}
        <div class="production_item">
            {#each $item as $field}
                <div class="production_{$field.key}">{$field.val}</div>
            {#end}
        </div>
    {#end}
</div>