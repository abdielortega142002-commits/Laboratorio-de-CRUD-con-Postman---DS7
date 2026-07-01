# 🔐 Laboratorio API REST con PHP + JWT + Postman

Proyecto listo para ejecutar en WAMP o XAMPP. Implementa un CRUD de productos
protegido con autenticación JWT, contraseñas hasheadas con BCRYPT, y un
Front Controller centralizado.

## 📁 Estructura del proyecto

```
ApiRestFull/
├── config.php              <- Clave secreta JWT y credenciales BD (no subir a git)
├── database.sql            <- Script para crear la base de datos
├── index.php                <- Front Controller (punto de entrada único)
├── login.html                <- Interfaz para registrar/loguear el usuario admin
├── composer.json
├── .gitignore
├── src/
│   ├── AuthService.php       <- Genera y valida tokens JWT
│   ├── Conexion.php          <- Conexión PDO a MySQL
│   ├── Usuario.php           <- Registro y login (password_hash / password_verify)
│   └── Producto.php          <- CRUD de productos
├── vendor/
│   ├── autoload.php           <- Autoload manual (App\ y Firebase\JWT\)
│   └── firebase/php-jwt/      <- Librería oficial firebase/php-jwt
└── Postman_Collection.json   <- Colección lista para importar en Postman
```

> ℹ️ Este proyecto ya incluye la librería `firebase/php-jwt` descargada y
> lista en `vendor/`, junto con un autoloader manual, así que **funciona
> directamente sin necesidad de tener Composer instalado**. Si más adelante
> quieres usar Composer normalmente, puedes borrar `vendor/` y ejecutar
> `composer install`.

---

## 🚀 Paso 1: Copiar el proyecto al servidor

Copia la carpeta `ApiRestFull` dentro de:
- **XAMPP:** `C:\xampp\htdocs\`
- **WAMP:** `C:\wamp64\www\`

## 🚀 Paso 2: Crear la base de datos

1. Inicia Apache y MySQL desde el panel de XAMPP/WAMP.
2. Abre **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Ve a la pestaña **SQL** y pega el contenido completo de `database.sql`,
   luego ejecútalo. Esto crea la base `lab_api_jwt` con las tablas
   `usuarios` y `productos`.

## 🚀 Paso 3: Revisar la configuración

Abre `config.php` y, si tu usuario/contraseña de MySQL son distintos al
valor por defecto (`root` sin contraseña), ajústalos ahí.

## 🚀 Paso 4: Probar que el servidor responde

Abre en el navegador:
```
http://localhost/ApiRestFull/index.php?ruta=productos
```
Como no enviaste token, debe devolver un **401 Unauthorized** en JSON.
Eso confirma que la seguridad está funcionando. ✅

## 🚀 Paso 5: Registrar el usuario admin

Abre en el navegador:
```
http://localhost/ApiRestFull/login.html
```
Usa el formulario de "Registrar usuario" (ejemplo: usuario `admin`,
contraseña `admin123`). Esto guarda la contraseña ya hasheada con BCRYPT.

## 🚀 Paso 6: Iniciar sesión y obtener el token

En la misma página, usa el formulario de "Iniciar sesión" con las mismas
credenciales. El resultado mostrará un JSON con el `token` JWT. Cópialo,
lo necesitarás en Postman.

## 🚀 Paso 7: Probar con Postman

1. Abre Postman y ve a **Import** → selecciona el archivo
   `Postman_Collection.json` incluido en este proyecto.
2. En la colección, edita la variable `base_url` si tu proyecto no está en
   `http://localhost/ApiRestFull` (por ejemplo, si usas otro puerto).
3. Ejecuta las peticiones en este orden:
   - **Login** → copia el `token` de la respuesta.
   - Pega ese token en la variable de colección `token`
     (clic en la colección → pestaña *Variables*).
   - **Productos - Sin token (401)** → confirma el escenario negativo.
   - **Productos - Crear (POST)**
   - **Productos - Listar (GET)**
   - **Productos - Actualizar (PUT)**
   - **Productos - Eliminar (DELETE)**

---

## 🧪 Endpoints disponibles

| Método | Endpoint                              | Requiere token | Descripción                  |
|--------|----------------------------------------|-----------------|-------------------------------|
| POST   | `/index.php?ruta=registro`            | No              | Crea el usuario admin         |
| POST   | `/index.php?ruta=login`               | No              | Devuelve el token JWT         |
| GET    | `/index.php?ruta=productos`           | Sí              | Lista todos los productos     |
| GET    | `/index.php?ruta=productos&id=1`      | Sí              | Obtiene un producto por id    |
| POST   | `/index.php?ruta=productos`           | Sí              | Crea un producto              |
| PUT    | `/index.php?ruta=productos&id=1`      | Sí              | Actualiza un producto         |
| DELETE | `/index.php?ruta=productos&id=1`      | Sí              | Elimina un producto           |

Para las rutas protegidas, agrega el header:
```
Authorization: Bearer <tu_token>
```

## 📦 Body de ejemplo (POST / PUT productos)

```json
{
  "codigo": "A001",
  "producto": "Mouse óptico",
  "precio": 10.50,
  "cantidad": 5
}
```

---

## ✅ Checklist según la rúbrica del laboratorio

- [x] Implementación de Seguridad con tokens JWT (`AuthService.php`)
- [x] `password_hash()` con `PASSWORD_BCRYPT` + `password_verify()`, con interfaz (`login.html`)
- [x] Métodos GET y POST implementados
- [x] Métodos PUT y DELETE funcionales
- [x] Centralización con `switch` (`index.php`)
- [x] Colección de Postman incluida
- [x] Código organizado en `src/` y comentado
- [x] Control de errores (try/catch, validación de campos, códigos HTTP)