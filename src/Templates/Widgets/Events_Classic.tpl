<div class="event_items">
    {#each $items as $item}
        <div class="event_item">
            <a href="{$item.url}" {#if $item.allowbook}class="entase_book" rel="{$item.entase_id}"{#end}>
                <div>
                    {#each $item.fields as $field}
                        <div class="event_{$field.key}">{$field.val}</div>
                    {#end}
                </div>
            </a>
        </div>
    {#end}
</div>