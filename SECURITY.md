# 🔒 Política de Seguridad - Trayectoria CUCEA

## 🎯 Objetivo

La seguridad de **Trayectoria CUCEA** y la privacidad de sus usuarios son nuestra máxima prioridad. Este documento describe nuestras políticas de seguridad, cómo reportar vulnerabilidades y las mejores prácticas para contribuyentes.

---

## 🛡️ Versiones Soportadas

Actualmente damos soporte de seguridad a las siguientes versiones:

| Versión | Soportada | Notas                        |
| ------- | --------- | ---------------------------- |
| 1.x.x   | ✅ Sí     | Versión actual en producción |
| < 1.0   | ❌ No     | Versiones de desarrollo      |

---

## 🚨 Reportar una Vulnerabilidad

### 📍 ¿Encontraste una vulnerabilidad de seguridad?

**NO** abras un issue público. En su lugar, repórtala de forma privada para proteger a nuestros usuarios.

### 📧 Proceso de Reporte

1. **Envía un email a:**

   - 📧 [Agregar email de seguridad del equipo]
   - 👤 Contacta directamente a [@KevinRaGA](https://github.com/kevinRaGa)

2. **Incluye la siguiente información:**

   - 🔍 Descripción detallada de la vulnerabilidad
   - 📋 Pasos para reproducir el problema
   - 🎯 Impacto potencial (qué datos o funcionalidades están en riesgo)
   - 💡 Posible solución (si la conoces)
   - 🖼️ Capturas de pantalla o logs (si aplica)

3. **Tiempo de respuesta:**
   - ⏱️ Respuesta inicial: **48 horas**
   - 🔧 Evaluación y plan de acción: **5 días hábiles**
   - ✅ Resolución: Depende de la severidad (crítica: 7 días, alta: 14 días)

### 🏆 Reconocimiento

Si deseas ser reconocido públicamente por tu reporte, lo haremos en nuestro archivo de agradecimientos una vez que la vulnerabilidad haya sido corregida.

---

## 🔐 Medidas de Seguridad Implementadas

### 🔑 Autenticación y Autorización

- ✅ **JWT (JSON Web Tokens)** para autenticación
- ✅ Tokens con **expiración de 1 hora**
- ✅ **Refresh tokens** para renovación segura
- ✅ Tokens almacenados de forma segura (HttpOnly cookies)
- ✅ **Roles diferenciados**: Alumno, Administrador Académico, Administrador Técnico
- ✅ Verificación de permisos en cada endpoint protegido

### 🗄️ Seguridad de Base de Datos

- ✅ **Prepared statements** y placeholders tipados (anti SQL Injection)
- ✅ Contraseñas hasheadas
- ✅ Conexiones cifradas a la base de datos
- ✅ Validación y sanitización de inputs
- ✅ Principio de mínimo privilegio en usuarios de BD
- ✅ Respaldos automáticos diarios

### 🌐 Seguridad Web

- ✅ **HTTPS obligatorio** en producción
- ✅ Headers de seguridad configurados:
  - `Content-Security-Policy`
  - `X-Frame-Options: DENY`
  - `X-Content-Type-Options: nosniff`
  - `Strict-Transport-Security`
- ✅ Protección contra **XSS** (Cross-Site Scripting)
- ✅ Protección contra **CSRF** (Cross-Site Request Forgery)
- ✅ Rate limiting en endpoints críticos
- ✅ Validación de origen de peticiones (CORS configurado)

### 📝 Manejo de Datos Sensibles

- ✅ Variables de entorno para credenciales (`.env`)
- ✅ `.gitignore` configurado para excluir archivos sensibles
- ✅ Logs sin información sensible (contraseñas, tokens)
- ✅ Cifrado de datos personales en BD
- ✅ Cumplimiento con leyes de protección de datos

### 🔄 Dependencias y Librerías

- ✅ Revisión regular de dependencias
- ✅ Actualización de parches de seguridad
- ✅ Uso de librerías mantenidas y confiables
- ✅ Análisis de vulnerabilidades conocidas (CVEs)

---

## 🚫 Vulnerabilidades Conocidas y Mitigadas

Mantenemos un registro de vulnerabilidades que han sido identificadas y corregidas:

### Historial

_Actualmente no hay vulnerabilidades reportadas públicamente._

---

## 📋 Checklist de Seguridad para Contribuyentes

Antes de hacer un Pull Request, verifica:

### Código

- [ ] ❌ No hay contraseñas hardcodeadas
- [ ] ❌ No hay claves de API en el código
- [ ] ❌ No hay archivos `.env` en el commit
- [ ] ✅ Uso de prepared statements para queries SQL
- [ ] ✅ Validación de inputs del usuario
- [ ] ✅ Sanitización de outputs (escapar HTML)
- [ ] ✅ Verificación de permisos en funciones sensibles
- [ ] ✅ Manejo apropiado de errores (sin exponer información sensible)

### Autenticación

- [ ] ✅ Verificación de token JWT en endpoints protegidos
- [ ] ✅ Validación de roles y permisos
- [ ] ✅ Logout correcto (invalidación de tokens)
- [ ] ✅ No se exponen IDs de sesión en URLs

### Base de Datos

- [ ] ✅ Uso de la clase `Database` con placeholders
- [ ] ✅ No hay consultas SQL construidas con concatenación de strings
- [ ] ✅ Validación de tipos de datos
- [ ] ✅ Límites en queries (LIMIT, paginación)

---

## 🔧 Mejores Prácticas para Desarrollo Seguro

### 1. **Validación de Entrada**

```php
// ❌ Malo
$id = $_GET['id'];
$query = "SELECT * FROM users WHERE id = $id";

// ✅ Bueno
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$user = $db->getRow("SELECT * FROM users WHERE id = ?i", $id);
```

### 2. **Escape de Salida**

```php
// ❌ Malo
echo "<div>" . $userData['name'] . "</div>";

// ✅ Bueno
echo "<div>" . htmlspecialchars($userData['name'], ENT_QUOTES, 'UTF-8') . "</div>";
```

### 3. **Manejo de Contraseñas**

```php
// ❌ Malo
$password = md5($input_password);

// ✅ Bueno
$password = password_hash($input_password, PASSWORD_ARGON2ID);
```

### 4. **Verificación de JWT**

```php
// ✅ Verificar siempre el token en endpoints protegidos
if (!$jwt->verify($token)) {
    http_response_code(401);
    exit('Token inválido');
}
```

---

## 📚 Recursos Adicionales

### Documentación de Seguridad

- 🔗 [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- 🔗 [PHP Security Guide](https://phptherightway.com/#security)
- 🔗 [JWT Best Practices](https://tools.ietf.org/html/rfc8725)
- 🔗 [SQL Injection Prevention](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)

### Herramientas Útiles

- 🛠️ **PHPStan** - Análisis estático de código PHP
- 🛠️ **Composer Audit** - Revisión de vulnerabilidades en dependencias
- 🛠️ **OWASP ZAP** - Scanner de vulnerabilidades web

---

## 🤝 Compromiso de Divulgación Responsable

Nos comprometemos a:

- ✅ Responder rápidamente a reportes de seguridad
- ✅ Mantener informado al reportador del progreso
- ✅ Dar crédito apropiado (si el reportador lo desea)
- ✅ Publicar detalles solo después de que la vulnerabilidad sea corregida
- ✅ Implementar correcciones en el menor tiempo posible según severidad

---

## 📞 Contacto

Para consultas de seguridad:

- 📧 **Email:** [Agregar email de seguridad]
- 👤 **Responsable:** [@KevinRaGA](https://github.com/kevinRaGa)
- 🔗 **Proyecto:** [https://github.com/kevinRaGa/trayectoria_cucea](https://github.com/kevinRaGa/trayectoria_cucea)

---

**Última actualización:** Octubre 2025

**Gracias por ayudarnos a mantener seguro Trayectoria CUCEA** 🛡️
