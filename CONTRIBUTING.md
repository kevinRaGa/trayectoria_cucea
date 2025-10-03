
### Tipos
- **feat** → Nueva funcionalidad.  
- **fix** → Corrección de errores.  
- **docs** → Documentación.  
- **style** → Formato o estilo.  
- **refactor** → Reestructuración sin cambiar lógica.  
- **test** → Pruebas.  
- **chore** → Configuración, dependencias.  
- **perf** → Rendimiento.  
- **security** → Cambios de seguridad.  

### Áreas
`auth`, `trayectoria`, `dashboard`, `frontend`, `backend`, `db`, `docs`  

### Ejemplos
- `feat(auth): implementar login con JWT`  
- `fix(trayectoria): corregir cálculo de promedio acumulado`  
- `docs: actualizar README con estrategia de ramas`  
- `refactor(frontend): limpiar CSS y separar sidebar.css`  
- `security(auth): encriptar contraseñas con bcrypt`  

---

## ✅ Buenas Prácticas de Commit
- Mensajes en **presente** y claros.  
- Máximo **72 caracteres** en el título.  
- Un commit = un cambio lógico.  
- Descripción larga si el cambio es complejo.  
- Probar antes de hacer commit.  

---

## 📝 Pull Requests (PR)
- Toda nueva funcionalidad o fix debe ir en un PR.  
- El PR debe incluir:  
  - Descripción breve de cambios.  
  - Issue relacionado (si aplica).  
- Al menos **1 aprobación de un colaborador** antes del merge.  

---

## 🔐 Seguridad en Git
- No subir archivos `.env` ni claves al repo.  
- Configurar variables sensibles solo en el servidor.  
- Revisar dependencias antes de agregar librerías externas.  

---

¡Gracias por contribuir a **Trayectoria CUCEA**! 🚀  
