<?php
namespace App;

require_once __DIR__ . '/../config.php';

use PDO;
use PDOException;

/**
 * Conexion
 * Clase responsable de crear y entregar una conexión PDO a MySQL.
 */
class Conexion
{
    public static function obtener(): PDO
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'error'   => true,
                'mensaje' => 'No se pudo conectar a la base de datos.',
                'detalle' => $e->getMessage()
            ]);
            exit;
        }
    }
}
