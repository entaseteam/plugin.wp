var SkinsBuilder = function (container, template, opts)
{
    var $ = jQuery;
    this.$container = $(container);
    this.$addBtn = $(container).find('._pnlAddContainer');
    this.$element = $(container).find('._pnlElement').remove().clone();

    this.widgetFields = {
        events: {
            production_post_title: '[Production] Post title',
            production_post_content: '[Production] Post content',
            production_post_feature_image: '[Production] Post feature image',
            entase_title: '[Production] Title',
            entase_story: '[Production] Story',
            entase_photo_poster: '[Production] Photo - Poster',
            entase_photo_og: '[Production] Photo - OG',

            // From Event
            post_title: '[Event] Post title',
            post_content: '[Event] Post content',
            post_feature_image: '[Event] Post feature image',
            entase_dateStart: '[Event] Start date - Full',
            entase_dateonly: '[Event] Start date - Date only',
            entase_timeonly: '[Event] Start date - Time only',
            entase_location_countryCode: '[Event] Location - Country code',
            entase_location_countryName: '[Event] Location - Country name',
            entase_location_cityName: '[Event] Location - City name',
            entase_location_postCode: '[Event] Location - Post code',
            entase_location_address: '[Event] Location - Address',
            entase_location_placeName: '[Event] Location - Place name',
            entase_location_lat: '[Event] Location - Latitude',
            entase_location_lng: '[Event] Location - Longitude',

            // Meta
            meta_key: 'Meta value',
        },
        productions: {
            post_title: 'Post title',
            post_content: 'Post content',
            post_feature_image: 'Post feature image',
            post_tags: 'Post tags',
            entase_title: '[Production] Title',
            entase_story: '[Production] Story',
            entase_photo_poster: '[Production] Photo poster',
            entase_photo_og: '[Production] Photo OG',
            multisource_image: 'Multi-source image',

            meta_key: 'Meta value',
        }
    };

    this.options = {
        onChange: null
    };
    $.extend(this.options, opts);

    this.BuildElements = function() {
        this.AssignEvents(this.$container);
        EnableElementReorder(this.$container, this);
        this.ImportTemplate(template);
        this.OnChange();
    };

    this.AssignEvents = function($container) {

        $container.find('._pnlAddContainer').click((e) => {
            this.AddElement(e.target);
            this.OnChange();
        });

        $container.find('._btnHeadAction').click((e) => {
            var $sender = $(e.target);
            var type = $sender.data('type');
            var $pnlElementType = $sender.closest('._pnlElement').find('._pnlElementType');
            var $pnlElementHead = $sender.closest('._pnlElement').find('._pnlElementHead');
            var $txtClass = $sender.closest('._pnlElement').find('._txtClass');
            
            $pnlElementType.hide();
            $pnlElementHead.hide();
            $txtClass.hide();

            $pnlElementType.filter('[data-type=' + type + ']').show();
            $txtClass.filter('[data-type=' + type + ']').show();

            this.OnChange();
        });

        $container.find('._btnRemoveElement').click((e) => {
            $(e.target).closest('._pnlElement').remove();
            this.OnChange();
        });

        $container.find('._ddlField').change((e) => {
            var $sender = $(e.target);
            var $pnlElementType = $sender.closest('._pnlElementType');
            if ($sender.val() == 'meta_key') $pnlElementType.find('._pnlMetaField').show();
            else $pnlElementType.find('._pnlMetaField').hide();

            this.OnChange();
        });

        $container.find('._txtMetaKey, ._txtCustomClass').blur(() => { this.OnChange(); });
        $container.find('._ddlMetaContext').change(() => { this.OnChange(); });
    };

    this.SetFieldOptions = function(widget, container) {
        var $container = container ? $(container) : $('._ddlField');
        var options = this.widgetFields[widget] ?? null;
        if (options != null)
        {
            $container.html('');
            for (var key in options)
            {
                var val = options[key];
                $('<option />').val(key).text(val).appendTo($container);
            }
        }
    };

    this.AddElement = function(sender, elementData) {
        var $sender = $(sender);
        var $element = this.$element.clone();
        
        $sender.before($element);
        $element.find('._pnlElementType[data-type=group]').append(this.$addBtn.clone());
        $element.find('*').attr('draggable', 'false');
        this.SetFieldOptions($('._ddlWidget').val(), $element.find('._ddlField'));
        this.AssignEvents($element);

        var data = {
            type: null,
            name: '',
            cssClass: '',
            meta: {
                field: '',
                context: ''
            }
        };
        $.extend(data, elementData);
        
        if (data.type != null)
        {
            $element.find('._btnHeadAction[data-type=' + data.type + ']').click();
            if (data.type == 'field')
            {
                $element.find('._ddlField').val(data.name).change();
                if (data.name == 'meta_key')
                {
                    console.log(data.meta);
                    $element.find('._txtMetaKey').val(data.meta.key);
                    $element.find('._ddlMetaContext').val(data.meta.context);
                }
            }
            else if (data.type == 'group')
            {
                $element.find('._txtClass').val(data.cssClass);
            }
        }
        

        return $element;
    };

    this.OnChange = function() {
        if (typeof this.options.onChange == 'function')
            this.options.onChange(this);
    };

    this.ImportTemplate = function(template, sender) {
        if (!Array.isArray(template))
            return;

        var $sender = sender ? $(sender) : $('._pnlAddContainer');
        for (var elementData of template)
        {
            if (elementData.type == 'field')
            {
                this.AddElement($sender, elementData);
            }
            else if (elementData.type == 'group')
            {
                var $element = this.AddElement($sender, elementData);
                this.ImportTemplate(elementData.elements, $element.find('._pnlAddContainer'));
            }
        }
    };
    
    this.ExportTemplate = function(parent) {
        var template = [];
        if (typeof parent == 'undefined')
            parent = $('._pnlElements');

        var elements = $(parent).children('._pnlElement').get();
        for (let elementNode of elements)
        {
            var $elementNode = $(elementNode);
            var $elementType = $elementNode.children('._pnlElementType:visible');
            var type = $elementType.attr('data-type');
            if ($elementType.length < 1 || typeof type == 'undefined') continue;

            var element = {type: type};
            if (type == 'field')
            {
                element.name = $elementType.find('._ddlField').val();
                if (element.name == 'meta_key') {
                    element.meta = {
                        key: $elementType.find('._txtMetaKey').val(),
                        context: $elementType.find('._ddlMetaContext').val()
                    }
                }
            }
            else if (type == 'group')
            {
                element.cssClass = $elementNode.find('._txtClass').val();
                element.elements = this.ExportTemplate($elementType.length > 0 ? $elementType : null);
            }

            template.push(element);
        }

        return template;
    };

    this.BuildElements();
};

var EnableElementReorder = function(container, builder) {

    (function (container, builder) {
        var $ = jQuery;
        var $container = $(container);

        const dragList = $container.get(0);
        var draggedItem = null;

        // Add event listeners for drag and drop events
        dragList.addEventListener('dragstart', handleDragStart);
        dragList.addEventListener('dragover', handleDragOver);
        dragList.addEventListener('drop', handleDrop);

        // Drag start event handler
        function handleDragStart(event) {
            draggedItem = event.target;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/html', draggedItem.innerHTML);
            event.target.style.opacity = '0.5';
        }

        // Drag over event handler
        function handleDragOver(event) {
            //console.log('over', draggedItem);
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            const targetItem = event.target;
            if (targetItem !== draggedItem && targetItem.classList.contains('drag-item')) {
                const $targetParent = $(targetItem).parent();
                $targetParent.find('.drag-item').css({'border-bottom': '', 'border-top': ''});
                $targetParent.find('.drag-item').removeAttr('data-target');

                const boundingRect = targetItem.getBoundingClientRect();
                const offset = boundingRect.y + (boundingRect.height / 2);
                if (event.clientY - offset > 0) {                    
                    targetItem.style.borderBottom = 'solid 2px #000';
                    targetItem.style.borderTop = '';

                    $(targetItem).attr('data-target', 'true');
                    $(targetItem).attr('data-position', 'after');

                } else {
                    targetItem.style.borderTop = 'solid 2px #000';
                    targetItem.style.borderBottom = '';
                    
                    $(targetItem).attr('data-target', 'true');
                    $(targetItem).attr('data-position', 'before');
                }
            }
        }

        // Drop event handler
        function handleDrop(event) {
            
            event.preventDefault();

            $(draggedItem).css({opacity: 1});
            let $target = $('.drag-item[data-target=true]');
            if ($target.length < 1) return;

            let position = $target.attr('data-position');

            if (position == 'before') $target.before(draggedItem);
            else if (position == 'after') $target.after(draggedItem);

            $target.removeAttr('data-target');
            $target.removeAttr('data-position');
            $target.css({'border-bottom': '', 'border-top': ''});

            builder.OnChange();
        }

    })(container, builder);
};