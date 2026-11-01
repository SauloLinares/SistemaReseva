// Permisos por rol (Seguridad → Matriz de Permisos): el Router los aplica en cada petición.
// Usa la recepcionista de la instalación (database.sql): E2E_RECEPCION_EMAIL / E2E_RECEPCION_PASSWORD.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

const RECEPCION = {
    email: process.env.E2E_RECEPCION_EMAIL || 'recepcion@hotel.com',
    clave: process.env.E2E_RECEPCION_PASSWORD || 'admin123',
};

// Cambian el estado de la recepcionista: una prueba a la vez
test.describe.configure({ mode: 'serial' });

async function entrarComoRecepcion(browser) {
    const contexto = await browser.newContext({ storageState: { cookies: [], origins: [] } });
    const page = await contexto.newPage();
    await page.goto(ruta('login'));
    await page.fill('input[name="email"]', RECEPCION.email);
    await page.fill('input[name="password"]', RECEPCION.clave);
    await Promise.all([
        page.waitForURL(url => !url.href.includes('url=login')),
        page.locator('form[action*="login/autenticar"] [type="submit"]').click(),
    ]);
    return { contexto, page };
}

test('la recepcionista no ve en el menú lo que su rol no permite', async ({ browser }) => {
    const { contexto, page } = await entrarComoRecepcion(browser);
    const menu = page.locator('.app-sidebar');
    await expect(menu).toContainText('Reservas');
    await expect(menu).toContainText('Check-in');
    await expect(menu).not.toContainText('Seguridad');
    await expect(menu).not.toContainText('Configuración');
    await expect(menu).not.toContainText('Reportes');
    // Cargar consumos es trabajo de recepción; el catálogo de servicios no
    await expect(menu).toContainText('Registrar consumo');
    await expect(menu).not.toContainText('Catálogo servicios');
    await contexto.close();
});

test('escribir la URL a mano tampoco sirve: vuelve al Inicio con un aviso', async ({ browser }) => {
    const { contexto, page } = await entrarComoRecepcion(browser);
    for (const pantalla of ['seguridad/usuarios', 'seguridad/permisos', 'configuracion/hotel', 'reportes/ingresos']) {
        await page.goto(ruta(pantalla));
        await expect(page).not.toHaveURL(new RegExp(pantalla.replace('/', '\\/')));
        await expect(page.locator('#sessionFlashToast')).toContainText('no tiene permiso');
    }
    // Lo que sí le corresponde abre normalmente
    for (const pantalla of ['reservas', 'recepcion/checkin', 'caja', 'huespedes']) {
        const resp = await page.goto(ruta(pantalla));
        expect(resp.status()).toBe(200);
        await expect(page).toHaveURL(new RegExp(pantalla.replace('/', '\\/')));
    }
    await contexto.close();
});

test('una petición directa sin permiso se rechaza con 403 y no cambia nada', async ({ browser }) => {
    const { contexto, page } = await entrarComoRecepcion(browser);
    await page.goto(ruta('reservas'));
    const resp = await page.evaluate(async (url) => {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'rol_id=2&permisos[seguridad][]=ver&permisos[seguridad][]=editar',
        });
        return { status: r.status, cuerpo: await r.json() };
    }, ruta('seguridad/guardarPermisos'));
    expect(resp.status).toBe(403);
    expect(resp.cuerpo.mensaje).toContain('Seguridad y Usuarios');
    await page.goto(ruta('seguridad/permisos'));
    await expect(page.locator('.app-sidebar')).not.toContainText('Seguridad');
    await contexto.close();
});

test('el atajo F2 y los botones rápidos solo aparecen si se pueden usar', async ({ browser }) => {
    const { contexto, page } = await entrarComoRecepcion(browser);
    await expect(page.locator('.app-header a[href*="reservas/crear"]').first()).toBeVisible();
    expect(await page.evaluate(() => window.leerJson('app-config').atajos)).toEqual(['reservas/crear', 'huespedes/buscar', 'recepcion/checkin']);
    await contexto.close();
});

test('los permisos del Administrador no se pueden cambiar', async ({ page }) => {
    await page.goto(ruta('seguridad/permisos?rol_id=1'));
    await expect(page.locator('.alert-info')).toContainText('tiene siempre todos los permisos');
    await expect(page.locator('input[name^="permisos["]').first()).toBeDisabled();
    await expect(page.locator('input[name^="permisos["]:not(:checked)')).toHaveCount(0);
});

test('un usuario desactivado pierde la sesión que tenía abierta', async ({ browser, page }) => {
    const { contexto, page: recepcion } = await entrarComoRecepcion(browser);
    await recepcion.goto(ruta('reservas'));
    await expect(recepcion).toHaveURL(/reservas/);

    // El administrador la desactiva
    const cambiar = (estado) => page.evaluate(async ({ url, estado }) => {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=2&estado=' + estado,
        });
        return r.json();
    }, { url: ruta('seguridad/cambiarEstadoUsuario'), estado });
    await page.goto(ruta('seguridad/usuarios'));
    try {
        expect((await cambiar('0')).success).toBe(true);
        await recepcion.goto(ruta('reservas'));
        await expect(recepcion).toHaveURL(/url=login/);
        await expect(recepcion.locator('body')).toContainText('Tu cuenta fue desactivada');
    } finally {
        expect((await cambiar('1')).success).toBe(true);
        await contexto.close();
    }
});

test('el administrador principal no puede perder su rol (ni por una petición directa)', async ({ page }) => {
    await page.goto(ruta('seguridad/usuarios'));
    await page.evaluate(async (url) => {
        await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=1&nombre=Administrador%20General&email=admin%40admin.com&rol_id=2&estado=1&password=',
        });
    }, ruta('seguridad/guardarUsuario'));
    // Sigue siendo administrador: puede abrir Seguridad y su fila dice Administrador
    await page.goto(ruta('seguridad/usuarios'));
    await expect(page).toHaveURL(/seguridad\/usuarios/);
    await expect(page.locator('table tbody tr', { hasText: 'admin@admin.com' })).toContainText('Administrador');
});
