-- ========================================================
-- BASE DE DATOS: sistema_reserva
-- SISTEMA DE GESTIÓN HOTELERA (HOTEL MANAGER)
-- Compatible con MySQL 8.x / MariaDB
-- ========================================================

CREATE DATABASE IF NOT EXISTS sistema_reserva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_reserva;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. ROLES Y PERMISOS
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. USUARIOS
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NULL,
    rol VARCHAR(50) DEFAULT 'Administrador',
    telefono VARCHAR(20) NULL,
    estado TINYINT(1) DEFAULT 1 COMMENT '1: Activo, 0: Inactivo',
    ultimo_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3. HUÉSPEDES
CREATE TABLE IF NOT EXISTS huespedes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento ENUM('DNI', 'Pasaporte', 'Carnet Extranjería', 'Otro') DEFAULT 'DNI',
    numero_documento VARCHAR(30) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NULL,
    telefono VARCHAR(30) NULL,
    nacionalidad VARCHAR(80) DEFAULT 'Perú',
    direccion VARCHAR(255) NULL,
    notas TEXT NULL,
    estado TINYINT(1) DEFAULT 1 COMMENT '1: Activo, 0: Inactivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_doc (numero_documento),
    INDEX idx_nombres (nombres, apellidos)
) ENGINE=InnoDB;

-- Compatibilidad hacia atrás si existe clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL UNIQUE,
    correo VARCHAR(150),
    telefono VARCHAR(20),
    estado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. TIPOS DE HABITACIÓN
CREATE TABLE IF NOT EXISTS tipos_habitacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    capacidad INT NOT NULL DEFAULT 1,
    precio_base DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    caracteristicas TEXT NULL COMMENT 'Wifi, TV, Jacuzzi, Aire Acondicionado, etc.',
    estado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. HABITACIONES
CREATE TABLE IF NOT EXISTS habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL UNIQUE,
    tipo_id INT NOT NULL,
    piso VARCHAR(10) NOT NULL DEFAULT '1',
    precio_diario DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    estado ENUM('disponible', 'ocupada', 'limpieza', 'mantenimiento') DEFAULT 'disponible',
    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_id) REFERENCES tipos_habitacion(id) ON DELETE RESTRICT,
    INDEX idx_estado (estado)
) ENGINE=InnoDB;

-- 6. MANTENIMIENTOS Y LIMPIEZA
CREATE TABLE IF NOT EXISTS mantenimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habitacion_id INT NOT NULL,
    tipo ENUM('limpieza', 'mantenimiento', 'reparacion', 'inspeccion') DEFAULT 'limpieza',
    descripcion TEXT NOT NULL,
    responsable VARCHAR(100) NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NULL,
    costo DECIMAL(10, 2) DEFAULT 0.00,
    estado ENUM('pendiente', 'en_proceso', 'finalizado') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. RESERVAS
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_reserva VARCHAR(30) NOT NULL UNIQUE,
    huesped_id INT NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    noches INT NOT NULL DEFAULT 1,
    adultos INT NOT NULL DEFAULT 1,
    ninos INT NOT NULL DEFAULT 0,
    estado ENUM('pendiente', 'confirmada', 'en_estadia', 'finalizada', 'cancelada') DEFAULT 'confirmada',
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    motivo_descuento VARCHAR(255) NULL,
    anticipo DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    saldo DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    metodo_pago VARCHAR(50) DEFAULT 'Efectivo',
    origen ENUM('manual', 'web', 'telefonica') DEFAULT 'manual',
    notas TEXT NULL,
    motivo_cancelacion TEXT NULL,
    usuario_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (huesped_id) REFERENCES huespedes(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_fechas (fecha_entrada, fecha_salida),
    INDEX idx_estado (estado)
) ENGINE=InnoDB;

-- 8. HABITACIONES ASIGNADAS A LA RESERVA
CREATE TABLE IF NOT EXISTS reserva_habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    habitacion_id INT NOT NULL,
    precio_noche DECIMAL(10, 2) NOT NULL,
    noches INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 9. RECEPCIÓN / ESTANCIAS (CHECK-IN / CHECK-OUT)
CREATE TABLE IF NOT EXISTS estancias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    habitacion_id INT NOT NULL,
    huesped_id INT NOT NULL,
    fecha_checkin DATETIME NOT NULL,
    fecha_checkout DATETIME NULL,
    observaciones TEXT NULL,
    estado ENUM('activa', 'finalizada') DEFAULT 'activa',
    usuario_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id) ON DELETE RESTRICT,
    FOREIGN KEY (huesped_id) REFERENCES huespedes(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 10. SERVICIOS ADICIONALES
CREATE TABLE IF NOT EXISTS servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria ENUM('Restaurante', 'Bar', 'Lavandería', 'Room Service', 'Spa', 'Transporte', 'Otro') DEFAULT 'Restaurante',
    descripcion TEXT NULL,
    precio DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    estado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 11. CONSUMOS / SERVICIOS CONTRATADOS POR HUÉSPED O RESERVA
CREATE TABLE IF NOT EXISTS reserva_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    servicio_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    fecha_consumo DATETIME NOT NULL,
    pagado TINYINT(1) DEFAULT 0 COMMENT '0: Pendiente añadir a cuenta, 1: Pagado',
    notas VARCHAR(255) NULL,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 12. PAGOS Y FACTURACIÓN
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    huesped_id INT NOT NULL,
    tipo ENUM('anticipo', 'pago', 'saldo_final', 'consumo', 'devolucion') DEFAULT 'pago',
    metodo_pago ENUM('Efectivo', 'Tarjeta de Crédito', 'Tarjeta de Débito', 'Transferencia Bancaria', 'Yape/Plin') DEFAULT 'Efectivo',
    monto DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    comprobante_tipo ENUM('Boleta', 'Factura', 'Recibo de Caja', 'Nota de Crédito') DEFAULT 'Boleta',
    comprobante_numero VARCHAR(50) NOT NULL,
    fecha_pago DATETIME NOT NULL,
    notas TEXT NULL,
    usuario_id INT NULL,
    turno_caja_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (huesped_id) REFERENCES huespedes(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (turno_caja_id) REFERENCES turnos_caja(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 12.1. TURNOS Y SESIONES DE CAJA
CREATE TABLE IF NOT EXISTS turnos_caja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_apertura DATETIME NOT NULL,
    monto_inicial DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    fecha_cierre DATETIME NULL,
    monto_esperado_efectivo DECIMAL(10, 2) NULL DEFAULT 0.00,
    monto_real_efectivo DECIMAL(10, 2) NULL DEFAULT 0.00,
    diferencia DECIMAL(10, 2) NULL DEFAULT 0.00,
    total_ventas_efectivo DECIMAL(10, 2) NULL DEFAULT 0.00,
    total_ventas_digital DECIMAL(10, 2) NULL DEFAULT 0.00,
    total_ingresos_extra DECIMAL(10, 2) NULL DEFAULT 0.00,
    total_egresos DECIMAL(10, 2) NULL DEFAULT 0.00,
    estado ENUM('abierta', 'cerrada') DEFAULT 'abierta',
    observaciones_apertura TEXT NULL,
    observaciones_cierre TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 12.2. MOVIMIENTOS Y GASTOS DE CAJA CHICA (INGRESOS Y EGRESOS)
CREATE TABLE IF NOT EXISTS caja_movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    turno_caja_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo ENUM('ingreso', 'egreso') NOT NULL,
    concepto VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) DEFAULT 'General',
    monto DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    metodo_pago ENUM('Efectivo', 'Tarjeta de Crédito', 'Tarjeta de Débito', 'Transferencia Bancaria', 'Yape/Plin') DEFAULT 'Efectivo',
    comprobante VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (turno_caja_id) REFERENCES turnos_caja(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 13. CONFIGURACIÓN DEL SISTEMA
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(80) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    grupo VARCHAR(50) DEFAULT 'general',
    descripcion VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Envíos ya procesados (evita registrar dos veces un cobro por doble clic o reintento)
CREATE TABLE IF NOT EXISTS operaciones (
    clave VARCHAR(64) NOT NULL PRIMARY KEY,
    usuario_id INT NOT NULL DEFAULT 0,
    ruta VARCHAR(150) NOT NULL DEFAULT '',
    estado ENUM('en_curso','completada') NOT NULL DEFAULT 'en_curso',
    tipo ENUM('json','redireccion') NULL,
    respuesta TEXT NULL,
    creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_creado (creado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. AUDITORÍA DEL SISTEMA
CREATE TABLE IF NOT EXISTS auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    usuario_nombre VARCHAR(100) NULL,
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    detalle TEXT NULL,
    ip VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 15. TABLA PRODUCTOS (del módulo educativo previo)
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    categoria VARCHAR(100) NULL,
    estado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- DATOS INICIALES Y SEMILLAS DE PRUEBA
-- ========================================================

-- Roles
INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'Administrador', 'Control total de módulos, reportes y configuración del hotel'),
(2, 'Recepcionista', 'Gestión de reservas, check-in, check-out, huéspedes y cobros'),
(3, 'Mantenimiento y Limpieza', 'Control de estado de habitaciones y partes de limpieza')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- Permisos iniciales
INSERT INTO permisos (rol_id, modulo, accion) VALUES
(1, 'todos', 'todos'),
(2, 'reservas', 'gestionar'),
(2, 'huespedes', 'gestionar'),
(2, 'habitaciones', 'ver'),
(2, 'recepcion', 'gestionar'),
(2, 'pagos', 'gestionar'),
(3, 'habitaciones', 'mantenimiento')
ON DUPLICATE KEY UPDATE id=id;

-- Usuario Administrador (password: admin123)
INSERT INTO usuarios (id, nombre, email, password, rol_id, rol, telefono, estado) VALUES
(1, 'Administrador General', 'admin@admin.com', '$2y$10$2Jlt.YCvEPNCu3PFJCc.1ux40sgvp0sAh/j8XaE/we8W4nGREsuo.', 1, 'Administrador', '+51 987654321', 1),
(2, 'Sofía Valdivia', 'recepcion@hotel.com', '$2y$10$2Jlt.YCvEPNCu3PFJCc.1ux40sgvp0sAh/j8XaE/we8W4nGREsuo.', 2, 'Recepcionista', '+51 984123456', 1)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), rol_id=VALUES(rol_id);

-- Tipos de Habitación
INSERT INTO tipos_habitacion (id, nombre, descripcion, capacidad, precio_base, caracteristicas, estado) VALUES
(1, 'Simple', 'Habitación acogedora con 1 cama individual, baño privado y escritorio.', 1, 80.00, 'Wi-Fi 5G, TV LED 40", Ducha española, Escritorio', 1),
(2, 'Doble Estándar', 'Habitación espaciosa con 2 camas individuales, ideal para amigos o compañeros.', 2, 130.00, 'Wi-Fi 5G, TV LED 50", Climatizador, Frigobar', 1),
(3, 'Matrimonial', 'Habitación romántica y cómoda con cama King Size y excelente vista.', 2, 160.00, 'Cama King, Smart TV 55", Balcón, Frigobar, Tina', 1),
(4, 'Suite Presidencial', 'Lujo total con sala de estar, jacuzzi privado, vista panorámica y amenities vip.', 4, 300.00, 'Jacuzzi, Cama Super King, Sala de estar, Smart TV 65", Desayuno incluido', 1),
(5, 'Junior Suite', 'Espacio elegante con sala de estar integrada, balcón privado y amenidades ejecutivas.', 3, 220.00, 'Cama King, Sofá cama, Smart TV 55", Balcón privado, Minibar, Climatizador', 1),
(6, 'Familiar Deluxe', 'Amplitud y confort para familias o grupos, con dos ambientes y múltiples camas.', 5, 260.00, '1 Cama Queen + 3 Camas individuales, 2 Baños, Smart TV 65", Frigobar, Wi-Fi 5G', 1)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), precio_base=VALUES(precio_base);

-- Habitaciones
INSERT INTO habitaciones (id, numero, tipo_id, piso, precio_diario, estado, descripcion) VALUES
(1, '101', 1, '1', 80.00, 'disponible', 'Habitación simple en primer piso con acceso para discapacitados'),
(2, '102', 1, '1', 80.00, 'disponible', 'Habitación simple con vista al patio interior'),
(3, '201', 2, '2', 130.00, 'ocupada', 'Doble estándar con dos camas cómodas y vista a la calle'),
(4, '202', 2, '2', 130.00, 'disponible', 'Doble estándar en segundo piso con aire acondicionado'),
(5, '301', 3, '3', 160.00, 'ocupada', 'Matrimonial de lujo con cama King Size y balcón privado'),
(6, '302', 3, '3', 160.00, 'limpieza', 'Matrimonial en tercer piso lista para limpieza rápida'),
(7, '401', 4, '4', 300.00, 'mantenimiento', 'Suite Presidencial con jacuzzi (en revisión de grifería)'),
(8, '402', 4, '4', 300.00, 'disponible', 'Suite con vista panorámica a la ciudad y sala privada'),
(9, '103', 1, '1', 80.00, 'disponible', 'Habitación individual cerca del jardín interior con escritorio de trabajo y baño privado.'),
(10, '104', 1, '1', 85.00, 'disponible', 'Habitación individual ejecutiva con Wi-Fi 5G de alta velocidad y vista al patio.'),
(11, '105', 2, '1', 130.00, 'disponible', 'Doble en planta baja con dos camas individuales y acceso facilitado.'),
(12, '106', 6, '1', 260.00, 'disponible', 'Habitación familiar amplia en primer piso, ideal para familias con niños.'),
(13, '203', 2, '2', 135.00, 'disponible', 'Habitación doble con balcón y dos camas individuales confortables.'),
(14, '204', 2, '2', 130.00, 'limpieza', 'Doble con aire acondicionado y vista a la piscina interior.'),
(15, '205', 1, '2', 80.00, 'disponible', 'Habitación individual acogedora con baño privado y Smart TV.'),
(16, '206', 6, '2', 270.00, 'disponible', 'Suite familiar espaciosa de dos ambientes con dos baños privados.'),
(17, '303', 3, '3', 165.00, 'disponible', 'Habitación matrimonial con cama King Size, frigobar y vista exterior.'),
(18, '304', 3, '3', 160.00, 'disponible', 'Matrimonial con cama Queen, Smart TV 55 pulgadas y tina de hidromasaje.'),
(19, '305', 2, '3', 140.00, 'disponible', 'Doble superior con vista panorámica a la ciudad y dos camas.'),
(20, '306', 5, '3', 220.00, 'disponible', 'Junior Suite con sala de estar integrada y balcón exterior privado.'),
(21, '403', 5, '4', 225.00, 'disponible', 'Junior Suite ejecutiva con escritorio de trabajo y minibar premium.'),
(22, '404', 3, '4', 170.00, 'limpieza', 'Matrimonial deluxe con cama King, iluminación ambiental y tina.'),
(23, '405', 4, '4', 320.00, 'disponible', 'Suite presidencial de lujo con terraza privada y jacuzzi de hidromasaje.'),
(24, '406', 5, '4', 220.00, 'disponible', 'Junior Suite espaciosa con cama King y sofá cama de diseño.'),
(25, '501', 5, '5', 230.00, 'disponible', 'Junior Suite en piso alto con vista panorámica hacia la bahía.'),
(26, '502', 3, '5', 175.00, 'disponible', 'Matrimonial superior con finos acabados y balcón con vista al atardecer.'),
(27, '503', 4, '5', 350.00, 'disponible', 'Suite presidencial VIP con vista 360°, jacuzzi privado y sala de estar lounge.'),
(28, '504', 6, '5', 280.00, 'disponible', 'Penthouse familiar con terraza privada, dos dormitorios y dos baños completos.')
ON DUPLICATE KEY UPDATE numero=VALUES(numero), estado=VALUES(estado);

-- Huéspedes
INSERT INTO huespedes (id, tipo_documento, numero_documento, nombres, apellidos, correo, telefono, nacionalidad, direccion, estado) VALUES
(1, 'DNI', '71829341', 'Carlos', 'Mendoza Quispe', 'carlos.mendoza@email.com', '987654321', 'Perú', 'Av. Larco 450, Miraflores', 1),
(2, 'DNI', '82910452', 'Ana', 'Torres Rojas', 'ana.torres@email.com', '912345678', 'Perú', 'Jr. Ayacucho 320, Trujillo', 1),
(3, 'Pasaporte', 'PAS902188', 'John', 'Smith', 'john.smith@travel.com', '+1 555-0199', 'Estados Unidos', '124 Main Street, NY', 1),
(4, 'Carnet Extranjería', 'CE4091823', 'Lucía', 'Gómez Morales', 'lucia.gomez@empresa.com', '976543210', 'Colombia', 'Calle 10 # 42-28, Medellín', 1),
(5, 'DNI', '45920138', 'Luis', 'García Paredes', 'luis.garcia@email.com', '954321987', 'Perú', 'Av. Arequipa 1280, Lince', 1)
ON DUPLICATE KEY UPDATE numero_documento=VALUES(numero_documento);

-- Sincronizar clientes con huespedes
INSERT INTO clientes (nombres, apellidos, dni, correo, telefono, estado)
SELECT nombres, apellidos, numero_documento, correo, telefono, estado FROM huespedes
ON DUPLICATE KEY UPDATE dni=dni;

-- Servicios Adicionales
INSERT INTO servicios (id, nombre, categoria, descripcion, precio, estado) VALUES
(1, 'Desayuno Buffet Continental', 'Restaurante', 'Variedad de panes, frutas de temporada, jugos naturales, embutidos y café gourmet', 25.00, 1),
(2, 'Almuerzo Ejecutivo Gourmet', 'Restaurante', 'Entrada, plato de fondo típico y bebida artesanal', 40.00, 1),
(3, 'Servicio de Lavandería (Prenda/Kilo)', 'Lavandería', 'Lavado, secado y planchado al vapor de prendas', 15.00, 1),
(4, 'Minibar - Bebidas y Licores', 'Bar', 'Selección de vinos, cervezas artesanales y aperitivos', 35.00, 1),
(5, 'Traslado Aeropuerto - Hotel', 'Transporte', 'Servicio de transporte privado en minivan ejecutiva', 60.00, 1),
(6, 'Sesión de Masajes y Spa 45 min', 'Spa', 'Masaje relajante corporal con aceites aromáticos', 90.00, 1)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), precio=VALUES(precio);

-- Mantenimientos
INSERT INTO mantenimientos (id, habitacion_id, tipo, descripcion, responsable, fecha_inicio, fecha_fin, costo, estado) VALUES
(1, 7, 'reparacion', 'Mantenimiento del motor del jacuzzi e hidromasaje', 'Técnico Roberto Salazar', NOW() - INTERVAL 1 DAY, NULL, 150.00, 'en_proceso'),
(2, 6, 'limpieza', 'Cambio de sábanas y desinfección integral tras check-out', 'Camarera Marta Díaz', NOW() - INTERVAL 2 HOUR, NULL, 0.00, 'pendiente')
ON DUPLICATE KEY UPDATE habitacion_id=VALUES(habitacion_id);

-- Reservas de ejemplo
INSERT INTO reservas (id, codigo_reserva, huesped_id, fecha_entrada, fecha_salida, noches, adultos, ninos, estado, total, anticipo, saldo, metodo_pago, origen, notas, usuario_id) VALUES
(1, 'RES-2026-001', 1, CURDATE() - INTERVAL 1 DAY, CURDATE() + INTERVAL 2 DAY, 3, 2, 0, 'en_estadia', 390.00, 150.00, 240.00, 'Tarjeta de Crédito', 'manual', 'Solicitó almohadas adicionales', 1),
(2, 'RES-2026-002', 3, CURDATE(), CURDATE() + INTERVAL 3 DAY, 3, 1, 0, 'en_estadia', 480.00, 480.00, 0.00, 'Transferencia Bancaria', 'web', 'Huésped corporativo internacional', 1),
(3, 'RES-2026-003', 2, CURDATE() + INTERVAL 1 DAY, CURDATE() + INTERVAL 4 DAY, 3, 1, 0, 'confirmada', 240.00, 100.00, 140.00, 'Yape/Plin', 'manual', 'Llegada aproximada a las 15:00', 1),
(4, 'RES-2026-004', 4, CURDATE() - INTERVAL 10 DAY, CURDATE() - INTERVAL 7 DAY, 3, 2, 0, 'finalizada', 480.00, 480.00, 0.00, 'Tarjeta de Débito', 'manual', 'Estadía completada con éxito', 1),
(5, 'RES-2026-005', 5, CURDATE() - INTERVAL 3 DAY, CURDATE() - INTERVAL 1 DAY, 2, 1, 0, 'cancelada', 160.00, 0.00, 0.00, 'Efectivo', 'telefonica', 'Cancelado por imprevisto médico del cliente', 1)
ON DUPLICATE KEY UPDATE codigo_reserva=VALUES(codigo_reserva);

-- Detalle de Habitaciones en Reservas
INSERT INTO reserva_habitaciones (id, reserva_id, habitacion_id, precio_noche, noches, subtotal) VALUES
(1, 1, 3, 130.00, 3, 390.00),
(2, 2, 5, 160.00, 3, 480.00),
(3, 3, 1, 80.00, 3, 240.00),
(4, 4, 5, 160.00, 3, 480.00),
(5, 5, 1, 80.00, 2, 160.00)
ON DUPLICATE KEY UPDATE reserva_id=VALUES(reserva_id);

-- Estancias activas (Check-in actual)
INSERT INTO estancias (id, reserva_id, habitacion_id, huesped_id, fecha_checkin, fecha_checkout, observaciones, estado, usuario_id) VALUES
(1, 1, 3, 1, NOW() - INTERVAL 1 DAY, NULL, 'Check-in realizado en recepción por recepcionista de turno.', 'activa', 1),
(2, 2, 5, 3, NOW() - INTERVAL 4 HOUR, NULL, 'Check-in de pasajero extranjero con pasaporte verificado.', 'activa', 1),
(3, 4, 5, 4, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 7 DAY, 'Check-out completado sin novedades.', 'finalizada', 1)
ON DUPLICATE KEY UPDATE reserva_id=VALUES(reserva_id);

-- Pagos
INSERT INTO pagos (id, reserva_id, huesped_id, tipo, metodo_pago, monto, comprobante_tipo, comprobante_numero, fecha_pago, notas, usuario_id) VALUES
(1, 1, 1, 'anticipo', 'Tarjeta de Crédito', 150.00, 'Boleta', 'B001-000045', NOW() - INTERVAL 2 DAY, 'Anticipo al reservar', 1),
(2, 2, 3, 'pago', 'Transferencia Bancaria', 480.00, 'Factura', 'F001-000012', NOW(), 'Pago total por adelantado', 1),
(3, 3, 2, 'anticipo', 'Yape/Plin', 100.00, 'Recibo de Caja', 'RC-000089', NOW() - INTERVAL 1 DAY, 'Anticipo por confirmación de reserva', 1),
(4, 4, 4, 'pago', 'Tarjeta de Débito', 480.00, 'Boleta', 'B001-000030', NOW() - INTERVAL 10 DAY, 'Cancelación total de estadía', 1)
ON DUPLICATE KEY UPDATE comprobante_numero=VALUES(comprobante_numero);

-- Consumo de servicios
INSERT INTO reserva_servicios (id, reserva_id, servicio_id, cantidad, precio_unitario, subtotal, fecha_consumo, pagado, notas) VALUES
(1, 1, 1, 2, 25.00, 50.00, NOW() - INTERVAL 12 HOUR, 0, 'Desayuno Buffet día 1'),
(2, 2, 5, 1, 60.00, 60.00, NOW() - INTERVAL 5 HOUR, 1, 'Traslado Aeropuerto pagado')
ON DUPLICATE KEY UPDATE id=id;

-- Configuración del Hotel
INSERT INTO configuracion (clave, valor, grupo, descripcion) VALUES
('hotel_nombre', 'Hotel Manager & Resort Gran Sol', 'general', 'Nombre comercial del hotel'),
('hotel_ruc', '20491823901', 'general', 'Identificación tributaria / RUC / NIF'),
('hotel_telefono', '+51 1 450-8900 / +51 987 654 321', 'general', 'Teléfonos de contacto y reservas'),
('hotel_correo', 'reservas@hotelmanager.com', 'general', 'Correo institucional de contacto'),
('hotel_direccion', 'Av. Del Sol 1245, Zona Turística, Perú', 'general', 'Dirección física del hotel'),
('hotel_checkin_hora', '14:00', 'politicas', 'Hora oficial de Check-in'),
('hotel_checkout_hora', '12:00', 'politicas', 'Hora oficial de Check-out'),
('hotel_moneda_simbolo', 'S/.', 'facturacion', 'Símbolo monetario principal'),
('hotel_igv_porcentaje', '18', 'facturacion', 'Porcentaje de impuesto a las ventas'),
('hotel_politica_cancelacion', 'Cancelación gratuita hasta 48 horas antes de la llegada.', 'politicas', 'Términos de cancelación')
ON DUPLICATE KEY UPDATE valor=VALUES(valor);

-- Auditoría inicial
INSERT INTO auditoria (usuario_id, usuario_nombre, modulo, accion, detalle, ip) VALUES
(1, 'Administrador General', 'Sistema', 'Inicio', 'Inicialización y configuración completa de la base de datos de Hotel Manager', '127.0.0.1')
ON DUPLICATE KEY UPDATE id=id;
