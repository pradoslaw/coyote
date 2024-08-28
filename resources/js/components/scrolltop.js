const scrollButton = document.getElementById('scroll-to-top');

function handleScroll() {
  document.body.classList.toggle('scrolled-down', document.documentElement.scrollTop > 300);
}

function scrollToTop() {
  document.documentElement.scrollTo({top: 0, behavior: 'smooth'});
}

if (scrollButton) {
  scrollButton.addEventListener('click', scrollToTop);
  document.addEventListener('scroll', handleScroll);
}
