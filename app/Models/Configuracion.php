<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Configuracion extends Model
{
    public function obtenerTodos(): array
    {
        $filas = $this->db->query("SELECT * FROM configuracion ORDER BY grupo ASC, id ASC")->fetchAll();
        $config = [];
        foreach ($filas as $f) {
            $config[$f['clave']] = $f['valor'];
        }
        return $config;
    }

    /**
     * Datos institucionales del hotel (claves hotel_*) para vouchers y comprobantes
     */
    public function obtenerDatosHotel(): array
    {
        $filas = $this->db->query("SELECT clave, valor FROM configuracion WHERE clave LIKE 'hotel\\_%'")->fetchAll();
        $hotel = [];
        foreach ($filas as $f) {
            $hotel[$f['clave']] = $f['valor'];
        }
        return $hotel;
    }

    public function obtenerTodosDetalle(): array
    {
        return $this->db->query("SELECT * FROM configuracion ORDER BY grupo ASC, id ASC")->fetchAll();
    }

    public function obtener(string $clave, string $default = ''): string
    {
        $stmt = $this->db->prepare("SELECT valor FROM configuracion WHERE clave = :c LIMIT 1");
        $stmt->execute([':c' => $clave]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? (string)$val : $default;
    }

    public function guardar(string $clave, string $valor, string $grupo = 'general', ?string $descripcion = null): bool
    {
        $stmt = $this->db->prepare("INSERT INTO configuracion (clave, valor, grupo, descripcion)
                                    VALUES (:clave, :valor, :grupo, :descripcion)
                                    ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        return $stmt->execute([
            ':clave'       => $clave,
            ':valor'       => $valor,
            ':grupo'       => $grupo,
            ':descripcion' => $descripcion
        ]);
    }

    public function guardarMultiples(array $clavesValores, string $grupo = 'general'): bool
    {
        foreach ($clavesValores as $clave => $valor) {
            $this->guardar($clave, (string)$valor, $grupo);
        }
        return true;
    }
}
