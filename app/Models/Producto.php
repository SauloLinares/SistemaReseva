<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/** 
 * Modelo Producto 
 * Representa la tabla 'productos' en MySQL y contiene las operaciones CRUD con PDO. 
 */
class Producto extends Model
{
    /** 
     * Obtiene todos los productos ordenados por ID descendente 
     */
    public function listarTodos(): array
    {
        $sql = "SELECT * FROM productos ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /** 
     * Obtiene un producto por su ID único 
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT * FROM productos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $producto = $stmt->fetch();
        return $producto ?: null;
    }

    /** 
     * Verifica si un código de producto ya está registrado (para evitar duplicados) 
     */
    public function existeCodigo(string $codigo, int $idExcluir = 0): bool
    {
        $sql = "SELECT COUNT(*) as total FROM productos WHERE codigo = :codigo AND id != :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':codigo' => trim($codigo),
            ':id'     => $idExcluir
        ]);
        $res = $stmt->fetch();
        return ((int)($res['total'] ?? 0)) > 0;
    }

    /** 
     * Registra un nuevo producto con sentencias preparadas seguras 
     */


    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, categoria, 
estado)  
                VALUES (:codigo, :nombre, :descripcion, :precio, :stock, :categoria, :estado)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':codigo'      => trim($datos['codigo'] ?? ''),
            ':nombre'      => trim($datos['nombre'] ?? ''),
            ':descripcion' => trim($datos['descripcion'] ?? ''),
            ':precio'      => (float)($datos['precio'] ?? 0.0),
            ':stock'       => (int)($datos['stock'] ?? 0),
            ':categoria'   => trim($datos['categoria'] ?? ''),
            ':estado'      => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    /** 
     * Actualiza los datos de un producto existente 
     */
    public function actualizar(int $id, array $datos): bool
    {
        $sql = "UPDATE productos  
                SET codigo = :codigo,  
                    nombre = :nombre,  
                    descripcion = :descripcion,  
                    precio = :precio,  
                    stock = :stock,  
                    categoria = :categoria,  
                    estado = :estado  
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'          => $id,
            ':codigo'      => trim($datos['codigo'] ?? ''),
            ':nombre'      => trim($datos['nombre'] ?? ''),
            ':descripcion' => trim($datos['descripcion'] ?? ''),
            ':precio'      => (float)($datos['precio'] ?? 0.0),
            ':stock'       => (int)($datos['stock'] ?? 0),
            ':categoria'   => trim($datos['categoria'] ?? ''),
            ':estado'      => isset($datos['estado']) ? (int)$datos['estado'] : 1
        ]);
    }

    /** 
     * Elimina físicamente un producto por su ID 
     */
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM productos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /** 
     * Cuenta el total de productos en inventario 
     */
    public function contarTotal(): int
    {
        $sql = "SELECT COUNT(*) as total FROM productos";
        $stmt = $this->db->query($sql);
        $resultado = $stmt->fetch();
        return (int)($resultado['total'] ?? 0);
    }

    /** 
     * Cuenta cuántos productos están activos 
     */
    public function contarActivos(): int
    {
        $sql = "SELECT COUNT(*) as total FROM productos WHERE estado = 1";
        $stmt = $this->db->query($sql);
        $resultado = $stmt->fetch();
        return (int)($resultado['total'] ?? 0);
    }
}
