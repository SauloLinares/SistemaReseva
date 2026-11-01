<?php partial('page_header', [
    'titulo'    => 'Respaldo de Datos',
    'icono'     => 'bi-database-down',
    'subtitulo' => 'Copia completa de la base de datos y las fotos para guardar fuera del servidor',
]) ?>

<?php
    $limite = \App\Controllers\ConfiguracionController::DIAS_RESPALDO;
    $hace = fn(int $d) => $d === 0 ? 'hoy' : ($d === 1 ? 'ayer' : "hace {$d} días");
?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-3">
            <div class="col-12 col-lg-8">

                <!-- Estado del último respaldo -->
                <?php if (!$ultimo): ?>
                    <div class="alert alert-warning d-flex gap-2 align-items-start" data-estado-respaldo="nunca">
                        <i class="bi bi-exclamation-triangle-fill fs-5" aria-hidden="true"></i>
                        <div><strong>Todavía no se ha descargado ningún respaldo.</strong> Si el servidor falla o la base se daña, no habría cómo recuperar las reservas, los pagos ni los huéspedes.</div>
                    </div>
                <?php elseif ($dias > $limite): ?>
                    <div class="alert alert-warning d-flex gap-2 align-items-start" data-estado-respaldo="viejo">
                        <i class="bi bi-clock-history fs-5" aria-hidden="true"></i>
                        <div><strong>El último respaldo es de <?= e($hace($dias)) ?></strong> (<?= fecha_es($ultimo['fecha'], true) ?>, por <?= e($ultimo['usuario'] ?? '—') ?>). Conviene descargar uno nuevo al menos cada <?= $limite ?> días.</div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success d-flex gap-2 align-items-start" data-estado-respaldo="al-dia">
                        <i class="bi bi-check-circle-fill fs-5" aria-hidden="true"></i>
                        <div><strong>Último respaldo: <?= e($hace($dias)) ?></strong> · <?= fecha_es($ultimo['fecha'], true) ?>, por <?= e($ultimo['usuario'] ?? '—') ?> (<?= number_format((int)($ultimo['kb'] ?? 0)) ?> KB).</div>
                    </div>
                <?php endif; ?>

                <form action="<?= url('configuracion/descargarRespaldo') ?>" method="POST" class="card no-loading" id="formRespaldo">
                    <div class="card-header">
                        <h2 class="card-title h5 mb-0"><i class="bi bi-file-earmark-zip me-2" aria-hidden="true"></i>Descargar respaldo completo</h2>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Se descarga un archivo <strong>.zip</strong> con:</p>
                        <ul class="list-unstyled mb-3">
                            <li class="d-flex gap-2 mb-2">
                                <i class="bi bi-database text-primary" aria-hidden="true"></i>
                                <span><strong>La base de datos completa</strong>: <?= (int)$tablas ?> tablas, unos <?= number_format($tamanoKb) ?> KB (reservas, huéspedes, pagos, caja, usuarios y configuración).</span>
                            </li>
                            <li class="d-flex gap-2 mb-2">
                                <i class="bi bi-images text-primary" aria-hidden="true"></i>
                                <span><strong>Las fotos</strong>: logo del hotel, habitaciones y usuarios (<?= number_format($fotosKb) ?> KB).</span>
                            </li>
                            <li class="d-flex gap-2">
                                <i class="bi bi-file-text text-primary" aria-hidden="true"></i>
                                <span><strong>LEEME.txt</strong> con los pasos para restaurarlo.</span>
                            </li>
                        </ul>
                        <div class="alert alert-light border small mb-0">
                            <i class="bi bi-shield-lock me-1" aria-hidden="true"></i>
                            Contiene los datos personales de los huéspedes y las contraseñas cifradas de los usuarios: guárdalo en un lugar seguro
                            (un disco externo o una nube privada) y no lo envíes por correo ni chat.
                        </div>
                    </div>
                    <div class="card-footer d-flex flex-wrap align-items-center gap-2">
                        <small class="text-body-secondary me-auto" data-aviso-respaldo aria-live="polite"></small>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download me-1" aria-hidden="true"></i> Descargar respaldo (.zip)
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title h6 mb-0"><i class="bi bi-arrow-counterclockwise me-2" aria-hidden="true"></i>¿Y para restaurarlo?</h2>
                    </div>
                    <div class="card-body small">
                        <p>Restaurar <strong>reemplaza todos los datos actuales</strong> por los del respaldo, por eso no se hace desde aquí: si saliera mal, se perderían datos reales.</p>
                        <p class="mb-0">Lo hace quien administra el servidor, siguiendo los pasos del archivo <code>LEEME.txt</code> que va dentro del .zip.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?= script_js('vistas/configuracion-respaldo.js') ?>
