// Pruebas end-to-end del frontend con Playwright.
// Ejecutar con Laragon encendido:  npm run test:e2e
// Usa el Chrome instalado en el equipo (no descarga navegadores).
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/e2e',
    // Las pruebas comparten la base de datos de desarrollo: una a la vez
    workers: 1,
    fullyParallel: false,
    timeout: 30_000,
    expect: { timeout: 5_000 },
    reporter: [['list'], ['html', { open: 'never' }]],
    globalSetup: require.resolve('./tests/e2e/global-setup.js'),
    use: {
        baseURL: process.env.E2E_BASE_URL || 'http://sistemareserva.test/public/',
        channel: 'chrome',
        storageState: 'tests/e2e/.auth/admin.json',
        locale: 'es-PE',
        viewport: { width: 1440, height: 900 },
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
});
