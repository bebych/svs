document.addEventListener("DOMContentLoaded", function() {
    // ssvyData передается из PHP через wp_localize_script
    if (typeof ssvyData === 'undefined') return;

    const container = document.getElementById(ssvyData.containerId);
    if (!container) return;

    const buttons = container.querySelectorAll(".ssvy-switch-button");
    const players = container.querySelectorAll(".ssvy-player");

    buttons.forEach(function(button) {
        button.addEventListener("click", function() {
            const targetPlayerClass = this.getAttribute("data-player");
            
            // Скрываем все плееры
            players.forEach(p => p.style.display = "none");
            
            // Показываем нужный плеер
            const targetPlayer = container.querySelector("." + targetPlayerClass + "-player");
            if (targetPlayer) targetPlayer.style.display = "block";
            
            // Управляем активным состоянием кнопок
            buttons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
