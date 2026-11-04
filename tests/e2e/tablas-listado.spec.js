// Todas las tablas de listado comparten el mismo diseño: "Mostrar N registros", "Buscar:",
// paginación y orden por columna (salvo Acciones). Los avisos "No hay…" se conservan.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

const LISTADOS = [
    'reservas', 'servicios', 'caja/historial', 'habitaciones/mantenimiento', 'pagos/anticipos',
    'pagos/comprobantes', 'pagos/pendientes', 'recepcion/estadias', 'seguridad/auditoria',
    'seguridad/usuarios', 'servicios/porHuesped', 'reportes/cancelaciones', 'reportes/huespedes',
    'reportes/ingresos', 'reportes/reservas', 'reportes/ocupacion', 'configuracion/documentos',
    'configuracion/metodosPago', 'huespedes/historial',
];

for (const pagina of LISTADOS) {
    test(`${pagina}: la tabla tiene buscador, límite de registros y paginación`, async ({ page }) => {
        const respuesta = await page.goto(ruta(pagina));
        expect(respuesta.status()).toBe(200);
        const envoltorios = page.locator('.datatable-wrapper');
        expect(await envoltorios.count()).toBeGreaterThan(0);

        for (const envoltorio of await envoltorios.all()) {
            await expect(envoltorio.locator('.datatable-search input')).toBeVisible();
            await expect(envoltorio.locator('.datatable-selector')).toBeVisible();
            // "Mostrando registros…" (la librería la oculta si la tabla está vacía)
            await expect(envoltorio.locator('.datatable-info')).toBeAttached();
            // La columna Acciones no se ordena
            const acciones = envoltorio.locator('thead th', { hasText: /^\s*Acci(ón|ones?)\s*$/i });
            if (await acciones.count()) {
                await expect(acciones.first().locator('.datatable-sorter')).toHaveCount(0);
            }
            // Una tabla vacía muestra su propio aviso, no una fila descuadrada
            const filas = envoltorio.locator('tbody tr');
            if ((await filas.count()) === 1 && (await filas.first().locator('td').count()) === 1) {
                await expect(filas.first()).not.toHaveText(/^\s*$/);
            }
        }
        await expect(page.locator('#filtroHistorialCaja')).toHaveCount(0);
    });
}

test('el buscador de Historial de Caja filtra los turnos', async ({ page }) => {
    await page.goto(ruta('caja/historial'));
    const filas = page.locator('#tablaHistorialCaja tbody tr');
    const total = await filas.count();
    test.skip(total < 1 || (await filas.first().locator('td').count()) === 1, 'No hay turnos en esta base');

    await page.locator('.datatable-search input').fill('zzzz-no-existe');
    await expect(page.locator('.datatable-wrapper tbody')).toContainText('Ningún resultado');
    await page.locator('.datatable-search input').fill('');
    await expect(filas).toHaveCount(total);
});

// Un formulario solo en la página va centrado (como Datos del Hotel), no pegado a la izquierda
const FORMULARIOS = [
    'configuracion/hotel', 'configuracion/parametros', 'clientes/crear', 'habitaciones/crear',
    'huespedes/crear', 'pagos/crear', 'recepcion/asignacion',
];
for (const pagina of FORMULARIOS) {
    test(`${pagina}: el formulario está centrado`, async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });
        await page.goto(ruta(pagina));
        const tarjeta = await page.locator('.app-content .card').first().boundingBox();
        const contenedor = await page.locator('.app-content .container-fluid').first().boundingBox();
        const izquierda = tarjeta.x - contenedor.x;
        const derecha = contenedor.x + contenedor.width - (tarjeta.x + tarjeta.width);
        expect(Math.abs(izquierda - derecha)).toBeLessThan(30);
    });
}
