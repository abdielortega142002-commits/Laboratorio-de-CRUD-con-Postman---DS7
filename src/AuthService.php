<?php
namespace App;

require_once __DIR__ . '/../config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

/**
 * AuthService
 * Actividad 1 - Construcción del Guardián.
 * Centraliza la generación y validación de tokens JWT usando firebase/php-jwt.
 */
class AuthService
{
    private string $secretKey;
    private string $algo;
    private int $expSeconds;

    public function __construct()
    {
        $this->secretKey  = JWT_SECRET_KEY;
        $this->algo       = JWT_ALGO;
        $this->expSeconds = JWT_EXP_SECONDS;
    }

    /**
     * Genera un token JWT firmado para un usuario autenticado.
     */
    public function generarToken(string $usuario): string
    {
        $issuedAt  = time();
        $expire    = $issuedAt + $this->expSeconds;

        $payload = [
            'iat'     => $issuedAt,   // emitido en
            'exp'     => $expire,     // expira en
            'usuario' => $usuario,
        ];

        return JWT::encode($payload, $this->secretKey, $this->algo);
    }

    /**
     * Obtiene el token enviado en el header Authorization: Bearer <token>
     * Devuelve null si no viene ningún token.
     */
    public function obtenerTokenDesdeHeader(): ?string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        // Algunos servidores no exponen "Authorization" directo en $headers,
        // por eso también revisamos $_SERVER como respaldo.
        $authHeader = $headers['Authorization']
            ?? $headers['authorization']
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? null;

        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Valida el token recibido. Si es inválido o no existe, corta la
     * ejecución respondiendo 401 en formato JSON.
     * Si es válido, devuelve el payload decodificado.
     */
    public function validarTokenOMorir(): object
    {
        $token = $this->obtenerTokenDesdeHeader();

        if (!$token) {
            $this->responderNoAutorizado('No se envió ningún token. Acceso denegado.');
        }

        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algo));
            return $decoded;
        } catch (ExpiredException $e) {
            $this->responderNoAutorizado('El token ha expirado.');
        } catch (SignatureInvalidException $e) {
            $this->responderNoAutorizado('Firma del token inválida.');
        } catch (Exception $e) {
            $this->responderNoAutorizado('Token inválido: ' . $e->getMessage());
        }

        // Nunca debería llegar aquí porque responderNoAutorizado hace exit,
        // pero PHP exige un valor de retorno declarado.
        exit;
    }

    private function responderNoAutorizado(string $mensaje): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'error'   => true,
            'mensaje' => $mensaje
        ]);
        exit;
    }
}
