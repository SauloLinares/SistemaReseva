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
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>

    <!-- Popper.js & Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>

    <!-- AdminLTE v4 JS -->
    <script src="<?= asset('adminlte/js/adminlte.min.js') ?>"></script>

    <!-- Configuración inicial de Scrollbars -->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        const isMobile = window.innerWidth <= 992;
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal !== 'undefined' && !isMobile) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>

    <!-- TomSelect JS (Búsqueda en selects) -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>

    <!-- SweetAlert2 JS (Modales y confirmaciones modernas) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.0/dist/sweetalert2.all.min.js"></script>

    <!-- Simple-DataTables JS (Búsqueda, paginación y ordenación en tablas) -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.2.1/dist/umd/simple-datatables.js"></script>

    <?php if (!empty($_SESSION['form_previo'])): ?>
        <!-- Datos y errores del último envío fallido (Controller::volverConErrores); se usan una sola vez -->
        <script>window.formPrevio = <?= json_encode($_SESSION['form_previo'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
        <?php unset($_SESSION['form_previo']); ?>
    <?php endif; ?>

    <!-- Scripts personalizados con control de caché dinámico -->
    <script src="<?= asset('js/custom.js') ?>?v=<?= file_exists(__DIR__ . '/../../../public/assets/js/custom.js') ? filemtime(__DIR__ . '/../../../public/assets/js/custom.js') : time() ?>"></script>
    <!--end::Scripts-->
</body>
</html>
