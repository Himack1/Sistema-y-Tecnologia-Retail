-- ============================================================
--  SISTEMA DE TOMA DE DECISIONES - RETAIL
--  Script: Creación de Base de Datos y Datos de Prueba
--  Motor: SQL Server (T-SQL)
-- ============================================================

USE master;
GO

-- Crear base de datos si no existe
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = 'Sistema_Retail')
BEGIN
    CREATE DATABASE Sistema_Retail;
END
GO

USE Sistema_Retail;
GO

-- ============================================================
-- 1. ELIMINAR TABLAS SI EXISTEN (orden correcto por FK)
-- ============================================================
IF OBJECT_ID('dbo.Ventas', 'U')      IS NOT NULL DROP TABLE dbo.Ventas;
IF OBJECT_ID('dbo.Productos', 'U')   IS NOT NULL DROP TABLE dbo.Productos;
IF OBJECT_ID('dbo.Categorias', 'U')  IS NOT NULL DROP TABLE dbo.Categorias;
IF OBJECT_ID('dbo.Sucursales', 'U')  IS NOT NULL DROP TABLE dbo.Sucursales;
GO

-- ============================================================
-- 2. TABLA: Sucursales
-- ============================================================
CREATE TABLE dbo.Sucursales (
    id_sucursal   INT           IDENTITY(1,1) PRIMARY KEY,
    nombre        NVARCHAR(100) NOT NULL,
    ciudad        NVARCHAR(100) NOT NULL,
    region        NVARCHAR(50)  NOT NULL,
    activo        BIT           NOT NULL DEFAULT 1,
    fecha_apertura DATE         NOT NULL
);
GO

-- ============================================================
-- 3. TABLA: Categorias
-- ============================================================
CREATE TABLE dbo.Categorias (
    id_categoria  INT           IDENTITY(1,1) PRIMARY KEY,
    nombre        NVARCHAR(100) NOT NULL,
    descripcion   NVARCHAR(255) NULL
);
GO

-- ============================================================
-- 4. TABLA: Productos
-- ============================================================
CREATE TABLE dbo.Productos (
    id_producto     INT           IDENTITY(1,1) PRIMARY KEY,
    id_categoria    INT           NOT NULL,
    nombre          NVARCHAR(150) NOT NULL,
    descripcion     NVARCHAR(255) NULL,
    precio_base     DECIMAL(10,2) NOT NULL,
    costo_base      DECIMAL(10,2) NOT NULL,
    stock_inicial   INT           NOT NULL DEFAULT 0,
    activo          BIT           NOT NULL DEFAULT 1,
    CONSTRAINT FK_Productos_Categorias FOREIGN KEY (id_categoria) 
        REFERENCES dbo.Categorias (id_categoria)
);
GO

-- ============================================================
-- 5. TABLA: Ventas
-- ============================================================
CREATE TABLE dbo.Ventas (
    id_venta         INT           IDENTITY(1,1) PRIMARY KEY,
    id_producto      INT           NOT NULL,
    id_sucursal      INT           NOT NULL,
    fecha_venta      DATETIME      NOT NULL,
    cantidad         INT           NOT NULL,
    precio_unitario  DECIMAL(10,2) NOT NULL,
    costo_unitario   DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_Ventas_Productos  FOREIGN KEY (id_producto)  REFERENCES dbo.Productos (id_producto),
    CONSTRAINT FK_Ventas_Sucursales FOREIGN KEY (id_sucursal)  REFERENCES dbo.Sucursales (id_sucursal)
);
GO

-- ============================================================
-- 6. DATOS: Sucursales
-- ============================================================
INSERT INTO dbo.Sucursales (nombre, ciudad, region, activo, fecha_apertura) VALUES
    (N'Sucursal Norte', N'Santiago',      N'Norte', 1, '2018-03-15'),
    (N'Sucursal Sur',   N'San Cristóbal', N'Sur',   1, '2019-07-20'),
    (N'Sucursal Este',  N'La Romana',     N'Este',  1, '2020-01-10');
GO

-- ============================================================
-- 7. DATOS: Categorías
-- ============================================================
INSERT INTO dbo.Categorias (nombre, descripcion) VALUES
    (N'Electrónica', N'Dispositivos electrónicos, gadgets y accesorios tecnológicos'),
    (N'Alimentos',   N'Productos alimenticios, bebidas y artículos de despensa'),
    (N'Hogar',       N'Artículos para el hogar, decoración y utensilios');
GO

-- ============================================================
-- 8. DATOS: 10 Productos (distribuidos en 3 categorías)
-- ============================================================
INSERT INTO dbo.Productos (id_categoria, nombre, descripcion, precio_base, costo_base, stock_inicial) VALUES
    -- Electrónica (id_categoria = 1)
    (1, N'Smartphone Samsung Galaxy A54', N'Teléfono inteligente 128GB pantalla AMOLED',         22500.00, 14000.00, 200),
    (1, N'Auriculares Bluetooth JBL',     N'Auriculares inalámbricos con cancelación de ruido',   4500.00,  2100.00, 350),
    (1, N'Laptop HP 15s',                 N'Laptop Intel Core i5 8GB RAM 512GB SSD',              42000.00, 28000.00, 80),
    (1, N'Tablet Lenovo Tab M10',         N'Tablet 10 pulgadas WiFi 64GB',                        14500.00,  8500.00, 150),
    -- Alimentos (id_categoria = 2)
    (2, N'Aceite de Oliva Premium',       N'Aceite de oliva extra virgen 1 litro',                   850.00,   380.00, 500),
    (2, N'Café Molido Artesanal 500g',    N'Mezcla de granos premium tostado oscuro',                680.00,   290.00, 600),
    (2, N'Cereal Integral Quaker 1kg',    N'Cereal de avena con frutas deshidratadas',               420.00,   180.00, 800),
    -- Hogar (id_categoria = 3)
    (3, N'Juego de Sábanas Microfibra',   N'Set de 4 piezas 100% microfibra king size',             2800.00,  1200.00, 300),
    (3, N'Cafetera Eléctrica Oster',      N'Cafetera programable 12 tazas con filtro',              3600.00,  1900.00, 180),
    (3, N'Set de Cuchillos Acero 6pzs',   N'Set profesional de cocina acero inoxidable',             1950.00,   780.00, 250);
GO

-- ============================================================
-- 9. GENERACIÓN DE 1,200 VENTAS ALEATORIAS (últimos 2 años)
--    Usa patrones estacionales para datos realistas
-- ============================================================
DECLARE @i          INT = 0;
DECLARE @total      INT = 1200;

DECLARE @id_prod    INT;
DECLARE @id_suc     INT;
DECLARE @fecha      DATETIME;
DECLARE @cantidad   INT;
DECLARE @precio     DECIMAL(10,2);
DECLARE @costo      DECIMAL(10,2);
DECLARE @dias_atras INT;
DECLARE @mes        INT;
DECLARE @factor     DECIMAL(5,2);

-- Tabla temporal para precios/costos por producto
CREATE TABLE #precios (
    id_producto    INT,
    precio_base    DECIMAL(10,2),
    costo_base     DECIMAL(10,2)
);

INSERT INTO #precios
SELECT id_producto, precio_base, costo_base FROM dbo.Productos;

WHILE @i < @total
BEGIN
    -- Producto y sucursal aleatorios
    SET @id_prod = (ABS(CHECKSUM(NEWID())) % 10) + 1;
    SET @id_suc  = (ABS(CHECKSUM(NEWID())) % 3)  + 1;

    -- Fecha aleatoria dentro de los últimos 730 días
    SET @dias_atras = ABS(CHECKSUM(NEWID())) % 730;
    SET @fecha = DATEADD(DAY, -@dias_atras, GETDATE());

    -- Factor estacional (diciembre y enero venden más)
    SET @mes = MONTH(@fecha);
    SET @factor = CASE
        WHEN @mes IN (11, 12) THEN 1.6   -- temporada alta navideña
        WHEN @mes IN (1, 8)   THEN 1.3   -- regreso a clases / inicio año
        WHEN @mes IN (5, 6)   THEN 1.2   -- día de las madres / mitad año
        WHEN @mes IN (2, 3)   THEN 0.85  -- temporada baja
        ELSE 1.0
    END;

    -- Cantidad aleatoria entre 1 y 8
    SET @cantidad = (ABS(CHECKSUM(NEWID())) % 8) + 1;

    -- Precio y costo del producto (con pequeña variación aleatoria)
    SELECT
        @precio = ROUND(precio_base * @factor * (1 + (ABS(CHECKSUM(NEWID())) % 11 - 5) / 100.0), 2),
        @costo  = ROUND(costo_base  * (1 + (ABS(CHECKSUM(NEWID())) % 7 - 3) / 100.0), 2)
    FROM #precios WHERE id_producto = @id_prod;

    INSERT INTO dbo.Ventas (id_producto, id_sucursal, fecha_venta, cantidad, precio_unitario, costo_unitario)
    VALUES (@id_prod, @id_suc, @fecha, @cantidad, @precio, @costo);

    SET @i = @i + 1;
END;

DROP TABLE #precios;
GO

-- ============================================================
-- 10. VERIFICACIÓN FINAL
-- ============================================================
SELECT 'Sucursales' AS Tabla, COUNT(*) AS Registros FROM dbo.Sucursales
UNION ALL
SELECT 'Categorias',          COUNT(*) FROM dbo.Categorias
UNION ALL
SELECT 'Productos',           COUNT(*) FROM dbo.Productos
UNION ALL
SELECT 'Ventas',              COUNT(*) FROM dbo.Ventas;
GO

PRINT 'Base de datos Sistema_Retail creada y poblada exitosamente.';
GO
