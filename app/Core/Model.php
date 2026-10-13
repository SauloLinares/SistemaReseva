<?php

namespace App\Core;

use App\Config\Database;
use PDO;

/**
 * Clase base Model
 * Todos los modelos heredan de esta clase y obtienen automáticamente la conexión PDO.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }
}
