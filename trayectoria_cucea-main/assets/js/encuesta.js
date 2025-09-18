// Static elements
const menuToggle = document.getElementById('menuToggle');
const content = document.getElementById('content');
const overlay = document.getElementById('overlay');

// Helper: sidebar arrives via fetch("sidebar.html") inside #sidebar-container
function getSidebar() {
  return document.getElementById('sidebar');
}

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

function handleResize() {
  const sidebar = getSidebar();
  if (!sidebar || !overlay || !content) return;

  if (window.innerWidth > 768) {
    // Desktop default: sidebar visible, content not centered, overlay hidden
    sidebar.classList.remove('open', 'closed');
    content.classList.remove('centered');
    overlay.style.display = 'none';
  } else {
    // Mobile default: sidebar hidden/closed, content centered
    sidebar.classList.remove('open');
    sidebar.classList.add('closed');
    content.classList.add('centered');
    overlay.style.display = 'none';
  }
}

// Listeners
if (menuToggle) menuToggle.addEventListener('click', toggleMenu);
if (overlay) overlay.addEventListener('click', toggleMenu);
window.addEventListener('resize', handleResize);

// Apply initial state once the sidebar HTML is injected
const sidebarContainer = document.getElementById('sidebar-container');
if (sidebarContainer) {
  const mo = new MutationObserver(() => {
    if (getSidebar()) {
      handleResize();
      mo.disconnect();
    }
  });
  mo.observe(sidebarContainer, { childList: true, subtree: true });
}

// Also run once after DOM is parsed (covers case where sidebar was already loaded)
document.addEventListener('DOMContentLoaded', handleResize);
