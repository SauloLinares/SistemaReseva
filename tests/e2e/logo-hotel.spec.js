// Datos del Hotel: "Quitar logo" deja la vista previa vacía y marca el formulario para quitarlo
// al guardar (no se envía nada aquí: el logo real del hotel no se toca).
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

test('el botón Quitar logo vacía la vista previa y marca el formulario', async ({ page }) => {
    await page.goto(ruta('configuracion/hotel'));
    const boton = page.locator('#btnQuitarLogo');
    test.skip(!(await boton.isVisible()), 'El hotel no tiene logo guardado en esta base');

    await expect(page.locator('#previewLogoHotel')).toBeVisible();
    await boton.click();
    await expect(page.locator('#previewLogoHotel')).toBeHidden();
    await expect(page.locator('#previewLogoVacio')).toBeVisible();
    await expect(page.locator('#quitarLogo')).toHaveValue('1');
    await expect(page.locator('#avisoQuitarLogo')).toBeVisible();
    await expect(boton).toBeHidden();
});

test('elegir un logo nuevo cancela el "quitar"', async ({ page }) => {
    await page.goto(ruta('configuracion/hotel'));
    const boton = page.locator('#btnQuitarLogo');
    test.skip(!(await boton.isVisible()), 'El hotel no tiene logo guardado en esta base');
    await boton.click();
    // PNG de 1×1 px
    const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=', 'base64');
    await page.setInputFiles('#inputLogoHotel', { name: 'logo.png', mimeType: 'image/png', buffer: png });
    await expect(page.locator('#previewLogoHotel')).toBeVisible();
    await expect(page.locator('#quitarLogo')).toHaveValue('0');
    await expect(page.locator('#avisoQuitarLogo')).toBeHidden();
});

test('el buscador de las tablas muestra una sola X para vaciarlo', async ({ page }) => {
    await page.goto(ruta('reservas'));
    const campo = page.locator('.datatable-input');
    await campo.fill('a');
    await expect(page.locator('.datatable-search .btn-limpiar')).toBeVisible();
    // La X nativa de type="search" se oculta con CSS (Chrome no expone su estilo calculado)
    const ocultaNativa = await page.evaluate(() => [...document.styleSheets].some(hoja => {
        try { return [...hoja.cssRules].some(r => /campo-limpiable[^{]*search-cancel-button/.test(r.cssText) && /display:\s*none/.test(r.cssText)); } catch { return false; }
    }));
    expect(ocultaNativa).toBe(true);
    await expect(page.locator('.datatable-search button')).toHaveCount(1);
});
