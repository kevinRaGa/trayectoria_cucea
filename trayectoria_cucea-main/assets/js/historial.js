// historial.js (cleaned)

// Cache static elements
const menuToggle = document.getElementById('menuToggle');
const content = document.getElementById('content');
const overlay = document.getElementById('overlay');

// Helper: get the sidebar (it arrives asynchronously via sidebar.html fetch)
function getSidebar() {
  return document.getElementById('sidebar');
}

// Toggle handler
function toggleMenu() {
  const sidebar = getSidebar();
  if (!sidebar || !content || !overlay) return;

  sidebar.classList.toggle('closed');
  content.classList.toggle('centered');

  if (window.innerWidth <= 768) {
    sidebar.classList.toggle('open');
    overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
  }
}

// Ensure state on resize
function handleResize() {
  const sidebar = getSidebar();
  if (!sidebar || !overlay || !content) return;

  if (window.innerWidth > 768) {
    sidebar.classList.remove('open');
    overlay.style.display = 'none';
    // Desktop default: sidebar visible, content not centered
    sidebar.classList.remove('closed');
    content.classList.remove('centered');
  } else {
    // Mobile default: sidebar hidden, content centered
    sidebar.classList.add('closed');
    content.classList.add('centered');
  }
}

// Initialize event listeners (works even if sidebar isn't in DOM yet)
if (menuToggle) menuToggle.addEventListener('click', toggleMenu);
if (overlay) overlay.addEventListener('click', toggleMenu);
window.addEventListener('resize', handleResize);

// If the sidebar arrives later, apply initial state once it exists
const sidebarContainer = document.getElementById('sidebar-container');
if (sidebarContainer) {
  const mo = new MutationObserver(() => {
    if (getSidebar()) {
      handleResize(); // set correct initial state
      mo.disconnect();
    }
  });
  mo.observe(sidebarContainer, { childList: true, subtree: true });
}

// Also run once after DOM is parsed (in case sidebar already loaded)
document.addEventListener('DOMContentLoaded', handleResize);
