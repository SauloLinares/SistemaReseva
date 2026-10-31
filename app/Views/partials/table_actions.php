<?php
/**
 * Botones de acción de una fila de tabla (Ver / Editar / Eliminar)
 *
 * @var string|null $ver        Ruta para url()
 * @var string|null $editar     Ruta para url()
 * @var string|null $eliminar   Ruta para url()
 * @var string|null $confirmar  Mensaje de confirmación para eliminar
 * @var array       $extras     Acciones propias del módulo, van antes de Editar:
 *                              [['url' => 'ruta', 'icono' => 'bi-*', 'titulo' => '', 'variante' => 'outline-secondary']]
 */
$extras = $extras ?? [];
?>
<div class="table-actions">
    <?php foreach ($extras as $extra): ?>
        <a href="<?= e(url($extra['url'])) ?>" class="btn btn-icon btn-<?= e($extra['variante'] ?? 'outline-secondary') ?>" title="<?= e($extra['titulo']) ?>">
            <i class="bi <?= e($extra['icono']) ?>"></i>
        </a>
    <?php endforeach; ?>
    <?php if (!empty($ver)): ?>
        <a href="<?= e(url($ver)) ?>" class="btn btn-icon btn-outline-secondary" title="Ver detalle">
            <i class="bi bi-eye"></i>
        </a>
    <?php endif; ?>
    <?php if (!empty($editar)): ?>
        <a href="<?= e(url($editar)) ?>" class="btn btn-icon btn-outline-primary" title="Editar">
            <i class="bi bi-pencil-square"></i>
        </a>
    <?php endif; ?>
    <?php if (!empty($eliminar)): ?>
        <a href="<?= e(url($eliminar)) ?>" class="btn btn-icon btn-outline-danger" title="Eliminar"
           data-confirm="<?= e($confirmar ?? '¿Está seguro de eliminar este registro?') ?>">
            <i class="bi bi-trash"></i>
        </a>
    <?php endif; ?>
</div>
