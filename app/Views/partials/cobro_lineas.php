<?php
/**
 * Cobro con uno o varios métodos de pago (ej. parte en efectivo y parte con tarjeta),
 * con el efectivo recibido y el vuelto. Lo maneja public/assets/js/cobro.js y lo lee
 * PagoController::leerCobro() (campos lineas[i][metodo], lineas[i][monto] y recibido).
 *
 * @var float     $monto        Monto inicial (normalmente el saldo pendiente)
 * @var string    $idMonto      id del campo de monto de la primera línea
 * @var bool|null $cajaAbierta  false muestra el aviso de caja cerrada; null no lo muestra
 * @var string    $atajo        Tecla para enfocar el monto (opcional), ej. 'F8'
 */
$monto = isset($monto) && (float)$monto > 0 ? number_format((float)$monto, 2, '.', '') : '';
$idMonto = $idMonto ?? 'cobroMonto';
$cajaAbierta = $cajaAbierta ?? null;
?>
<div class="cobro" data-cobro>
    <?php if ($cajaAbierta === false): ?>
        <div class="alert alert-warning d-flex align-items-start gap-2 py-2 small" role="alert">
            <i class="bi bi-exclamation-triangle fs-6 mt-1" aria-hidden="true"></i>
            <div>
                <strong>No hay una caja abierta.</strong> El cobro se registrará, pero no se sumará a ningún turno
                y no aparecerá en el arqueo. <a href="<?= url('caja') ?>" class="alert-link">Abrir caja</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="form-label mb-0" id="<?= e($idMonto) ?>-etiqueta">Cobro <span class="text-danger">*</span></span>
        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-agregar-linea>
            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Dividir en otro método
        </button>
    </div>

    <!-- Aquí aparece el error del servidor sobre las líneas (custom.js, data-error-para) -->
    <div data-error-para="lineas"></div>
    <div data-lineas-lista class="d-grid gap-2">
        <div class="cobro-linea" data-linea>
            <select name="lineas[0][metodo]" class="form-select" aria-label="Método de pago" data-linea-metodo>
                <?= opciones_metodo_pago() ?>
            </select>
            <div class="input-group">
                <span class="input-group-text"><?= e(moneda()) ?></span>
                <input type="number" step="0.01" min="0.01" name="lineas[0][monto]" id="<?= e($idMonto) ?>"
                       class="form-control text-end fw-bold" inputmode="decimal" value="<?= e($monto) ?>"
                       aria-label="Monto" <?= !empty($atajo) ? 'data-atajo="' . e($atajo) . '" data-atajo-desc="Monto a cobrar"' : '' ?> required data-linea-monto>
            </div>
            <button type="button" class="btn btn-outline-danger" data-quitar-linea aria-label="Quitar este método" hidden>
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="cobro-efectivo mt-2" data-efectivo hidden>
        <div class="row g-2 align-items-end">
            <div class="col-6">
                <label for="<?= e($idMonto) ?>-recibido" class="form-label small mb-1">Efectivo recibido</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><?= e(moneda()) ?></span>
                    <input type="number" step="0.01" min="0" name="recibido" id="<?= e($idMonto) ?>-recibido" class="form-control text-end"
                           inputmode="decimal" placeholder="Opcional" data-recibido>
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="small text-body-secondary">Vuelto</div>
                <div class="fs-5 fw-bold" data-vuelto aria-live="polite">—</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center border-top mt-2 pt-2">
        <span class="text-body-secondary">Total a cobrar</span>
        <strong class="fs-5" data-total><?= e(moneda()) ?> 0.00</strong>
    </div>
</div>
