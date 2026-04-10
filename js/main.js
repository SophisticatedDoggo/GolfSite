const header = document.querySelector('header');

window.addEventListener('scroll', function() {
    if (window.scrollY > 80) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

const hamburger = document.getElementById('hamburger');
const navMenu = document.querySelector('nav > ul');
const navLinks = document.querySelectorAll('nav > ul > li > a')

hamburger.addEventListener('click', function() {
    if(navMenu.classList.contains('open')) {
        navMenu.classList.remove('open')
    } else (
        navMenu.classList.toggle('open')
    );
    
});

navLinks.forEach(function(link) {
    link.addEventListener('click', function() {
        navMenu.classList.remove('open');
    });
    navMenu.classList.remove('open');
});