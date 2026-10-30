# 🏨 Hotel Manager - Sistema de Gestión Hotelera

Sistema web completo de reservas y administración hotelera desarrollado con **PHP 8+**, **MySQL**, **HTML5**, **Bootstrap 5** y **AdminLTE v4**, bajo una arquitectura limpia **MVC (Modelo - Vista - Controlador)** y **Programación Orientada a Objetos (POO)**.

---

## 📋 Módulos y Estructura del Menú Lateral

El sistema está organizado en **10 módulos funcionales** accesibles desde la barra lateral de navegación con AdminLTE v4:

### 1. Panel de Control (Dashboard)
- **Métricas en tiempo real**: Total de habitaciones, habitaciones disponibles y ocupadas, en limpieza o en mantenimiento.
- **Movimientos del día**: Reservas de hoy (check-in y check-outs programados).
- **Control de caja**: Ingresos cobrados en el día e ingresos acumulados del mes.
- **Mapa visual de habitaciones**: Tablero interactivo codificado por colores según estado.
- **Lista de huéspedes alojados** y acceso rápido a salidas inmediatas.

### GESTIÓN HOTELERA

### 2. Reservas
- **Registrar reserva**: Selector de huésped titular, rango de fechas con cálculo automático de noches y selección de habitaciones.
- **Listar reservas**: Tabla completa con filtros por estado y por rango de fechas.
- **Consultar disponibilidad**: Verificación inteligente que comprueba la **no superposición de fechas** (`Entrada < Salida_Reserva` y `Salida > Entrada_Reserva`) y excluye habitaciones en mantenimiento.
- **Modificar reserva**: Ajuste de fechas, cambio de habitaciones, control de balance financiero y consumos.
- **Cancelar reserva**: Modal con registro obligatorio del motivo de cancelación y liberación automática de habitaciones.
- **Calendario de reservas**: Integración interactiva con **FullCalendar v6**, vista mensual/semanal y colores por estado de reserva.

### 3. Huéspedes
- **Registrar huésped**: DNI, Pasaporte, Carnet de Extranjería, datos de contacto, nacionalidad y preferencias.
- **Listar huéspedes**: Directorio de clientes con conteo de visitas y última estadía.
- **Buscar huésped**: Búsqueda especializada por número de documento (DNI o pasaporte).
- **Historial de huéspedes**: Resumen cronológico de todas las estadías y reservas previas de cada cliente.

### 4. Habitaciones
- **Registrar habitación**: Número, piso, tipo de habitación, precio diario y características.
- **Listar habitaciones**: Inventario con filtros por piso y estado.
- **Tipos de habitación**: CRUD de categorías (Simple, Doble Estándar, Matrimonial, Suite Presidencial), capacidad y tarifa base.
- **Tarifas por noche**: Edición y actualización rápida de precios diarios de cada habitación.
- **Estado de habitaciones**: Tablero operativo para recepción y housekeeping con cambio rápido de estado con 1 clic.
- **Mantenimiento y limpieza**: Órdenes de trabajo, partes de limpieza tras check-out e incidencias con finalización automática a "Disponible".

### 5. Recepción
- **Check-in (Ingreso)**: Registro formal de llegada de huéspedes con reserva confirmada, asignación de llaves y pase automático a estado **OCUPADA**.
- **Check-out (Salida)**: Liquidación de saldos pendientes y pase automático a estado **LIMPIEZA** con orden de mantenimiento asignada.
- **Asignación / Cambio de habitación**: Reubicación de huéspedes durante su estadía liberando la habitación anterior a limpieza.
- **Control de estadías**: Monitoreo en vivo de todos los huéspedes alojados actualmente en el hotel y registro histórico.

### 6. Pagos y Facturación
- **Registrar pago**: Cobro de anticipos, liquidación final o consumos con emisión de comprobante.
- **Anticipos**: Seguimiento de depósitos y adelantos entregados por los clientes al reservar.
- **Saldos pendientes**: Listado de cuentas por cobrar activas con botón de cobro directo.
- **Comprobantes de pago**: Generación de Boletas de Venta, Facturas y Recibos de Caja con desglose de I.G.V. (18%) y vista imprimible (`window.print`).
- **Historial de pagos**: Registro de todas las transacciones de caja y devoluciones / notas de crédito.

### ADMINISTRACIÓN

### 7. Servicios Adicionales
- **Gestionar servicios**: Catálogo de restaurante, room service, bar, lavandería, spa y traslados al aeropuerto.
- **Registrar consumo**: Carga de consumos adicionales a la habitación o cobro directo en el acto.
- **Servicios por huésped**: Reporte detallado de consumos contratados por cada cliente.

### 8. Reportes y Estadísticas
- **Reporte de reservas**: Filtros por período, estado y monto facturado.
- **Reporte de ocupación**: Gráfico y tabla diaria de porcentaje de ocupación del mes y ranking de habitaciones más solicitadas.
- **Reporte de ingresos**: Desglose financiero por período y distribución porcentual por método de pago (Efectivo, Tarjeta, Transferencia, Yape/Plin).
- **Reporte de huéspedes**: Ranking de clientes frecuentes y gasto acumulado.
- **Reporte de cancelaciones**: Auditoría de reservas canceladas y motivos registrados.

### 9. Seguridad
- **Usuarios**: Control de acceso del personal, creación de cuentas con contraseñas encriptadas con `password_hash` (Bcrypt).
- **Roles**: Administrador, Recepcionista, Personal de Limpieza y perfiles personalizados.
- **Permisos**: Matriz de permisos granular (ver, crear, editar, eliminar) por cada módulo.
- **Auditoría del sistema**: Bitácora de operaciones y eventos de seguridad con fecha, usuario e IP de origen.

### 10. Configuración
- **Datos del hotel**: Razón social, RUC/NIF, teléfonos, correo oficial, dirección y horarios de Check-in (14:00) y Check-out (12:00).
- **Tipos de documento**: Catálogo de identificaciones admitidas (DNI, Pasaporte, CE, RUC).
- **Métodos de pago**: Configuración de pasarelas y comisiones.
- **Parámetros generales**: Moneda (S/.), porcentaje de I.G.V. / IVA (18%) y políticas de cancelación.

---

## 🗄️ Estructura de Base de Datos MySQL (`sistema_reserva`)

| Tabla | Propósito |
|---|---|
| `usuarios` | Cuentas de acceso al sistema con contraseñas encriptadas (Bcrypt) |
| `roles` | Perfiles de usuario (Administrador, Recepcionista, Limpieza) |
| `permisos` | Permisos asignados por rol a cada módulo |
| `huespedes` | Fichas personales de clientes (DNI, pasaporte, teléfono, nacionalidad) |
| `tipos_habitacion` | Categorías de habitaciones (Simple, Doble, Matrimonial, Suite) |
| `habitaciones` | Número, tipo, piso, precio diario y estado |
| `reservas` | Fechas de entrada y salida, huésped, noches, total, anticipo, saldo y estado |
| `reserva_habitaciones` | Detalle de habitaciones asignadas a cada reserva |
| `estancias` | Registro formal de Check-in y Check-out en tiempo real |
| `pagos` | Anticipos, pagos, devoluciones y comprobantes |
| `servicios` | Catálogo de servicios adicionales (restaurante, lavandería, bar, spa) |
| `reserva_servicios` | Consumos contratados por huésped o habitación |
| `mantenimientos` | Incidencias, reparaciones y protocolos de limpieza |
| `configuracion` | Datos comerciales del hotel y parámetros generales |
| `auditoria` | Registro de trazabilidad y eventos del sistema |

---

## 🚀 Credenciales de Acceso

- **URL de Acceso:** `http://localhost/SistemaReserva/login` o `http://localhost/SistemaReserva/public/index.php?url=login`
- **Correo Electrónico:** `admin@admin.com`
- **Contraseña:** `admin123`
