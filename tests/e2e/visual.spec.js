// Comparación visual de pantallas (claro y oscuro) para refactorizaciones de CSS.
// No corre por defecto porque las capturas dependen de los datos y de la fecha del día.
// Uso:  1) VISUAL=1 npx playwright test visual --update-snapshots   (referencia, antes del cambio)
//       2) VISUAL=1 npx playwright test visual                        (compara después del cambio)
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

test.skip(!process.env.VISUAL, 'Solo se ejecuta con VISUAL=1');

// VISUAL_MOVIL=1 compara a ancho de teléfono
const movil = !!process.env.VISUAL_MOVIL;
if (movil) test.use({ viewport: { width: 375, height: 812 } });

const PANTALLAS = [
    'home', 'reservas', 'reservas/crear', 'reservas/calendario', 'reservas/planificacion',
    'reservas/disponibilidad', 'huespedes', 'huespedes/crear', 'habitaciones', 'habitaciones/estados',
    'recepcion/checkin', 'recepcion/estadias', 'pagos', 'caja', 'reportes/ingresos',
    'reportes/ocupacion', 'servicios', 'configuracion/hotel',
];

for (const tema of ['light', 'dark']) {
    for (const pantalla of PANTALLAS) {
        test(`${pantalla} (${tema})`, async ({ page }) => {
            await page.addInitScript((t) => localStorage.setItem('lte-theme', t), tema);
            await page.goto(ruta(pantalla));
            await page.waitForLoadState('networkidle');
            await page.evaluate(() => document.fonts.ready);
            await expect(page).toHaveScreenshot(`${pantalla.replace(/\//g, '-')}-${tema}${movil ? '-movil' : ''}.png`, {
                fullPage: true,
                animations: 'disabled',
                caret: 'hide',
                maxDiffPixelRatio: 0.002,
                // La hora en vivo y los pulsos animados cambian entre capturas
                mask: [page.locator('.pulse-dot'), page.locator('#reloj, .reloj-vivo')],
            });
        });
    }
}
