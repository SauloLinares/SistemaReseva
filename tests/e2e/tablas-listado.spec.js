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

test('Huéspedes tiene un solo buscador y un ?q= del enlace se aplica en él', async ({ page }) => {
    await page.goto(ruta('huespedes'));
    await expect(page.locator('input[name="q"]')).toHaveCount(0);
    await expect(page.locator('.app-content input[type="search"], .app-content .datatable-input')).toHaveCount(1);

    const total = await page.locator('#tablaHuespedes tbody tr').count();
    const nombre = (await page.locator('#tablaHuespedes tbody tr td').first().innerText()).split('\n')[0].trim();
    test.skip(total < 2, 'Hacen falta al menos 2 huéspedes');

    await page.goto(ruta('huespedes') + '&q=' + encodeURIComponent(nombre));
    await expect(page.locator('.datatable-input')).toHaveValue(nombre);
    await expect(page.locator('#tablaHuespedes tbody tr').first()).toContainText(nombre);
    expect(await page.locator('#tablaHuespedes tbody tr').count()).toBeLessThan(total);
});

test('Parámetros Generales usa la misma tarjeta que Datos del Hotel', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    const medir = async (pagina) => {
        await page.goto(ruta(pagina));
        const tarjeta = page.locator('.app-content form.card').first();
        const caja = await tarjeta.boundingBox();
        return {
            x: Math.round(caja.x), ancho: Math.round(caja.width),
            borde: await tarjeta.evaluate(el => getComputedStyle(el).borderTopColor),
            insignia: await tarjeta.locator('.card-header .badge').innerText(),
            // La vista no deja <div> sin abrir: el contenido sigue dentro de .app-content
            dentro: await page.locator('.app-content form.card').count(),
        };
    };
    const hotel = await medir('configuracion/hotel');
    const parametros = await medir('configuracion/parametros');
    expect(parametros).toEqual(hotel);
});
