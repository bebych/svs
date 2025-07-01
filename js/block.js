(function(blocks, element, components, editor) {
    var el = element.createElement;
    var TextControl = components.TextControl;
    var InspectorControls = editor.InspectorControls;
    var PanelBody = components.PanelBody;

    blocks.registerBlockType('smart-video-switcher/video-block', {
        title: 'Smart Video',
        icon: 'video-alt3',
        category: 'embed',
        attributes: {
            youtubeUrl: { type: 'string', default: '' },
            vkUrl: { type: 'string', default: '' },
            width: { type: 'string', default: '640' },
            height: { type: 'string', default: '360' }
        },
        
        edit: function(props) {
            var attributes = props.attributes;
            
            return [
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Video Settings' },
                        el(TextControl, {
                            label: 'YouTube URL',
                            value: attributes.youtubeUrl,
                            onChange: (newVal) => props.setAttributes({ youtubeUrl: newVal }),
                        }),
                        el(TextControl, {
                            label: 'VK URL',
                            value: attributes.vkUrl,
                            onChange: (newVal) => props.setAttributes({ vkUrl: newVal }),
                        }),
                        el(TextControl, {
                            label: 'Width',
                            value: attributes.width,
                            onChange: (newVal) => props.setAttributes({ width: newVal }),
                            type: 'number'
                        }),
                        el(TextControl, {
                            label: 'Height',
                            value: attributes.height,
                            onChange: (newVal) => props.setAttributes({ height: newVal }),
                            type: 'number'
                        })
                    )
                ),
                el('div', { className: props.className }, 'Smart Video Switcher Block. Enter URLs in the settings sidebar.')
            ];
        },
        
        save: function(props) {
            var attributes = props.attributes;
            // === ИСПРАВЛЕНИЕ 3: Замена префикса шорткода ===
            var shortcode = '[ssvy_video';
            
            if (attributes.youtubeUrl) shortcode += ' youtube_url="' + attributes.youtubeUrl + '"';
            if (attributes.vkUrl) shortcode += ' vk_url="' + attributes.vkUrl + '"';
            if (attributes.width) shortcode += ' width="' + attributes.width + '"';
            if (attributes.height) shortcode += ' height="' + attributes.height + '"';
            shortcode += ']';
            
            return el('div', null, shortcode);
        }
    });
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.editor);
