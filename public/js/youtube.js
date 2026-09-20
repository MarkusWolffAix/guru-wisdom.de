document.addEventListener("DOMContentLoaded", function() {
    const containers = document.querySelectorAll('.two-click-container');

    containers.forEach(function(container) {
        const btn = container.querySelector('.load-media-btn');
        if (!btn) return;

        btn.addEventListener('click', function() {
            const src = container.getAttribute('data-src');
            const type = container.getAttribute('data-type');
            
            // iFrame Element erstellen
            const iframe = document.createElement('iframe');
            iframe.setAttribute('src', src);
            iframe.setAttribute('frameborder', '0');
            
            // Spezifische Einstellungen je nach Typ (wie in deiner alten Funktion)
            if (type === 'youtube') {
                iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
                iframe.setAttribute('allowfullscreen', 'true');
                iframe.style.width = '100%';
                iframe.style.aspectRatio = '16/9'; // Ersetzt die Bootstrap ratio-Klasse auf moderne Weise
            } else if (type === 'spotify') {
                iframe.setAttribute('allow', 'autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture');
                iframe.setAttribute('loading', 'lazy');
                iframe.style.width = '100%';
                iframe.style.height = '352px';
                iframe.style.borderRadius = '12px';
            }

            // Platzhalter löschen und iFrame einfügen
            container.innerHTML = '';
            container.appendChild(iframe);
        });
    });
});