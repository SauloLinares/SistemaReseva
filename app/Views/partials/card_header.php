<?php
/**
 * Encabezado de tarjeta estándar
 *
 * @var string      $titulo
 * @var string|null $icono
 * @var array       $acciones  Ver partials/_acciones.php
 */
$acciones = $acciones ?? [];
?>
<div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <h3 class="card-title mb-0">
        <?php if (!empty($icono)): ?><i class="bi <?= e($icono) ?> me-2"></i><?php endif; ?>
        <?= e($titulo) ?>
    </h3>
    <?php if (!empty($acciones)): ?>
        <div class="d-flex flex-wrap gap-2">
            <?php partial('_acciones', ['acciones' => $acciones]) ?>
        </div>
    <?php endif; ?>
</div>
