<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Auditoria extends Model
{
    public function registrar(string $modulo, string $accion, string $detalle = ''): bool
    {
        try {
            $sql = "INSERT INTO auditoria (usuario_id, usuario_nombre, modulo, accion, detalle, ip)
                    VALUES (:uid, :unombre, :mod, :acc, :det, :ip)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':uid'     => auth_user('id') ?: null,
                ':unombre' => auth_user('nombre') ?: 'Sistema / Invitado',
                ':mod'     => $modulo,
                ':acc'     => $accion,
                ':det'     => $detalle,
                ':ip'      => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function listarTodos(int $limite = 100): array
    {
        $sql = "SELECT * FROM auditoria ORDER BY id DESC LIMIT :limite";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
