CREATE DATABASE IF NOT EXISTS red_alimentos;
USE red_alimentos;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'admin') DEFAULT 'usuario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE alimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_caducidad DATE,
    imagen VARCHAR(255),
    ubicacion VARCHAR(200),
    usuario_id INT NOT NULL,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('disponible', 'intercambiado') DEFAULT 'disponible',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear usuario administrador por defecto
-- Usuario: admin, Contraseña: admin123
INSERT INTO usuarios (username, email, password, rol) VALUES 
('admin', 'admin@redalimentos.com', 'admin123', 'admin');

INSERT INTO usuarios (username, email, password, rol) VALUES 
('usuario1', 'usuario1@test.com', '123456', 'usuario'),
('usuario2', 'usuario2@test.com', '123456', 'usuario');

INSERT INTO alimentos (nombre, descripcion, fecha_caducidad, imagen, ubicacion, usuario_id) VALUES 
('Pan integral', 'Pan integral casero, muy fresco', '2025-08-25', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop', 'San José Centro', 2),
('Leche entera', 'Leche fresca de vaca, 1 litro', '2025-08-24', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400&h=300&fit=crop', 'Cartago', 3),
('Frutas mixtas', 'Manzanas y peras maduras', '2025-08-26', 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=400&h=300&fit=crop', 'Heredia', 2);