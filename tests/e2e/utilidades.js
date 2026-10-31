// Ayudas comunes de las pruebas.

/** Ruta interna del sistema ("reservas/crear") -> URL relativa del front controller */
const ruta = (r) => 'index.php?url=' + r.replace('?', '&');

/**
 * Vigila una página mientras se usa: errores de PHP en el HTML, errores de JS,
 * violaciones de CSP y pedidos a otros dominios. Llamar antes de navegar.
 */
function vigilar(pagina) {
    const problemas = [];
    const origen = new URL(process.env.E2E_BASE_URL || 'http://sistemareserva.test/public/').host;

    pagina.on('pageerror', (e) => problemas.push('Error JS: ' + e.message));
    pagina.on('console', (m) => {
        if (m.type() === 'error' && !/favicon/i.test(m.text())) problemas.push('Consola: ' + m.text());
    });
    pagina.on('request', (req) => {
        const host = new URL(req.url()).host;
        if (host && host !== origen && !req.url().startsWith('data:')) problemas.push('Pedido externo: ' + req.url());
    });
    pagina.on('response', async (res) => {
        const tipo = res.headers()['content-type'] || '';
        if (res.request().resourceType() === 'document' && tipo.includes('text/html')) {
            const html = await res.text().catch(() => '');
            const m = html.match(/<b>(Warning|Fatal error|Notice|Deprecated|Parse error)<\/b>:[^<]*/);
            if (m) problemas.push('PHP: ' + m[0].replace(/<\/?b>/g, ''));
        }
    });
    return problemas;
}

module.exports = { ruta, vigilar };
