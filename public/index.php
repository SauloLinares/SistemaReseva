<?php

require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

$database = new Database();

$conexion = $database->conectar();

echo "Conexión exitosa a MySQL";
?>