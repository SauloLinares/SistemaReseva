// Fase 3: recepción más rápida. Atajos con F1, cobro dividido en varios métodos con vuelto,
// accesos rápidos del Inicio, búsqueda con × y teclado numérico. No registra cobros: las
// validaciones del servidor se prueban con envíos que rechaza.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

async function elegirReservaConSaldo(page) {
    const opcion = page.locator('#selectReserva option[data-saldo]').first();
    const saldo = parseFloat(await opcion.getAttribute('data-saldo'));
    await page.selectOption('#selectReserva', await opcion.getAttribute('value'));
    return saldo;
}

test('Inicio: accesos rápidos de recepción', async ({ page }) => {
    await page.goto(ruta('home'));
    const accesos = page.locator('.accesos-rapidos');
    await expect(accesos.locator('a')).toHaveText([/Nueva reserva/, /Check-in/, /Check-out/, /Cobrar/, /Consumo/, /Planificación/]);
    await accesos.locator('a', { hasText: 'Check-out' }).click();
    await expect(page).toHaveURL(/url=recepcion\/checkout/);
});

test('F1 muestra los atajos de la pantalla y F7 lleva al campo', async ({ page }) => {
    await page.goto(ruta('pagos/crear'));
    await expect(page.locator('label[for="selectReserva"] .kbd-atajo')).toHaveText('F7');
    await page.keyboard.press('F1');
    const ayuda = page.locator('.tabla-atajos');
    await expect(ayuda).toContainText('Elegir la reserva');
    await expect(ayuda).toContainText('Monto a cobrar');
    await expect(ayuda).toContainText('Nueva reserva');
    await page.locator('.swal2-confirm').click();
    await expect(page.locator('.swal2-container')).toHaveCount(0); // con un diálogo abierto los atajos esperan

    await page.keyboard.press('F7');
    await expect(page.locator('#selectReserva')).toBeFocused();
});

test('cobro dividido: el primer método se queda con lo que falta y se calcula el vuelto', async ({ page }) => {
    await page.goto(ruta('pagos/crear'));
    const saldo = await elegirReservaConSaldo(page);
    await expect(page.locator('#inputMonto')).toHaveValue(saldo.toFixed(2));
    await expect(page.locator('#inputMonto')).toHaveAttribute('inputmode', 'decimal');

    await page.click('[data-agregar-linea]');
    const segunda = page.locator('[data-linea]').nth(1);
    await expect(segunda.locator('select')).not.toHaveValue('Efectivo'); // propone otro método
    await segunda.locator('input').fill('10');
    await expect(page.locator('#inputMonto')).toHaveValue((saldo - 10).toFixed(2));
    await expect(page.locator('.cobro [data-total]')).toHaveText('S/. ' + saldo.toFixed(2));

    await page.fill('#inputMonto-recibido', String(Math.ceil(saldo - 10) + 20));
    await expect(page.locator('[data-vuelto]')).toHaveText(/^S\/\. \d+\.\d{2}$/);
    await page.fill('#inputMonto-recibido', '1');
    await expect(page.locator('[data-vuelto]')).toHaveText(/^Falta/);

    // Quitar el segundo método
    await segunda.locator('[data-quitar-linea]').click();
    await expect(page.locator('[data-linea]')).toHaveCount(1);
});

test('el servidor rechaza un cobro mayor al saldo o con métodos repetidos', async ({ page }) => {
    await page.goto(ruta('pagos/crear'));
    const saldo = await elegirReservaConSaldo(page);

    await page.fill('#inputMonto', (saldo + 50).toFixed(2));
    await page.getByRole('button', { name: /Registrar Cobro/ }).click();
    await expect(page.locator('[data-error-para="lineas"]')).toContainText('supera el saldo pendiente');
    // Lo escrito vuelve al formulario
    await expect(page.locator('#inputMonto')).toHaveValue((saldo + 50).toFixed(2));

    await page.fill('#inputMonto', '1.00');
    await page.click('[data-agregar-linea]');
    await page.locator('[data-linea]').nth(1).locator('select').selectOption('Efectivo');
    await page.locator('[data-linea]').nth(1).locator('input').fill('1');
    await page.getByRole('button', { name: /Registrar Cobro/ }).click();
    await expect(page.locator('[data-error-para="lineas"]')).toContainText('No repitas un método');
    await expect(page.locator('[data-linea]')).toHaveCount(2); // las dos líneas se restauran
});

test('cobro rápido (fetch): métodos que no existen se rechazan con un mensaje claro', async ({ page }) => {
    await page.goto(ruta('pagos/crear'));
    const reservaId = await page.locator('#selectReserva option[data-saldo]').first().getAttribute('value');
    const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    const enviar = (form) => page.request.post(ruta('pagos/apiGuardar'), { headers: { 'X-CSRF-Token': token }, form }).then(r => r.json());

    // "Yape" a secas era lo que mandaba el modal del check-out y hacía fallar el cobro
    const metodoMalo = await enviar({ reserva_id: reservaId, 'lineas[0][metodo]': 'Yape', 'lineas[0][monto]': '1' });
    expect(metodoMalo.success).toBe(false);
    expect(metodoMalo.mensaje).toContain('método de pago válido');

    const excedido = await enviar({ reserva_id: reservaId, 'lineas[0][metodo]': 'Yape/Plin', 'lineas[0][monto]': '999999' });
    expect(excedido.success).toBe(false);
    expect(excedido.mensaje).toContain('supera el saldo');
});

test('búsqueda de tablas con botón × para vaciarla', async ({ page }) => {
    await page.goto(ruta('huespedes'));
    const buscador = page.locator('.datatable-input');
    const filas = page.locator('table tbody tr');
    const total = await filas.count();
    const boton = page.locator('.campo-limpiable .btn-limpiar');
    await expect(boton).toBeHidden();

    await buscador.fill('zzzz-no-existe');
    await buscador.press('End'); // simple-datatables filtra al soltar una tecla
    await expect(boton).toBeVisible();
    await boton.click();
    await expect(buscador).toHaveValue('');
    await expect(buscador).toBeFocused();
    await expect(filas).toHaveCount(total);
});
