# 🛠️ Configuración del Sistema

## 📂 Estructura

Este directorio contiene:

- **infrastructure/**: Base de datos, conexiones y dependencias
- **routes.php**: Sistema de rutas optimizado con soporte para rutas dinámicas

---

## 🚀 Sistema de Rutas Optimizado

### Características Principales

✅ **Rutas dinámicas** con parámetros (`/alumno/{id}`, `/materia/{codigo}`)  
✅ **Métodos HTTP** (GET, POST, PUT, DELETE)  
✅ **Middlewares** para autenticación y autorización  
✅ **Grupos de rutas** con prefijos comunes  
✅ **Validación automática** de accesos

---

## 📖 Guía de Uso

### 1. Rutas Básicas

```php
// Ruta GET simple
Router::get('/inicio', function () {
   view('inicio', ['title' => 'Inicio']);
});

// Ruta POST
Router::post('/guardar', function () {
   // Procesar formulario
});
```

### 2. Rutas Dinámicas

Las rutas dinámicas usan `{parametro}` para capturar valores de la URL:

```php
// Ejemplo: /alumno/12345
Router::get('/alumno/{id}', function ($id) {
   view('alumno-detalle', [
      'alumno_id' => $id
   ]);
});

// Ejemplo: /materia/MAT101
Router::get('/materia/{codigo}', function ($codigo) {
   view('materia-detalle', [
      'codigo' => $codigo
   ]);
});

// Múltiples parámetros: /alumno/12345/materia/MAT101
Router::get('/alumno/{id}/materia/{codigo}', function ($id, $codigo) {
   view('calificacion', [
      'alumno_id' => $id,
      'materia_codigo' => $codigo
   ]);
});
```

### 3. Middlewares

Los middlewares protegen rutas y validan accesos:

#### Middlewares Disponibles

- **`auth`**: Requiere usuario autenticado
- **`guest`**: Solo usuarios NO autenticados
- **`admin`**: Solo administradores

```php
// Ruta protegida (solo usuarios autenticados)
Router::get('/perfil', function () {
   view('perfil');
}, 'auth');

// Solo invitados (no autenticados)
Router::get('/registro', function () {
   view('registro');
}, 'guest');

// Solo administradores
Router::get('/panel', function () {
   view('admin/panel');
}, 'admin');
```

#### Crear Middleware Personalizado

```php
Router::middleware('profesor', function () {
   session_start();
   if ($_SESSION['user_role'] !== 'profesor') {
      redirect('/');
      return false;
   }
   return true;
});

// Usar el middleware
Router::get('/calificaciones', function () {
   view('profesor/calificaciones');
}, 'profesor');
```

### 4. Grupos de Rutas

Agrupa rutas con prefijo y middleware común:

```php
// Todas las rutas dentro tendrán el prefijo /alumno
// y requerirán autenticación
Router::group('/alumno', 'auth', function () {
   
   // Ruta: /alumno/dashboard
   Router::get('/dashboard', function () {
      view('alumno/dashboard');
   });

   // Ruta: /alumno/materias
   Router::get('/materias', function () {
      view('alumno/materias');
   });

   // Ruta dinámica: /alumno/materia/MAT101
   Router::get('/materia/{codigo}', function ($codigo) {
      view('alumno/materia-detalle', ['codigo' => $codigo]);
   });

});
```

### 5. Métodos HTTP

```php
// GET - Obtener datos
Router::get('/usuarios', function () {
   // Listar usuarios
});

// POST - Crear nuevo
Router::post('/usuarios', function () {
   // Crear usuario
});

// PUT - Actualizar
Router::put('/usuarios/{id}', function ($id) {
   // Actualizar usuario
});

// DELETE - Eliminar
Router::delete('/usuarios/{id}', function ($id) {
   // Eliminar usuario
});
```

### 6. API Endpoints

Para crear endpoints JSON:

```php
Router::post('/api/login', function () {
   header('Content-Type: application/json');
   
   $data = json_decode(file_get_contents('php://input'), true);
   
   // Procesar login
   echo json_encode([
      'success' => true,
      'token' => 'abc123'
   ]);
});
```

---

## 🔧 Funciones Helper

### `view($archivo, $datos)`

Renderiza una vista con datos:

```php
view('inicio', [
   'title' => 'Bienvenido',
   'mensaje' => 'Hola mundo'
]);
```

### `redirect($ruta, $codigo)`

Redirige a otra ruta:

```php
redirect('/inicio');           // Redirección 302
redirect('/perfil', 301);      // Redirección permanente
```

### `Router::params($clave)`

Obtiene parámetros de la ruta actual:

```php
Router::get('/alumno/{id}/materia/{codigo}', function () {
   $id = Router::params('id');          // ID del alumno
   $codigo = Router::params('codigo');  // Código de materia
   $todos = Router::params();           // Todos los parámetros
});
```

---

## 🎯 Ejemplos Completos

### Ejemplo 1: Sistema de Blog

```php
// Listar posts
Router::get('/blog', function () {
   view('blog/lista');
});

// Ver post individual
Router::get('/blog/{slug}', function ($slug) {
   view('blog/detalle', ['slug' => $slug]);
});

// Crear post (solo admin)
Router::post('/blog/crear', function () {
   // Crear post
}, 'admin');

// Editar post (solo admin)
Router::get('/blog/{id}/editar', function ($id) {
   view('blog/editar', ['id' => $id]);
}, 'admin');
```

### Ejemplo 2: Sistema de Calificaciones

```php
Router::group('/calificaciones', 'auth', function () {
   
   // Ver todas
   Router::get('/', function () {
      view('calificaciones/lista');
   });

   // Ver por materia
   Router::get('/materia/{codigo}', function ($codigo) {
      view('calificaciones/materia', ['codigo' => $codigo]);
   });

   // Ver detalle específico
   Router::get('/alumno/{alumno_id}/materia/{materia_id}', function ($alumno_id, $materia_id) {
      view('calificaciones/detalle', [
         'alumno' => $alumno_id,
         'materia' => $materia_id
      ]);
   });

});
```

---

## ⚡ Ventajas del Nuevo Sistema

| Antes | Ahora |
|-------|-------|
| Rutas estáticas solamente | ✅ Rutas dinámicas con parámetros |
| Sin protección de rutas | ✅ Middlewares de autenticación |
| Código repetitivo | ✅ Grupos de rutas con prefijos |
| Solo método GET | ✅ GET, POST, PUT, DELETE |
| Sin validación de accesos | ✅ Validación automática por rol |
| Difícil de mantener | ✅ Código limpio y organizado |

---

## 🔐 Seguridad

El sistema incluye protección automática:

1. **Middleware `auth`**: Verifica sesión activa
2. **Middleware `guest`**: Previene acceso de usuarios autenticados a páginas de login/registro
3. **Middleware `admin`**: Valida rol de administrador
4. **Redirecciones automáticas**: Si no tienes acceso, te redirige apropiadamente

---

## 📝 Notas Adicionales

- Las rutas se procesan en orden de definición
- Los parámetros dinámicos aceptan letras, números y guiones bajos
- Los middlewares se ejecutan antes de la función de la ruta
- El sistema es compatible con subdirectorios (ej: `/carpeta/proyecto/`)

---

## 🆘 Troubleshooting

### La ruta no funciona

- Verifica que el método HTTP sea correcto (GET vs POST)
- Revisa que el middleware no esté bloqueando el acceso
- Confirma que el patrón de la ruta coincida con la URL

### Error 404 en todas las rutas

- Verifica que el archivo `.htaccess` esté configurado correctamente
- Asegúrate de que `mod_rewrite` esté habilitado en Apache

### Los parámetros no se capturan

- Usa `{parametro}` en la definición de la ruta
- El nombre del parámetro en la función debe coincidir

---

**Última actualización**: Octubre 2025
