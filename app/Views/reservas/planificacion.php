<?php
/**
 * Planificación por habitación: filas = habitaciones, columnas = días.
 * Cada estadía es una barra que ocupa sus noches (de la llegada al día anterior a la salida).
 */
$inicio = new DateTimeImmutable($desde);
$hoy = date('Y-m-d');
$diasSemana = [1 => 'lun', 2 => 'mar', 3 => 'mié', 4 => 'jue', 5 => 'vie', 6 => 'sáb', 7 => 'dom'];
$meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
$etiquetaEstado = ['confirmada' => 'Confirmada', 'pendiente' => 'Pendiente', 'en_estadia' => 'En estadía', 'finalizada' => 'Finalizada'];

// Columnas del período
$columnas = [];
for ($i = 0; $i < $dias; $i++) {
    $d = $inicio->modify("+{$i} days");
    $columnas[] = [
        'fecha'   => $d->format('Y-m-d'),
        'dia'     => (int)$d->format('j'),
        'semana'  => $diasSemana[(int)$d->format('N')],
        'finde'   => (int)$d->format('N') >= 6,
        'hoy'     => $d->format('Y-m-d') === $hoy,
        'pasado'  => $d->format('Y-m-d') < $hoy,
    ];
}

// Índice de columna de una fecha (puede quedar fuera del rango: negativo o >= $dias)
$indice = fn(string $fecha) => (int)$inicio->diff(new DateTimeImmutable($fecha))->format('%r%a');

$fin = $inicio->modify('+' . ($dias - 1) . ' days');
$rango = $inicio->format('j') . ' ' . $meses[(int)$inicio->format('n') - 1]
       . ' – ' . $fin->format('j') . ' ' . $meses[(int)$fin->format('n') - 1] . ' ' . $fin->format('Y');

$enlace = fn(string $nuevoDesde, int $nuevosDias = 0) => url('reservas/planificacion?desde=' . $nuevoDesde . '&dias=' . ($nuevosDias ?: $dias));
$paso = $dias >= 14 ? 7 : $dias;

// Agrupar habitaciones por piso (ya vienen ordenadas por piso y número)
$porPiso = [];
foreach ($habitaciones as $h) {
    $porPiso[$h['piso']][] = $h;
}
?>

<?php partial('page_header', [
    'titulo'    => 'Planificación por Habitación',
    'icono'     => 'bi-grid-3x3',
    'subtitulo' => 'Qué habitación está ocupada y cuándo. Haz clic en un hueco libre para reservar.',
    'acciones'  => [
        ['texto' => 'Calendario', 'url' => 'reservas/calendario', 'icono' => 'bi-calendar3', 'variante' => 'outline-secondary'],
        ['texto' => 'Nueva Reserva', 'url' => 'reservas/crear', 'icono' => 'bi-plus-lg'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-body p-3 p-md-4">

                <!-- Navegación del período -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <div class="btn-group" role="group" aria-label="Cambiar período">
                        <a href="<?= e($enlace($inicio->modify("-{$paso} days")->format('Y-m-d'))) ?>" class="btn btn-sm btn-outline-secondary" title="<?= $paso ?> días antes" aria-label="<?= $paso ?> días antes"><i class="bi bi-chevron-left"></i></a>
                        <a href="<?= e($enlace(date('Y-m-d', strtotime('-1 day')))) ?>" class="btn btn-sm btn-outline-secondary">Hoy</a>
                        <a href="<?= e($enlace($inicio->modify("+{$paso} days")->format('Y-m-d'))) ?>" class="btn btn-sm btn-outline-secondary" title="<?= $paso ?> días después" aria-label="<?= $paso ?> días después"><i class="bi bi-chevron-right"></i></a>
                    </div>
                    <h2 class="h6 fw-bold mb-0 mx-2"><?= e($rango) ?></h2>

                    <div class="btn-group ms-md-auto" role="group" aria-label="Días a mostrar">
                        <?php foreach ([7 => '7 días', 14 => '14 días', 30 => '30 días'] as $n => $texto): ?>
                            <a href="<?= e($enlace($desde, $n)) ?>" class="btn btn-sm <?= $n === $dias ? 'btn-primary' : 'btn-outline-secondary' ?>" <?= $n === $dias ? 'aria-current="true"' : '' ?>><?= $texto ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="calendario-leyenda mb-3" aria-label="Leyenda de estados">
                    <span class="ev-chip ev-estado-confirmada">Confirmada</span>
                    <span class="ev-chip ev-estado-pendiente">Pendiente</span>
                    <span class="ev-chip ev-estado-en_estadia">En estadía</span>
                    <span class="ev-chip ev-estado-finalizada">Finalizada</span>
                    <span class="text-body-secondary small ms-md-auto"><i class="bi bi-plus-square me-1"></i>Hueco libre: clic para reservar</span>
                </div>

                <div class="planning-scroll">
                    <table class="planning planning-<?= $dias ?>">
                        <thead>
                            <tr>
                                <th scope="col" class="planning-hab">Habitación</th>
                                <?php foreach ($columnas as $c): ?>
                                    <th scope="col" class="planning-dia<?= $c['finde'] ? ' es-finde' : '' ?><?= $c['hoy'] ? ' es-hoy' : '' ?>">
                                        <span class="planning-dia-semana"><?= $c['semana'] ?></span>
                                        <span class="planning-dia-num"><?= $c['dia'] ?></span>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($porPiso as $piso => $habsPiso): ?>
                                <tr class="planning-piso">
                                    <th scope="rowgroup" colspan="<?= $dias + 1 ?>"><span class="planning-piso-texto">Piso <?= e($piso) ?></span></th>
                                </tr>
                                <?php foreach ($habsPiso as $h): ?>
                                    <?php
                                        $reservas = $ocupacion[(int)$h['id']] ?? [];
                                        $fueraDeServicio = $h['estado'] === 'mantenimiento';
                                    ?>
                                    <tr>
                                        <th scope="row" class="planning-hab">
                                            <span class="fw-semibold">Hab. <?= e($h['numero']) ?></span>
                                            <small class="d-block text-body-secondary text-truncate"><?= e($h['tipo_nombre']) ?></small>
                                            <?php if ($h['estado'] === 'mantenimiento' || $h['estado'] === 'limpieza'): ?>
                                                <span class="planning-aviso" title="Ahora: <?= e($h['estado']) ?>"><i class="bi <?= $h['estado'] === 'limpieza' ? 'bi-stars' : 'bi-tools' ?>"></i></span>
                                            <?php endif; ?>
                                        </th>
                                        <?php
                                            $col = 0;
                                            while ($col < $dias):
                                                $fecha = $columnas[$col]['fecha'];
                                                // ¿Alguna estadía ocupa esta noche?
                                                $actual = null;
                                                foreach ($reservas as $r) {
                                                    if ($r['fecha_entrada'] <= $fecha && $r['fecha_salida'] > $fecha) {
                                                        $actual = $r;
                                                        break;
                                                    }
                                                }

                                                if ($actual):
                                                    $ultima = min($indice($actual['fecha_salida']), $dias); // salida = columna siguiente a la última noche
                                                    $span = $ultima - $col;
                                                    $antes = $actual['fecha_entrada'] < $desde;
                                                    $despues = $indice($actual['fecha_salida']) > $dias;
                                                    $nombre = $actual['nombres'] . ' ' . $actual['apellidos'];
                                                    $titulo = $actual['codigo_reserva'] . ' · ' . $nombre . ' · '
                                                            . fecha_es($actual['fecha_entrada']) . ' → ' . fecha_es($actual['fecha_salida'])
                                                            . ' · ' . ($etiquetaEstado[$actual['estado']] ?? $actual['estado']);
                                        ?>
                                            <td colspan="<?= $span ?>" class="planning-celda-ocupada">
                                                <a href="<?= url('reservas/editar/' . $actual['id']) ?>"
                                                   class="plan-barra ev-estado-<?= e($actual['estado']) ?><?= $antes ? ' sigue-antes' : '' ?><?= $despues ? ' sigue-despues' : '' ?>"
                                                   title="<?= e($titulo) ?>">
                                                    <?= e($span >= 2 ? $nombre : $actual['nombres']) ?>
                                                </a>
                                            </td>
                                        <?php
                                                    $col = $ultima;
                                                else:
                                                    $c = $columnas[$col];
                                                    $clases = 'planning-libre' . ($c['finde'] ? ' es-finde' : '') . ($c['hoy'] ? ' es-hoy' : '');
                                        ?>
                                            <td class="<?= $clases ?>">
                                                <?php if (!$c['pasado'] && !$fueraDeServicio): ?>
                                                    <a href="<?= url('reservas/crear?habitacion_id=' . $h['id'] . '&entrada=' . $c['fecha'] . '&salida=' . date('Y-m-d', strtotime($c['fecha'] . ' +1 day'))) ?>"
                                                       class="plan-libre" title="Reservar Hab. <?= e($h['numero']) ?> desde el <?= fecha_es($c['fecha']) ?>" aria-label="Reservar Hab. <?= e($h['numero']) ?> desde el <?= fecha_es($c['fecha']) ?>">
                                                        <i class="bi bi-plus"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        <?php
                                                    $col++;
                                                endif;
                                            endwhile;
                                        ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>
