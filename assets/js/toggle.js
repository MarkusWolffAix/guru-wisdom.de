document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById('toggle-button');
    const detailsContainer = document.getElementById('detailsContainer');
    const previewContainer = document.getElementById('previewContainer');

    if (toggleBtn && detailsContainer && previewContainer) {
        
        function toggleDetails(e) {
            // Zeigt in der Entwicklerkonsole (F12) an, dass geklickt wurde!
            // console.log("Klick wurde erkannt auf:", e.currentTarget.id);
            
            // Verhindert nur bei Links/Buttons das Standardverhalten
            // Bei reinen divs (wie der Preview) stört preventDefault manchmal
            if(e.currentTarget.tagName.toLowerCase() === 'button' || e.currentTarget.tagName.toLowerCase() === 'a') {
                e.preventDefault(); 
            }
            
            // Auf- und Zuklappen
            detailsContainer.classList.toggle('open');
            previewContainer.classList.toggle('hidden');
            
            // Lupe drehen
            toggleBtn.classList.toggle('open-icon');
            
            // Sanftes Scrollen
            if (detailsContainer.classList.contains('open')) {
                setTimeout(() => {
                    detailsContainer.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }, 400); 
            }
        }

        // Klick-Events zuweisen
        toggleBtn.addEventListener('click', toggleDetails);
        previewContainer.addEventListener('click', toggleDetails);
    } else {
        // console.warn("Eines der Elemente (Lupe, Details oder Preview) wurde auf der Seite nicht gefunden!");
    }
});