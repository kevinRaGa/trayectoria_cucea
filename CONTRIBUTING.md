# 🤝 Guía de Contribución - Trayectoria CUCEA

¡Gracias por tu interés en contribuir al proyecto **Trayectoria CUCEA**! Esta guía te ayudará a realizar contribuciones de forma organizada y profesional.

---

## 📑 Tabla de Contenidos

1. [Convención de Commits](#-convención-de-commits)
2. [Buenas Prácticas](#-buenas-prácticas-de-commit)
3. [Proceso de Fork y Pull Request](#-proceso-de-fork-y-pull-request)
4. [Seguridad](#-seguridad-en-git)

---

## 🏷️ Convención de Commits

Utilizamos el formato **Conventional Commits** para mantener un historial limpio y legible.

### Estructura del mensaje

```
<tipo>(<área>): <descripción breve>

[cuerpo opcional - explicación detallada]
[footer opcional - referencias a issues]
```

### Tipos de Commit

| Tipo | Descripción | Ejemplo |
|------|-------------|---------|
| **feat** | Nueva funcionalidad | `feat(auth): agregar autenticación con JWT` |
| **fix** | Corrección de errores | `fix(trayectoria): corregir cálculo de promedio` |
| **docs** | Cambios en documentación | `docs: actualizar README con instalación` |
| **style** | Formato o estilo (sin cambios lógicos) | `style(frontend): aplicar formato CSS` |
| **refactor** | Reestructuración sin cambiar funcionalidad | `refactor(db): optimizar queries de usuarios` |
| **test** | Agregar o modificar pruebas | `test(auth): agregar pruebas de login` |
| **chore** | Configuración, dependencias | `chore: actualizar dependencias npm` |
| **perf** | Mejoras de rendimiento | `perf(db): optimizar consultas pesadas` |
| **security** | Cambios relacionados con seguridad | `security(auth): encriptar contraseñas` |

### Áreas del Proyecto

- `auth` - Autenticación y autorización
- `trayectoria` - Lógica de trayectoria académica
- `dashboard` - Panel de control
- `frontend` - Interfaz de usuario
- `backend` - API y lógica del servidor
- `db` - Base de datos y esquemas
- `docs` - Documentación

### Ejemplos Completos

```bash
# Nueva funcionalidad
feat(auth): implementar login con JWT y roles

# Corrección de bug
fix(trayectoria): corregir cálculo de promedio acumulado para materias reprobadas

# Documentación
docs: actualizar README con estrategia de ramas

# Refactorización
refactor(frontend): separar componentes del sidebar en archivos independientes

# Seguridad
security(auth): implementar bcrypt para hash de contraseñas
```

---

## ✅ Buenas Prácticas de Commit

### Reglas Generales

- ✍️ **Escribe en tiempo presente**: "agregar" no "agregado"
- 📏 **Máximo 72 caracteres** en el título del commit
- 🎯 **Un commit = un cambio lógico** (no mezcles múltiples funcionalidades)
- 📝 **Agrega descripción detallada** cuando el cambio sea complejo
- 🧪 **Prueba antes de hacer commit** - asegúrate que el código funciona
- 🔍 **Revisa tus cambios** con `git diff` antes del commit

### Ejemplo de Commit Detallado

```bash
feat(auth): implementar autenticación con JWT

- Se agregó middleware de autenticación
- Se implementaron roles (Alumno, Admin)
- Se validó token en cada petición protegida
- Se agregaron pruebas unitarias

Closes #42
```

---

## 🔀 Proceso de Fork y Pull Request

### 1️⃣ Hacer Fork del Repositorio

Ve a: [https://github.com/kevinRaGa/trayectoria_cucea](https://github.com/kevinRaGa/trayectoria_cucea)

Haz clic en **"Fork"** (esquina superior derecha)

Esto creará tu copia: `https://github.com/TU-USUARIO/trayectoria_cucea`

### 2️⃣ Clonar Tu Fork

```bash
# Clonar el repositorio
git clone https://github.com/TU-USUARIO/trayectoria_cucea.git
cd trayectoria_cucea
```

### 3️⃣ Configurar Repositorio Upstream

```bash
# Agregar el repositorio original como upstream
git remote add upstream https://github.com/kevinRaGa/trayectoria_cucea.git

# Verificar configuración
git remote -v
```

Deberías ver:
```
origin    https://github.com/TU-USUARIO/trayectoria_cucea.git (fetch)
origin    https://github.com/TU-USUARIO/trayectoria_cucea.git (push)
upstream  https://github.com/kevinRaGa/trayectoria_cucea.git (fetch)
upstream  https://github.com/kevinRaGa/trayectoria_cucea.git (push)
```

### 4️⃣ Crear Rama de Trabajo

```bash
# Actualizar desde upstream
git fetch upstream

# Opción A: Crear nueva rama desde upstream
git checkout -b mi-feature upstream/backend

# Opción B: Si la rama ya existe en tu fork
git checkout backend
git pull upstream backend

# Crear tu rama de trabajo
git checkout -b feature/mi-nueva-funcionalidad
```

**Nomenclatura de ramas:**
- `feature/nombre` - Nueva funcionalidad
- `fix/nombre` - Corrección de bug
- `docs/nombre` - Documentación
- `refactor/nombre` - Refactorización

### 5️⃣ Realizar Cambios y Commit

```bash
# Ver archivos modificados
git status

# Agregar archivos al staging
git add .
# O agregar archivos específicos
git add config/infrastructure/database.php

# Hacer commit con mensaje descriptivo
git commit -m "feat(db): agregar clase Database con placeholders seguros"

# Push a tu fork
git push origin feature/mi-nueva-funcionalidad
```

### 6️⃣ Crear Pull Request

1. Ve a tu fork en GitHub
2. Haz clic en **"Compare & pull request"**
3. **IMPORTANTE**: Configura correctamente el destino:

```
Base repository: kevinRaGa/trayectoria_cucea
Base branch:     backend  ← ¡Importante! No usar main/master

Head repository: TU-USUARIO/trayectoria_cucea
Compare branch:  feature/mi-nueva-funcionalidad
```

4. Completa el template del PR con:
   - Descripción clara de cambios
   - Issue relacionado (si aplica)
   - Checklist de verificación

### 7️⃣ Revisión y Aprobación

- ⏳ Espera al menos **1 aprobación** de un colaborador
- 💬 Atiende comentarios y sugerencias
- ✅ Una vez aprobado, tu PR será mergeado

### 8️⃣ Mantener tu Fork Actualizado

```bash
# Obtener cambios del repositorio original
git fetch upstream

# Actualizar tu rama local
git checkout backend
git merge upstream/backend

# Actualizar tu fork en GitHub
git push origin backend
```

---

## 🔐 Seguridad en Git

### ⚠️ Archivos Sensibles

**NUNCA** subas al repositorio:
- ❌ Archivos `.env` con credenciales
- ❌ Claves de API o tokens
- ❌ Contraseñas o secretos
- ❌ Certificados o llaves privadas

### ✅ Configuración Segura

```bash
# El archivo .gitignore ya incluye:
.env
.env.local
config/secrets.php
```

### 🛡️ Buenas Prácticas de Seguridad

- 🔑 Usa variables de entorno para datos sensibles
- 🔒 Encripta contraseñas con bcrypt/argon2
- 🚫 Nunca hardcodees credenciales en el código
- 📋 Revisa dependencias antes de agregar librerías
- 🔍 Usa `git diff` para verificar que no subes archivos sensibles

---

## 📝 Pull Requests (PR)

### Requisitos para un PR

- ✅ Título descriptivo con formato de commit
- ✅ Descripción clara de cambios realizados
- ✅ Issue relacionado (si aplica): `Closes #42`
- ✅ Sin archivos sensibles (`.env`, credenciales)
- ✅ Código probado y funcional
- ✅ Al menos 1 aprobación antes del merge

### Template de PR

Usa el template en [PULL_REQUEST.md](./PULL_REQUEST.md) para estructurar tu PR correctamente.

---

## 🎯 Resumen Rápido

1. **Fork** el repositorio
2. **Clona** tu fork localmente
3. **Configura** upstream
4. **Crea** una rama de trabajo
5. **Realiza** tus cambios
6. **Commit** con mensajes claros
7. **Push** a tu fork
8. **Crea** Pull Request a `backend`
9. **Espera** revisión y aprobación

---

## 🆘 ¿Necesitas Ayuda?

- 📖 Lee la [documentación del proyecto](./README.md)
- 💬 Abre un [issue](https://github.com/kevinRaGa/trayectoria_cucea/issues) con tus dudas
- 👥 Contacta a [@KevinRaGA](https://github.com/kevinRaGa)

---

**¡Gracias por contribuir a Trayectoria CUCEA!** 🚀

Tu colaboración ayuda a mejorar la experiencia académica de los estudiantes del CUCEA.
