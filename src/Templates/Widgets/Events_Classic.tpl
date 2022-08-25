<div class="event_items">
    {#each $items as $item}
        <div class="event_item">
            <div>
                <a href="{$item.url}" {#if $item.allowbook}class="entase_book" rel="{$item.entase_id}"{#end}>
                    {#if $item.entase_photo_poster}<div class="event_entase_photo_poster">{$item.entase_photo_poster}</div>{#endif}
                    {#if $item.entase_photo_og}<div class="event_entase_photo_og">{$item.entase_photo_og}</div>{#endif}
                    {#if $item.post_feature_image}<div class="event_post_feature_image">{$item.post_feature_image}</div>{#endif}
                    {#if $item.post_title}<div class="event_post_title">{$item.post_title}</div>{#endif}
                    {#if $item.entase_title}<div class="event_entase_title">{$item.entase_title}</div>{#endif}
                    {#if $item.post_content}<div class="event_post_content">{$item.post_content}</div>{#endif}
                    {#if $item.entase_story}<div class="event_entase_story">{$item.entase_story}</div>{#endif}
                    <div>
                        {#each $item.fields as $field}
                            <div class="event_{$field.key}">{$field.val}</div>
                        {#end}
                    </div>
                </a>
            </div>
            {#if $item.entase_book}<div>{$item.entase_book}</div>{#endif}
        </div>
    {#end}
</div>