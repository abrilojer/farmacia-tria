-- Base de Datos Farmacia Tria en 3ra Forma Normal (3FN)

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(255) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_correo (correo)
);

-- Tabla de Categorías de Productos
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
);

-- Tabla de Productos
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_producto VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL CHECK (precio >= 0),
    imagen_url VARCHAR(500),
    id_categoria INT NOT NULL,
    stock INT DEFAULT 0 CHECK (stock >= 0),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE RESTRICT,
    INDEX idx_categoria (id_categoria),
    INDEX idx_nombre (nombre_producto)
);

-- Tabla de Pedidos
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL CHECK (total >= 0),
    estado_pedido ENUM('pendiente', 'procesando', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_fecha (fecha_pedido)
);

-- Tabla de Detalle de Pedidos
CREATE TABLE detalle_pedidos (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    precio_unitario DECIMAL(10, 2) NOT NULL CHECK (precio_unitario >= 0),
    subtotal DECIMAL(10, 2) NOT NULL CHECK (subtotal >= 0),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT,
    INDEX idx_pedido (id_pedido),
    INDEX idx_producto (id_producto)
);

-- Tabla de Métodos de Pago
CREATE TABLE metodos_pago (
    id_metodo_pago INT AUTO_INCREMENT PRIMARY KEY,
    nombre_metodo VARCHAR(50) NOT NULL UNIQUE,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de Pagos
CREATE TABLE pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_metodo_pago INT NOT NULL,
    dni VARCHAR(8) NOT NULL,
    codigo_transaccion VARCHAR(255),
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_pago ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_metodo_pago) REFERENCES metodos_pago(id_metodo_pago) ON DELETE RESTRICT,
    INDEX idx_pedido (id_pedido)
);

-- ============================================
-- INSERCIÓN DE DATOS INICIALES
-- ============================================

-- Insertar categorías
INSERT INTO categorias (nombre_categoria, descripcion) VALUES
('Medicamentos', 'Medicamentos de venta libre y con receta'),
('Productos', 'Productos de cuidado personal e higiene'),
('Cosméticos', 'Productos de belleza y cosméticos');

-- Insertar métodos de pago
INSERT INTO metodos_pago (nombre_metodo) VALUES
('Mercado Pago'),
('Tarjeta');

-- Insertar productos de Medicamentos (Categoría 1)
INSERT INTO productos (nombre_producto, descripcion, precio, imagen_url, id_categoria, stock) VALUES
('Paracetamol', 'Para aliviar el dolor leve o moderado / Reducir la fiebre.', 5500.00, 'img/PACK PARACETAMOL BAYER.jpg', 1, 50),
('Tafirol', 'Ayuda a aliviar dolores / Fiebre/ Molestias causada por gripe', 3000.00, 'img/Tafirol-Forte-Caja.jpg', 1, 75),
('Alernix', 'Para alergias de piel / Aliviar síntomas como picazón de ojos, nariz y garganta, lagrimeo y estornudos.', 1500.00, 'img/alernix.jpeg', 1, 60),
('Geniol- 16 comp.', 'Analgesico antifebril / Para aliviar el dolor de cabeza, muscular, menstrual, articular y dental', 2500.00, 'img/geniol.jpg', 1, 40),
('Mylantra Frutilla- 24comp. masticables', 'Para aliviar síntomas como malestar y acidez estomacal, indigestión ácida /Sin azucar', 2300.00, 'img/Mylanta_frutilla-300-dpi.jpg', 1, 55),
('Ibuprofeno 20comp.', 'Para aliviar dolores/ Fiebre/ Inflamacion / Lesiones leves', 4000.00, 'img/ibu400.jpg', 1, 80),
('Bayaspirina-500mg', 'Contiene ácido acetilsalicílico / Para aliviar el dolor leve o moderado.', 2500.00, 'img/bayaspirina-x-30-comprimidos.jpg', 1, 45);

-- Insertar Productos de cuidado personal (Categoría 2)
INSERT INTO productos (nombre_producto, descripcion, precio, imagen_url, id_categoria, stock) VALUES
('Johnson\'s baby Aceite- 10ml', 'Puro/ humecta/ Protege piel y cabello', 5000.00, 'img/aceite de bebe.jpg', 2, 30),
('Shampoo Capilatis Ortiga- 410ml', 'Shampoo tratante/ Con extractos de ortiga/ Para la caida del cabello.', 8300.00, 'img/shampoo ortiga capilatis.jpeg', 2, 25),
('Acondicionador capilatis Ortiga - 410ml', 'Enjuague tratante / para la caida del cabello.', 8300.00, 'img/acondicionador capilatis.jpeg', 2, 25),
('Garneir Fructis Aloe Hidra Clean - 250ml', 'Crema para peinar / 72h de hidratacion / Sin parabenos', 2500.00, 'img/crema para peinar.png', 2, 35),
('Jabon en Barra Dove - 90gr', 'Beauty bar humetante / Nutricion 24h.', 1500.00, 'img/JabonDove.jpg', 2, 100),
('Veritas Jabon de glicerina - 120gr', 'Hipolargenico / Para pieles sensibles / Sin perfume.', 3200.00, 'img/VeritasJabon.jpeg', 2, 70);

-- Insertar Cosméticos (Categoría 3)
INSERT INTO productos (nombre_producto, descripcion, precio, imagen_url, id_categoria, stock) VALUES
('Labial Super Lustrous 802', 'fórmula super humectante/ Acabado cremoso/ promete cuidado: gracias a su vitamina E y aceite de palta.', 1300.00, 'img/revlon lavial.jpeg', 3, 40),
('Máscara de Pestañas Extreme One Shot', 'Alarga y da volumen levantando cada pestaña', 9240.00, 'img/pestanias.webp', 3, 20),
('Rubor Compacto Natura', 'textura suave/ Deja tu piel más viva y con aspecto saludable.', 12000.00, 'img/cosm1.jpg', 3, 15),
('Balsamo Labial Tododia', 'Hidratación profunda para labios / Textura cremosa', 5000.00, 'img/tododia balsamo.jpeg', 3, 50);

-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista de productos con categoría
CREATE VIEW vista_productos_catalogo AS
SELECT 
    p.id_producto,
    p.nombre_producto,
    p.descripcion,
    p.precio,
    p.imagen_url,
    p.stock,
    c.nombre_categoria
FROM productos p
INNER JOIN categorias c ON p.id_categoria = c.id_categoria
WHERE p.activo = TRUE
ORDER BY c.nombre_categoria, p.nombre_producto;

-- Vista de pedidos completos
CREATE VIEW vista_pedidos_completos AS
SELECT 
    ped.id_pedido,
    u.correo AS usuario_correo,
    ped.fecha_pedido,
    ped.total,
    ped.estado_pedido,
    pag.estado_pago,
    mp.nombre_metodo AS metodo_pago
FROM pedidos ped
INNER JOIN usuarios u ON ped.id_usuario = u.id_usuario
LEFT JOIN pagos pag ON ped.id_pedido = pag.id_pedido
LEFT JOIN metodos_pago mp ON pag.id_metodo_pago = mp.id_metodo_pago
ORDER BY ped.fecha_pedido DESC;

-- Vista de detalle de ventas
CREATE VIEW vista_detalle_ventas AS
SELECT 
    dp.id_detalle,
    ped.id_pedido,
    u.correo AS usuario_correo,
    prod.nombre_producto,
    cat.nombre_categoria,
    dp.cantidad,
    dp.precio_unitario,
    dp.subtotal,
    ped.fecha_pedido
FROM detalle_pedidos dp
INNER JOIN pedidos ped ON dp.id_pedido = ped.id_pedido
INNER JOIN usuarios u ON ped.id_usuario = u.id_usuario
INNER JOIN productos prod ON dp.id_producto = prod.id_producto
INNER JOIN categorias cat ON prod.id_categoria = cat.id_categoria
ORDER BY ped.fecha_pedido DESC;

CREATE TABLE IF NOT EXISTS codigos_recuperacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(255) NOT NULL,
    codigo VARCHAR(6) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiracion DATETIME NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    INDEX idx_correo (correo),
    INDEX idx_codigo (codigo)
);