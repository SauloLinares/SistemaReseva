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
- Ancho del formulario: `<div class="row justify-content-center"><div class="col-12 col-lg-8">`.
- Botones del pie: partial `form_actions` (ver abajo).

## 4. Tarjetas

- `<div class="card">` sin `shadow-sm`, `card-outline` ni `border-*`: el CSS ya aplica sombra y borde.
- Encabezado: partial `card_header`.
- Las cabeceras de color (`section-header-blue`, etc.) solo para paneles de resumen del dashboard o la caja.

## 5. Mensajes flash

Los muestra `layouts/header.php` como toast. **No** agregues bloques `$_SESSION['flash']` en las vistas.

## 6. Partials

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
