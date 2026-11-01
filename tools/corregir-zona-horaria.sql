-- Corrige las horas que PHP guardó en UTC (5 horas de más) antes del commit 09f92e4, cuando PHP
-- corría en UTC y MySQL en hora de Perú. Un registro afectado se reconoce porque su fecha quedó
-- unos 300 minutos después de created_at (que pone MySQL); los demás no se tocan.
--
-- Uso, en la base de cada instalación y con el código nuevo ya desplegado:
--   1. Respaldar:  mysqldump <base> pagos turnos_caja > antes-zona-horaria.sql
--   2. mysql <base> < tools/corregir-zona-horaria.sql
-- Supone que el servidor MySQL de esa instalación está en hora de Perú (SELECT NOW() lo muestra).
-- Ejecutarlo dos veces no hace nada: los registros ya corregidos dejan de cumplir el criterio.

SET time_zone = '-05:00';

-- Lo que se va a corregir
SELECT 'pagos' AS tabla, id, fecha_pago AS antes, fecha_pago - INTERVAL 5 HOUR AS despues
  FROM pagos
 WHERE TIMESTAMPDIFF(MINUTE, created_at, fecha_pago) BETWEEN 295 AND 305
UNION ALL
SELECT 'turnos_caja', id, fecha_apertura, fecha_apertura - INTERVAL 5 HOUR
  FROM turnos_caja
 WHERE TIMESTAMPDIFF(MINUTE, created_at, fecha_apertura) BETWEEN 295 AND 305;

START TRANSACTION;
UPDATE pagos SET fecha_pago = fecha_pago - INTERVAL 5 HOUR
 WHERE TIMESTAMPDIFF(MINUTE, created_at, fecha_pago) BETWEEN 295 AND 305;
-- El cierre de un turno abierto con la hora mal también la tiene mal
UPDATE turnos_caja SET fecha_apertura = fecha_apertura - INTERVAL 5 HOUR,
       fecha_cierre = IF(fecha_cierre IS NULL, NULL, fecha_cierre - INTERVAL 5 HOUR)
 WHERE TIMESTAMPDIFF(MINUTE, created_at, fecha_apertura) BETWEEN 295 AND 305;
COMMIT;
