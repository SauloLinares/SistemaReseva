<?php
/**
 * Interruptor de activo/inactivo dentro de una tabla o tarjeta. Es una casilla de verdad
 * (se usa con el teclado y la lee el lector de pantalla); custom.js envía el cambio por
 * fetch a la ruta indicada y, si el servidor lo rechaza, lo deja como estaba.
 *
 * @var string $ruta    Ruta POST que recibe id y estado (1/0) y responde {success, mensaje}
 * @var int    $id      Registro
 * @var bool   $activo  Estado actual
 * @var string $nombre  Qué se activa, para el lector de pantalla y el aviso (ej. 'Desayuno buffet')
 * @var array  $textos  [activo, inactivo]; por defecto ['Activo', 'Inactivo']
 * @var bool   $bloqueado  true si no se puede cambiar (ej. el propio usuario)
 */
$textos = $textos ?? ['Activo', 'Inactivo'];
$idCampo = 'estado-' . preg_replace('/[^a-z0-9]+/i', '-', $ruta) . '-' . (int)$id;
?>
<div class="form-check form-switch interruptor-estado mb-0">
    <input class="form-check-input" type="checkbox" role="switch" id="<?= e($idCampo) ?>"
           data-interruptor="<?= e($ruta) ?>" data-id="<?= (int)$id ?>" data-nombre="<?= e($nombre) ?>"
           data-texto-activo="<?= e($textos[0]) ?>" data-texto-inactivo="<?= e($textos[1]) ?>"
           <?= $activo ? 'checked' : '' ?> <?= !empty($bloqueado) ? 'disabled title="No puedes cambiar este estado"' : '' ?>>
    <label class="form-check-label small" for="<?= e($idCampo) ?>">
        <span class="visually-hidden"><?= e($nombre) ?>: </span><span data-texto-estado><?= e($activo ? $textos[0] : $textos[1]) ?></span>
    </label>
</div>
