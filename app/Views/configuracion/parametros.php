<?php partial('page_header', [
    'titulo'    => 'Parámetros Generales',
    'icono'     => 'bi-sliders',
    'subtitulo' => 'Configuración comercial, moneda, impuestos y políticas de reserva',
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <form action="<?= url('configuracion/guardarParametros') ?>" method="POST" class="card border-top border-4 border-secondary">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-gear-fill me-2"></i>Parámetros de Operación</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Símbolo Monetario Principal <span class="text-danger">*</span></label>
                                <input type="text" name="hotel_moneda_simbolo" class="form-control form-control-lg" value="<?= htmlspecialchars($config['hotel_moneda_simbolo'] ?? 'S/.') ?>" required>
                                <div class="form-text">Ejemplo: S/., $, €, etc.</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Impuesto a las Ventas (I.G.V. / IVA %) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <input type="number" step="0.1" name="hotel_igv_porcentaje" class="form-control" value="<?= htmlspecialchars($config['hotel_igv_porcentaje'] ?? '18') ?>" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Política de Cancelación Estándar</label>
                                <textarea name="hotel_politica_cancelacion" class="form-control" rows="3"><?= htmlspecialchars($config['hotel_politica_cancelacion'] ?? 'Cancelación gratuita hasta 48 horas antes de la fecha de entrada programada.') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar Parámetros
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
