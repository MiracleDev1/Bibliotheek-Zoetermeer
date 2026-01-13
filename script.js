// Get the carousel track and buttons
const track = document.querySelector('.carousel-track');
const prevButton = document.getElementById('prev');
const nextButton = document.getElementById('next');

// Get all the cards
const cards = document.querySelectorAll('.card');

// Keep track of which card is currently visible
let currentIndex = 0;

// Function to show a card at a specific index
function showCard(index) {
  // Move the track to show the correct card
  track.style.transform = `translateX(-${index * 250}px)`;
  // Note: 250px is the width of one card. Adjust if your card is bigger or smaller.
}

// When next button is clicked
nextButton.addEventListener('click', () => {
  currentIndex++;
  if (currentIndex >= cards.length) {
    currentIndex = 0; // Loop back to the first card
  }
  showCard(currentIndex);
});

// When previous button is clicked
prevButton.addEventListener('click', () => {
  currentIndex--;
  if (currentIndex < 0) {
    currentIndex = cards.length - 1; // Go to the last card
  }
  showCard(currentIndex);
});

// Initialize first card
showCard(currentIndex);
