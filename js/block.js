( function( blocks, element, components ) {
    var el = element.createElement;
    var TextControl = components.TextControl;
    var NumberControl = components.TextControl;

    blocks.registerBlockType( 'smart-video-switcher/video-block', {
        title: 'Smart Video',
        icon: 'video-alt3',
        category: 'embed',
        attributes: {
            youtubeUrl: {
                type: 'string',
                default: ''
            },
            vkUrl: {
                type: 'string',
                default: ''
            },
            width: {
                type: 'string',
                default: '640'
            },
            height: {
                type: 'string',
                default: '360'
            }
        },
        
        edit: function( props ) {
            var attributes = props.attributes;
            
            function onChangeYoutubeUrl( newUrl ) {
                props.setAttributes( { youtubeUrl: newUrl } );
            }
            
            function onChangeVkUrl( newUrl ) {
                props.setAttributes( { vkUrl: newUrl } );
            }
            
            function onChangeWidth( newWidth ) {
                props.setAttributes( { width: newWidth } );
            }
            
            function onChangeHeight( newHeight ) {
                props.setAttributes( { height: newHeight } );
            }
            
            return el( 'div', { className: props.className },
                el( TextControl, {
                    label: 'YouTube URL',
                    value: attributes.youtubeUrl,
                    onChange: onChangeYoutubeUrl,
                } ),
                el( TextControl, {
                    label: 'VK URL',
                    value: attributes.vkUrl,
                    onChange: onChangeVkUrl,
                } ),
                el( NumberControl, {
                    label: 'Ширина',
                    value: attributes.width,
                    onChange: onChangeWidth,
                    type: 'number'
                } ),
                el( NumberControl, {
                    label: 'Высота',
                    value: attributes.height,
                    onChange: onChangeHeight,
                    type: 'number'
                } )
            );
        },
        
        save: function( props ) {
            var attributes = props.attributes;
            var shortcode = '[svs_video';
            
            if ( attributes.youtubeUrl ) {
                shortcode += ' youtube_url="' + attributes.youtubeUrl + '"';
            }
            if ( attributes.vkUrl ) {
                shortcode += ' vk_url="' + attributes.vkUrl + '"';
            }
            if ( attributes.width ) {
                shortcode += ' width="' + attributes.width + '"';
            }
            if ( attributes.height ) {
                shortcode += ' height="' + attributes.height + '"';
            }
            shortcode += ']';
            
            return el( 'div', null, shortcode );
        }
    } );
} )( window.wp.blocks, window.wp.element, window.wp.components ); 