// Tarifas por noche: agrupadas por tipo, diferencia con el precio base, edición de varias
// tarifas con una barra de cambios y guardado en un solo paso. Deja los precios como estaban.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

const campos = (page) => page.locator('#formTarifas input[name^="tarifas["]');
const barra = (page) => page.locator('[data-barra-cambios]');

test('muestra el precio base real de cada tipo (antes salía S/. 0.00)', async ({ page }) => {
    await page.goto(ruta('habitaciones/tarifas'));
    const bases = await campos(page).evaluateAll(cs => cs.map(c => parseFloat(c.dataset.base)));
    expect(bases.length).toBeGreaterThan(0);
    expect(bases.every(b => b > 0)).toBe(true);
    await expect(page.locator('.tarifas-grupo').first().locator('.card-header')).not.toContainText('S/. 0.00');
});

test('editar varias tarifas: barra de cambios, deshacer y descartar', async ({ page }) => {
    await page.goto(ruta('habitaciones/tarifas'));
    await expect(barra(page)).toBeHidden();
    const [a, b] = [campos(page).nth(0), campos(page).nth(1)];
    const originalA = await a.inputValue();

    await a.fill((parseFloat(originalA) + 5).toFixed(2));
    await b.fill('999');
    await expect(barra(page)).toBeVisible();
    await expect(barra(page)).toContainText('2 tarifas cambiadas');
    await expect(a.locator('xpath=ancestor::tr')).toHaveClass(/tarifa-cambiada/);
    await expect(a.locator('xpath=ancestor::tr').locator('[data-diferencia]')).toContainText('+');

    await a.locator('xpath=ancestor::tr').locator('[data-deshacer]').click();
    await expect(a).toHaveValue(originalA);
    await expect(barra(page)).toContainText('1 tarifa cambiada');

    await page.locator('[data-descartar]').click();
    await expect(barra(page)).toBeHidden();
});

test('acciones por tipo: ajustar un porcentaje e igualar al precio base', async ({ page }) => {
    await page.goto(ruta('habitaciones/tarifas'));
    const grupo = page.locator('.tarifas-grupo').first();
    const enGrupo = grupo.locator('input[name^="tarifas["]');
    const originales = await enGrupo.evaluateAll(cs => cs.map(c => parseFloat(c.dataset.original)));

    await grupo.locator('[data-porcentaje]').fill('10');
    await grupo.locator('[data-aplicar-porcentaje]').click();
    // Dos veces no acumula: siempre sobre la tarifa original
    await grupo.locator('[data-aplicar-porcentaje]').click();
    expect(await enGrupo.evaluateAll(cs => cs.map(c => c.value))).toEqual(originales.map(o => (Math.round(o * 110) / 100).toFixed(2)));

    const base = await grupo.locator('[data-aplicar-base]').getAttribute('data-aplicar-base');
    await grupo.locator('[data-aplicar-base]').click();
    expect(new Set(await enGrupo.evaluateAll(cs => cs.map(c => c.value)))).toEqual(new Set([base]));
    await page.locator('[data-descartar]').click();
});

test('buscar por número de habitación', async ({ page }) => {
    await page.goto(ruta('habitaciones/tarifas'));
    const numero = await page.locator('[data-tarifa-fila]').nth(3).getAttribute('data-numero');
    await page.fill('#tarifasBuscar', numero);
    await expect(page.locator('[data-tarifa-fila]:visible')).toHaveCount(1);
    await page.fill('#tarifasBuscar', 'zzz');
    await expect(page.locator('[data-sin-resultados]')).toBeVisible();
});

test('guardar en un solo paso y dejar la tarifa como estaba', async ({ page }) => {
    await page.goto(ruta('habitaciones/tarifas'));
    const campo = campos(page).first();
    const nombre = await campo.getAttribute('name');
    const original = await campo.inputValue();
    const nuevo = (parseFloat(original) + 1).toFixed(2);

    await campo.fill(nuevo);
    await page.locator('[data-barra-cambios] [type="submit"]').click();
    await expect(page.locator('#sessionFlashToast')).toContainText('Se actualizó 1 tarifa');
    await expect(page.locator(`input[name="${nombre}"]`)).toHaveValue(nuevo);

    await page.locator(`input[name="${nombre}"]`).fill(original);
    await page.locator('[data-barra-cambios] [type="submit"]').click();
    await expect(page.locator('#sessionFlashToast')).toContainText('Se actualizó 1 tarifa');
    await expect(page.locator(`input[name="${nombre}"]`)).toHaveValue(original);
});

test('un precio muy por debajo del base pide confirmación', async ({ page }) => {
    await page.goto(ruta('habitaciones/tarifas'));
    const campo = campos(page).first();
    const base = parseFloat(await campo.getAttribute('data-base'));
    await campo.fill((base * 0.5).toFixed(2));
    await page.locator('[data-barra-cambios] [type="submit"]').click();
    await expect(page.locator('.swal2-title')).toHaveText('¿Precios tan bajos?');
    await page.locator('.swal2-cancel').click();
    await expect(page).toHaveURL(/habitaciones\/tarifas/);
    await expect(barra(page)).toBeVisible(); // no se guardó: el cambio sigue pendiente
    await page.locator('[data-descartar]').click();
});

test('el servidor rechaza precios en cero y no guarda nada', async ({ page }) => {
    await page.goto(ruta('habitaciones/tarifas'));
    const nombre = await campos(page).first().getAttribute('name');
    const original = await campos(page).first().inputValue();
    const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');

    await page.evaluate(({ nombre, token, url }) => {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = url;
        for (const [k, v] of [['_csrf', token], [nombre, '0']]) {
            const i = document.createElement('input');
            i.name = k;
            i.value = v;
            f.appendChild(i);
        }
        document.body.appendChild(f);
        HTMLFormElement.prototype.submit.call(f);
    }, { nombre, token, url: ruta('habitaciones/guardarTarifas') });

    await expect(page.locator('#sessionFlashToast')).toContainText('no se guardó ningún cambio');
    await expect(page.locator(`input[name="${nombre}"]`)).toHaveClass(/is-invalid/);
    await page.reload();
    await expect(page.locator(`input[name="${nombre}"]`)).toHaveValue(original);
});
