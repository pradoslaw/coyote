const stickyAside = document.querySelector(".sticky-aside");
const navbar = document.querySelector("nav.navbar");
const header = document.querySelector("header");

function reposition() {
  const asideHeight = stickyAside.getBoundingClientRect().height;
  const viewportHeight = window.innerHeight;
  let navbarHeight = getNavbarHeight();
  if (navbarHeight + asideHeight < viewportHeight) {
    stickyAside.style.top = navbarHeight + "px"; // pin top top
  } else {
    stickyAside.style.top = viewportHeight - asideHeight + "px"; // pin to bottom
  }
}

function getNavbarHeight() {
  if (header.classList.contains("fixed-top")) {
    // if navbar is fixed, then `.bottom` will always be bigger
    // than 0, but we add Math.max() in case styles are changed
    // or something gets renamed
    return Math.max(navbar.getBoundingClientRect().bottom, 0);
  }
  return 0;
}

if (stickyAside) {
  window.addEventListener("resize", reposition, {passive: true});
  reposition();
  [0, 1000, 4000, 6000, 10000].forEach(time => setTimeout(reposition, time));
}
