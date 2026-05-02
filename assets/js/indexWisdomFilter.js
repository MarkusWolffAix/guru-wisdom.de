const scrollArea = document.getElementById("wisdomContainer");
const btnUp = document.getElementById("scrollUpBtn");
const btnDown = document.getElementById("scrollDownBtn");

// ==========================================
// LOGIK FÜR DIE PFEIL-BUTTONS
// ==========================================
function scrollWisdomList(direction) {
    const cards = Array.from(scrollArea.querySelectorAll('.wisdom-card:not(.hidden)'));
    if (cards.length === 0) return;

    // 1. Die absolute Bildschirm-Position unseres Containers messen
    const containerRect = scrollArea.getBoundingClientRect();
    const containerCenterY = containerRect.top + (containerRect.height / 2);
    
    let closestIndex = 0;
    let minDistance = Infinity;

    // 2. Messen, welche Karte GANZ GENAU in der Mitte des Bildschirms liegt
    cards.forEach((card, index) => {
        const cardRect = card.getBoundingClientRect();
        const cardCenterY = cardRect.top + (cardRect.height / 2);
        
        const distance = Math.abs(containerCenterY - cardCenterY);
        
        if (distance < minDistance) {
            minDistance = distance;
            closestIndex = index;
        }
    });

    // 3. Ziel-Karte bestimmen
    let targetIndex = closestIndex;
    if (direction === 'down' && closestIndex < cards.length - 1) {
        targetIndex++;
    } else if (direction === 'up' && closestIndex > 0) {
        targetIndex--;
    } else {
        return; // Ende der Liste erreicht
    }

    // 4. Berechnen, um wie viele Pixel wir scrollen müssen
    const targetCardRect = cards[targetIndex].getBoundingClientRect();
    const targetCardCenterY = targetCardRect.top + (targetCardRect.height / 2);
    
    const offsetToCenter = targetCardCenterY - containerCenterY;

    // 5. Exakt um diese Pixelanzahl verschieben (OHNE die Haupt-Webseite zu berühren!)
    scrollArea.scrollBy({
        top: offsetToCenter,
        behavior: 'smooth'
    });
}

// Buttons mit der neuen Logik verknüpfen
if (btnUp && btnDown) {
    btnUp.onclick = () => scrollWisdomList('up');
    btnDown.onclick = () => scrollWisdomList('down');
}

// ==========================================
// INITIALES ZENTRIEREN BEIM SEITENAUFRUF
// ==========================================
window.addEventListener('DOMContentLoaded', () => {
    // Kurze Verzögerung (150ms), damit Bilder sicher geladen sind und die Höhe exakt stimmt
    setTimeout(() => {
        if (!scrollArea) return;
        
        const firstCard = scrollArea.querySelector('.wisdom-card:not(.hidden)');
        if (!firstCard) return;

        const containerRect = scrollArea.getBoundingClientRect();
        const containerCenterY = containerRect.top + (containerRect.height / 2);
        
        const firstCardRect = firstCard.getBoundingClientRect();
        const firstCardCenterY = firstCardRect.top + (firstCardRect.height / 2);
        
        const offsetToCenter = firstCardCenterY - containerCenterY;

        // Springt exakt in die Mitte, ohne weiche Animation ('auto'), 
        // damit es beim Starten der Seite sofort perfekt sitzt!
        scrollArea.scrollBy({
            top: offsetToCenter,
            behavior: 'auto' 
        });
    }, 150); 
});