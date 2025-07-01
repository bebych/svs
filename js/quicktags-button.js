function insertSSVYShortcode() {
    var youtubeUrl = document.getElementById('ssvy-youtube-url').value;
    var vkUrl = document.getElementById('ssvy-vk-url').value;
    var width = document.getElementById('ssvy-width').value;
    var height = document.getElementById('ssvy-height').value;

    var shortcode = '[ssvy_video';
    if (youtubeUrl) shortcode += ' youtube_url="' + youtubeUrl + '"';
    if (vkUrl) shortcode += ' vk_url="' + vkUrl + '"';
    if (width) shortcode += ' width="' + width + '"';
    if (height) shortcode += ' height="' + height + '"';
    shortcode += ']';

    QTags.insertContent(shortcode);

    var dialog = document.getElementById('ssvy-dialog');
    if(dialog) dialog.style.display = 'none';
    
    // Очистка полей не требуется, так как диалог будет создаваться заново
}

document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('ssvy-media-button');
    if (btn) {
        btn.addEventListener('click', function() {
            // Удаляем старое диалоговое окно, если оно есть
            var oldDialog = document.getElementById('ssvy-dialog');
            if (oldDialog) oldDialog.parentNode.removeChild(oldDialog);

            // Создаем новое диалоговое окно
            var dialog = document.createElement('div');
            dialog.id = 'ssvy-dialog';
            dialog.innerHTML = `
                <div class="ssvy-dialog-overlay"></div>
                <div class="ssvy-dialog-content">
                    <div class="ssvy-dialog-header">
                        <h2>Insert Smart Video</h2>
                        <button type="button" class="ssvy-dialog-close">&times;</button>
                    </div>
                    <div class="ssvy-dialog-body">
                        <p><label for="ssvy-youtube-url">YouTube URL:</label><br><input type="text" id="ssvy-youtube-url" class="ssvy-input" placeholder="https://www.youtube.com/watch?v=..."></p>
                        <p><label for="ssvy-vk-url">VK URL:</label><br><input type="text" id="ssvy-vk-url" class="ssvy-input" placeholder="https://vk.com/video..."></p>
                        <div class="ssvy-dimensions">
                            <p><label for="ssvy-width">Width:</label><br><input type="number" id="ssvy-width" class="ssvy-input-number" value="640"></p>
                            <p><label for="ssvy-height">Height:</label><br><input type="number" id="ssvy-height" class="ssvy-input-number" value="360"></p>
                        </div>
                    </div>
                    <div class="ssvy-dialog-footer">
                        <button type="button" class="button ssvy-dialog-cancel">Cancel</button>
                        <button type="button" class="button button-primary button-large" id="ssvy-insert-shortcode">Add Video</button>
                    </div>
                </div>`;
            document.body.appendChild(dialog);

            // Показываем окно
            dialog.style.display = 'block';
            
            // Добавляем обработчики событий
            dialog.querySelector('.ssvy-dialog-close').addEventListener('click', () => dialog.style.display = 'none');
            dialog.querySelector('.ssvy-dialog-cancel').addEventListener('click', () => dialog.style.display = 'none');
            dialog.querySelector('#ssvy-insert-shortcode').addEventListener('click', insertSSVYShortcode);
        });
    }
});
