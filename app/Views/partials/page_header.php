<?php
/**
 * Encabezado de página estándar
 *
 * @var string      $titulo
 * @var string|null $icono      Clase de Bootstrap Icons, ej: 'bi-people'
 * @var string|null $subtitulo
 * @var array       $migas      ['Etiqueta' => 'ruta' | null (página actual)]
 * @var array       $acciones   Ver partials/_acciones.php
 * @var bool        $ocultar_al_imprimir  true en vistas imprimibles (voucher, comprobante)
 * @var array|null  $insignia   Etiqueta junto al título: ['texto' => '', 'variante' => 'success']
 * @var string|null $extra      HTML propio en la zona de acciones (ej. un filtro); escápalo antes
 */
$migas    = $migas ?? [];
$acciones = $acciones ?? [];
?>
<!--begin::App Content Header-->
<div class="app-content-header page-header<?= !empty($ocultar_al_imprimir) ? ' d-print-none' : '' ?>">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <?php if (!empty($migas)): ?>
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= e(url()) ?>">Inicio</a></li>
                        <?php foreach ($migas as $etiqueta => $ruta): ?>
                            <?php if ($ruta === null): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= e($etiqueta) ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?= e(url($ruta)) ?>"><?= e($etiqueta) ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <h3 class="page-title mb-0">
                    <?php if (!empty($icono)): ?><i class="bi <?= e($icono) ?> me-2"></i><?php endif; ?>
                    <?= e($titulo) ?>
                    <?php if (!empty($insignia)): ?>
                        <span class="badge badge-soft-<?= e($insignia['variante'] ?? 'primary') ?> align-middle ms-2"><?= e($insignia['texto']) ?></span>
                    <?php endif; ?>
                </h3>
                <?php if (!empty($subtitulo)): ?>
                    <p class="page-subtitle mb-0"><?= e($subtitulo) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($acciones) || !empty($extra)): ?>
                <div class="d-flex flex-wrap align-items-center gap-2 d-print-none">
                    <?= $extra ?? '' ?>
                    <?php partial('_acciones', ['acciones' => $acciones]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!--end::App Content Header-->
