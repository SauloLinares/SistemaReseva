// Copia minificada del JavaScript y el CSS propios en public/assets/dist (npm run build).
// Fuera de desarrollo, asset_version() (app/Core/Helpers.php) sirve estas copias si están al
// día con el original; si falta alguna o es más vieja, se sirve el original. Así el sistema
// funciona igual sin construir nada.
//
// Solo se minifica cada archivo (sin empaquetar): siguen siendo scripts clásicos y los nombres
// globales que comparten entre sí (appUrl, reponerCampos...) no cambian.
import { transform } from 'esbuild';
import { readdir, readFile, writeFile, mkdir, rm } from 'node:fs/promises';
import path from 'node:path';

const raiz = path.resolve(import.meta.dirname, '..');
const assets = path.join(raiz, 'public', 'assets');
const destino = path.join(assets, 'dist');

async function archivos(carpeta, extension) {
    const entradas = await readdir(carpeta, { withFileTypes: true, recursive: true });
    return entradas
        .filter(e => e.isFile() && e.name.endsWith(extension))
        .map(e => path.join(e.parentPath ?? e.path, e.name));
}

const kb = n => (n / 1024).toFixed(1) + ' KB';

async function minificar(origen, loader) {
    const relativo = path.relative(assets, origen);
    const codigo = await readFile(origen, 'utf8');
    const resultado = await transform(codigo, { loader, minify: true, target: 'es2020', charset: 'utf8', legalComments: 'none' });
    const salida = path.join(destino, relativo);
    await mkdir(path.dirname(salida), { recursive: true });
    await writeFile(salida, resultado.code);
    return { relativo, antes: Buffer.byteLength(codigo), despues: Buffer.byteLength(resultado.code) };
}

await rm(destino, { recursive: true, force: true });
const tareas = [
    ...(await archivos(path.join(assets, 'js'), '.js')).map(f => minificar(f, 'js')),
    ...(await archivos(path.join(assets, 'css'), '.css')).map(f => minificar(f, 'css')),
];
const hechos = await Promise.all(tareas);
let antes = 0;
let despues = 0;
for (const h of hechos.sort((a, b) => a.relativo.localeCompare(b.relativo))) {
    antes += h.antes;
    despues += h.despues;
}
console.log(`${hechos.length} archivos en public/assets/dist: ${kb(antes)} -> ${kb(despues)} (${Math.round((1 - despues / antes) * 100)} % menos)`);
