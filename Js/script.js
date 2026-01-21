// Knop om night mode aan/uit te zetten ophalen
const nightModeBtn = document.getElementById('night-mode-toggle');

// De hele pagina (body) opslaan
const body = document.body;


// Kijken of night mode eerder is aangezet
// localStorage onthoudt dit, ook na refresh
if (localStorage.getItem('nightMode') === 'enabled') {

    // Dark mode class toevoegen aan body
    body.classList.add('dark-mode');

    // Als de knop bestaat, zet het icoon op zon
    if (nightModeBtn) {
        nightModeBtn.textContent = '☀️';
    }
}


// Check of de knop bestaat (veiligheid)
if (nightModeBtn) {

    // Luisteren naar klik op de knop
    nightModeBtn.addEventListener('click', () => {

        // Dark mode aan/uit zetten
        body.classList.toggle('dark-mode');

        // Controleren of dark mode nu actief is
        if (body.classList.contains('dark-mode')) {

            // Zon-icoon tonen
            nightModeBtn.textContent = '☀️';

            // Onthouden dat night mode aan staat
            localStorage.setItem('nightMode', 'enabled');

        } else {

            // Maan-icoon tonen
            nightModeBtn.textContent = '🌙';

            // Onthouden dat night mode uit staat
            localStorage.setItem('nightMode', 'disabled');
        }
    });
}


// Carousel (slider) elementen ophalen
const track = document.querySelector('.carousel-track');
const prevButton = document.getElementById('prev');
const nextButton = document.getElementById('next');
const cards = document.querySelectorAll('.card');


// Alleen uitvoeren als alle elementen bestaan
if (track && nextButton && prevButton) {

    // Bijhouden welke kaart nu zichtbaar is
    let currentIndex = 0;

    // Hoeveel pixels de carousel per stap verschuift
    const stepSize = 280;


    // Klik op "volgende" knop
    nextButton.onclick = () => {

        // Naar volgende kaart gaan
        // Als we aan het einde zijn, terug naar begin
        if (currentIndex < cards.length - 3) {
            currentIndex = currentIndex + 1;
        } else {
            currentIndex = 0;
        }

        // Carousel verplaatsen met CSS transform
        track.style.transform =
            `translateX(-${currentIndex * stepSize}px)`;
    };


    // Klik op "vorige" knop
    prevButton.onclick = () => {

        // Naar vorige kaart gaan
        // Als we aan het begin zijn, naar het einde
        if (currentIndex > 0) {
            currentIndex = currentIndex - 1;
        } else {
            currentIndex = cards.length - 3;
        }

        // Carousel verplaatsen met CSS transform
        track.style.transform =
            `translateX(-${currentIndex * stepSize}px)`;
    };
}
