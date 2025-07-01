<?php
/**
 * Plugin Name: Smart Switcher for VK and YouTube
 * Description: Автоматически подставляет видео с YouTube или VK в зависимости от геолокации пользователя.
 * Version: 2.0.0
 * Author: bebych
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Запрет прямого доступа
}

// === ИСПРАВЛЕНИЕ 3: Замена префикса на более уникальный (ssvy_) ===

// Добавляем кнопку в редактор TinyMCE (Classic Editor)
function ssvy_add_tinymce_button() {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }
    if (get_user_option('rich_editing') == 'true') {
        add_filter('mce_external_plugins', 'ssvy_add_tinymce_plugin');
        add_filter('mce_buttons', 'ssvy_register_tinymce_button');
    }
}
add_action('admin_init', 'ssvy_add_tinymce_button');

// Регистрируем новую кнопку
function ssvy_register_tinymce_button($buttons) {
    array_push($buttons, 'ssvy_button');
    return $buttons;
}

// Добавляем JavaScript для кнопки
function ssvy_add_tinymce_plugin($plugin_array) {
    $plugin_array['ssvy_button'] = plugins_url('js/editor-button.js', __FILE__);
    return $plugin_array;
}

// === ИСПРАВЛЕНИЕ 1: Правильное подключение скриптов и стилей ===
function ssvy_enqueue_admin_assets($hook) {
    if (!in_array($hook, array('post.php', 'post-new.php'))) {
        return;
    }

    // Подключаем CSS для модального окна
    wp_enqueue_style(
        'ssvy-admin-styles',
        plugins_url('css/admin-dialog.css', __FILE__)
    );

    // Подключаем JS для кнопки QuickTags
    wp_enqueue_script(
        'ssvy-quicktags',
        plugins_url('js/quicktags-button.js', __FILE__),
        array('quicktags'),
        '2.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'ssvy_enqueue_admin_assets');


// Регистрация блока Gutenberg
function ssvy_register_gutenberg_block() {
    if (!function_exists('register_block_type')) {
        return;
    }
    wp_register_script(
        'ssvy-block-editor',
        plugins_url('js/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor'),
        '2.0.0',
        true
    );
    register_block_type('smart-video-switcher/video-block', array(
        'editor_script' => 'ssvy-block-editor',
    ));
}
add_action('init', 'ssvy_register_gutenberg_block');


// Определение страны пользователя по IP
function ssvy_get_user_country() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $ip = filter_var($ip, FILTER_VALIDATE_IP);
    if (!$ip) {
        return 'US'; // Возвращаем значение по умолчанию, если IP некорректен
    }
    // Используем ipwho.is, как и раньше
    $response = wp_remote_get("https://ipwho.is/{$ip}");
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return 'US'; // Возвращаем значение по умолчанию в случае ошибки
    }
    $data = json_decode(wp_remote_retrieve_body($response), true);
    return isset($data['country_code']) ? sanitize_text_field($data['country_code']) : 'US';
}


// Регистрация кастомного обработчика для ссылок VK
function ssvy_register_vkontakte_embed_handler() {
    wp_embed_register_handler(
        'ssvy_vkontakte',
        '#https?://vk\.com/(video(-?\d+)_(\d+)|clip(-?\d+)_(\d+))#i',
        'ssvy_embed_handler_vkontakte'
    );
}
add_action('init', 'ssvy_register_vkontakte_embed_handler');


// Кастомный обработчик для вставки видео из VK
function ssvy_embed_handler_vkontakte($matches, $attr, $url, $rawattr) {
    $owner_id = !empty($matches[2]) ? $matches[2] : $matches[4];
    $video_id = !empty($matches[3]) ? $matches[3] : $matches[5];

    $embed = sprintf(
        '<iframe src="https://vk.com/video_ext.php?oid=%1$s&id=%2$s&hd=2" width="640" height="360" frameborder="0" allowfullscreen></iframe>',
        esc_attr($owner_id),
        esc_attr($video_id)
    );

    return apply_filters('embed_vkontakte', $embed, $matches, $attr, $url, $rawattr);
}


// Шорткод [ssvy_video]
function ssvy_video_shortcode($atts) {
    $atts = shortcode_atts([
        'youtube_url' => '',
        'vk_url' => '',
        'width' => 640,
        'height' => 360,
    ], $atts, 'ssvy_video');

    $youtube_url = esc_url_raw($atts['youtube_url']);
    $vk_url = esc_url_raw($atts['vk_url']);
    $width = absint($atts['width']);
    $height = absint($atts['height']);
    
    $country = ssvy_get_user_country();
    $unique_id = uniqid('ssvy_');
    
    // Определяем, какое видео показывать по умолчанию
    $show_youtube_first = ($country !== 'RU');

    // Формируем HTML для видео
    $youtube_embed_code = '';
    if (!empty($youtube_url)) {
        $youtube_embed_code = wp_oembed_get($youtube_url, ['width' => $width, 'height' => $height]);
    }

    $vk_embed_code = '';
    if (!empty($vk_url)) {
        $vk_embed_code = wp_oembed_get($vk_url); // Используем oEmbed для VK
    }

    // Собираем итоговый HTML
    $output = "<div class='ssvy-video-container' id='{$unique_id}'>";
    $output .= "<div class='ssvy-video-buttons'>";
    $output .= "<button class='ssvy-switch-button active' data-player='youtube'>YouTube</button>";
    $output .= "<button class='ssvy-switch-button' data-player='vk'>VK</button>";
    $output .= "</div>";
    
    $output .= "<div class='ssvy-video-players'>";
    $output .= sprintf(
        "<div class='ssvy-player youtube-player' style='display: %s;'>%s</div>",
        $show_youtube_first ? 'block' : 'none',
        $youtube_embed_code ?: 'YouTube video not available.'
    );
    $output .= sprintf(
        "<div class='ssvy-player vk-player' style='display: %s;'>%s</div>",
        !$show_youtube_first ? 'block' : 'none',
        $vk_embed_code ?: 'VK video not available.'
    );
    $output .= "</div></div>";
    
    // === ИСПРАВЛЕНИЕ 1: Правильное подключение скрипта и передача данных ===
    wp_enqueue_script(
        'ssvy-frontend-script',
        plugins_url('js/frontend-script.js', __FILE__),
        [],
        '2.0.0',
        true
    );
    // Передаем ID контейнера в скрипт
    wp_localize_script('ssvy-frontend-script', 'ssvyData', ['containerId' => $unique_id]);

    return $output;
}
add_shortcode('ssvy_video', 'ssvy_video_shortcode');


// Добавляем кнопку 'Smart Video' рядом с 'Add Media'
function ssvy_add_media_button() {
    echo '<button type="button" class="button ssvy-media-button" id="ssvy-media-button" style="margin-left: 4px;">
        <span class="dashicons dashicons-video-alt3" style="vertical-align:middle;margin-right:4px;"></span>
        Smart Video
    </button>';
}
add_action('media_buttons', 'ssvy_add_media_button', 15);
