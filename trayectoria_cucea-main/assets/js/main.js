// main.js (cleaned)

// Static elements present at page load
const menuToggle = document.getElementById('menuToggle');
const content = document.getElementById('content');
const overlay = document.getElementById('overlay');

// Sidebar arrives asynchronously via fetch("sidebar.html") into #sidebar-container
function getSidebar() {
  return document.getElementById('sidebar');
}

// Toggle open/closed + overlay for mobile
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

// Ensure correct state for current viewport
function handleResize() {
  const sidebar = getSidebar();
  if (!sidebar || !overlay || !content) return;

  if (window.innerWidth > 768) {
    // Desktop default: sidebar visible, content not centered
    sidebar.classList.remove('open', 'closed');
    content.classList.remove('centered');
    overlay.style.display = 'none';
  } else {
    // Mobile default: sidebar hidden, content centered
    sidebar.classList.remove('open');
    sidebar.classList.add('closed');
    content.classList.add('centered');
    overlay.style.display = 'none';
  }
}

// Attach listeners
if (menuToggle) menuToggle.addEventListener('click', toggleMenu);
if (overlay) overlay.addEventListener('click', toggleMenu);
window.addEventListener('resize', handleResize);

// Initialize after sidebar is injected
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

// Also run once after DOM is parsed (covers case where sidebar already loaded)
document.addEventListener('DOMContentLoaded', handleResize);

/* ========= Dashboard animations =========
   Elements exist in main.html:
     - #creditos, #promedio numbers
     - #progressCreditos, #progressPromedio SVG circles
*/

// Animate the number text from start → end over duration (ms)
function animateValue(id, start, end, duration) {
  const el = document.getElementById(id);
  if (!el) return;

  let startTime = null;
  const isDecimal = id === 'promedio';

  function step(ts) {
    if (!startTime) startTime = ts;
    const p = Math.min((ts - startTime) / duration, 1);
    const val = start + (end - start) * p;
    el.textContent = isDecimal ? val.toFixed(1) : Math.floor(val);
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

// Animate an SVG circle’s strokeDashOffset to a target percentage (0–100)
function animateCircle(circleId, targetPercent, duration = 2000) {
  const circle = document.getElementById(circleId);
  if (!circle) return;

  const radius = circle.r.baseVal.value;
  const circumference = 2 * Math.PI * radius;
  circle.style.strokeDasharray = `${circumference}`;
  circle.style.strokeDashoffset = `${circumference}`;

  let startTime = null;

  function frame(ts) {
    if (!startTime) startTime = ts;
    const p = Math.min((ts - startTime) / duration, 1);
    const currentPercent = targetPercent * p;
    const offset = circumference - (currentPercent / 100) * circumference;
    circle.style.strokeDashoffset = `${offset}`;
    if (p < 1) requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
}

// Kick off the dashboard animations once DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Example values used in your original script
  animateValue('creditos', 0, 220, 2000);
  animateValue('promedio', 0, 9.3, 2000);

  // Animate rings to their percentages (e.g., 73% and 93%)
  animateCircle('progressCreditos', 73, 2000);
  animateCircle('progressPromedio', 93, 2000);
});

/* Contenedor a la derecha */
.header-right {
  display: flex;
  align-items: center;
  margin-left: auto;
}

/* Icono de usuario */
.user-icon {
  height: 40px;
  width: 40px;
  border-radius: 50%; /* redondeado tipo avatar */
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.user-icon:hover {
  transform: scale(1.1);
  box-shadow: 0 0 8px rgba(255,255,255,0.5);
}
