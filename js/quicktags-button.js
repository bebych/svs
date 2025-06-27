// Функция для вставки шорткода
function insertSVSShortcode() {
    var youtubeUrl = document.getElementById('svs-youtube-url').value;
    var vkUrl = document.getElementById('svs-vk-url').value;
    var width = document.getElementById('svs-width').value;
    var height = document.getElementById('svs-height').value;

    var shortcode = '[svs_video';
    if (youtubeUrl) shortcode += ' youtube_url="' + youtubeUrl + '"';
    if (vkUrl) shortcode += ' vk_url="' + vkUrl + '"';
    if (width) shortcode += ' width="' + width + '"';
    if (height) shortcode += ' height="' + height + '"';
    shortcode += ']';

    // Вставляем шорткод в редактор
    QTags.insertContent(shortcode);

    // Закрываем диалог
    document.getElementById('svs-dialog').style.display = 'none';

    // Очищаем поля
    document.getElementById('svs-youtube-url').value = '';
    document.getElementById('svs-vk-url').value = '';
    document.getElementById('svs-width').value = '640';
    document.getElementById('svs-height').value = '360';
}

// Открытие модального окна по клику на кнопку над редактором

document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('svs-media-button');
    if (btn) {
        btn.addEventListener('click', function() {
            if (!document.getElementById('svs-dialog')) {
                // Дублируем логику создания окна
                var dialog = document.createElement('div');
                dialog.id = 'svs-dialog';
                dialog.style.display = 'none';
                dialog.innerHTML = `
                    <div class="svs-dialog-content">
                        <div class="svs-dialog-header">
                            <h2>Вставить Smart Video</h2>
                        </div>
                        <div class="svs-dialog-body">
                            <p>
                                <label for="svs-youtube-url">YouTube URL:</label><br>
                                <input type="text" id="svs-youtube-url" class="svs-input" placeholder="https://youtube.com/watch?v=...">
                            </p>
                            <p>
                                <label for="svs-vk-url">VK URL:</label><br>
                                <input type="text" id="svs-vk-url" class="svs-input" placeholder="https://vk.com/video...">
                            </p>
                            <div class="svs-dimensions">
                                <p>
                                    <label for="svs-width">Ширина:</label><br>
                                    <input type="number" id="svs-width" class="svs-input-number" value="640">
                                </p>
                                <p>
                                    <label for="svs-height">Высота:</label><br>
                                    <input type="number" id="svs-height" class="svs-input-number" value="360">
                                </p>
                            </div>
                        </div>
                        <div class="svs-dialog-footer">
                            <button type="button" class="button" onclick="document.getElementById('svs-dialog').style.display='none'">Отмена</button>
                            <button type="button" class="button button-primary button-large" onclick="insertSVSShortcode()">Добавить видео</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(dialog);
            }
            document.getElementById('svs-dialog').style.display = 'block';
        });
    }
}); 