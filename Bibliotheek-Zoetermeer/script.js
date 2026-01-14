const nightModeBtn = document.getElementById('night-mode-toggle');
const body = document.body;

if (localStorage.getItem('nightMode') === 'enabled') {
    body.classList.add('dark-mode');
    if(nightModeBtn) nightModeBtn.textContent = '☀️';
}

if(nightModeBtn) {
    nightModeBtn.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        if (body.classList.contains('dark-mode')) {
            nightModeBtn.textContent = '☀️';
            localStorage.setItem('nightMode', 'enabled');
        } else {
            nightModeBtn.textContent = '🌙';
            localStorage.setItem('nightMode', 'disabled');
        }
    });
}

// Carousel code
const track = document.querySelector('.carousel-track');
const prevButton = document.getElementById('prev');
const nextButton = document.getElementById('next');
const cards = document.querySelectorAll('.card');

if (track && nextButton && prevButton) {
    let currentIndex = 0;
    const stepSize = 280; 

    nextButton.onclick = () => {
        currentIndex = (currentIndex < cards.length - 3) ? currentIndex + 1 : 0;
        track.style.transform = `translateX(-${currentIndex * stepSize}px)`;
    };

    prevButton.onclick = () => {
        currentIndex = (currentIndex > 0) ? currentIndex - 1 : cards.length - 3;
        track.style.transform = `translateX(-${currentIndex * stepSize}px)`;
    };
}