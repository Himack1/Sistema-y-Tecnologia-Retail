<?php
// Configuración de zona horaria (República Dominicana)
date_default_timezone_set('America/Santo_Domingo');

// Incluir configuración general si existe
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// Parámetros de conexión a SQL Server
$serverName = defined('DB_SERVER') ? DB_SERVER : "localhost,1433";  
$connectionOptions = array(
    "Database"     => defined('DB_NAME') ? DB_NAME : "Sistema_Retail",    
    "UID"          => defined('DB_USER') ? DB_USER : "sa",                
    "PWD"          => defined('DB_PASS') ? DB_PASS : "123456",      
    "CharacterSet" => "UTF-8"       
);

// Establecer conexión con SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("Error al conectar con la base de datos: " . print_r(sqlsrv_errors(), true));  
}

// Conexión establecida correctamente (sin echo para no contaminar respuestas JSON/API)

// Función helper para obtener fecha/hora actual (zona horaria dominicana)
function fecha_hora_actual($formato = 'd/m/Y H:i:s') {
    return date($formato);
}

// Verificación opcional de licencia
if (file_exists(__DIR__ . '/includes/licencia_helper.php')) {
    require_once __DIR__ . '/includes/licencia_helper.php';
    if (function_exists('verificarLicencia')) {
        verificarLicencia($conn);
    }
}
