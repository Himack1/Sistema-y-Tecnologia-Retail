# 📊 Sistema de Toma de Decisiones y Analítica para Retail (DSS / BI)

Plataforma ejecutiva de inteligencia comercial y soporte a la toma de decisiones (*Decision Support System - DSS*) para cadenas de retail y tiendas comerciales. Permite el monitoreo de indicadores clave de rendimiento (KPIs), análisis comparativo de ventas por sucursales y regiones, y métricas de rentabilidad por categoría de productos en tiempo real. Desarrollada con **PHP**, **Microsoft SQL Server** y **Chart.js**.

---

## 🚀 Funcionalidades Principales

### 📈 1. Tablero Ejecutivo de KPIs
- **Métricas Financieras:** Total de ingresos por ventas, margen de contribución, ticket promedio y cantidad de transacciones.
- **Comparativas:** Indicadores de crecimiento porcentual respecto a periodos anteriores.
- **Visualización Gráfica:** Gráficos de tendencias temporales, distribución de cuota de mercado por sucursal y mezcla de categorías con **Chart.js**.

### 🏢 2. Análisis Multidimensional por Sucursales y Regiones
- Comparativa de rendimiento entre distintas tiendas físicas a nivel de ciudad o región.
- Detección de sucursales con mejor desempeño y puntos de venta que requieren optimización comercial.

### 🏷️ 3. Desempeño por Categorías y Productos
- Ranking de productos más vendidos (*Top Sellers*) en unidades e ingresos.
- Clasificación de artículos por rentabilidad y rotación de inventario.

### ⚡ 4. API Analítica Asíncrona (`api_dashboard.php`)
- Endpoint que procesa consultas agregadas y métricas en SQL Server retornando datos en formato JSON.
- Actualización fluida de gráficos e indicadores en el frontend sin recargar la página mediante la API `fetch()`.

### 💾 5. Base de Datos Relacional y Datos de Prueba (`database_setup.sql`)
- Script completo en T-SQL con la definición de tablas normalizadas: `Sucursales`, `Categorias`, `Productos` y `Ventas`.
- Incluye datos semilla (*seed data*) con transacciones históricas realistas para demostración inmediata.

---

## 🛠️ Stack Tecnológico

- **Backend:** PHP 8.x (Consultas parametrizadas con driver nativo `sqlsrv`).
- **Base de Datos:** Microsoft SQL Server (T-SQL, agregaciones `SUM`, `COUNT`, `DATEPART`, índices y relaciones con integridad referencial).
- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Chart.js y Bootstrap.
- **Servidor Web:** Apache 2.4 (WampServer).

---

## 💻 Instalación y Configuración

1. **Clonar el repositorio:**
   ```bash
   cd C:\wamp64\www\
   git clone https://github.com/Himack1/Sistema-y-Tecnologia-Retail.git
   ```

2. **Crear y Poblar la Base de Datos:**
   - Abrir **SQL Server Management Studio (SSMS)**.
   - Abrir y ejecutar el archivo [`database_setup.sql`](database_setup.sql).
   - Esto creará la base de datos `Sistema_Retail`, sus 4 tablas y cargará los registros de prueba automáticamente.

3. **Verificar la Conexión:**
   - Revisar [`conexion.php`](conexion.php) y ajustar las credenciales de tu SQL Server si difieren de las estándar:
     ```php
     $connectionOptions = array(
         "Database"     => "Sistema_Retail",
         "UID"          => "sa",
         "PWD"          => "123456",
         "CharacterSet" => "UTF-8"
     );
     ```

4. **Acceder a la Aplicación:**
   - Iniciar WampServer y abrir en el navegador:
     ```text
     http://localhost/Sistema%20y%20Tecnologia%20Retail/login.php
     ```

---

## 👨‍💻 Autor

Desarrollado por **Himack1**  
GitHub: [@Himack1](https://github.com/Himack1)
