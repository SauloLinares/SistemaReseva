// Recorre las pantallas principales: ninguna debe tener errores de PHP o JS,
// violaciones de CSP ni pedidos a otros dominios, y todas deben tener título y un <h1>/<h3> de página.
const { test, expect } = require('@playwright/test');
const { ruta, vigilar } = require('./utilidades');

const PANTALLAS = [
    'home', 'reservas', 'reservas/crear', 'reservas/editar/1', 'reservas/voucher/1',
    'reservas/calendario', 'reservas/planificacion', 'reservas/disponibilidad',
    'huespedes', 'huespedes/crear', 'huespedes/buscar', 'clientes', 'clientes/crear',
    'habitaciones', 'habitaciones/crear', 'habitaciones/editar/1', 'habitaciones/estados',
    'habitaciones/tipos', 'habitaciones/mantenimiento',
    'recepcion/checkin', 'recepcion/checkout', 'recepcion/estadias', 'recepcion/asignacion',
    'pagos', 'pagos/crear', 'pagos/pendientes', 'servicios', 'servicios/consumo',
    'caja', 'caja/historial', 'reportes', 'reportes/ocupacion', 'reportes/ingresos',
    'seguridad/usuarios', 'seguridad/roles', 'configuracion/hotel', 'configuracion/parametros', 'productos',
];

for (const pantalla of PANTALLAS) {
    test(`pantalla ${pantalla} carga sin errores`, async ({ page }) => {
        const problemas = vigilar(page);
        const respuesta = await page.goto(ruta(pantalla));
        expect(respuesta.status(), 'código HTTP').toBeLessThan(400);
        await page.waitForLoadState('networkidle');

        await expect(page).toHaveTitle(/·/);
        await expect(page.locator('.page-title, h1').first()).toBeVisible();
        expect(problemas, problemas.join('\n')).toEqual([]);
    });
}

test('las etiquetas de formulario apuntan a un campo y no hay ids repetidos', async ({ page }) => {
    for (const pantalla of ['huespedes/crear', 'reservas/crear', 'pagos/crear', 'habitaciones/crear', 'configuracion/hotel']) {
        await page.goto(ruta(pantalla));
        const resultado = await page.evaluate(() => {
            const sinCampo = [...document.querySelectorAll('label.form-label')]
                .filter(l => !l.htmlFor || !document.getElementById(l.htmlFor))
                .map(l => l.textContent.trim());
            const ids = {};
            document.querySelectorAll('[id]').forEach(el => { ids[el.id] = (ids[el.id] || 0) + 1; });
            return { sinCampo, repetidos: Object.keys(ids).filter(k => ids[k] > 1) };
        });
        expect(resultado.sinCampo, `${pantalla}: etiquetas sin campo`).toEqual([]);
        expect(resultado.repetidos, `${pantalla}: ids repetidos`).toEqual([]);
    }
});

test('el foco del teclado se ve en los botones', async ({ page }) => {
    await page.goto(ruta('huespedes'));
    for (let i = 0; i < 4; i++) await page.keyboard.press('Tab');
    const contorno = await page.evaluate(() => {
        const cs = getComputedStyle(document.activeElement);
        return cs.outlineStyle !== 'none' && parseFloat(cs.outlineWidth) >= 2;
    });
    expect(contorno, 'el elemento enfocado debe tener contorno visible').toBe(true);
});
