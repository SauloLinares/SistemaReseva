# Librerías de terceros (servidas desde el proyecto)

El sistema no depende de CDN externos: si el hotel se queda sin internet, la interfaz sigue funcionando.
Cada paquete se descargó del registro oficial de npm con `npm pack`, que verifica su integridad (sha512),
y se copiaron solo los archivos que se usan.

| Carpeta | Paquete | Versión | Licencia | Integridad del paquete (npm) |
|---|---|---|---|---|
| `bootstrap/` | bootstrap | 5.3.8 | MIT | `sha512-HP1SZDqaLDPwsNiqRqi5NcP...` |
| `popper/` | @popperjs/core | 2.11.8 | MIT | `sha512-P1st0aksCrn9sGZhp8GMYwB...` |
| `bootstrap-icons/` | bootstrap-icons | 1.13.1 | MIT | `sha512-ijombt4v6bv5CLeXvRWKy7C...` |
| `overlayscrollbars/` | overlayscrollbars | 2.11.0 | MIT (declarada en su package.json; el paquete no incluye archivo LICENSE) | `sha512-4le6aI4VzimWPnqKXifqdhj...` |
| `tom-select/` | tom-select | 2.4.3 | Apache-2.0 | `sha512-MFFrMxP1bpnAMPbdvPCZk0K...` |
| `sweetalert2/` | sweetalert2 | 11.14.0 | MIT | `sha512-kF1Q/+GtZZXr+rYVcBNwlEs...` |
| `simple-datatables/` | simple-datatables | 9.2.1 | LGPL-3.0 | `sha512-R+KRyZ4GNQdqj5IoW08OYi9...` |
| `fullcalendar/` | fullcalendar | 6.1.15 | MIT | `sha512-CFnh1yswjRh9puJVDk8VGwT...` |
| `fonts/plus-jakarta-sans/` | @fontsource/plus-jakarta-sans (latin 400–800) | 5.3.0 | OFL-1.1 | `sha512-9WRw3G74Ve4cyPApAZSXLPY...` |

Notas:
- FullCalendar 6 inyecta sus estilos desde el JavaScript: no tiene archivo CSS.
- SweetAlert2 `*.all.min.js` ya incluye su CSS.
- Para actualizar una librería: `npm pack <paquete>@<versión>`, extraer el `.tgz`, reemplazar los archivos de su carpeta y actualizar esta tabla.
