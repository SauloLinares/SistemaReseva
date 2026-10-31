# Guía de Interfaz (UI) — Hotel Manager

Reglas para que todas las pantallas se vean y se comporten igual.
Si una vista necesita algo que esta guía no cubre, se agrega aquí primero.

## 1. Colores y medidas

Todos los colores, radios y sombras salen de los tokens de `public/assets/css/custom.css` (sección **0. TOKENS DE DISEÑO**).

- En CSS nuevo usa los tokens semánticos: `var(--hm-primary)`, `var(--hm-surface)`, `var(--hm-border)`, `var(--hm-text-muted)`, `var(--hm-radius)`…
- No escribas colores hex ni `!important` en componentes nuevos.
- No uses `style="..."` en las vistas: crea una clase en `custom.css`. Para textos pequeños usa `text-sm` (0.78rem), `text-xs` (0.7rem) o `text-2xs` (0.66rem). Solo se acepta `style` para valores calculados en PHP (ej. `width: <?= $porcentaje ?>%`).
- El modo oscuro se resuelve solo con los tokens; no hace falta duplicar reglas `[data-bs-theme="dark"]`.

## 2. Botones

| Acción | Clases | Icono |
|---|---|---|
| Guardar / Crear / Registrar | `btn btn-primary` | `bi-check-lg` |
| Cancelar / Volver | `btn btn-outline-secondary` | `bi-arrow-left` |
| Confirmar operación de dinero o estadía (cobrar, check-in, abrir caja) | `btn btn-success` | `bi-check2-circle` |
| Eliminar / Anular | `btn btn-outline-danger` (en tabla) o `btn btn-danger` (confirmación final) | `bi-trash` |
| Acción secundaria (buscar, filtrar, exportar) | `btn btn-outline-primary` | según la acción |

**Tamaños**

- Encabezado de página: `btn-sm`.
- Pie de formulario: tamaño normal (sin `btn-sm` ni `btn-lg`).
- Tablas: `btn btn-icon` (cuadrado de 32px, solo icono, con `title`).
- `btn-lg` solo para la acción final de un flujo largo (por ejemplo, confirmar una reserva).
- `btn-xs` solo para chips y filtros compactos.

**No usar** `fw-bold`, `fw-semibold`, `shadow-sm`, `py-*`, `px-*` ni `rounded-*` en botones: el CSS ya define todo eso.

**Textos**: verbo + objeto. "Guardar Huésped", "Registrar Pago". El de cancelar siempre dice "Cancelar".

**Confirmaciones**: usa `data-confirm="mensaje"` (abre SweetAlert). No uses `onclick="return confirm(...)"`.

## 3. Formularios

```php
<div class="row g-3">
    <div class="col-md-6">
        <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombres" name="nombres" required>
    </div>
</div>
```

- Separación entre campos: siempre `row g-3`.
- Etiquetas: solo `form-label`, siempre con `for` y el `id` del campo. Sin `fw-*`, `small` ni `text-muted`.
- Campo obligatorio: `required` + `<span class="text-danger">*</span>` en la etiqueta.
- Tamaño normal de campo. `form-control-sm` / `form-select-sm` solo en barras de filtros.
- Prefijos con `input-group-text` sin `bg-white` ni `bg-light`.
- Ancho del formulario: `<div class="row"><div class="col-12 col-lg-8">` alineado a la izquierda, en el mismo eje que el título de la página. Solo los documentos imprimibles (voucher, comprobante) van centrados.
- Botones del pie: partial `form_actions` (ver abajo).
- **Errores de validación:** en el controlador, junta los errores por campo y llama a `$this->volverConErrores('ruta/del/formulario', ['nombre_campo' => 'Mensaje'])`. Al volver, el formulario se rellena con lo que el usuario escribió y cada campo con error se marca en rojo con su mensaje debajo; no hay que tocar la vista. Ejemplos: `validarHuesped()`, `validarCliente()`, `validarHabitacion()`.
- **Seguridad (CSRF):** todo POST se valida con un token de sesión. Los `<form method="POST">` lo reciben solos al renderizar y `fetch()` lo envía en la cabecera `X-CSRF-Token`; no hay que escribir nada. Las acciones que borran o cambian datos van por POST: usa un formulario o un enlace con `data-confirm` (se envía por POST). Las rutas `eliminar*` rechazan GET.

## 4. Color

- El color comunica **estado** (disponible, ocupada, deuda, pagado) o marca **la acción principal**. No se usa para decorar.
- Una sola acción principal (`btn-primary`) por zona; el resto `btn-outline-secondary`.
- Indicadores (KPIs): superficie neutra; el color solo en el icono y el enlace.
- Importes en tablas: texto alineado a la derecha y `text-nowrap`; el saldo en rojo solo si hay deuda.
- Tamaño mínimo de texto: `text-2xs` (0.72rem).

## 5. Tarjetas

- `<div class="card">` sin `shadow-sm`, `card-outline` ni `border-*`: el CSS ya aplica sombra y borde.
- Encabezado: partial `card_header`.
- Las cabeceras de color (`section-header-blue`, etc.) solo para paneles de resumen del dashboard o la caja.

## 5.1 Modales

- `modal-header` sin fondo de color: el color va solo en el icono del título (`text-danger` si la acción borra o cierra algo, `text-success` para cobros, `text-primary` para el resto).
- Cerrar con `btn-close` (sin `btn-close-white`) y `aria-label="Cerrar"`.
- Pie: `btn btn-outline-secondary` "Cancelar" + la acción principal, ambos de tamaño normal. Si la acción es "Confirmar cancelación", el botón que cierra dice "Volver".
- Avisos de validación en JavaScript: `avisar(titulo, texto)`, no `alert()`.

## 5.2 JavaScript

- **Nada de JavaScript inline**: ni `<script>` con código ni `onclick`/`onchange` en el HTML. La CSP (`script-src 'self'`) los bloquea.
- El código de una vista va en `public/assets/js/vistas/<carpeta>-<archivo>.js` y se incluye con `<?= script_js('vistas/archivo.js') ?>` al final de la vista.
- Datos de PHP para el JS: atributos `data-*` o un bloque `<script type="application/json" id="...">` que se lee con `leerJson('id')`.
- Direcciones: `appUrl('reservas/editar/3')` (equivalente de `url()` de PHP), definida en `js/nucleo.js`.
- Acciones comunes sin escribir JS: `data-accion="imprimir"`, `data-autoenvio` (select que envía su formulario), `data-navegar="url-base"`, `data-abrir-modal="#id"`, `data-confirm="mensaje"`.
- En tablas con DataTables usa delegación (`document.addEventListener('click', ...)` + `closest()`): DataTables reconstruye las filas.
- `!important` solo cuando hay que ganarle a AdminLTE/Bootstrap y no alcanza con la especificidad.

## 5.3 Envíos seguros y borradores

- **Sin cobros duplicados:** todo POST (formulario o `fetch`) viaja con una clave de operación que
  pone `envios.js`. Si se reenvía lo mismo sin que el servidor lo haya confirmado (doble clic, corte
  de red, F5), la clave se repite y `App\Core\Operacion` responde como la primera vez sin volver a
  registrar. No hay que hacer nada en el formulario. `data-sin-operacion` lo excluye.
  Para que funcione, la acción debe terminar con `redirect()` (éxito) o `volverConErrores()` /
  flash `danger` (error), o con JSON `{"success": true|false}`.
- **Borrador:** `<form data-borrador>` guarda lo que se va llenando (por usuario, 12 h) y lo
  recupera con un aviso y un botón «Descartar». Se borra cuando el servidor confirma el envío.
  Usarlo en formularios largos o que se llenan mientras se atiende (POS de reserva, consumo).
- **Conexión y sesión:** sin conexión aparece una barra roja arriba y los formularios no se envían.
  Si la sesión venció, cualquier `fetch` lleva al login con un aviso. Un error inesperado del
  servidor muestra una página o un JSON con un código de referencia, que queda en el log de PHP.

## 6. Mensajes flash

Los muestra `layouts/header.php` como toast. **No** agregues bloques `$_SESSION['flash']` en las vistas.

## 7. Partials

Se incluyen con `partial('nombre', [...datos])`. Están en `app/Views/partials/`.

### `page_header`
```php
<?php partial('page_header', [
    'titulo'    => 'Gestión de Huéspedes',
    'icono'     => 'bi-people',
    'subtitulo' => 'Directorio de clientes e historial',      // opcional
    'migas'     => ['Huéspedes' => 'huespedes', 'Nuevo' => null], // opcional
    'acciones'  => [                                          // opcional
        ['texto' => 'Registrar Huésped', 'url' => 'huespedes/crear', 'icono' => 'bi-person-plus'],
        ['texto' => 'Buscar por DNI', 'url' => 'huespedes/buscar', 'icono' => 'bi-search', 'variante' => 'outline-primary'],
        ['texto' => 'Nuevo Tipo', 'modal' => '#modalTipo', 'icono' => 'bi-plus-circle'],       // abre un modal
        ['texto' => 'Imprimir', 'imprimir' => true, 'icono' => 'bi-printer', 'variante' => 'outline-secondary'],
    ],
    'insignia'  => ['texto' => 'Abierto #12', 'variante' => 'success'], // opcional, junto al título
    'extra'     => '<form>...</form>',     // opcional, HTML propio (ya escapado) junto a los botones
    'ocultar_al_imprimir' => true,         // opcional, para vouchers y comprobantes
]) ?>
```

Si una acción depende de una condición, arma el arreglo antes (ver `reservas/editar.php`).

### `card_header`
```php
<?php partial('card_header', ['titulo' => 'Ficha del Huésped', 'icono' => 'bi-person-vcard']) ?>
```
Acepta `acciones` con el mismo formato que `page_header`.

### `form_actions`
```php
<?php partial('form_actions', [
    'cancelar' => 'huespedes',        // ruta para url()
    'texto'    => 'Guardar Huésped',
    'icono'    => 'bi-check-lg',      // opcional
    'variante' => 'primary',          // opcional: primary | success | danger
]) ?>
```

### `table_actions`
```php
<?php partial('table_actions', [
    'ver'      => 'huespedes/historial/' . $h['id'],   // opcional
    'editar'   => 'huespedes/editar/' . $h['id'],      // opcional
    'eliminar' => 'huespedes/eliminar/' . $h['id'],    // opcional
    'confirmar'=> '¿Eliminar a ' . $h['nombres'] . '?',// opcional
]) ?>
```
