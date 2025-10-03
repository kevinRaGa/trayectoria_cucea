# 🚀 Proyecto Trayectoria CUCEA

Aplicación web para apoyar a alumnos y administrativos en el **seguimiento académico** dentro de CUCEA.  
Permite consultar materias cursadas y pendientes, horarios, cupos, promedios, recomendaciones, encuestas y noticias.  
El sistema cuenta con autenticación segura con **JWT** y roles diferenciados.

---

## 📌 Descripción General
- Seguimiento de materias (cursadas, pendientes, aprobadas).  
- Consulta de horarios, cupos y disponibilidad.  
- Cálculo de promedio acumulado y por semestre.  
- Recomendación automática de materias según avance y prerrequisitos.  
- Encuestas estudiantiles.  
- Noticias y comunicados (opcional).  

---

## 👥 Colaboradores
- bernar-95  
- Joss100010001  
- jesusomardev1  
- KevinRaGA  

---

## 🏗️ Arquitectura Técnica
Servicios principales:
1. Autenticación y roles (JWT).  
2. Gestión de perfil de alumnos.  
3. Dashboard para alumnos y administradores.  
4. Seguimiento académico (trayectoria).  
5. Recomendación de materias.  
6. Encuestas estudiantiles.  
7. Noticias (opcional).  

---

## ⚙️ Tecnologías
- **Backend:** PHP  
- **Frontend:** HTML, CSS, JavaScript  
- **Base de datos:** MySQL  
- **Servidor:** Hostinger  
- **Autenticación:** JWT (JSON Web Tokens)  

---

## 🔒 Seguridad
- **JWT** para autenticación y autorización.  
- Tokens con expiración (1h) y refresh tokens.  
- Roles: Alumno, Administrador Académico, Administrador Técnico.  
- HTTPS obligatorio para todo el tráfico.  
- Contraseñas cifradas con bcrypt/argon2.  
- Prevención de **SQL Injection**, **XSS** y **CSRF**.  
- Respaldos automáticos de base de datos.  

---

## 📊 Métricas de Éxito
- 90% de los alumnos pueden consultar su trayectoria sin errores.  
- Soporte de al menos 200 usuarios concurrentes sin caída.  
- Respuesta de consultas < 3 segundos.  
- Participación en encuestas ≥ 60%.  

---

## 🗺️ Roadmap / Fases de Desarrollo
1. **Fase 1** – Autenticación y roles (JWT).  
2. **Fase 2** – Trayectoria académica + dashboard.  
3. **Fase 3** – Recomendación de materias.  
4. **Fase 4** – Encuestas y estadísticas.  
5. **Fase 5** – Noticias y avisos (opcional).  

---

## 🌿 Estrategia de Ramas (Branches)
- **`main`** → Rama estable en producción.  
- **`develop`** → Rama de integración para nuevas funcionalidades.  
- **`test`** → Rama de pruebas antes de pasar a `develop`.  
- **`frontend`** → Desarrollo de interfaz (HTML, CSS, JS).  
- **`backend`** → Desarrollo de lógica y base de datos (PHP, MySQL).  

### Buenas prácticas
- Nunca hacer commits directos en `main`.  
- Nuevas funcionalidades → crear rama desde `develop`.  
