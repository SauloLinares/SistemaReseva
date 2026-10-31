<?php
/**
 * Acciones de una fila: las 1 o 2 de uso diario a la vista y el resto en un menú «⋯».
 * Las peligrosas (cancelar, eliminar) van al final del menú, separadas y en rojo.
 * El menú lo abre custom.js (posición fija: no lo recorta la tabla con scroll).
 *
 * @var array  $visibles  [['url' => 'ruta', 'icono' => 'bi-*', 'titulo' => '', 'variante' => 'outline-primary']]
 * @var array  $menu      [['texto' => '', 'icono' => 'bi-*', 'url' => 'ruta'] | ['modal' => '#id'] | ['externo' => 'https://...'],
 *                         opcional 'peligro' => true]
 * @var string $titulo    Para el lector de pantalla, ej. 'Más acciones de RES-2026-001'
 */
$visibles = $visibles ?? [];
$menu = $menu ?? [];
$normales = array_filter($menu, fn($m) => empty($m['peligro']));
$peligrosas = array_filter($menu, fn($m) => !empty($m['peligro']));

$item = function (array $m): string {
    $icono = '<i class="bi ' . e($m['icono'] ?? 'bi-dot') . ' me-2" aria-hidden="true"></i>';
    $clase = 'dropdown-item' . (!empty($m['peligro']) ? ' text-danger' : '');
    if (!empty($m['modal'])) {
        return '<button type="button" class="' . $clase . '" data-bs-toggle="modal" data-bs-target="' . e($m['modal']) . '">' . $icono . e($m['texto']) . '</button>';
    }
    $destino = !empty($m['externo']) ? e($m['externo']) . '" target="_blank" rel="noopener' : e(url($m['url']));
    return '<a class="' . $clase . '" href="' . $destino . '">' . $icono . e($m['texto']) . '</a>';
};
?>
<div class="table-actions">
    <?php foreach ($visibles as $v): ?>
        <a href="<?= e(url($v['url'])) ?>" class="btn btn-icon btn-<?= e($v['variante'] ?? 'outline-secondary') ?>" title="<?= e($v['titulo']) ?>" aria-label="<?= e($v['titulo']) ?>">
            <i class="bi <?= e($v['icono']) ?>" aria-hidden="true"></i>
        </a>
    <?php endforeach; ?>
    <?php if ($menu): ?>
        <div class="dropdown">
            <button type="button" class="btn btn-icon btn-outline-secondary" data-bs-toggle="dropdown" data-menu-acciones aria-expanded="false"
                    title="Más acciones" aria-label="<?= e($titulo ?? 'Más acciones') ?>">
                <i class="bi bi-three-dots" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <?php foreach ($normales as $m): ?>
                    <li><?= $item($m) ?></li>
                <?php endforeach; ?>
                <?php if ($peligrosas): ?>
                    <?php if ($normales): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                    <?php foreach ($peligrosas as $m): ?>
                        <li><?= $item($m) ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
