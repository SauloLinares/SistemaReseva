<?php partial('page_header', [
    'titulo'    => 'Estado de Habitaciones',
    'icono'     => 'bi-layout-text-window',
    'subtitulo' => 'Control operativo de housekeeping, recepción y disponibilidad',
    'acciones'  => [
        ['texto' => 'Mantenimiento', 'url' => 'habitaciones/mantenimiento', 'icono' => 'bi-tools', 'variante' => 'outline-secondary'],
        ['texto' => 'Lista', 'url' => 'habitaciones', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<?php
$estadosResumen = [
    'disponible'    => ['texto' => 'Disponibles',      'icono' => 'bi-check-circle'],
    'ocupada'       => ['texto' => 'Ocupadas',         'icono' => 'bi-person-fill'],
    'limpieza'      => ['texto' => 'En Limpieza',      'icono' => 'bi-stars'],
    'mantenimiento' => ['texto' => 'En Mantenimiento', 'icono' => 'bi-tools'],
];
$pisos = array_unique(array_column($habitaciones, 'piso'));
sort($pisos, SORT_NATURAL);
?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Resumen de Estados (también filtran la grilla al hacer clic) -->
        <div class="row g-3 mb-3">
            <?php foreach ($estadosResumen as $clave => $info): ?>
                <div class="col-6 col-md-3">
                    <button type="button" class="estado-stat estado-stat-<?= $clave ?>" data-estado="<?= $clave ?>" aria-pressed="false" title="Mostrar solo <?= e(mb_strtolower($info['texto'])) ?>">
                        <span class="estado-stat-numero"><?= (int) ($conteo[$clave] ?? 0) ?></span>
                        <span class="estado-stat-texto"><i class="bi <?= $info['icono'] ?> me-1"></i><?= e($info['texto']) ?></span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Barra de búsqueda y filtros -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-lg-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" id="buscarHabitacion" class="form-control" placeholder="Buscar por número o tipo (ej. 204, doble)" aria-label="Buscar habitación" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <select id="filtroPiso" class="form-select form-select-sm" aria-label="Filtrar por piso">
                            <option value="">Todos los pisos</option>
                            <?php foreach ($pisos as $piso): ?>
                                <option value="<?= e($piso) ?>">Piso <?= e($piso) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <select id="filtroEstado" class="form-select form-select-sm" aria-label="Filtrar por estado">
                            <option value="">Todos los estados</option>
                            <option value="disponible">Disponible</option>
                            <option value="ocupada">Ocupada</option>
                            <option value="limpieza">Limpieza</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-3 d-flex align-items-center justify-content-between justify-content-lg-end gap-2">
                        <span id="contadorHabitaciones" class="text-sm text-body-secondary"></span>
                        <button type="button" id="limpiarFiltros" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grilla Interactiva de Habitaciones con Cambio Rápido -->
        <div class="row g-3" id="grillaHabitaciones">
            <?php foreach ($habitaciones as $h): ?>
                <?php
                    $estado = $h['estado'];
                    $stateClass = match($estado) {
                        'disponible'    => 'room-state-disponible',
                        'ocupada'       => 'room-state-ocupada',
                        'limpieza'      => 'room-state-limpieza',
                        'mantenimiento' => 'room-state-mantenimiento',
                        default         => ''
                    };
                    $badgeSoft = match($estado) {
                        'disponible'    => 'badge-soft-success',
                        'ocupada'       => 'badge-soft-danger',
                        'limpieza'      => 'badge-soft-warning',
                        'mantenimiento' => 'badge-soft-secondary',
                        default         => 'badge-soft-light'
                    };
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 item-habitacion"
                     data-numero="<?= e($h['numero']) ?>"
                     data-tipo="<?= e($h['tipo_nombre']) ?>"
                     data-piso="<?= e($h['piso']) ?>"
                     data-estado="<?= e($estado) ?>">
                    <div class="card room-card-modern <?= $stateClass ?> h-100 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold fs-5 text-body-emphasis">Hab. <?= e($h['numero']) ?></span>
                            <span class="room-floor-badge">
                                Piso <?= e($h['piso']) ?>
                            </span>
                        </div>

                        <div class="mb-2">
                            <h6 class="fw-bold mb-0 text-truncate"><?= e($h['tipo_nombre']) ?></h6>
                            <small class="text-muted"><?= dinero($h['precio_diario']) ?> / noche</small>
                        </div>

                        <div class="mb-3">
                            <span class="badge <?= $badgeSoft ?> text-uppercase">
                                <span class="pulse-dot pulse-dot-<?= $estado ?>"></span>
                                <?= e($estado) ?>
                            </span>
                        </div>

                        <form method="POST" action="<?= url('habitaciones/cambiarEstado') ?>" class="mt-auto pt-2 border-top border-light-subtle">
                            <input type="hidden" name="id" value="<?= $h['id'] ?>">
                            <label for="estado_<?= $h['id'] ?>" class="form-label mb-1">Cambiar a:</label>
                            <div class="input-group input-group-sm">
                                <select name="estado" id="estado_<?= $h['id'] ?>" class="form-select form-select-sm">
                                    <option value="disponible" <?= ($estado === 'disponible') ? 'selected' : '' ?>>Disponible</option>
                                    <option value="ocupada" <?= ($estado === 'ocupada') ? 'selected' : '' ?>>Ocupada</option>
                                    <option value="limpieza" <?= ($estado === 'limpieza') ? 'selected' : '' ?>>Limpieza</option>
                                    <option value="mantenimiento" <?= ($estado === 'mantenimiento') ? 'selected' : '' ?>>Mantenimiento</option>
                                </select>
                                <button class="btn btn-primary btn-sm" type="submit" title="Actualizar">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sin resultados -->
        <div id="sinResultados" class="card d-none">
            <div class="card-body text-center py-5 text-body-secondary">
                <i class="bi bi-search fs-1 d-block mb-2"></i>
                <p class="mb-3">Ninguna habitación coincide con la búsqueda.</p>
                <button type="button" class="btn btn-sm btn-outline-primary" data-limpiar-filtros>
                    <i class="bi bi-x-lg me-1"></i>Limpiar filtros
                </button>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBuscar  = document.getElementById('buscarHabitacion');
    const selectPiso   = document.getElementById('filtroPiso');
    const selectEstado = document.getElementById('filtroEstado');
    const contador     = document.getElementById('contadorHabitaciones');
    const sinResultados = document.getElementById('sinResultados');
    const items        = document.querySelectorAll('.item-habitacion');
    const stats        = document.querySelectorAll('.estado-stat');

    // Quita tildes y mayúsculas para que "estandar" encuentre "Estándar"
    const normalizar = texto => texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();

    function aplicarFiltros() {
        const termino = normalizar(inputBuscar.value);
        const piso    = selectPiso.value;
        const estado  = selectEstado.value;
        let visibles  = 0;

        items.forEach(item => {
            const texto = normalizar(item.dataset.numero + ' ' + item.dataset.tipo);
            const coincide = (!termino || texto.includes(termino))
                && (!piso || item.dataset.piso === piso)
                && (!estado || item.dataset.estado === estado);
            item.classList.toggle('d-none', !coincide);
            if (coincide) visibles++;
        });

        stats.forEach(stat => {
            const activo = stat.dataset.estado === estado;
            stat.classList.toggle('active', activo);
            stat.setAttribute('aria-pressed', activo ? 'true' : 'false');
        });

        contador.textContent = visibles + ' de ' + items.length + ' habitaciones';
        sinResultados.classList.toggle('d-none', visibles > 0);
    }

    function limpiarFiltros() {
        inputBuscar.value = '';
        selectPiso.value = '';
        selectEstado.value = '';
        aplicarFiltros();
        inputBuscar.focus();
    }

    inputBuscar.addEventListener('input', aplicarFiltros);
    selectPiso.addEventListener('change', aplicarFiltros);
    selectEstado.addEventListener('change', aplicarFiltros);
    document.getElementById('limpiarFiltros').addEventListener('click', limpiarFiltros);
    document.querySelector('[data-limpiar-filtros]').addEventListener('click', limpiarFiltros);

    // Clic en una tarjeta de resumen: filtra por ese estado (un segundo clic lo quita)
    stats.forEach(stat => {
        stat.addEventListener('click', function () {
            selectEstado.value = selectEstado.value === this.dataset.estado ? '' : this.dataset.estado;
            aplicarFiltros();
        });
    });

    aplicarFiltros();
});
</script>
