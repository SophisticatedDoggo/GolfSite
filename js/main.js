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

const params = new URLSearchParams(window.location.search);
const status_of_page = params.get('status');
console.log(status_of_page);

if(status_of_page === 'success') {
    const message = document.getElementById('toast-message')
    const toast_div = document.getElementById('toast')
    message.textContent = "Message sent! We'll be in touch soon."
    toast_div.removeAttribute('hidden')
    setTimeout(() => {
        toast_div.setAttribute('hidden', '')
    }, 8000) 
} else if(status_of_page === 'error') {
    const message = document.getElementById('toast-message')
    const toast_div = document.getElementById('toast')
    message.textContent = "Error: Message not sent."
    toast_div.removeAttribute('hidden')
    setTimeout(() => {
        toast_div.setAttribute('hidden', '')
    }, 8000)
};