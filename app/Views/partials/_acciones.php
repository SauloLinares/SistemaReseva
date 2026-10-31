<?php
/**
 * Lista de botones de acción (uso interno de page_header y card_header)
 *
 * @var array $acciones Cada acción es un enlace, un botón de modal o un botón de imprimir:
 *   ['texto' => '', 'url' => 'ruta',       'icono' => 'bi-*', 'variante' => 'primary']
 *   ['texto' => '', 'modal' => '#idModal', 'icono' => 'bi-*', 'variante' => 'primary']
 *   ['texto' => '', 'imprimir' => true,    'icono' => 'bi-printer', 'variante' => 'outline-secondary']
 */
foreach ($acciones as $accion):
    $clase = 'btn btn-sm btn-' . ($accion['variante'] ?? 'primary');
    $icono = !empty($accion['icono']) ? '<i class="bi ' . e($accion['icono']) . ' me-1"></i>' : '';
?>
    <?php if (!empty($accion['modal'])): ?>
        <button type="button" class="<?= e($clase) ?>" data-bs-toggle="modal" data-bs-target="<?= e($accion['modal']) ?>">
            <?= $icono ?><?= e($accion['texto']) ?>
        </button>
    <?php elseif (!empty($accion['imprimir'])): ?>
        <button type="button" class="<?= e($clase) ?>" data-accion="imprimir">
            <?= $icono ?><?= e($accion['texto']) ?>
        </button>
    <?php else: ?>
        <a href="<?= e(url($accion['url'])) ?>" class="<?= e($clase) ?>">
            <?= $icono ?><?= e($accion['texto']) ?>
        </a>
    <?php endif; ?>
<?php endforeach; ?>
