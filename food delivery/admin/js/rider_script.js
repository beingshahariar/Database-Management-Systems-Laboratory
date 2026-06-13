// Get DOM elements
let userBtn = document.querySelector('#user-btn');
let navbar = document.querySelector('.header .flex .navbar');
let profile = document.querySelector('.header .flex .profile');

// Toggle the profile box
userBtn.onclick = () => {
   profile.classList.toggle('active');
   navbar.classList.remove('active');
};

// Hide profile when scrolling
window.onscroll = () => {
   profile.classList.remove('active');
};
