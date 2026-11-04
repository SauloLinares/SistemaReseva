// Una habitación no puede quedar en dos reservas activas con fechas que se cruzan, tampoco al
// editar solo las fechas (sin tocar las habitaciones). El intento se rechaza: no cambia datos.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

test('al editar las fechas no se puede pisar otra reserva de la misma habitación', async ({ page }) => {
    await page.goto(ruta('reservas'));
    // Reservas activas: id, código, habitaciones y fechas tal como las muestra el listado
    const filas = await page.locator('table tbody tr').evaluateAll(trs => trs.map(tr => {
        const editar = tr.querySelector('a[href*="reservas/editar/"]');
        const texto = tr.innerText;
        const fechas = texto.match(/\d{2}\/\d{2}\/\d{4}/g) || [];
        return {
            id: editar ? editar.href.match(/editar\/(\d+)/)[1] : null,
            codigo: (texto.match(/RES-\d{4}-\d+/) || [])[0],
            habitaciones: (tr.children[2]?.innerText.match(/\d+/g)) || [],
            activa: /Confirmada|Pendiente/i.test(texto),
            fechas: fechas.slice(0, 2).map(f => f.split('/').reverse().join('-')),
        };
    }).filter(f => f.id && f.activa && f.fechas.length === 2));

    let a = null, b = null;
    for (const x of filas) {
        b = filas.find(y => y.id !== x.id && y.habitaciones.some(h => x.habitaciones.includes(h)));
        if (b) { a = x; break; }
    }
    test.skip(!a, 'No hay dos reservas activas en la misma habitación en esta base');

    await page.goto(ruta('reservas/editar/' + a.id));
    const respuesta = await page.evaluate(async ({ entrada, salida }) => {
        const form = document.querySelector('form[action*="reservas/actualizar"]');
        const datos = new URLSearchParams(new FormData(form));
        datos.set('fecha_entrada', entrada);
        datos.set('fecha_salida', salida);
        if (!datos.get('_csrf')) datos.set('_csrf', document.querySelector('meta[name="csrf-token"]').content);
        const r = await fetch(form.action, { method: 'POST', body: datos });
        return r.text();
    }, { entrada: b.fechas[0], salida: b.fechas[1] });

    expect(respuesta).toContain('ya está reservada');
    expect(respuesta).toMatch(/RES-\d{4}-\d+\)/);
});
