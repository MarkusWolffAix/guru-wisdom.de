const scrollArea = document.getElementById("wisdomContainer");
const btnUp = document.getElementById("scrollUpBtn");
const btnDown = document.getElementById("scrollDownBtn");

function scrollWisdomList(direction) {
    // HIER IST DIE ÄNDERUNG: Wir suchen jetzt nach '.wisdom-card' anstatt '.wisdom-link-wrapper'
    const cards = Array.from(scrollArea.querySelectorAll('.wisdom-card:not(.hidden)'));
    
    if (cards.length === 0) return;

    // Wir berechnen die Höhe der ersten Karte plus den Abstand (mb-3 entspricht 16px)
    const cardHeight = cards[0].offsetHeight + 16; 

    if (direction === 'down') {
        // Scrolle genau eine Karte weiter runter
        scrollArea.scrollBy({ top: cardHeight, behavior: 'smooth' });
    } else {
        // Scrolle genau eine Karte weiter hoch
        scrollArea.scrollBy({ top: -cardHeight, behavior: 'smooth' });
    }
}

// Event Listener für die Buttons
if (btnUp && btnDown) {
    btnUp.addEventListener("click", () => scrollWisdomList('up'));
    btnDown.addEventListener("click", () => scrollWisdomList('down'));
}