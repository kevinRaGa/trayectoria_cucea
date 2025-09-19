// ==================== SECCIÓN DE LA BARRA LATERAL ====================
// Código copiado de encuesta.js para gestionar la apertura y cierre de la barra lateral
// Elementos estáticos
const menuToggle = document.getElementById('menuToggle');
const content = document.getElementById('content');
const overlay = document.getElementById('overlay');

// Helper: busca el elemento de la barra lateral
function getSidebar() {
  return document.getElementById('sidebar');
}

// Función para abrir/cerrar la barra lateral
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

// Función para manejar el redimensionamiento de la ventana
function handleResize() {
  const sidebar = getSidebar();
  if (!sidebar || !overlay || !content) return;

  if (window.innerWidth > 768) {
    sidebar.classList.remove('open', 'closed');
    content.classList.remove('centered');
    overlay.style.display = 'none';
  } else {
    sidebar.classList.remove('open');
    sidebar.classList.add('closed');
    content.classList.add('centered');
    overlay.style.display = 'none';
  }
}

// Listeners: escucha los clics en el botón de menú y el overlay, y el evento de redimensionar la ventana
if (menuToggle) menuToggle.addEventListener('click', toggleMenu);
if (overlay) overlay.addEventListener('click', toggleMenu);
window.addEventListener('resize', handleResize);

// Aplica el estado inicial cuando el HTML de la barra lateral es inyectado
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

// También se ejecuta una vez al cargar el DOM (cubre el caso si la barra lateral ya estaba cargada)
document.addEventListener('DOMContentLoaded', handleResize);

// ==================== SECCIÓN DE CARGA DE DATOS DEL PERFIL ====================
// Se ejecuta una vez que el DOM de la página esté completamente cargado
document.addEventListener('DOMContentLoaded', () => {
    // Simulamos la carga de datos del usuario desde una fuente externa (ej. una base de datos)
    const userData = {
        user_id: 'BernaH',
        codigo: '212028636',
        nombre: 'Bernardino',
        apellidos: 'Hermosillo Paez',
        telefono: '3314022188',
        direccion: 'Calle Falsa 123',
        ciudad: 'Guadalajara, Jalisco',
        carrera_id: 'LTIN',
        semestre_ingreso: '2020A'
    };

    // Obtenemos los elementos <span> de la tabla por su ID y les asignamos el texto de los datos
    document.getElementById('user_id').textContent = userData.user_id;
    document.getElementById('codigo').textContent = userData.codigo;
    document.getElementById('nombre').textContent = userData.nombre;
    document.getElementById('apellidos').textContent = userData.apellidos;
    document.getElementById('telefono').textContent = userData.telefono;
    document.getElementById('direccion').textContent = userData.direccion;
    document.getElementById('ciudad').textContent = userData.ciudad;
    document.getElementById('carrera_id').textContent = userData.carrera_id;
    document.getElementById('semestre_ingreso').textContent = userData.semestre_ingreso;
});