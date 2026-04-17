// public/assets/js/youtube.js
document.addEventListener('DOMContentLoaded', function() {
    // Sucht alle Platzhalter-Bilder
    document.querySelectorAll('.youtube-placeholder').forEach(function(placeholder) {
        
        placeholder.addEventListener('click', function() {
            var videoId = this.getAttribute('data-video-id');
            
            // Baut den echten Iframe mit Autoplay zusammen
            var iframe = '<iframe src="https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="border-radius: 8px;"></iframe>';
            
            // Ersetzt das Bild durch das Video
            this.innerHTML = '<div class="ratio ratio-16x9">' + iframe + '</div>';
        });
        
    });
});