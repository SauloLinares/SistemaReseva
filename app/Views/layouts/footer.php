<?php
/**
 * Footer del Layout de AdminLTE v4
 */
?>
        <!--begin::Footer-->
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">
                Plantilla Educativa MVC & POO en PHP
            </div>
            <strong>
                Copyright &copy; <?= date('Y') ?> 
                <a href="<?= url() ?>" class="text-decoration-none">Sistema Reserva</a>.
            </strong>
            Todos los derechos reservados.
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

    <!-- Scripts personalizados para los alumnos -->
    <script src="<?= asset('js/custom.js') ?>"></script>
    <!--end::Scripts-->
</body>
</html>
