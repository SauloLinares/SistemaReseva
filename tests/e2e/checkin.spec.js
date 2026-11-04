// Check-in: solo reservas pendientes o confirmadas, desde el día de entrada (antes, confirmando
// el ingreso anticipado), nunca pasada la salida ni con la habitación ocupada por otra estadía.
// No hace ningún check-in real: solo comprueba los rechazos y los avisos.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

const enviarCheckin = (page, campos) => page.evaluate(async ({ url, campos }) => {
    const datos = new URLSearchParams({ _csrf: document.querySelector('meta[name="csrf-token"]').content, ...campos });
    // fetch sigue la redirección: la página de destino trae el aviso del servidor
    const r = await fetch(url, { method: 'POST', body: datos });
    return r.text();
}, { url: ruta('recepcion/procesarCheckin'), campos });

/** id de la primera reserva de la lista de check-in cuyo texto cumpla el patrón */
async function reservaDeLaLista(page, patron) {
    await page.goto(ruta('recepcion/checkin'));
    const opciones = await page.locator('#selectReserva option').evaluateAll(os => os.map(o => [o.value, o.textContent]));
    return (opciones.find(([v, t]) => v && patron.test(t)) || [])[0] || null;
}

test('una reserva que ya está en estadía no vuelve a hacer check-in', async ({ page }) => {
    await page.goto(ruta('reservas?estado=en_estadia'));
    const enlaces = page.locator('a[href*="reservas/voucher/"]');
    const enlace = (await enlaces.count()) ? await enlaces.first().getAttribute('href') : null;
    test.skip(!enlace, 'No hay reservas en estadía en esta base');
    const id = enlace.match(/voucher\/(\d+)/)[1];

    expect(await enviarCheckin(page, { reserva_id: id })).toContain('ya está en estadía');
});

test('antes de la fecha de entrada hay que confirmar el ingreso anticipado', async ({ page }) => {
    // Una reserva futura que admita el ingreso anticipado (si su habitación tiene otra reserva
    // antes, el anticipo se bloquea y no sirve para esta prueba)
    await page.goto(ruta('recepcion/checkin'));
    const futuras = await page.locator('#selectReserva option').evaluateAll(os =>
        os.filter(o => o.value && /llega el/.test(o.textContent)).map(o => o.value));
    let id = null;
    for (const candidato of futuras) {
        await page.goto(ruta('recepcion/checkin?reserva_id=' + candidato));
        if (await page.locator('[data-checkin-anticipado]').count()) { id = candidato; break; }
    }
    test.skip(!id, 'No hay reservas futuras que admitan check-in anticipado en esta base');

    await expect(page.locator('[data-checkin-anticipado]')).toContainText('Check-in anticipado');
    await expect(page.locator('#confirmaAnticipado')).toHaveAttribute('required', '');

    // Sin la confirmación, el servidor lo rechaza (aunque se salte el formulario)
    expect(await enviarCheckin(page, { reserva_id: id })).toContain('confirma el check-in anticipado');
});

test('una reserva cuya salida ya pasó no deja hacer check-in', async ({ page }) => {
    await page.goto(ruta('recepcion/checkin'));
    const atrasadas = await page.locator('#selectReserva option').evaluateAll(os =>
        os.filter(o => /atrasada/.test(o.textContent)).map(o => o.value));
    let id = null;
    for (const candidato of atrasadas) {
        await page.goto(ruta('recepcion/checkin?reserva_id=' + candidato));
        if (await page.locator('[data-problema-checkin]').count()) { id = candidato; break; }
    }
    test.skip(!id, 'No hay reservas no presentadas con la salida vencida en esta base');

    await expect(page.locator('[data-problema-checkin]')).toContainText('no se presentó');
    await expect(page.locator('form[action*="procesarCheckin"] [type="submit"]')).toBeDisabled();
});

test('"Pasar a Check-in Hoy" del voucher abre el check-in con esa reserva ya elegida', async ({ page }) => {
    const id = await reservaDeLaLista(page, /llega hoy/);
    test.skip(!id, 'No hay reservas que lleguen hoy en esta base');

    // El botón está en el aviso de reserva recién confirmada (?nuevo=1)
    await page.goto(ruta('reservas/voucher/' + id + '?nuevo=1'));
    const boton = page.locator('[data-pasar-checkin]');
    await expect(boton).toBeVisible();
    await boton.click();

    await expect(page).toHaveURL(new RegExp('recepcion/checkin&reserva_id=' + id + '$'));
    await expect(page.locator('#selectReserva')).toHaveValue(id);
    // Con la reserva elegida ya se ve su ficha para confirmar el ingreso
    await expect(page.locator('form[action*="procesarCheckin"] [type="submit"]')).toBeEnabled();
});
