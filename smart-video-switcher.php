<?php
/**
 * Plugin Name: Smart Switcher for VK and YouTube
 * Description: Автоматически подставляет видео с YouTube или VK в зависимости от геолокации пользователя.
 * Version: 1.9
 * Author: bebych
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

// Добавляем кнопку в редактор
function svs_add_tinymce_button() {
    // Проверяем права пользователя
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }
    
    // Проверяем, включен ли визуальный редактор
    if (get_user_option('rich_editing') == 'true') {
        add_filter('mce_external_plugins', 'svs_add_tinymce_plugin');
        add_filter('mce_buttons', 'svs_register_tinymce_button');
    }
}
add_action('admin_init', 'svs_add_tinymce_button');

// Регистрируем новую кнопку
function svs_register_tinymce_button($buttons) {
    array_push($buttons, 'svs_button');
    return $buttons;
}

// Добавляем JavaScript для кнопки
function svs_add_tinymce_plugin($plugin_array) {
    $plugin_array['svs_button'] = plugins_url('js/editor-button.js', __FILE__);
    return $plugin_array;
}

// Добавляем скрипты и стили для диалогового окна
function svs_enqueue_admin_scripts($hook) {
    if (!in_array($hook, array('post.php', 'post-new.php'))) {
        return;
    }

    // Регистрируем и подключаем скрипт для QuickTags кнопки
    wp_enqueue_script(
        'svs-quicktags',
        plugins_url('js/quicktags-button.js', __FILE__),
        array('quicktags'),
        '1.0',
        true
    );

    // Добавляем стили для диалогового окна
    wp_add_inline_style('wp-admin', '
        #svs-dialog {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 159000;
            min-width: 500px;
            max-width: 90%;
        }
        #svs-dialog::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: -1;
        }
        .svs-dialog-content {
            display: flex;
            flex-direction: column;
        }
        .svs-dialog-header {
            padding: 20px 20px 0;
            border-bottom: 1px solid #ddd;
        }
        .svs-dialog-header h2 {
            margin: 0 0 20px;
            padding: 0;
            font-size: 1.3em;
            font-weight: 600;
        }
        .svs-dialog-body {
            padding: 20px;
        }
        .svs-dialog-footer {
            padding: 15px 20px;
            background: #f5f5f5;
            border-top: 1px solid #ddd;
            text-align: right;
            border-radius: 0 0 5px 5px;
        }
        .svs-dialog-body p {
            margin: 0 0 15px;
        }
        .svs-dialog-body label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .svs-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .svs-dimensions {
            display: flex;
            gap: 20px;
        }
        .svs-dimensions p {
            flex: 1;
        }
        .svs-input-number {
            width: 100px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .svs-dialog-footer button {
            margin-left: 10px;
        }
        .svs-dialog-footer .button-large {
            padding: 0 20px;
            height: 40px;
            line-height: 38px;
        }
    ');
}
add_action('admin_enqueue_scripts', 'svs_enqueue_admin_scripts');

// Регистрация блока Gutenberg
function svs_register_block() {
    if (!function_exists('register_block_type')) {
        return;
    }
    wp_register_script(
        'smart-video-switcher-block',
        plugins_url('js/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components'),
        '1.9',
        true
    );
    register_block_type('smart-video-switcher/video-block', array(
        'editor_script' => 'smart-video-switcher-block',
    ));
}
add_action('init', 'svs_register_block');

// Определение страны по IP
function svs_get_user_country() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $ip = filter_var($ip, FILTER_VALIDATE_IP);
    if (!$ip) {
        return 'US';
    }
    $response = wp_remote_get("https://ipwho.is/{$ip}");
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return 'US';
    }
    $data = json_decode(wp_remote_retrieve_body($response), true);
    return isset($data['country_code']) ? sanitize_text_field($data['country_code']) : 'US';
}

// Регистрация кастомного обработчика VK
function svs_register_vkontakte_handler() {
    wp_embed_register_handler(
        'svs_vkontakte', // ИЗМЕНЕНО: Добавлен префикс
        '#https?://vk\.com/(video(-?\d+)_(\d+)|video\?z=video(-?\d+)_(\d+)(%2F|/)[^\s]+)#i',
        'svs_embed_handler_vkontakte' // ИЗМЕНЕНО: Имя функции теперь с префиксом
    );
}
add_action('init', 'svs_register_vkontakte_handler');

// Обработчик VK видео
// ИЗМЕНЕНО: Имя функции теперь с префиксом `svs_` вместо `wp_`
function svs_embed_handler_vkontakte($matches, $attr, $url, $rawattr) {
    if (!empty($matches[2]) && !empty($matches[3])) {
        $owner_id = $matches[2];
        $video_id = $matches[3];
    } elseif (!empty($matches[4]) && !empty($matches[5])) {
        $owner_id = $matches[4];
        $video_id = $matches[5];
    } else {
        return '';
    }

    return sprintf(
        '<div class="vkontakte-embed-wrapper"><iframe width="650" height="365" src="https://vk.com/video_ext.php?oid=%1$s&id=%2$s&hd=2" frameborder="0" allowfullscreen></iframe></div>',
        esc_attr($owner_id),
        esc_attr($video_id)
    );
}

// Шорткод [svs_video]
function svs_video_shortcode($atts) {
    $atts = shortcode_atts([
        'youtube_url' => '',
        'vk_url' => '',
        'unavailable_message' => 'Видео недоступно',
        'width' => 640,
        'height' => 360,
    ], $atts);

    $youtube_url = esc_url_raw($atts['youtube_url']);
    $vk_url = esc_url_raw($atts['vk_url']);
    $unavailable_message = sanitize_text_field($atts['unavailable_message']);
    $width = absint($atts['width']);
    $height = absint($atts['height']);

    $country = svs_get_user_country();
    $unique_id = uniqid('svs_');
    $container_id = esc_attr($unique_id . '_container');

    $output = '<div class="smart-video-container" id="' . $container_id . '">';

    $output .= '<div class="video-switch-buttons" style="margin-bottom:10px;">';
    $output .= '<button class="video-switch-button" data-target="youtube-video">YouTube</button> ';
    $output .= '<button class="video-switch-button" data-target="vkontakte-video">VK</button>';
    $output .= '</div>';

    $vk_embed_code = '';
    if (!empty($vk_url)) {
        $owner_id = '';
        $video_id = '';
        if (preg_match('#(-?\d+)_(\d+)#', $vk_url, $id_parts)) {
            $owner_id = $id_parts[1];
            $video_id = $id_parts[2];
        }
        if (!empty($owner_id) && !empty($video_id)) {
            $vk_embed_code = sprintf(
                '<div class="vkontakte-video" data-group="%s" style="display: %s;"><iframe width="%d" height="%d" src="https://vk.com/video_ext.php?oid=%s&id=%s&hd=2" frameborder="0" allowfullscreen></iframe></div>',
                esc_attr($container_id),
                ($country === 'RU') ? 'block' : 'none',
                $width, $height,
                esc_attr($owner_id), esc_attr($video_id)
            );
        }
    }

    $youtube_embed_code = '';
    if (!empty($youtube_url)) {
        $embed_code = wp_oembed_get($youtube_url, ['width' => $width, 'height' => $height]);
        if ($embed_code) {
            $youtube_embed_code = sprintf(
                '<div class="youtube-video" data-group="%s" style="display: %s;">%s</div>',
                esc_attr($container_id),
                ($country !== 'RU') ? 'block' : 'none',
                $embed_code
            );
        }
    }

    $output .= $vk_embed_code;
    $output .= $youtube_embed_code;
    $output .= '</div>';

    $output .= "
    <script type=\"text/javascript\">
        document.addEventListener(\"DOMContentLoaded\", function() {
            const container = document.getElementById(\"{$container_id}\");
            if (!container) return;
            const buttons = container.querySelectorAll(\".video-switch-button\");
            buttons.forEach(function(button) {
                button.addEventListener(\"click\", function() {
                    const targetClass = this.getAttribute(\"data-target\");
                    container.querySelectorAll(\".youtube-video, .vkontakte-video\").forEach(function(video) {
                        video.style.display = \"none\";
                    });
                    const target = container.querySelector(\".\" + targetClass);
                    if (target) target.style.display = \"block\";
                });
            });
        });
    </script>";

    return $output;
}
add_shortcode('svs_video', 'svs_video_shortcode'); // ИЗМЕНЕНО: Шорткод теперь с префиксом

// Добавляю кнопку 'Smart Video' справа от 'Add Media' в классическом редакторе
function svs_add_media_button() {
    echo '<button type="button" class="button svs-media-button" id="svs-media-button" style="margin-left: 4px;">
        <span class="dashicons dashicons-video-alt3" style="vertical-align:middle;margin-right:4px;"></span>
        Smart Video
    </button>';
}
add_action('media_buttons', 'svs_add_media_button', 15);