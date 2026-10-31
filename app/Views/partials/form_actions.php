<?php
/**
 * Pie de formulario estándar: Cancelar + botón de envío
 *
 * @var string      $cancelar  Ruta para url() a la que vuelve "Cancelar"
 * @var string      $texto     Texto del botón de envío, ej: 'Guardar Huésped'
 * @var string|null $icono     Por defecto 'bi-check-lg'
 * @var string|null $variante  primary (defecto) | success | danger
 */
$icono    = $icono ?? 'bi-check-lg';
$variante = $variante ?? 'primary';
?>
<div class="card-footer d-flex justify-content-end gap-2">
    <a href="<?= e(url($cancelar)) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Cancelar
    </a>
    <button type="submit" class="btn btn-<?= e($variante) ?>">
        <i class="bi <?= e($icono) ?> me-1"></i> <?= e($texto) ?>
    </button>
</div>
