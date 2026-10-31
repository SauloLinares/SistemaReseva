// Revisión del JavaScript del frontend (npm run lint). Detecta errores comunes antes de
// probar en el navegador: variables sin declarar, código inalcanzable, comparaciones raras...
const js = require('@eslint/js');
const globals = require('globals');

module.exports = [
    {
        ignores: ['public/assets/vendor/**', 'public/assets/dist/**', 'node_modules/**', 'test-results/**', 'playwright-report/**'],
    },
    js.configs.recommended,
    {
        // Scripts clásicos del navegador (no módulos): cada archivo comparte el ámbito global
        files: ['public/assets/js/**/*.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'script',
            globals: {
                ...globals.browser,
                // Librerías servidas desde public/assets/vendor
                bootstrap: 'readonly',
                Swal: 'readonly',
                TomSelect: 'readonly',
                simpleDatatables: 'readonly',
                FullCalendar: 'readonly',
                OverlayScrollbarsGlobal: 'readonly',
                // Ayudantes propios (nucleo.js, custom.js, envios.js)
                leerJson: 'readonly',
                appUrl: 'readonly',
                assetUrl: 'readonly',
                avisar: 'readonly',
            },
        },
        rules: {
            // Funciones de nivel superior que usan otras vistas o el HTML: no son "sin uso"
            'no-unused-vars': ['warn', { vars: 'local', args: 'none', caughtErrors: 'none' }],
            'no-empty': ['error', { allowEmptyCatch: true }],
            eqeqeq: ['warn', 'smart'],
            'no-var': 'warn',
            'prefer-const': 'warn',
        },
    },
    {
        files: ['tests/**/*.js', 'playwright.config.js', 'eslint.config.js', 'tools/**/*.js', 'tools/**/*.mjs'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'commonjs',
            globals: { ...globals.node, ...globals.browser },
        },
    },
    {
        files: ['tools/**/*.mjs'],
        languageOptions: { sourceType: 'module' },
    },
];
