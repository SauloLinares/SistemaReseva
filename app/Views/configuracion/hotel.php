<?php partial('page_header', [
    'titulo'    => 'Datos del Hotel',
    'icono'     => 'bi-building',
    'subtitulo' => 'Información institucional y comercial para comprobantes y reportes',
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                <form action="<?= url('configuracion/guardarHotel') ?>" method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-sliders me-2"></i>Perfil y Marca del Establecimiento</h5>
                        <span class="badge badge-soft-info"><i class="bi bi-shield-check me-1"></i>Configuración Global</span>
                    </div>
                    <div class="card-body">
                        
                        <!-- Sección: Logotipo Oficial del Hotel -->
                        <div class="p-3 bg-light rounded-3 mb-4 border">
                            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-image me-2"></i>Logotipo del Establecimiento</h6>
                            <div class="row align-items-center g-3">
                                <div class="col-12 col-sm-auto text-center">
                                    <div class="position-relative d-inline-block">
                                        <?php 
                                            $logoActual = $config['hotel_logo'] ?? '';
                                            $rutaLogo = !empty($logoActual) ? base_url('uploads/hotel/' . $logoActual) : '';
                                        ?>
                                        <div class="logo-preview">
                                            <!-- Sin logo: icono de marcador de posición (no hay imagen por defecto) -->
                                            <i class="bi bi-buildings" id="previewLogoVacio"<?= $rutaLogo ? ' hidden' : '' ?>></i>
                                            <img id="previewLogoHotel" src="<?= e($rutaLogo) ?>" alt="Logo del Hotel"<?= $rutaLogo ? '' : ' hidden' ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm">
                                    <label class="form-label mb-1" for="inputLogoHotel">Adjuntar o Actualizar Logotipo</label>
                                    <input type="file" name="hotel_logo" id="inputLogoHotel" class="form-control" accept="image/png, image/jpeg, image/webp">
                                    <div class="form-text mt-1 text-muted small">
                                        <i class="bi bi-info-circle me-1"></i>Formatos recomendados: <strong>PNG transparente, WebP o JPG</strong> (máx. 2 MB). Este logotipo aparecerá en la barra lateral del sistema, en comprobantes impresos y recibos oficiales.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="hotel_nombre" class="form-label">Nombre Comercial del Hotel <span class="text-danger">*</span></label>
                                <input id="hotel_nombre" type="text" name="hotel_nombre" class="form-control form-control-lg" value="<?= htmlspecialchars($config['hotel_nombre'] ?? 'Hotel Manager & Resort Gran Sol') ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="hotel_ruc" class="form-label">RUC / NIF / Identificación Tributaria <span class="text-danger">*</span></label>
                                <input id="hotel_ruc" type="text" name="hotel_ruc" class="form-control form-control-lg" value="<?= htmlspecialchars($config['hotel_ruc'] ?? '20491823901') ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="hotel_telefono" class="form-label">Teléfonos de Contacto</label>
                                <input id="hotel_telefono" type="text" name="hotel_telefono" class="form-control" value="<?= htmlspecialchars($config['hotel_telefono'] ?? '+51 1 450-8900 / +51 987 654 321') ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="hotel_correo" class="form-label">Correo Electrónico Oficial</label>
                                <input id="hotel_correo" type="email" name="hotel_correo" class="form-control" value="<?= htmlspecialchars($config['hotel_correo'] ?? 'reservas@hotelmanager.com') ?>">
                            </div>

                            <div class="col-12">
                                <label for="hotel_direccion" class="form-label">Dirección Física del Hotel</label>
                                <input id="hotel_direccion" type="text" name="hotel_direccion" class="form-control" value="<?= htmlspecialchars($config['hotel_direccion'] ?? 'Av. Del Sol 1245, Zona Turística, Perú') ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="hotel_checkin_hora" class="form-label">Hora Oficial de Check-in</label>
                                <input id="hotel_checkin_hora" type="time" name="hotel_checkin_hora" class="form-control" value="<?= htmlspecialchars($config['hotel_checkin_hora'] ?? '14:00') ?>">
                                <div class="form-text">Hora oficial de asignación y entrega de llaves.</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="hotel_checkout_hora" class="form-label">Hora Oficial de Check-out</label>
                                <input id="hotel_checkout_hora" type="time" name="hotel_checkout_hora" class="form-control" value="<?= htmlspecialchars($config['hotel_checkout_hora'] ?? '12:00') ?>">
                                <div class="form-text">Hora límite para recepción de habitaciones.</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar Datos del Hotel
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputLogo = document.getElementById('inputLogoHotel');
    const previewLogo = document.getElementById('previewLogoHotel');

    if (inputLogo && previewLogo) {
        inputLogo.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewLogo.src = evt.target.result;
                    previewLogo.hidden = false;
                    document.getElementById('previewLogoVacio')?.setAttribute('hidden', '');
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

    </div>
</div>
