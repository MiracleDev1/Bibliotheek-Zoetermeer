const track = document.querySelector('.carousel-track');
const prevButton = document.getElementById('prev');
const nextButton = document.getElementById('next');
const cards = document.querySelectorAll('.card');

let currentIndex = 0;
const stepSize = 280; // Breedte kaart + gap

if (nextButton && prevButton) {
    nextButton.onclick = () => {
        currentIndex = (currentIndex < cards.length - 3) ? currentIndex + 1 : 0;
        updateCarousel();
    };

    prevButton.onclick = () => {
        currentIndex = (currentIndex > 0) ? currentIndex - 1 : cards.length - 3;
        updateCarousel();
    };
}

function updateCarousel() {
    track.style.transform = `translateX(-${currentIndex * stepSize}px)`;
}

document.getElementById("darkmode-btn").click();

function toggleMode(){
    document.getElementById("body").
    classList.toggle("dark");
}