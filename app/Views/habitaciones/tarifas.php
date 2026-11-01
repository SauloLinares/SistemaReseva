<?php partial('page_header', [
    'titulo'    => 'Tarifas por Noche',
    'icono'     => 'bi-cash-stack',
    'subtitulo' => 'Cambia los precios que necesites y guárdalos todos juntos',
    'acciones'  => [
        ['texto' => 'Precios Base por Tipo', 'url' => 'habitaciones/tipos', 'icono' => 'bi-tags', 'variante' => 'outline-primary'],
        ['texto' => 'Volver a Habitaciones', 'url' => 'habitaciones', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<?php
// Habitaciones agrupadas por tipo, en el orden del catálogo de tipos
$porTipo = [];
foreach ($habitaciones as $h) {
    $porTipo[$h['tipo_id']][] = $h;
}
$pisos = array_values(array_unique(array_map(fn($h) => (string)$h['piso'], $habitaciones)));
sort($pisos, SORT_NATURAL);
?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Buscar y filtrar -->
        <div class="card mb-3">
            <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2">
                <div class="tarifas-buscar">
                    <label for="tarifasBuscar" class="visually-hidden">Buscar habitación</label>
                    <input type="search" id="tarifasBuscar" class="form-control form-control-sm" placeholder="N.º de habitación" inputmode="numeric" data-limpiable autocomplete="off">
                </div>
                <div>
                    <label for="tarifasPiso" class="visually-hidden">Piso</label>
                    <select id="tarifasPiso" class="form-select form-select-sm">
                        <option value="">Todos los pisos</option>
                        <?php foreach ($pisos as $piso): ?>
                            <option value="<?= e($piso) ?>">Piso <?= e($piso) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ms-auto small text-body-secondary d-flex flex-wrap gap-3">
                    <span><span class="tarifa-dif tarifa-dif-arriba">+</span> sobre el precio base del tipo</span>
                    <span><span class="tarifa-dif tarifa-dif-abajo">−</span> por debajo</span>
                </div>
            </div>
        </div>

        <form action="<?= url('habitaciones/guardarTarifas') ?>" method="POST" id="formTarifas" class="no-loading">
            <?php foreach ($tipos as $tipo): ?>
                <?php $lista = $porTipo[$tipo['id']] ?? []; if (!$lista) continue; ?>
                <section class="card mb-3 tarifas-grupo" data-grupo aria-labelledby="tipo-<?= (int)$tipo['id'] ?>">
                    <div class="card-header d-flex flex-wrap align-items-center gap-2">
                        <div class="me-auto">
                            <h2 class="h6 mb-0" id="tipo-<?= (int)$tipo['id'] ?>">
                                <?= e($tipo['nombre']) ?>
                                <?php if (!(int)$tipo['estado']): ?><span class="badge badge-soft-secondary ms-1">Inactivo</span><?php endif; ?>
                            </h2>
                            <small class="text-body-secondary">
                                <?= count($lista) ?> <?= count($lista) === 1 ? 'habitación' : 'habitaciones' ?> ·
                                precio base <strong><?= dinero($tipo['precio_base']) ?></strong>
                            </small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-aplicar-base="<?= e(number_format((float)$tipo['precio_base'], 2, '.', '')) ?>">
                            <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Igualar al precio base
                        </button>
                        <div class="input-group input-group-sm tarifas-ajuste">
                            <label class="input-group-text" for="ajuste-<?= (int)$tipo['id'] ?>">Ajustar</label>
                            <input type="number" step="1" min="-90" max="300" class="form-control text-end" id="ajuste-<?= (int)$tipo['id'] ?>" placeholder="10" data-porcentaje>
                            <span class="input-group-text">%</span>
                            <button type="button" class="btn btn-outline-primary" data-aplicar-porcentaje>Aplicar</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 tabla-tarifas">
                            <thead>
                                <tr>
                                    <th>Habitación</th>
                                    <th class="text-end">Tarifa por noche</th>
                                    <th>Frente al precio base</th>
                                    <th class="text-end" data-no-exportar><span class="visually-hidden">Deshacer</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista as $h): ?>
                                    <?php $precio = number_format((float)$h['precio_diario'], 2, '.', ''); ?>
                                    <tr data-tarifa-fila data-numero="<?= e($h['numero']) ?>" data-piso="<?= e($h['piso']) ?>">
                                        <td>
                                            <span class="fw-semibold">Hab. <?= e($h['numero']) ?></span>
                                            <small class="text-body-secondary ms-1">Piso <?= e($h['piso']) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <div class="input-group input-group-sm tarifa-campo ms-auto">
                                                <span class="input-group-text">S/.</span>
                                                <input type="number" step="0.01" min="0.01" name="tarifas[<?= (int)$h['id'] ?>]" id="tarifa-<?= (int)$h['id'] ?>"
                                                       class="form-control text-end fw-semibold" value="<?= e($precio) ?>" inputmode="decimal"
                                                       data-original="<?= e($precio) ?>" data-base="<?= e(number_format((float)$h['precio_base'], 2, '.', '')) ?>"
                                                       aria-label="Tarifa por noche de la habitación <?= e($h['numero']) ?>" required>
                                            </div>
                                        </td>
                                        <td><span class="tarifa-dif" data-diferencia aria-live="polite"></span></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" data-deshacer hidden>
                                                <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Antes <span data-antes><?= dinero($precio) ?></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endforeach; ?>

            <p class="text-center text-body-secondary py-4" data-sin-resultados hidden>Ninguna habitación coincide con la búsqueda.</p>

            <!-- Barra de cambios: aparece al modificar una tarifa -->
            <div class="tarifas-barra shadow-lg" data-barra-cambios role="region" aria-label="Cambios sin guardar" hidden>
                <span class="fw-semibold" data-resumen-cambios aria-live="polite"></span>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-outline-secondary" data-descartar>Descartar</button>
                    <button type="submit" class="btn btn-primary" data-atajo="F9" data-atajo-desc="Guardar las tarifas">
                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Guardar cambios
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<?= script_js('vistas/habitaciones-tarifas.js') ?>
