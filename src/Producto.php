<?php
namespace App;

use PDO;
use PDOException;

/**
 * Producto
 * Actividad 4 - Implementación del CRUD Seguro.
 * Contiene la lógica de acceso a datos para crear, leer, actualizar
 * y eliminar productos. Incluye control de errores.
 */
class Producto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::obtener();
    }

    /** GET - obtiene todos los productos, o uno solo si se pasa $id */
    public function obtener(?int $id = null): array
    {
        try {
            if ($id !== null) {
                $stmt = $this->db->prepare('SELECT * FROM productos WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $fila = $stmt->fetch();
                return $fila ? ['ok' => true, 'data' => $fila] : ['ok' => false, 'mensaje' => 'Producto no encontrado.'];
            }

            $stmt = $this->db->query('SELECT * FROM productos ORDER BY id DESC');
            return ['ok' => true, 'data' => $stmt->fetchAll()];
        } catch (PDOException $e) {
            return ['ok' => false, 'mensaje' => 'Error al consultar productos: ' . $e->getMessage()];
        }
    }

    /** POST - crea un producto nuevo */
    public function crear(array $datos): array
    {
        $faltantes = $this->validarCampos($datos, ['codigo', 'producto', 'precio', 'cantidad']);
        if ($faltantes) {
            return ['ok' => false, 'mensaje' => 'Faltan campos: ' . implode(', ', $faltantes)];
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO productos (codigo, producto, precio, cantidad) 
                 VALUES (:codigo, :producto, :precio, :cantidad)'
            );
            $stmt->execute([
                'codigo'   => $datos['codigo'],
                'producto' => $datos['producto'],
                'precio'   => $datos['precio'],
                'cantidad' => $datos['cantidad'],
            ]);

            return [
                'ok'   => true,
                'data' => [
                    'id' => $this->db->lastInsertId(),
                ] + $datos,
            ];
        } catch (PDOException $e) {
            return ['ok' => false, 'mensaje' => 'Error al crear el producto: ' . $e->getMessage()];
        }
    }

    /** PUT - actualiza un producto existente por completo */
    public function actualizar(int $id, array $datos): array
    {
        $faltantes = $this->validarCampos($datos, ['codigo', 'producto', 'precio', 'cantidad']);
        if ($faltantes) {
            return ['ok' => false, 'mensaje' => 'Faltan campos: ' . implode(', ', $faltantes)];
        }

        try {
            $stmt = $this->db->prepare('SELECT id FROM productos WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if (!$stmt->fetch()) {
                return ['ok' => false, 'mensaje' => 'Producto no encontrado.'];
            }

            $stmt = $this->db->prepare(
                'UPDATE productos 
                 SET codigo = :codigo, producto = :producto, precio = :precio, cantidad = :cantidad 
                 WHERE id = :id'
            );
            $stmt->execute([
                'codigo'   => $datos['codigo'],
                'producto' => $datos['producto'],
                'precio'   => $datos['precio'],
                'cantidad' => $datos['cantidad'],
                'id'       => $id,
            ]);

            return ['ok' => true, 'mensaje' => 'Producto actualizado correctamente.'];
        } catch (PDOException $e) {
            return ['ok' => false, 'mensaje' => 'Error al actualizar el producto: ' . $e->getMessage()];
        }
    }

    /** DELETE - elimina un producto por id */
    public function eliminar(int $id): array
    {
        try {
            $stmt = $this->db->prepare('SELECT id FROM productos WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if (!$stmt->fetch()) {
                return ['ok' => false, 'mensaje' => 'Producto no encontrado.'];
            }

            $stmt = $this->db->prepare('DELETE FROM productos WHERE id = :id');
            $stmt->execute(['id' => $id]);

            return ['ok' => true, 'mensaje' => 'Producto eliminado correctamente.'];
        } catch (PDOException $e) {
            return ['ok' => false, 'mensaje' => 'Error al eliminar el producto: ' . $e->getMessage()];
        }
    }

    /** Verifica que existan todos los campos requeridos en el arreglo de datos */
    private function validarCampos(array $datos, array $requeridos): array
    {
        $faltantes = [];
        foreach ($requeridos as $campo) {
            if (!array_key_exists($campo, $datos) || $datos[$campo] === '' || $datos[$campo] === null) {
                $faltantes[] = $campo;
            }
        }
        return $faltantes;
    }
}
