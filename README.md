# 🚀 Trayectoria CUCEA

<div align="center">

**Sistema de seguimiento académico para estudiantes del CUCEA**

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

[Características](#-características) • [Instalación](#-instalación) • [Tecnologías](#-tecnologías) • [Contribuir](#-cómo-contribuir) • [Seguridad](#-seguridad)

</div>

---

## 📖 Descripción

**Trayectoria CUCEA** es una aplicación web diseñada para facilitar el seguimiento académico de alumnos y administrativos del Centro Universitario de Ciencias Económico Administrativas (CUCEA).

El sistema permite a los estudiantes consultar su trayectoria académica, visualizar materias cursadas y pendientes, calcular promedios, recibir recomendaciones personalizadas de materias, y participar en encuestas estudiantiles.

### 🎯 Objetivo

Proporcionar una herramienta integral que mejore la experiencia académica de los estudiantes del CUCEA, facilitando la planificación de su trayectoria educativa y la toma de decisiones informadas sobre su carrera.

---

## ✨ Características

### Para Estudiantes

- 📊 **Seguimiento Académico**

  - Visualización de materias cursadas, aprobadas y pendientes
  - Cálculo automático de promedio general y por semestre
  - Historial completo de calificaciones

- 🎓 **Recomendador Inteligente**

  - Sugerencias personalizadas de materias según avance
  - Verificación automática de prerrequisitos
  - Optimización de carga académica

- 📅 **Consulta de Horarios**

  - Disponibilidad de materias por ciclo
  - Información de cupos disponibles
  - Horarios y salones asignados

- 📝 **Encuestas Estudiantiles**
  - Participación en evaluaciones académicas
  - Retroalimentación sobre materias y profesores

### Para Administradores

- 👥 **Gestión de Usuarios**

  - Control de acceso por roles
  - Administración de perfiles estudiantiles

- 📈 **Dashboard Administrativo**

  - Estadísticas de desempeño académico
  - Reportes y métricas del sistema
  - Monitoreo de participación en encuestas

- 📢 **Comunicación**
  - Publicación de noticias y avisos
  - Comunicados académicos importantes

---

## 👥 Equipo de Desarrollo

| Colaborador   | GitHub                                             | Rol          |
| ------------- | -------------------------------------------------- | ------------ |
| Kevin Ramírez | [@KevinRaGA](https://github.com/KevinRaGA)         | Project Lead |
| Jesús Omar    | [@jesusomardev1](https://github.com/jesusomardev1) | Developer    |
| José          | [@Joss100010001](https://github.com/Joss100010001) | Developer    |
| Bernardo      | [@bernar-95](https://github.com/bernar-95)         | Developer    |

---

## 🛠️ Tecnologías

### Backend

- **PHP 8.0+** - Lenguaje del servidor
- **MySQL 8.0+** - Base de datos relacional
- **JWT** - Autenticación y autorización
- **MySQLi** - Conexión segura con placeholders tipados

### Frontend

- **HTML5** - Estructura
- **CSS3** - Estilos y diseño responsivo
- **JavaScript (Vanilla)** - Interactividad

### Infraestructura

- **Hostinger** - Servidor de producción
- **HTTPS** - Comunicación cifrada
- **Git** - Control de versiones

---

## 📦 Instalación

### Prerrequisitos

- PHP 8.0 o superior
- MySQL 8.0 o superior
- Servidor web (Apache/Nginx)
- Composer (opcional)

### Pasos de Instalación

1. **Clonar el repositorio**

```bash
git clone https://github.com/kevinRaGa/trayectoria_cucea.git
cd trayectoria_cucea
```

2. **Configurar variables de entorno**

```bash
cp .env.example .env
```

Edita `.env` con tus credenciales:

```env
DB_HOST=localhost
DB_NAME=trayectoria_cucea
DB_USER=tu_usuario
DB_PASSWORD=tu_contraseña
DB_CHARSET=utf8mb4

JWT_SECRET=tu_clave_secreta_aqui
JWT_EXPIRATION=3600
```

3. **Importar la base de datos**

```bash
mysql -u tu_usuario -p trayectoria_cucea < database/schema.sql
```

4. **Configurar permisos**

```bash
chmod 755 config/
chmod 644 .env
```

5. **Acceder a la aplicación**

Navega a: `http://localhost/trayectoria_cucea`

---

## 🔒 Seguridad

La seguridad es una prioridad en Trayectoria CUCEA:

- ✅ **Autenticación JWT** con tokens de expiración
- ✅ **Contraseñas hasheadas** con bcrypt/argon2
- ✅ **Prepared statements** contra SQL Injection
- ✅ **Validación de inputs** en todas las entradas
- ✅ **Headers de seguridad** configurados
- ✅ **HTTPS obligatorio** en producción
- ✅ **Rate limiting** en endpoints críticos
- ✅ **CORS configurado** correctamente

🔐 Para reportar vulnerabilidades, consulta [SECURITY.md](SECURITY.md)

---

## 🌿 Estrategia de Ramas

| Rama        | Propósito                   | Protegida  |
| ----------- | --------------------------- | ---------- |
| `main`      | Producción estable          | ✅ Sí      |
| `develop`   | Integración de features     | ✅ Sí      |
| `test`      | Pruebas antes de develop    | ⚠️ Parcial |
| `backend`   | Desarrollo de lógica PHP/DB | ❌ No      |
| `frontend`  | Desarrollo de UI            | ❌ No      |
| `feature/*` | Nuevas funcionalidades      | ❌ No      |
| `fix/*`     | Corrección de bugs          | ❌ No      |

### 📋 Flujo de Trabajo

1. Crear rama desde `backend` o `frontend`
2. Desarrollar y hacer commits con [formato convencional](CONTRIBUTING.md)
3. Hacer Pull Request a `backend` o `frontend`
4. Revisión por al menos 1 colaborador
5. Merge a rama base
6. Integración a `develop` → `test` → `main`

---

## 🗺️ Roadmap

### ✅ Fase 1 - Autenticación (Completada)

- [x] Sistema de login con JWT
- [x] Gestión de roles (Alumno, Admin)
- [x] Refresh tokens

### 🚧 Fase 2 - Trayectoria (En Progreso)

- [x] Clase Database con placeholders seguros
- [ ] Consulta de materias cursadas
- [ ] Cálculo de promedios
- [ ] Dashboard de estudiante

### 📋 Fase 3 - Recomendador (Próximamente)

- [ ] Motor de recomendación de materias
- [ ] Verificación de prerrequisitos
- [ ] Sugerencias personalizadas

### 📋 Fase 4 - Encuestas

- [ ] Sistema de encuestas
- [ ] Reportes y estadísticas
- [ ] Dashboard administrativo

### 📋 Fase 5 - Extras

- [ ] Módulo de noticias
- [ ] Notificaciones push

---

## 🤝 Cómo Contribuir

¡Nos encantaría contar con tu ayuda! Sigue estos pasos:

1. Lee nuestra [Guía de Contribución](CONTRIBUTING.md)
2. Revisa el [Código de Conducta](CODE_OF_CONDUCT.md)
3. Haz fork del repositorio
4. Crea una rama: `git checkout -b feature/mi-feature`
5. Haz commit: `git commit -m 'feat(area): descripción'`
6. Push: `git push origin feature/mi-feature`
7. Abre un Pull Request

### 🎯 ¿En qué puedes ayudar?

- 🐛 Reportar bugs
- 💡 Sugerir nuevas funcionalidades
- 📝 Mejorar la documentación
- 🔧 Corregir errores
- ✨ Desarrollar nuevas features

---

## 📊 Métricas de Éxito

| Métrica                    | Objetivo       | Estado |
| -------------------------- | -------------- | ------ |
| Disponibilidad             | 99.5% uptime   | 🟢     |
| Tiempo de respuesta        | < 3 segundos   | 🟢     |
| Usuarios concurrentes      | 200+ sin caída | 🟡     |
| Participación en encuestas | ≥ 60%          | ⚪     |
| Satisfacción de usuarios   | ≥ 4.5/5        | ⚪     |

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

---

## 📞 Contacto y Soporte

- 📧 **Email:** [Agregar email del equipo]
- 🐛 **Issues:** [GitHub Issues](https://github.com/kevinRaGa/trayectoria_cucea/issues)
- 📖 **DeepWiki:** [DeepWiki](https://deepwiki.com/kevinRaGa/trayectoria_cucea)
- 👥 **Discusiones:** [GitHub Discussions](https://github.com/kevinRaGa/trayectoria_cucea/discussions)

---

## 🙏 Agradecimientos

- Al **CUCEA** por el apoyo al proyecto
- A todos los **contribuyentes** que hacen posible este proyecto
- A la comunidad de **desarrolladores** por sus herramientas open source

---

<div align="center">

**Hecho con ❤️ para la comunidad del CUCEA**

[⬆ Volver arriba](#-trayectoria-cucea)

</div>
