const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const content = document.getElementById('content');
const overlay = document.getElementById('overlay');

function toggleMenu() {
  sidebar.classList.toggle('closed');
  content.classList.toggle('centered');
  if (window.innerWidth <= 768) {
    sidebar.classList.toggle('open');
    overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
  }
}

menuToggle.addEventListener('click', toggleMenu);
overlay.addEventListener('click', toggleMenu);

window.addEventListener('resize', function() {
  if (window.innerWidth > 768) {
    sidebar.classList.remove('open');
    overlay.style.display = 'none';
  } else {
    sidebar.classList.add('closed');
    content.classList.add('centered');
  }
});

if (window.innerWidth <= 768) {
  sidebar.classList.add('closed');
  content.classList.add('centered');
}

// Cerrar menú al hacer scroll
window.addEventListener("scroll", function() {
  sidebar.classList.add("closed");
  content.classList.add("centered");
  if (window.innerWidth <= 768) {
    sidebar.classList.remove("open");
    overlay.style.display = "none";
  }
});