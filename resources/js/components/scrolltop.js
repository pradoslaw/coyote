const scrollButton = document.getElementById('scroll-to-top');

function handleScroll() {
  if (document.documentElement.scrollTop > 300) {
    scrollButton.style.display = 'block';
  } else {
    scrollButton.style.display = 'none';
  }
}

function scrollToTop() {
  document.documentElement.scrollTo({ top: 0, behavior: 'smooth' });
}

if (scrollButton) {
  scrollButton.addEventListener('click', scrollToTop);
  document.addEventListener('scroll', handleScroll);
}
