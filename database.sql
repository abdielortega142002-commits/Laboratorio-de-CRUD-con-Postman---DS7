-- ============================================================
-- Laboratorio API REST + JWT - Script de base de datos
-- Ejecuta este script completo en phpMyAdmin o en la consola
-- de MySQL antes de correr el proyecto.
-- ============================================================

CREATE DATABASE IF NOT EXISTS lab_api_jwt
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE lab_api_jwt;

-- Tabla de usuarios (contraseñas SIEMPRE hasheadas, nunca en texto plano)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos (recurso protegido por el CRUD)
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL,
    producto VARCHAR(150) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Producto de ejemplo (opcional, puedes borrarlo)
INSERT INTO productos (codigo, producto, precio, cantidad)
VALUES ('A001', 'Mouse óptico', 10.50, 5);
