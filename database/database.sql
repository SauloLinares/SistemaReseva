-- Script de base de datos para la plantilla del curso
-- Base de datos: sistema_reserva

CREATE DATABASE IF NOT EXISTS sistema_reserva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema_reserva;

-- Tabla de Clientes (Ejemplo de módulo CRUD)
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni VARCHAR(8) NOT NULL UNIQUE,
    correo VARCHAR(150),
    telefono VARCHAR(20),
    estado TINYINT(1) DEFAULT 1 COMMENT '1: Activo, 0: Inactivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Datos iniciales de prueba para que los alumnos visualicen información
INSERT INTO clientes (nombres, apellidos, dni, correo, telefono, estado) VALUES
('Carlos', 'Mendoza Quispe', '71829341', 'carlos.mendoza@email.com', '987654321', 1),
('Ana', 'Torres Rojas', '82910452', 'ana.torres@email.com', '912345678', 1),
('Luis', 'García Paredes', '45920138', 'luis.garcia@email.com', '976543210', 0),
('María', 'Fernández Soto', '60192837', 'maria.fernandez@email.com', '954321987', 1);