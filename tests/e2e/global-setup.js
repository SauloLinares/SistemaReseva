// Inicia sesión una vez y guarda la sesión para todas las pruebas.
// Credenciales: variables E2E_EMAIL / E2E_PASSWORD; por defecto, el usuario de prueba del README.
const { chromium } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

module.exports = async (config) => {
    const { baseURL } = config.projects[0].use;
    const archivo = path.join(__dirname, '.auth', 'admin.json');
    fs.mkdirSync(path.dirname(archivo), { recursive: true });

    const navegador = await chromium.launch({ channel: 'chrome' });
    const pagina = await navegador.newPage({ baseURL });
    await pagina.goto('index.php?url=login');
    await pagina.fill('input[name="email"]', process.env.E2E_EMAIL || 'admin@admin.com');
    await pagina.fill('input[name="password"]', process.env.E2E_PASSWORD || 'admin123');
    await Promise.all([
        pagina.waitForURL(url => !url.href.includes('url=login')),
        pagina.locator('form[action*="login/autenticar"] [type="submit"]').click(),
    ]);
    await pagina.context().storageState({ path: archivo });
    await navegador.close();
};
