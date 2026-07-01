<?php

/**
 * index.php
 * Actividad 3 - Punto de Entrada Único (Front Controller).
 *
 * Rutas (sin token, NO requieren autenticación):
 *   POST /index.php?ruta=registro   -> crea el usuario admin (password_hash)
 *   POST /index.php?ruta=login      -> valida credenciales y devuelve un JWT
 *
 * Rutas de productos (CON token JWT obligatorio):
 *   GET    /index.php?ruta=productos             -> lista todos los productos
 *   GET    /index.php?ruta=productos&id=1         -> obtiene un producto
 *   POST   /index.php?ruta=productos              -> crea un producto (body JSON)
 *   PUT    /index.php?ruta=productos&id=1          -> actualiza un producto (body JSON)
 *   DELETE /index.php?ruta=productos&id=1          -> elimina un producto
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/vendor/autoload.php';

use App\AuthService;
use App\Usuario;
use App\Producto;

// Leemos el cuerpo de la petición (para POST / PUT) como JSON
$entrada = json_decode(file_get_contents('php://input'), true) ?? [];

$ruta   = $_GET['ruta'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

$auth = new AuthService();

try {
    switch ($ruta) {

        // ------------------------------------------------------------
        // Rutas públicas (no requieren token)
        // ------------------------------------------------------------
        case 'registro':
            if ($metodo !== 'POST') {
                responder(405, ['error' => true, 'mensaje' => 'Método no permitido para esta ruta.']);
            }
            if (empty($entrada['usuario']) || empty($entrada['password'])) {
                responder(400, ['error' => true, 'mensaje' => 'Debes enviar "usuario" y "password".']);
            }

            $usuarioModelo = new Usuario();
            $resultado = $usuarioModelo->registrar($entrada['usuario'], $entrada['password']);
            responder($resultado['ok'] ? 201 : 409, $resultado);
            break;

        case 'login':
            if ($metodo !== 'POST') {
                responder(405, ['error' => true, 'mensaje' => 'Método no permitido para esta ruta.']);
            }
            if (empty($entrada['usuario']) || empty($entrada['password'])) {
                responder(400, ['error' => true, 'mensaje' => 'Debes enviar "usuario" y "password".']);
            }

            $usuarioModelo = new Usuario();
            $credencialesValidas = $usuarioModelo->validarCredenciales($entrada['usuario'], $entrada['password']);

            if (!$credencialesValidas) {
                responder(401, ['error' => true, 'mensaje' => 'Usuario o contraseña incorrectos.']);
            }

            $token = $auth->generarToken($entrada['usuario']);
            responder(200, ['ok' => true, 'token' => $token]);
            break;

        // ------------------------------------------------------------
        // Rutas protegidas (requieren token JWT válido)
        // ------------------------------------------------------------
        case 'productos':
            // Si no hay token válido, esto responde 401 y detiene la ejecución.
            $auth->validarTokenOMorir();

            $productoModelo = new Producto();

            switch ($metodo) {
                case 'GET':
                    $resultado = $productoModelo->obtener($id);
                    responder($resultado['ok'] ? 200 : 404, $resultado);
                    break;

                case 'POST':
                    $resultado = $productoModelo->crear($entrada);
                    responder($resultado['ok'] ? 201 : 400, $resultado);
                    break;

                case 'PUT':
                    if ($id === null) {
                        responder(400, ['error' => true, 'mensaje' => 'Debes indicar el id del producto (?id=).']);
                    }
                    $resultado = $productoModelo->actualizar($id, $entrada);
                    responder($resultado['ok'] ? 200 : 400, $resultado);
                    break;

                case 'DELETE':
                    if ($id === null) {
                        responder(400, ['error' => true, 'mensaje' => 'Debes indicar el id del producto (?id=).']);
                    }
                    $resultado = $productoModelo->eliminar($id);
                    responder($resultado['ok'] ? 200 : 400, $resultado);
                    break;

                default:
                    responder(405, ['error' => true, 'mensaje' => 'Método HTTP no soportado.']);
            }
            break;

        default:
            responder(404, [
                'error'   => true,
                'mensaje' => 'Ruta no encontrada. Usa ?ruta=registro, ?ruta=login o ?ruta=productos'
            ]);
    }
} catch (\Throwable $e) {
    // Control de errores genérico de último recurso
    responder(500, [
        'error'   => true,
        'mensaje' => 'Error interno del servidor.',
        'detalle' => $e->getMessage()
    ]);
}

/**
 * Helper para enviar la respuesta JSON con el código HTTP correcto
 * y terminar la ejecución.
 */
function responder(int $codigoHttp, array $payload): void
{
    http_response_code($codigoHttp);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
