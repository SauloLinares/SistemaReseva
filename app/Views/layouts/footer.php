<?php
/**
 * Footer del Layout de AdminLTE v4
 */
?>
        <!--begin::Footer-->
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline text-body-secondary">
                Hotel Manager · Sistema de gestión hotelera
            </div>
            &copy; <?= date('Y') ?>
            <a href="<?= url() ?>" class="text-decoration-none fw-semibold"><?= e($nombreEmpresa ?? 'Hotel Manager') ?></a>.
            <span class="text-body-secondary">Todos los derechos reservados.</span>
        </footer>
        <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->

    <!--begin::Scripts-->
    <!-- OverlayScrollbars -->
    <script src="<?= asset('vendor/overlayscrollbars/overlayscrollbars.browser.es6.min.js') ?>"></script>

    <!-- Popper.js & Bootstrap 5 JS -->
    <script src="<?= asset('vendor/popper/popper.min.js') ?>"></script>
    <script src="<?= asset('vendor/bootstrap/bootstrap.min.js') ?>"></script>

    <!-- AdminLTE v4 JS -->
    <script src="<?= asset('adminlte/js/adminlte.min.js') ?>"></script>

    <!-- Configuración inicial de Scrollbars -->

    <!-- TomSelect JS (Búsqueda en selects) -->
    <script data-lib="tomselect" src="<?= asset('vendor/tom-select/tom-select.complete.min.js') ?>"></script>

    <!-- SweetAlert2 JS (Modales y confirmaciones modernas) -->
    <script src="<?= asset('vendor/sweetalert2/sweetalert2.all.min.js') ?>"></script>

    <!-- Simple-DataTables JS (Búsqueda, paginación y ordenación en tablas) -->
    <script data-lib="datatables" src="<?= asset('vendor/simple-datatables/simple-datatables.js') ?>"></script>

    <?php if (!empty($_SESSION['form_previo'])): ?>
        <!-- Datos y errores del último envío fallido (Controller::volverConErrores); se usan una sola vez -->
        <script type="application/json" id="form-previo"><?= json_encode($_SESSION['form_previo'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
        <?php unset($_SESSION['form_previo']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['operaciones_completadas'])): ?>
        <!-- Envíos que el servidor ya registró: el navegador olvida esos intentos pendientes -->
        <script type="application/json" id="operaciones-completadas"><?= json_encode(array_values($_SESSION['operaciones_completadas'])) ?></script>
        <?php unset($_SESSION['operaciones_completadas']); ?>
    <?php endif; ?>

    <!-- Envíos seguros (sin duplicar cobros, aviso de conexión) y borradores de formularios -->
    <?= script_js('envios.js') ?>
    <?= script_js('borrador.js') ?>
    <?= script_js('cobro.js') ?>
    <?= script_js('logo.js') ?>

    <!-- Scripts personalizados con control de caché dinámico -->
    <?= script_js('custom.js') ?>
    <!--end::Scripts-->
</body>
</html>
