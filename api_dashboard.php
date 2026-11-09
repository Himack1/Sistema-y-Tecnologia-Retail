<?php
// ============================================================
//  api_dashboard.php - API REST para Dashboard de Retail
//  Devuelve JSON con 3 datasets para los gráficos
// ============================================================

// Cabeceras JSON y CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Incluir conexión
require_once __DIR__ . '/conexion.php';

// ============================================================
// FUNCIÓN HELPER: Ejecutar consulta y retornar array
// Siempre devuelve un array []; los errores van en $errores_sql
// ============================================================
$errores_sql = [];

function ejecutarConsulta($conn, $sql, $params = []) {
    global $errores_sql;

    // sqlsrv_query acepta params como array de valores simples
    $stmt = count($params) > 0
        ? sqlsrv_query($conn, $sql, $params)
        : sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        $errs = sqlsrv_errors();
        $errores_sql[] = [
            'sql'    => trim(preg_replace('/\s+/', ' ', $sql)),
            'errors' => $errs,
        ];
        return []; // siempre retorna array vacío para que JS no rompa
    }

    $resultado = [];
    while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Convertir objetos DateTime a string legible
        foreach ($fila as $key => $val) {
            if ($val instanceof DateTime) {
                $fila[$key] = $val->format('Y-m-d H:i:s');
            } elseif (is_float($val) || is_double($val)) {
                $fila[$key] = round($val, 2);
            }
        }
        $resultado[] = $fila;
    }

    sqlsrv_free_stmt($stmt);
    return $resultado;
}

// ============================================================
// RECOGER Y SANEAR FILTROS GET
// ============================================================
$fecha_inicio  = isset($_GET['fecha_inicio'])  && $_GET['fecha_inicio']  !== '' ? $_GET['fecha_inicio']  : null;
$fecha_fin     = isset($_GET['fecha_fin'])     && $_GET['fecha_fin']     !== '' ? $_GET['fecha_fin']     : null;
$id_sucursal   = isset($_GET['id_sucursal'])   && is_numeric($_GET['id_sucursal'])   ? (int)$_GET['id_sucursal']   : null;
$id_categoria  = isset($_GET['id_categoria'])  && is_numeric($_GET['id_categoria'])  ? (int)$_GET['id_categoria']  : null;
$agrupacion    = isset($_GET['agrupacion'])    && in_array($_GET['agrupacion'], ['diario', 'semanal', 'mensual']) ? $_GET['agrupacion'] : 'mensual';

// Validar formato de fechas
if ($fecha_inicio && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) $fecha_inicio = null;
if ($fecha_fin    && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin))    $fecha_fin    = null;

// ============================================================
// CONSTRUCCIÓN DINÁMICA DEL WHERE BASE
// ============================================================
$where_parts = [];
$params_base = [];

if ($fecha_inicio) {
    $where_parts[] = "v.fecha_venta >= ?";
    $params_base[] = $fecha_inicio;
}
if ($fecha_fin) {
    $where_parts[] = "v.fecha_venta <= ?";
    $params_base[] = $fecha_fin . ' 23:59:59';
}
if ($id_sucursal) {
    $where_parts[] = "v.id_sucursal = ?";
    $params_base[] = $id_sucursal;
}
if ($id_categoria) {
    $where_parts[] = "p.id_categoria = ?";
    $params_base[] = $id_categoria;
}

$where_sql = count($where_parts) > 0 ? 'WHERE ' . implode(' AND ', $where_parts) : '';

// ============================================================
// DATASET A: TOP 5 PRODUCTOS MÁS RENTABLES
// Ganancia = (precio_unitario - costo_unitario) * cantidad
// ============================================================
$sql_top5 = "
    SELECT TOP 5
        p.nombre                                                             AS producto,
        SUM((v.precio_unitario - v.costo_unitario) * v.cantidad)             AS ganancia_total,
        SUM(v.precio_unitario * v.cantidad)                                  AS venta_total,
        SUM(v.cantidad)                                                      AS unidades_vendidas
    FROM dbo.Ventas v
    INNER JOIN dbo.Productos  p ON v.id_producto  = p.id_producto
    INNER JOIN dbo.Sucursales s ON v.id_sucursal  = s.id_sucursal
    $where_sql
    GROUP BY p.nombre
    ORDER BY ganancia_total DESC
";

// ============================================================
// DATASET B: TENDENCIA MENSUAL/SEMANAL/DIARIA DE VENTAS
// ============================================================
$select_group_periodo = "FORMAT(v.fecha_venta, 'yyyy-MM')";
if ($agrupacion === 'diario') {
    $select_group_periodo = "FORMAT(v.fecha_venta, 'yyyy-MM-dd')";
} elseif ($agrupacion === 'semanal') {
    $select_group_periodo = "CONCAT(YEAR(v.fecha_venta), '-W', RIGHT('0' + CAST(DATEPART(iso_week, v.fecha_venta) AS VARCHAR(2)), 2))";
}

$sql_mensual = "
    SELECT
        $select_group_periodo                                      AS periodo,
        SUM(v.precio_unitario * v.cantidad)                        AS venta_total,
        SUM((v.precio_unitario - v.costo_unitario) * v.cantidad)   AS ganancia_total,
        COUNT(v.id_venta)                                          AS num_transacciones
    FROM dbo.Ventas v
    INNER JOIN dbo.Productos  p ON v.id_producto  = p.id_producto
    INNER JOIN dbo.Sucursales s ON v.id_sucursal  = s.id_sucursal
    $where_sql
    GROUP BY $select_group_periodo
    ORDER BY periodo ASC
";


// ============================================================
// DATASET C: VENTAS POR SUCURSAL × CATEGORÍA (Tabla Heatmap)
// ============================================================
$sql_heatmap = "
    SELECT
        c.nombre                                                   AS categoria,
        s.nombre                                                   AS sucursal,
        SUM(v.precio_unitario * v.cantidad)                        AS venta_total,
        SUM((v.precio_unitario - v.costo_unitario) * v.cantidad)   AS ganancia_total,
        SUM(v.cantidad)                                            AS unidades
    FROM dbo.Ventas v
    INNER JOIN dbo.Productos  p ON v.id_producto  = p.id_producto
    INNER JOIN dbo.Categorias c ON p.id_categoria = c.id_categoria
    INNER JOIN dbo.Sucursales s ON v.id_sucursal  = s.id_sucursal
    $where_sql
    GROUP BY c.nombre, s.nombre
    ORDER BY c.nombre, s.nombre
";

// ============================================================
// DATASET EXTRA: Listas para poblar los selects del frontend
// ============================================================
$sql_sucursales = "SELECT id_sucursal, nombre FROM dbo.Sucursales WHERE activo = 1 ORDER BY nombre";
$sql_categorias = "SELECT id_categoria, nombre FROM dbo.Categorias ORDER BY nombre";

// ============================================================
// EJECUTAR CONSULTAS
// ============================================================
$dataset_a    = ejecutarConsulta($conn, $sql_top5,      $params_base);
$dataset_b    = ejecutarConsulta($conn, $sql_mensual,   $params_base);
$dataset_c    = ejecutarConsulta($conn, $sql_heatmap,   $params_base);
$sucursales   = ejecutarConsulta($conn, $sql_sucursales);
$categorias   = ejecutarConsulta($conn, $sql_categorias);

// ============================================================
// RESPUESTA JSON FINAL
// ============================================================
$response = [
    'status'       => count($errores_sql) === 0 ? 'ok' : 'partial_error',
    'sql_errors'   => $errores_sql,   // vacío si todo fue bien
    'filtros'      => [
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin'    => $fecha_fin,
        'id_sucursal'  => $id_sucursal,
        'id_categoria' => $id_categoria,
    ],
    'dataset_a' => [
        'titulo'       => 'Top 5 Productos más Rentables',
        'descripcion'  => 'Ganancia neta por producto (precio - costo × cantidad)',
        'data'         => array_values($dataset_a),
    ],
    'dataset_b' => [
        'titulo'       => 'Tendencia de Ventas Mensuales',
        'descripcion'  => 'Ingresos totales agrupados por mes y año',
        'data'         => array_values($dataset_b),
    ],
    'dataset_c' => [
        'titulo'       => 'Ventas por Sucursal y Categoría',
        'descripcion'  => 'Tabla cruzada de ventas para análisis de pasillos',
        'data'         => array_values($dataset_c),
    ],
    'catalogos' => [
        'sucursales' => array_values($sucursales),
        'categorias' => array_values($categorias),
    ],
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
