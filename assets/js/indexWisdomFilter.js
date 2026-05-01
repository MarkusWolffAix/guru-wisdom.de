// --- Logik für die Schriftrollen-Buttons ---
const scrollArea = document.getElementById("wisdomContainer");
const btnUp = document.getElementById("scrollUpBtn");
const btnDown = document.getElementById("scrollDownBtn");

// Funktion, um exakt um die Höhe einer Karte nach oben oder unten zu scrollen
function scrollWisdomList(direction) {
    // Finde eine sichtbare Karte, um deren Höhe als Maßstab zu nehmen
    const firstVisibleCard = Array.from(scrollArea.querySelectorAll('.wisdom-link-wrapper')).find(card => !card.classList.contains('hidden'));
    
    if (!firstVisibleCard) return; // Wenn nichts gefunden wurde, abbrechen

    // Wir berechnen die Höhe der Karte + den Abstand (margin-bottom)
    const style = window.getComputedStyle(firstVisibleCard.querySelector('.wisdom-card'));
    const marginBottom = parseFloat(style.marginBottom);
    const cardHeight = firstVisibleCard.offsetHeight + marginBottom;

    // Scrolle um diesen Wert
    if (direction === 'up') {
        scrollArea.scrollBy({ top: -cardHeight, left: 0, behavior: 'smooth' });
    } else {
        scrollArea.scrollBy({ top: cardHeight, left: 0, behavior: 'smooth' });
    }
}

// Klick-Ereignisse an die Buttons binden
if (btnUp && btnDown && scrollArea) {
    btnUp.addEventListener("click", () => scrollWisdomList('up'));
    btnDown.addEventListener("click", () => scrollWisdomList('down'));
}