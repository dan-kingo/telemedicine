document.addEventListener("DOMContentLoaded", () => {
  const hamburger = document.getElementById('hamburger-menu');
  const mobileMenu = document.getElementById('mobile-menu');

  const toggleMenu = () => mobileMenu.classList.toggle('active');

  hamburger.addEventListener('click', toggleMenu);

  // Close drawer when clicking a link
  mobileMenu.querySelectorAll('a').forEach(link =>
    link.addEventListener('click', () => {
      mobileMenu.classList.remove('active');
    })
  );

  // Close drawer on outside click
  document.addEventListener('click', (e) => {
    if (
      !mobileMenu.contains(e.target) &&
      !hamburger.contains(e.target)
    ) {
      mobileMenu.classList.remove('active');
    }
  });
});
