<?php
// Nombre y logo del hotel (la pantalla de acceso no pasa por header.php)
$configApp = (new \App\Models\Configuracion())->obtenerTodos();
$nombreEmpresa = $configApp['hotel_nombre'] ?? 'Hotel Manager';
$logoArchivo = $configApp['hotel_logo'] ?? '';
$rutaLogo = ($logoArchivo !== '' && file_exists(__DIR__ . '/../../../public/uploads/hotel/' . $logoArchivo))
    ? base_url('uploads/hotel/' . $logoArchivo) : '';
?>
<!doctype html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Acceso al sistema de gestión hotelera de <?= e($nombreEmpresa) ?>." />
    <meta name="theme-color" content="#0b1120" />
    <title><?= e($pageTitle ?? 'Iniciar Sesión') ?> · <?= e($nombreEmpresa) ?></title>
    <?php if ($rutaLogo): ?>
        <link rel="icon" href="<?= e($rutaLogo) ?>" />
    <?php endif; ?>

    <!-- Script de inicialización de tema claro/oscuro (AdminLTE v4) -->
    <?= script_js('tema.js') ?>

    <!-- Fuentes de Google -->
    <link rel="stylesheet" href="<?= asset('vendor/fonts/plus-jakarta-sans/fuente.css') ?>" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" />

    <!-- AdminLTE v4 CSS -->
    <link rel="stylesheet" href="<?= asset('adminlte/css/adminlte.min.css') ?>" />

    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>" />

    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
            position: relative;
        }

        .theme-toggle-top {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
        }

        .login-box .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .input-group-text {
            background-color: transparent;
        }

        .btn-toggle-password {
            cursor: pointer;
            border-left: none;
        }

        .demo-credentials-box {
            font-size: 0.85rem;
            border-left: 4px solid #0d6efd;
            background-color: rgba(13, 110, 253, 0.06);
        }
    </style>
</head>

<body class="login-page bg-body-secondary">

    <!-- Botón de cambio de tema en esquina superior -->
    <div class="theme-toggle-top dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center shadow-sm"
            id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" data-bs-display="static">
            <span class="theme-icon-active me-1"><i class="bi bi-circle-half"></i></span> Tema
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme">
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light">
                    <i class="bi bi-sun-fill me-2 opacity-50"></i> Claro
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark">
                    <i class="bi bi-moon-stars-fill me-2 opacity-50"></i> Oscuro
                </button>
            </li>
        </ul>
    </div>

    <div class="login-box">

        <!-- Tarjeta de Login -->
        <div class="card card-outline card-primary shadow">
            <div class="card-header text-center py-3 bg-body">
                <!-- Logo y Nombre del Sistema -->
                <div class="login-logo text-center mb-3">
                    <a href="<?= url('login') ?>" class="text-decoration-none d-inline-flex align-items-center justify-content-center">
                        <?php if ($rutaLogo): ?>
                            <img src="<?= e($rutaLogo) ?>" alt="Logo de <?= e($nombreEmpresa) ?>" class="login-logo-img" width="72" height="72" />
                        <?php else: ?>
                            <span class="brand-logo-icon login-logo-icono"><i class="bi bi-buildings-fill"></i></span>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="text-center">
                    <h1 class="h4 fw-bold mb-0"><?= e($nombreEmpresa) ?></h1>
                    <small class="text-muted">Inicia sesión para continuar</small>
                </div>
            </div>

            <div class="card-body login-card-body p-4">

                <!-- Mensajes Flash de Sesión -->
                <?php if (!empty($_SESSION['flash'])): ?>
                    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi <?= $_SESSION['flash']['tipo'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2 fs-5"></i>
                            <div><?= htmlspecialchars($_SESSION['flash']['mensaje']) ?></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <!-- Formulario de Autenticación -->
                <form action="<?= url('login/autenticar') ?>" method="POST" autocomplete="on">

                    <!-- Campo Correo -->
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold text-muted">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-at text-muted"></i></span>
                            <input type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                placeholder="ejemplo@correo.com"
                                value="<?= htmlspecialchars($correoPrevio ?? '') ?>"
                                required
                                autofocus />
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold text-muted">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="••••••••"
                                required />
                            <button type="button"
                                class="btn btn-outline-secondary btn-toggle-password"
                                id="togglePasswordBtn"
                                title="Mostrar u ocultar contraseña">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Recordarme y Botón -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recordar" name="recordar" checked>
                            <label class="form-check-label small text-muted" for="recordar">
                                Recordar sesión
                            </label>
                        </div>
                    </div>

                    <!-- Botón de Ingreso -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                        </button>
                    </div>
                </form>

                <!-- Caja de Credenciales Didácticas / Demo -->
                <div class="demo-credentials-box p-3 rounded mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-primary"><i class="bi bi-key-fill me-1"></i> Credenciales de prueba:</span>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 text-xs" id="btnAutocompletar">
                            Autocompletar
                        </button>
                    </div>
                    <div class="text-body-secondary">
                        <div><strong>Usuario:</strong> <code>admin@admin.com</code></div>
                        <div><strong>Clave:</strong> <code>admin123</code></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>

    <?= script_js('login.js') ?>
</body>

</html>