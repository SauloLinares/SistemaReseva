<?php

namespace App\Core;

use App\Config\Database;
use PDO;
use ZipArchive;

/**
 * Respaldo completo del sistema en un .zip: la base de datos como script SQL (generado desde
 * PHP, así funciona aunque el servidor no tenga mysqldump) y la carpeta public/uploads (logo
 * y fotos). Restaurar se hace a mano a propósito: si saliera mal, borraría datos reales.
 * Las instrucciones van dentro del .zip (LEEME.txt).
 */
class Respaldo
{
    /** Tablas de trabajo interno: se respalda su estructura pero no su contenido */
    private const SIN_DATOS = ['operaciones', 'intentos_login'];
    private const FILAS_POR_INSERT = 200;

    /**
     * Arma el .zip en un archivo temporal y devuelve su ruta (quien lo use debe borrarlo).
     */
    public static function generar(): string
    {
        $db = Database::conectar();
        $zipRuta = tempnam(sys_get_temp_dir(), 'respaldo');
        $sqlRuta = tempnam(sys_get_temp_dir(), 'respaldo-sql');

        $sql = fopen($sqlRuta, 'wb');
        self::volcarBase($db, $sql);
        fclose($sql);

        $zip = new ZipArchive();
        if ($zip->open($zipRuta, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo del respaldo.');
        }
        $zip->addFile($sqlRuta, 'base-de-datos.sql');
        $zip->addFromString('LEEME.txt', self::instrucciones());

        $uploads = realpath(__DIR__ . '/../../public/uploads');
        if ($uploads !== false) {
            $archivos = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploads, \FilesystemIterator::SKIP_DOTS));
            foreach ($archivos as $archivo) {
                if ($archivo->isFile()) {
                    $relativo = str_replace('\\', '/', substr($archivo->getPathname(), strlen($uploads) + 1));
                    $zip->addFile($archivo->getPathname(), 'uploads/' . $relativo);
                }
            }
        }
        $zip->close();
        unlink($sqlRuta);
        return $zipRuta;
    }

    /** Escribe en $salida el script SQL que recrea todas las tablas con sus datos. */
    private static function volcarBase(PDO $db, $salida): void
    {
        $base = $db->query('SELECT DATABASE()')->fetchColumn();
        fwrite($salida, "-- Respaldo de la base `{$base}` · " . date('Y-m-d H:i:s') . ' (' . date_default_timezone_get() . ")\n"
            . "-- Generado por el sistema (Configuración → Respaldo). Ver LEEME.txt para restaurarlo.\n\n"
            . "SET NAMES utf8mb4;\nSET time_zone = '" . (new \DateTime())->format('P') . "';\n"
            . "SET FOREIGN_KEY_CHECKS = 0;\nSET UNIQUE_CHECKS = 0;\n\n");

        $tablas = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
        foreach ($tablas as [$tabla]) {
            $nombre = '`' . str_replace('`', '``', $tabla) . '`';
            $crear = $db->query("SHOW CREATE TABLE {$nombre}")->fetch(PDO::FETCH_NUM)[1];
            fwrite($salida, "-- Tabla {$tabla}\nDROP TABLE IF EXISTS {$nombre};\n{$crear};\n\n");

            if (in_array($tabla, self::SIN_DATOS, true)) {
                continue;
            }
            // Sin buffer: una tabla grande no se carga entera en memoria
            $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            $filas = $db->query("SELECT * FROM {$nombre}");
            $lote = [];
            $columnas = null;
            while ($fila = $filas->fetch(PDO::FETCH_ASSOC)) {
                $columnas ??= '(' . implode(', ', array_map(fn($c) => '`' . str_replace('`', '``', $c) . '`', array_keys($fila))) . ')';
                $lote[] = '(' . implode(', ', array_map(fn($v) => $v === null ? 'NULL' : $db->quote((string) $v), $fila)) . ')';
                if (count($lote) >= self::FILAS_POR_INSERT) {
                    fwrite($salida, "INSERT INTO {$nombre} {$columnas} VALUES\n" . implode(",\n", $lote) . ";\n");
                    $lote = [];
                }
            }
            $filas->closeCursor();
            $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            if ($lote) {
                fwrite($salida, "INSERT INTO {$nombre} {$columnas} VALUES\n" . implode(",\n", $lote) . ";\n");
            }
            fwrite($salida, "\n");
        }
        fwrite($salida, "SET UNIQUE_CHECKS = 1;\nSET FOREIGN_KEY_CHECKS = 1;\n");
    }

    private static function instrucciones(): string
    {
        return "RESPALDO DEL SISTEMA DE RESERVAS\r\n"
            . "Generado el " . date('d/m/Y \a \l\a\s H:i') . "\r\n\r\n"
            . "Contenido\r\n"
            . "  base-de-datos.sql   Todas las tablas con sus datos (huéspedes, reservas, pagos, caja, usuarios...).\r\n"
            . "  uploads/            Logo del hotel y fotos de habitaciones y usuarios.\r\n\r\n"
            . "IMPORTANTE: contiene datos personales de los huéspedes y las contraseñas cifradas de los\r\n"
            . "usuarios. Guárdalo en un lugar seguro y no lo compartas.\r\n\r\n"
            . "Cómo restaurarlo (reemplaza TODOS los datos actuales por los del respaldo)\r\n"
            . "  1. Haz primero un respaldo de lo que hay ahora, por si acaso.\r\n"
            . "  2. Base de datos:   mysql -u USUARIO -p NOMBRE_DE_LA_BASE < base-de-datos.sql\r\n"
            . "     (o en phpMyAdmin / HeidiSQL: elegir la base > Importar > base-de-datos.sql)\r\n"
            . "  3. Archivos: copiar el contenido de la carpeta uploads/ a public/uploads/ del sistema.\r\n";
    }
}
