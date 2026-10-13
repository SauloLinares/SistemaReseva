<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Panel de Control (Dashboard)</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= url() ?>">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Tarjetas de Estadísticas (Small Boxes) -->
        <div class="row">
            <!-- Total Clientes -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3><?= $totalClientes ?></h3>
                        <p>Total Clientes</p>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <a href="<?= url('clientes') ?>" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Ver listado <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
            </div>

            <!-- Clientes Activos -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3><?= $clientesActivos ?></h3>
                        <p>Clientes Activos</p>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <a href="<?= url('clientes') ?>" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Gestionar <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
            </div>

            <!-- Clientes Inactivos -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3><?= $clientesInactivos ?></h3>
                        <p>Clientes Inactivos</p>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <a href="<?= url('clientes') ?>" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                        Revisar <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
            </div>

            <!-- Conexión a Base de Datos -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3>MySQL</h3>
                        <p>PDO Conectado</p>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-database-check"></i>
                    </div>
                    <a href="<?= url('clientes/crear') ?>" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Nuevo Registro <i class="bi bi-plus-circle"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Guía Didáctica para los Alumnos -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-mortarboard-fill me-2 text-primary"></i>
                            Guía de Arquitectura del Proyecto (Para Alumnos)
                        </h3>
                    </div>
                    <div class="card-body">
                        <h5>¡Bienvenidos a la Plantilla Base con PHP POO, MySQL y AdminLTE v4!</h5>
                        <p class="text-muted">
                            Este proyecto implementa el patrón de arquitectura <strong>MVC (Modelo - Vista - Controlador)</strong> 
                            junto con los principios fundamentales de la Programación Orientada a Objetos (POO).
                        </p>

                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 bg-body">
                                    <h6 class="text-primary fw-bold">
                                        <i class="bi bi-database me-1"></i> 1. Modelos (app/Models)
                                    </h6>
                                    <p class="small text-secondary mb-0">
                                        Se encargan de comunicarse con la base de datos MySQL usando <code>PDO</code> y sentencias preparadas para máxima seguridad.
                                        Ejemplo: <code>Cliente.php</code>.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 bg-body">
                                    <h6 class="text-success fw-bold">
                                        <i class="bi bi-cpu me-1"></i> 2. Controladores (app/Controllers)
                                    </h6>
                                    <p class="small text-secondary mb-0">
                                        Contienen la lógica de negocio, reciben peticiones del usuario, interactúan con los modelos y cargan las vistas adecuadas.
                                        Ejemplo: <code>ClienteController.php</code>.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 bg-body">
                                    <h6 class="text-info fw-bold">
                                        <i class="bi bi-layout-text-window-reverse me-1"></i> 3. Vistas (app/Views)
                                    </h6>
                                    <p class="small text-secondary mb-0">
                                        Archivos PHP/HTML con diseño responsivo usando AdminLTE v4. Están organizadas modularmente en <code>layouts/</code> y carpetas por cada módulo.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border mt-4 mb-0">
                            <strong><i class="bi bi-lightbulb-fill text-warning me-1"></i> Módulo de ejemplo listo:</strong>
                            Puedes revisar el funcionamiento del CRUD en el módulo de <a href="<?= url('clientes') ?>" class="fw-bold">Clientes</a> y usarlo como base para crear nuevos módulos (ej: Habitaciones, Reservas, Pagos, Usuarios).
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->
