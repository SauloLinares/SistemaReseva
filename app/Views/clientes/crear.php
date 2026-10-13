<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Registrar Cliente</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= url() ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('clientes') ?>">Clientes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nuevo</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <!-- Mensajes Flash de Sesión -->
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Formulario en Tarjeta de AdminLTE -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-person-plus-fill me-2"></i> Datos del Nuevo Cliente
                        </h3>
                    </div>

                    <form action="<?= url('clientes/guardar') ?>" method="POST">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" required placeholder="Ej: Juan Carlos" />
                                </div>

                                <div class="col-md-6">
                                    <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" required placeholder="Ej: Pérez Gomez" />
                                </div>

                                <div class="col-md-6">
                                    <label for="dni" class="form-label">DNI / Documento <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="dni" name="dni" maxlength="8" required placeholder="Ej: 71234567" />
                                </div>

                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ej: 987654321" />
                                </div>

                                <div class="col-md-8">
                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="correo" name="correo" placeholder="cliente@correo.com" />
                                </div>

                                <div class="col-md-4">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="1" selected>Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-body d-flex justify-content-between">
                            <a href="<?= url('clientes') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancelar / Regresar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Guardar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->
