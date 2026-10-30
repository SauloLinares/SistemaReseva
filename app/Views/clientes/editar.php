<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Editar Cliente</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= url() ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('clientes') ?>">Clientes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <!-- Formulario en Tarjeta de AdminLTE -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-pencil-square me-2"></i> Modificar Datos del Cliente #<?= htmlspecialchars($cliente['id']) ?>
                        </h3>
                    </div>

                    <form action="<?= url('clientes/actualizar') ?>" method="POST">
                        <!-- Campo oculto con el ID del cliente -->
                        <input type="hidden" name="id" value="<?= htmlspecialchars($cliente['id']) ?>" />

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" 
                                           value="<?= htmlspecialchars($cliente['nombres']) ?>" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                           value="<?= htmlspecialchars($cliente['apellidos']) ?>" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="dni" class="form-label">DNI / Documento <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="dni" name="dni" maxlength="8" 
                                           value="<?= htmlspecialchars($cliente['dni']) ?>" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>" />
                                </div>

                                <div class="col-md-8">
                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="correo" name="correo" 
                                           value="<?= htmlspecialchars($cliente['correo'] ?? '') ?>" />
                                </div>

                                <div class="col-md-4">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="1" <?= ((int)$cliente['estado'] === 1) ? 'selected' : '' ?>>Activo</option>
                                        <option value="0" <?= ((int)$cliente['estado'] === 0) ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-body d-flex justify-content-between">
                            <a href="<?= url('clientes') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancelar / Regresar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle me-1"></i> Actualizar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->
