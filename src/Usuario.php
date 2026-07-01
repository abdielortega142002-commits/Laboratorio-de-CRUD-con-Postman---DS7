<?php
namespace App;

use PDO;

/**
 * Usuario
 * Actividad 2 - Hashing de Contraseñas.
 * Las contraseñas NUNCA se guardan en texto plano: se usa password_hash()
 * al crear el usuario y password_verify() al momento del login.
 */
class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::obtener();
    }

    /**
     * Crea un usuario nuevo con la contraseña hasheada con BCRYPT.
     */
    public function registrar(string $usuario, string $passwordPlano): array
    {
        // Evitar usuarios duplicados
        $stmt = $this->db->prepare('SELECT id FROM usuarios WHERE usuario = :usuario');
        $stmt->execute(['usuario' => $usuario]);
        if ($stmt->fetch()) {
            return ['ok' => false, 'mensaje' => 'El usuario ya existe.'];
        }

        $hash = password_hash($passwordPlano, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (usuario, password) VALUES (:usuario, :password)'
        );
        $stmt->execute([
            'usuario'  => $usuario,
            'password' => $hash,
        ]);

        return ['ok' => true, 'mensaje' => 'Usuario registrado correctamente.'];
    }

    /**
     * Verifica las credenciales contra el hash guardado.
     * Devuelve true si son correctas, false si no.
     */
    public function validarCredenciales(string $usuario, string $passwordPlano): bool
    {
        $stmt = $this->db->prepare('SELECT password FROM usuarios WHERE usuario = :usuario');
        $stmt->execute(['usuario' => $usuario]);
        $fila = $stmt->fetch();

        if (!$fila) {
            return false;
        }

        return password_verify($passwordPlano, $fila['password']);
    }
}
