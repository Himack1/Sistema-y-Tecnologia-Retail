<?php
// Guard de sesión — redirige a login si no hay sesión activa
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
$usuario_activo = htmlspecialchars($_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Retail - Toma de Decisiones</title>
    <meta name="description" content="Sistema de análisis y toma de decisiones para cadena de Retail. Visualiza ventas, rentabilidad y tendencias por sucursal y categoría.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Hammer.js (requerido por chartjs-plugin-zoom) -->
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js"></script>
    <!-- chartjs-plugin-zoom -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
    <!-- chartjs-plugin-annotation -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.0.1/dist/chartjs-plugin-annotation.min.js"></script>

    <style>
        /* ============================================================
           DESIGN TOKENS
        ============================================================ */
        :root {
            --bg-base:       #0a0e1a;
            --bg-card:       #111827;
            --bg-card2:      #1a2235;
            --accent-1:      #6366f1;   /* indigo */
            --accent-2:      #22d3ee;   /* cyan   */
            --accent-3:      #f59e0b;   /* amber  */
            --accent-4:      #10b981;   /* emerald*/
            --text-primary:  #f1f5f9;
            --text-muted:    #94a3b8;
            --border:        #1e2d47;
            --danger:        #ef4444;
            --success:       #22c55e;
            --radius:        14px;
            --shadow:        0 4px 24px rgba(0,0,0,.45);
        }

        /* ============================================================
           RESET & BASE
        ============================================================ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ============================================================
           HEADER
        ============================================================ */
        .site-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #0f2132 100%);
            border-bottom: 1px solid var(--border);
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .header-brand .icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .header-brand h1 {
            font-size: 1.35rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--text-primary), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-brand span {
            font-size: .78rem;
            font-weight: 400;
            color: var(--text-muted);
            display: block;
            -webkit-text-fill-color: var(--text-muted);
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .8rem;
            color: var(--text-muted);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 8px var(--success);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .6; transform: scale(1.3); }
        }

        /* ============================================================
           LAYOUT
        ============================================================ */
        .main-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 0;
            min-height: calc(100vh - 85px);
        }

        /* ============================================================
           SIDEBAR (Filtros)
        ============================================================ */
        .sidebar {
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            padding: 28px 22px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .sidebar-title {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--accent-2);
            margin-bottom: -12px;
        }

        .filter-group label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 7px;
            letter-spacing: .04em;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: 9px;
            color: var(--text-primary);
            padding: 10px 13px;
            font-size: .85rem;
            font-family: 'Inter', sans-serif;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            appearance: none;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(99,102,241,.18);
        }

        .filter-group select option {
            background: #1a2235;
        }

        /* Date range row */
        .date-range {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* CTA Button */
        #btn-aplicar {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--accent-1), #818cf8);
            border: none;
            border-radius: 11px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: .92rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: .03em;
            transition: transform .15s, box-shadow .15s, opacity .15s;
            box-shadow: 0 4px 16px rgba(99,102,241,.4);
        }

        #btn-aplicar:hover   { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(99,102,241,.55); }
        #btn-aplicar:active  { transform: translateY(1px); }
        #btn-aplicar:disabled { opacity: .55; cursor: not-allowed; transform: none; }

        #btn-limpiar {
            width: 100%;
            padding: 10px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 11px;
            color: var(--text-muted);
            font-family: 'Inter', sans-serif;
            font-size: .82rem;
            cursor: pointer;
            transition: border-color .2s, color .2s;
            margin-top: -12px;
        }

        #btn-limpiar:hover { border-color: var(--accent-2); color: var(--accent-2); }

        .sidebar-divider {
            height: 1px;
            background: var(--border);
            border: none;
        }

        /* KPI mini panel in sidebar */
        .kpi-mini {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .kpi-mini-item {
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 13px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kpi-mini-icon {
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .kpi-mini-label {
            font-size: .68rem;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .kpi-mini-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-top: 1px;
        }

        /* ============================================================
           MAIN CONTENT
        ============================================================ */
        .main-content {
            padding: 28px 30px;
            display: flex;
            flex-direction: column;
            gap: 28px;
            overflow-x: hidden;
        }

        /* ============================================================
           LOADER
        ============================================================ */
        #loader {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 20px;
            color: var(--text-muted);
            font-size: .9rem;
        }

        .spinner {
            width: 28px;
            height: 28px;
            border: 3px solid var(--border);
            border-top-color: var(--accent-1);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ============================================================
           SECTION HEADERS
        ============================================================ */
        .section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .section-subtitle {
            font-size: .78rem;
            color: var(--text-muted);
            margin-top: 3px;
        }

        .section-badge {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            background: rgba(99,102,241,.15);
            color: var(--accent-1);
            border: 1px solid rgba(99,102,241,.3);
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* ============================================================
           CARDS
        ============================================================ */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            transition: border-color .25s;
        }

        .card:hover { border-color: rgba(99,102,241,.35); }

        /* Charts row */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .chart-wrapper {
            position: relative;
            height: 320px;
        }

        /* ============================================================
           HEATMAP TABLE
        ============================================================ */
        .heatmap-container {
            overflow-x: auto;
        }

        #tabla-heatmap {
            width: 100%;
            border-collapse: collapse;
            font-size: .85rem;
        }

        #tabla-heatmap th,
        #tabla-heatmap td {
            padding: 12px 18px;
            text-align: right;
            border-bottom: 1px solid var(--border);
        }

        #tabla-heatmap th:first-child,
        #tabla-heatmap td:first-child {
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
        }

        #tabla-heatmap thead th {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding-bottom: 14px;
            border-bottom: 2px solid var(--border);
        }

        #tabla-heatmap tbody tr:last-child td { border-bottom: none; }

        #tabla-heatmap tbody tr:hover td { background: rgba(99,102,241,.07); }

        .heatmap-cell {
            border-radius: 7px;
            padding: 8px 14px;
            font-weight: 700;
            font-size: .88rem;
            display: inline-block;
            min-width: 90px;
            text-align: right;
        }

        .heat-0 { background: rgba(16,185,129,.08);  color: #6ee7b7; }
        .heat-1 { background: rgba(16,185,129,.18);  color: #34d399; }
        .heat-2 { background: rgba(16,185,129,.32);  color: #10b981; }
        .heat-3 { background: rgba(99,102,241,.25);  color: #a5b4fc; }
        .heat-4 { background: rgba(245,158,11,.3);   color: #fcd34d; }

        .total-row td { background: rgba(30,45,71,.6); font-weight: 700; }
        .total-row .heatmap-cell { background: rgba(99,102,241,.2); color: #a5b4fc; }

        /* ============================================================
           ERROR / EMPTY STATE
        ============================================================ */
        .state-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 50px 20px;
            color: var(--text-muted);
            text-align: center;
            font-size: .9rem;
        }

        .state-empty .icon-lg { font-size: 2.5rem; }

        .alert-error {
            background: rgba(239,68,68,.12);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 10px;
            padding: 14px 18px;
            color: #fca5a5;
            font-size: .85rem;
            display: none;
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 1024px) {
            .main-layout { grid-template-columns: 1fr; }
            .sidebar { border-right: none; border-bottom: 1px solid var(--border); }
            .charts-row { grid-template-columns: 1fr; }
        }

        /* ============================================================
           PRINT BUTTON (screen only)
        ============================================================ */
        #btn-print {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 9px 16px;
            background: rgba(99,102,241,.15);
            border: 1px solid rgba(99,102,241,.4);
            border-radius: 10px;
            color: #c7d2fe;
            font-family: 'Inter', sans-serif;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: .02em;
            transition: background .2s, border-color .2s, transform .15s, box-shadow .2s;
            white-space: nowrap;
        }

        #btn-print:hover {
            background: rgba(99,102,241,.28);
            border-color: rgba(99,102,241,.75);
            color: #e0e7ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(99,102,241,.3);
        }

        #btn-print:active { transform: translateY(0); }

        /* Botón Exportar CSV */
        #btn-csv {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 9px 16px;
            background: rgba(16,185,129,.12);
            border: 1px solid rgba(16,185,129,.35);
            border-radius: 10px;
            color: #6ee7b7;
            font-family: 'Inter', sans-serif;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: .02em;
            transition: background .2s, border-color .2s, transform .15s, box-shadow .2s;
            white-space: nowrap;
        }

        #btn-csv:hover {
            background: rgba(16,185,129,.24);
            border-color: rgba(16,185,129,.7);
            color: #a7f3d0;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(16,185,129,.25);
        }

        #btn-csv:active { transform: translateY(0); }

        /* Botón Cerrar Sesión */
        #btn-logout {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 9px 16px;
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 10px;
            color: #fca5a5;
            font-family: 'Inter', sans-serif;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: .02em;
            text-decoration: none;
            transition: background .2s, border-color .2s, transform .15s;
            white-space: nowrap;
        }

        #btn-logout:hover {
            background: rgba(239,68,68,.22);
            border-color: rgba(239,68,68,.65);
            color: #fecaca;
            transform: translateY(-1px);
        }

        /* Badge de usuario activo */
        .header-user {
            font-size: .78rem;
            font-weight: 600;
            color: var(--text-muted);
            padding: 7px 12px;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border);
            border-radius: 9px;
            white-space: nowrap;
        }

        /* ============================================================
           @MEDIA PRINT — Reporte limpio apto para impresora
        ============================================================ */
        @media print {
            /* Ocultar elementos de UI que no van en el reporte */
            .sidebar,
            #btn-print,
            #btn-csv,
            #btn-logout,
            .header-user,
            #btn-aplicar,
            #btn-limpiar,
            #loader,
            #alert-error,
            .status-dot {
                display: none !important;
            }

            /* Reset general para impresión */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: #ffffff !important;
                color: #111827 !important;
                font-size: 11pt;
            }

            /* Header simplificado */
            .site-header {
                background: #1e1b4b !important;
                color: #ffffff !important;
                position: static;
                border-bottom: 2px solid #6366f1;
                padding: 12px 20px;
            }

            .header-brand h1 {
                -webkit-text-fill-color: #ffffff;
                color: #ffffff !important;
            }

            .header-brand span {
                -webkit-text-fill-color: #a5b4fc;
                color: #a5b4fc !important;
            }

            /* Layout: sidebar oculto, contenido al 100% */
            .main-layout {
                display: block !important;
                width: 100% !important;
            }

            .main-content {
                padding: 16px 20px !important;
                width: 100% !important;
                gap: 20px !important;
            }

            /* Cards: fondo blanco, sin sombras */
            .card {
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                break-inside: avoid;
                page-break-inside: avoid;
                margin-bottom: 16px;
            }

            /* Colores de texto */
            .section-title   { color: #1e293b !important; }
            .section-subtitle { color: #475569 !important; }
            .section-badge   { color: #6366f1 !important; border-color: #6366f1 !important; }
            .kpi-mini-label  { color: #475569 !important; }
            .kpi-mini-value  { color: #1e293b !important; }

            /* Gráficos: tamaño fijo para que no se corten */
            .charts-row {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 16px !important;
                page-break-inside: avoid;
            }

            .chart-wrapper {
                height: 260px !important;
                width: 100% !important;
            }

            canvas {
                max-width: 100% !important;
                height: 260px !important;
            }

            /* Tabla heatmap */
            #tabla-heatmap th,
            #tabla-heatmap td {
                color: #1e293b !important;
                border-color: #e2e8f0 !important;
            }

            #tabla-heatmap thead th {
                color: #475569 !important;
            }

            .heatmap-cell {
                border: 1px solid #e2e8f0 !important;
                color: #1e293b !important;
            }

            .heat-0 { background: #dcfce7 !important; }
            .heat-1 { background: #bbf7d0 !important; }
            .heat-2 { background: #86efac !important; }
            .heat-3 { background: #c7d2fe !important; }
            .heat-4 { background: #fde68a !important; }

            /* Justificaciones de negocio */
            div[style*='rgba(16,185,129'] {
                background: #dcfce7 !important;
                color: #065f46 !important;
            }
            div[style*='rgba(245,158,11'] {
                background: #fef3c7 !important;
                color: #92400e !important;
            }
            div[style*='rgba(99,102,241'] {
                background: #ede9fe !important;
                color: #4c1d95 !important;
            }

            /* Forzar salto de página antes del heatmap */
            .card:last-of-type {
                page-break-before: auto;
            }
        }
    </style>
</head>

<body>
<!-- ================================================================
     HEADER
================================================================ -->
<header class="site-header">
    <div class="header-brand">
        <div class="icon">📊</div>
        <div>
            <h1>RetailAnalytics</h1>
            <span>Sistema de Toma de Decisiones · Cadena Retail</span>
        </div>
    </div>
    <div class="header-status">
        <div class="status-dot"></div>
        <span id="last-update">Conectando...</span>
        <span class="header-user">👤 <?= $usuario_activo ?></span>
        <button id="btn-print" onclick="window.print()" title="Imprimir reporte en PDF o papel">
            🖨️ Imprimir
        </button>
        <button id="btn-csv" onclick="exportarCSV()" title="Exportar tabla a CSV">
            📥 Exportar CSV
        </button>
        <a href="logout.php" id="btn-logout" title="Cerrar sesión">
            🚪 Cerrar Sesión
        </a>
    </div>
</header>

<!-- ================================================================
     LAYOUT
================================================================ -->
<div class="main-layout">

    <!-- ============================================================
         SIDEBAR: Panel de Filtros
    ============================================================ -->
    <aside class="sidebar">
        <p class="sidebar-title">🔍 Filtros de Análisis</p>

        <!-- Rango de Fechas -->
        <div class="filter-group">
            <label for="fecha_inicio">Fecha Desde</label>
            <div class="date-range">
                <input type="date" id="fecha_inicio" name="fecha_inicio">
                <label for="fecha_fin" style="margin-top:4px">Fecha Hasta</label>
                <input type="date" id="fecha_fin" name="fecha_fin">
            </div>
        </div>

        <!-- Agrupación de Tiempo -->
        <div class="filter-group">
            <label for="agrupacion_tiempo">Agrupar Por</label>
            <select id="agrupacion_tiempo" name="agrupacion_tiempo">
                <option value="mensual" selected>Mensual</option>
                <option value="semanal">Semanal</option>
                <option value="diario">Diario</option>
            </select>
        </div>

        <hr class="sidebar-divider">

        <!-- Sucursal -->
        <div class="filter-group">
            <label for="id_sucursal">Sucursal</label>
            <select id="id_sucursal" name="id_sucursal">
                <option value="">— Todas las sucursales —</option>
            </select>
        </div>

        <!-- Categoría -->
        <div class="filter-group">
            <label for="id_categoria">Categoría</label>
            <select id="id_categoria" name="id_categoria">
                <option value="">— Todas las categorías —</option>
            </select>
        </div>

        <hr class="sidebar-divider">

        <!-- Botones -->
        <button id="btn-aplicar" onclick="cargarDashboard()">✦ Aplicar Filtros</button>
        <button id="btn-limpiar" onclick="limpiarFiltros()">↺ Limpiar filtros</button>

        <hr class="sidebar-divider">

        <!-- KPIs Mini -->
        <p class="sidebar-title">📈 Resumen</p>
        <div class="kpi-mini">
            <div class="kpi-mini-item">
                <span class="kpi-mini-icon">💰</span>
                <div>
                    <div class="kpi-mini-label">Ganancia Total</div>
                    <div class="kpi-mini-value" id="kpi-ganancia">—</div>
                    <span class="kpi-trend" id="trend-ganancia"></span>
                </div>
            </div>
            <div class="kpi-mini-item">
                <span class="kpi-mini-icon">🛒</span>
                <div>
                    <div class="kpi-mini-label">Ventas Totales</div>
                    <div class="kpi-mini-value" id="kpi-ventas">—</div>
                    <span class="kpi-trend" id="trend-ventas"></span>
                </div>
            </div>
            <div class="kpi-mini-item">
                <span class="kpi-mini-icon">📦</span>
                <div>
                    <div class="kpi-mini-label">Unidades Vendidas</div>
                    <div class="kpi-mini-value" id="kpi-unidades">—</div>
                    <span class="kpi-trend" id="trend-unidades"></span>
                </div>
            </div>
        </div>
    </aside>

    <!-- ============================================================
         MAIN: Gráficos y Tabla
    ============================================================ -->
    <main class="main-content">

        <!-- Loader -->
        <div id="loader" style="display:none; align-items:center; gap:14px; padding:20px; color:var(--text-muted)">
            <div class="spinner"></div>
            <span>Cargando datos...</span>
        </div>

        <!-- Error -->
        <div class="alert-error" id="alert-error">⚠️ <span id="error-msg"></span></div>

        <!-- --------------------------------------------------------
             FILA 1: Barras + Líneas
        -------------------------------------------------------- -->
        <div class="charts-row">

            <!-- DATASET A: Top 5 rentables -->
            <div class="card">
                <div class="section-header">
                    <div>
                        <div class="section-title">🏆 Top 5 Productos Rentables</div>
                        <div class="section-subtitle">Ganancia neta acumulada por producto</div>
                        <div style="margin-top:8px; font-size:.72rem; color: #6ee7b7; background: rgba(16,185,129,.1); border-radius:6px; padding:5px 10px; display:inline-block">
                            💡 <strong>Decisión:</strong> Dirigir presupuesto de marketing a estos productos
                        </div>
                    </div>
                    <span class="section-badge">Dataset A</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="chart-barras" aria-label="Gráfico de barras Top 5 productos rentables"></canvas>
                    <div id="empty-a" class="state-empty" style="display:none">
                        <span class="icon-lg">📦</span>
                        <span>Sin datos con los filtros actuales</span>
                    </div>
                </div>
            </div>

            <!-- DATASET B: Tendencia mensual/temporal -->
            <div class="card">
                <div class="section-header">
                    <div>
                        <div class="section-title">📈 Tendencia de Ventas en el Tiempo</div>
                        <div class="section-subtitle">Ingresos totales agrupados por período &mdash; <em>Arrastra para panear · Rueda para zoom</em></div>
                        <div style="margin-top:8px; font-size:.72rem; color: #fcd34d; background: rgba(245,158,11,.1); border-radius:6px; padding:5px 10px; display:inline-block">
                            💡 <strong>Decisión:</strong> Contratación de personal temporal por estacionalidad
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px">
                        <span class="section-badge" style="background:rgba(34,211,238,.1); color:var(--accent-2); border-color:rgba(34,211,238,.3)">Dataset B</span>
                        <button id="btn-reset-zoom"
                            onclick="if(chartLineas) chartLineas.resetZoom()"
                            title="Restablecer zoom original"
                            style="
                                font-size:.72rem; font-weight:600; font-family:'Inter',sans-serif;
                                padding:5px 11px; cursor:pointer;
                                background:rgba(34,211,238,.1); border:1px solid rgba(34,211,238,.3);
                                border-radius:8px; color:#67e8f9;
                                transition:background .2s, transform .15s;
                            "
                            onmouseover="this.style.background='rgba(34,211,238,.22)'"
                            onmouseout="this.style.background='rgba(34,211,238,.1)'">
                            🔍 Reset Zoom
                        </button>
                    </div>
                </div>
                <div class="chart-wrapper" style="cursor:grab">
                    <canvas id="chart-lineas" aria-label="Gráfico de líneas tendencia mensual de ventas"></canvas>
                    <div id="empty-b" class="state-empty" style="display:none">
                        <span class="icon-lg">📅</span>
                        <span>Sin datos con los filtros actuales</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- --------------------------------------------------------
             FILA 2: Tabla Heatmap
        -------------------------------------------------------- -->
        <div class="card">
            <div class="section-header">
                <div>
                    <div class="section-title">🗺️ Mapa de Calor: Ventas por Categoría × Sucursal</div>
                    <div class="section-subtitle">Tabla dinámica cruzada para análisis de rendimiento por pasillo</div>
                    <div style="margin-top:8px; font-size:.72rem; color: #c4b5fd; background: rgba(99,102,241,.12); border-radius:6px; padding:5px 10px; display:inline-block">
                        💡 <strong>Decisión:</strong> Eliminar o potenciar pasillos poco rentables en ciertas sucursales
                    </div>
                </div>
                <span class="section-badge" style="background:rgba(245,158,11,.1); color:var(--accent-3); border-color:rgba(245,158,11,.3)">Dataset C</span>
            </div>
            <div class="heatmap-container">
                <table id="tabla-heatmap" aria-label="Tabla heatmap de ventas por categoría y sucursal">
                    <thead id="thead-heatmap"></thead>
                    <tbody id="tbody-heatmap"></tbody>
                </table>
                <div id="empty-c" class="state-empty" style="display:none">
                    <span class="icon-lg">🗺️</span>
                    <span>Sin datos con los filtros actuales</span>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- ================================================================
     JAVASCRIPT
================================================================ -->
<script>
    // ============================================================
    // VARIABLES GLOBALES
    // ============================================================
    let chartBarras = null;
    let chartLineas = null;

    const API_URL = 'api_dashboard.php';

    // Paleta de colores
    const PALETA = {
        barras:   ['#6366f1','#22d3ee','#f59e0b','#10b981','#f87171'],
        barrasBg: ['rgba(99,102,241,.75)','rgba(34,211,238,.75)','rgba(245,158,11,.75)','rgba(16,185,129,.75)','rgba(248,113,113,.75)'],
        linea:    'rgba(34,211,238,1)',
        lineaBg:  'rgba(34,211,238,.12)',
    };

    // Formatear moneda
    const fmt = (n) => new Intl.NumberFormat('es-DO', {
        style: 'currency', currency: 'DOP', minimumFractionDigits: 0, maximumFractionDigits: 0
    }).format(n);

    const fmtNum = (n) => new Intl.NumberFormat('es-DO').format(n);

    // ============================================================
    // CARGAR CATÁLOGOS (sucursales y categorías para selects)
    // ============================================================
    async function cargarCatalogos() {
        try {
            const res  = await fetch(API_URL);
            const data = await res.json();

            if (data.catalogos) {
                poblarSelect('id_sucursal', data.catalogos.sucursales, 'id_sucursal', 'nombre');
                poblarSelect('id_categoria', data.catalogos.categorias, 'id_categoria', 'nombre');
            }
        } catch (e) {
            console.warn('No se pudieron cargar catálogos:', e);
        }
    }

    function poblarSelect(selectId, items, valKey, labelKey) {
        const sel = document.getElementById(selectId);
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value       = item[valKey];
            opt.textContent = item[labelKey];
            sel.appendChild(opt);
        });
    }

    // ============================================================
    // CARGAR DASHBOARD
    // ============================================================
    async function cargarDashboard() {
        const btn = document.getElementById('btn-aplicar');
        btn.disabled = true;

        mostrarLoader(true);
        ocultarError();

        // Construir URL con filtros
        const params = new URLSearchParams();
        const fi   = document.getElementById('fecha_inicio').value;
        const ff   = document.getElementById('fecha_fin').value;
        const suc  = document.getElementById('id_sucursal').value;
        const cat  = document.getElementById('id_categoria').value;
        const agr  = document.getElementById('agrupacion_tiempo').value;

        if (fi)  params.append('fecha_inicio', fi);
        if (ff)  params.append('fecha_fin', ff);
        if (suc) params.append('id_sucursal', suc);
        if (cat) params.append('id_categoria', cat);
        if (agr) params.append('agrupacion', agr);

        try {
            const res  = await fetch(`${API_URL}?${params.toString()}`);

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const data = await res.json();

            // Mostrar errores SQL como advertencia, pero NO bloquear el render
            if (data.sql_errors && data.sql_errors.length > 0) {
                const msgs = data.sql_errors.map(e => {
                    const codigo = e.errors?.[0]?.code || 'desconocido';
                    const msg    = e.errors?.[0]?.message || 'Error SQL';
                    return `[${codigo}] ${msg}`;
                }).join(' | ');
                mostrarError('⚠️ Error SQL (verifica que ejecutaste database_setup.sql): ' + msgs);
            }

            // Obtener arrays con fallback a []
            const da = Array.isArray(data.dataset_a?.data) ? data.dataset_a.data : [];
            const db = Array.isArray(data.dataset_b?.data) ? data.dataset_b.data : [];
            const dc = Array.isArray(data.dataset_c?.data) ? data.dataset_c.data : [];

            // Renderizar
            renderBarras(da);
            renderLineas(db);
            renderHeatmap(dc);
            actualizarKPIs(db, da, dc);

            // Timestamp
            document.getElementById('last-update').textContent =
                'Actualizado ' + new Date().toLocaleTimeString('es-DO');

        } catch (err) {
            mostrarError('Error al cargar los datos: ' + err.message);
            console.error(err);
        } finally {
            mostrarLoader(false);
            btn.disabled = false;
        }
    }

    // ============================================================
    // GRÁFICO A: Barras - Top 5 Rentables
    // ============================================================
    function renderBarras(data) {
        const canvas = document.getElementById('chart-barras');
        const emptyEl = document.getElementById('empty-a');

        // Destruir gráfico anterior
        if (chartBarras) { chartBarras.destroy(); chartBarras = null; }

        if (!data || data.length === 0) {
            canvas.style.display = 'none';
            emptyEl.style.display = 'flex';
            return;
        }

        canvas.style.display = 'block';
        emptyEl.style.display = 'none';

        const labels    = data.map(d => truncar(d.producto, 22));
        const ganancias = data.map(d => parseFloat(d.ganancia_total));

        chartBarras = new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Ganancia Neta (DOP)',
                    data: ganancias,
                    backgroundColor: PALETA.barrasBg,
                    borderColor:     PALETA.barras,
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 600, easing: 'easeInOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + fmt(ctx.raw)
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#94a3b8', font: { size: 11 } },
                        grid:  { color: 'rgba(30,45,71,.6)' }
                    },
                    y: {
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11 },
                            callback: v => fmt(v)
                        },
                        grid: { color: 'rgba(30,45,71,.6)' }
                    }
                }
            }
        });
    }

    // ============================================================
    // GRÁFICO B: Líneas - Tendencia Mensual
    // ============================================================
    function renderLineas(data) {
        const canvas  = document.getElementById('chart-lineas');
        const emptyEl = document.getElementById('empty-b');

        if (chartLineas) { chartLineas.destroy(); chartLineas = null; }

        if (!data || data.length === 0) {
            canvas.style.display = 'none';
            emptyEl.style.display = 'flex';
            return;
        }

        canvas.style.display = 'block';
        emptyEl.style.display = 'none';

        const labels    = data.map(d => d.periodo);
        const ventas    = data.map(d => parseFloat(d.venta_total));
        const ganancias = data.map(d => parseFloat(d.ganancia_total));

        // --------------------------------------------------------
        // ANOTACIONES: dividir período en Base vs Comparación
        // --------------------------------------------------------
        const mid       = Math.max(1, Math.floor(labels.length / 2));
        const labelBase = labels[0]       || '';
        const labelMid  = labels[mid]     || '';
        const labelEnd  = labels[labels.length - 1] || '';

        const anotaciones = {};

        if (labels.length >= 2) {
            // Zona 1 — Período Base (primera mitad)
            anotaciones.zonaBase = {
                type:        'box',
                xMin:        labelBase,
                xMax:        labels[mid - 1] || labelBase,
                yMin:        'min',
                yMax:        'max',
                backgroundColor: 'rgba(34,211,238,0.07)',
                borderColor:     'rgba(34,211,238,0.25)',
                borderWidth:     1,
                borderDash:      [4, 4],
                label: {
                    display:     true,
                    content:     '📊 Período Base',
                    position:    { x: 'start', y: 'start' },
                    color:       '#67e8f9',
                    font:        { size: 10, weight: '700', family: 'Inter' },
                    padding:     { top: 5, left: 7 },
                    backgroundColor: 'rgba(0,0,0,0)',
                },
            };

            // Zona 2 — Período de Comparación (segunda mitad)
            anotaciones.zonaComp = {
                type:        'box',
                xMin:        labelMid,
                xMax:        labelEnd,
                yMin:        'min',
                yMax:        'max',
                backgroundColor: 'rgba(245,158,11,0.07)',
                borderColor:     'rgba(245,158,11,0.25)',
                borderWidth:     1,
                borderDash:      [4, 4],
                label: {
                    display:     true,
                    content:     '📅 Período de Comparación',
                    position:    { x: 'start', y: 'start' },
                    color:       '#fcd34d',
                    font:        { size: 10, weight: '700', family: 'Inter' },
                    padding:     { top: 5, left: 7 },
                    backgroundColor: 'rgba(0,0,0,0)',
                },
            };

            // Línea divisoria en el punto medio
            anotaciones.lineaMid = {
                type:        'line',
                xMin:        labelMid,
                xMax:        labelMid,
                borderColor: 'rgba(255,255,255,0.2)',
                borderWidth:  1.5,
                borderDash:  [6, 4],
                label: {
                    display: false,
                },
            };
        }

        // --------------------------------------------------------
        // CONSTRUIR CHART
        // --------------------------------------------------------
        chartLineas = new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Ventas (DOP)',
                        data:  ventas,
                        borderColor:          PALETA.linea,
                        backgroundColor:      PALETA.lineaBg,
                        borderWidth:          2.5,
                        pointRadius:          4,
                        pointHoverRadius:     7,
                        pointBackgroundColor: PALETA.linea,
                        fill: true,
                        tension: 0.4,
                    },
                    {
                        label: 'Ganancia (DOP)',
                        data:  ganancias,
                        borderColor:          '#10b981',
                        backgroundColor:      'rgba(16,185,129,.08)',
                        borderWidth:          2,
                        pointRadius:          3,
                        pointHoverRadius:     6,
                        pointBackgroundColor: '#10b981',
                        fill:     true,
                        tension:  0.4,
                        borderDash: [4, 3],
                    }
                ]
            },
            options: {
                responsive:          true,
                maintainAspectRatio: false,
                animation:           { duration: 650, easing: 'easeInOutCubic' },
                interaction:         { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: {
                            color:           '#94a3b8',
                            font:            { size: 12 },
                            usePointStyle:   true,
                            pointStyleWidth: 10,
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.dataset.label + ': ' + fmt(ctx.raw)
                        }
                    },
                    // --- ZOOM & PAN ---
                    zoom: {
                        pan: {
                            enabled:   true,
                            mode:      'x',
                            threshold: 5,
                        },
                        zoom: {
                            wheel:  { enabled: true, speed: 0.08 },
                            pinch:  { enabled: true },
                            mode:   'x',
                            onZoomComplete: ({ chart }) => {
                                chart.update('none');
                            },
                        },
                        limits: {
                            x: { minRange: 2 },
                        },
                    },
                    // --- ANOTACIONES ---
                    annotation: {
                        clip:        false,
                        annotations: anotaciones,
                    },
                },
                scales: {
                    x: {
                        ticks: { color: '#94a3b8', font: { size: 10 }, maxRotation: 45 },
                        grid:  { color: 'rgba(30,45,71,.6)' }
                    },
                    y: {
                        ticks: {
                            color: '#94a3b8',
                            font:  { size: 11 },
                            callback: v => fmt(v)
                        },
                        grid: { color: 'rgba(30,45,71,.6)' }
                    }
                }
            }
        });
    }

    // ============================================================
    // TABLA HEATMAP (Dataset C)
    // ============================================================
    function renderHeatmap(data) {
        const thead   = document.getElementById('thead-heatmap');
        const tbody   = document.getElementById('tbody-heatmap');
        const table   = document.getElementById('tabla-heatmap');
        const emptyEl = document.getElementById('empty-c');

        thead.innerHTML = '';
        tbody.innerHTML = '';

        if (!data || data.length === 0) {
            table.style.display = 'none';
            emptyEl.style.display = 'flex';
            return;
        }

        table.style.display = 'table';
        emptyEl.style.display = 'none';

        // Extraer sucursales únicas y categorías únicas
        const sucursales = [...new Set(data.map(d => d.sucursal))].sort();
        const categorias = [...new Set(data.map(d => d.categoria))].sort();

        // Construir mapa rápido
        const mapa = {};
        data.forEach(d => {
            if (!mapa[d.categoria]) mapa[d.categoria] = {};
            mapa[d.categoria][d.sucursal] = parseFloat(d.venta_total);
        });

        // Calcular máximo para escala de color
        const valores = data.map(d => parseFloat(d.venta_total));
        const maxVal  = Math.max(...valores);

        // ---- THEAD ----
        const trHead = document.createElement('tr');
        const thCat  = document.createElement('th');
        thCat.textContent = 'Categoría / Sucursal';
        thCat.style.textAlign = 'left';
        trHead.appendChild(thCat);

        sucursales.forEach(suc => {
            const th = document.createElement('th');
            th.textContent = suc;
            trHead.appendChild(th);
        });

        const thTotal = document.createElement('th');
        thTotal.textContent = 'TOTAL';
        trHead.appendChild(thTotal);
        thead.appendChild(trHead);

        // ---- TBODY ----
        let totalGlobal = 0;
        const totalesSuc = {};
        sucursales.forEach(s => totalesSuc[s] = 0);

        categorias.forEach(cat => {
            const tr = document.createElement('tr');
            const tdCat = document.createElement('td');
            tdCat.textContent = cat;
            tr.appendChild(tdCat);

            let totalFila = 0;

            sucursales.forEach(suc => {
                const val = mapa[cat]?.[suc] ?? 0;
                totalFila         += val;
                totalesSuc[suc]   += val;
                totalGlobal       += val;

                const td = document.createElement('td');
                const pct = maxVal > 0 ? val / maxVal : 0;
                const nivel = pct < 0.2 ? 0 : pct < 0.4 ? 1 : pct < 0.6 ? 2 : pct < 0.8 ? 3 : 4;
                td.innerHTML = `<span class="heatmap-cell heat-${nivel}">${val > 0 ? fmt(val) : '—'}</span>`;
                tr.appendChild(td);
            });

            const tdTotal = document.createElement('td');
            tdTotal.innerHTML = `<span class="heatmap-cell heat-3">${fmt(totalFila)}</span>`;
            tr.appendChild(tdTotal);
            tbody.appendChild(tr);
        });

        // Fila de totales
        const trTotales = document.createElement('tr');
        trTotales.className = 'total-row';
        const tdLabel = document.createElement('td');
        tdLabel.textContent = 'TOTAL';
        trTotales.appendChild(tdLabel);

        sucursales.forEach(suc => {
            const td = document.createElement('td');
            td.innerHTML = `<span class="heatmap-cell">${fmt(totalesSuc[suc])}</span>`;
            trTotales.appendChild(td);
        });

        const tdGT = document.createElement('td');
        tdGT.innerHTML = `<span class="heatmap-cell">${fmt(totalGlobal)}</span>`;
        trTotales.appendChild(tdGT);
        tbody.appendChild(trTotales);
    }

    // ============================================================
    // KPIs DEL SIDEBAR (con flechas de tendencia)
    // ============================================================
    function actualizarKPIs(dataB, dataA, dataC) {
        // Totales globales
        const gananciaTotal = dataB.reduce((s, d) => s + parseFloat(d.ganancia_total || 0), 0);
        const ventaTotal    = dataB.reduce((s, d) => s + parseFloat(d.venta_total   || 0), 0);
        const unidades      = dataC.reduce((s, d) => s + parseFloat(d.unidades      || 0), 0);

        document.getElementById('kpi-ganancia').textContent = fmt(gananciaTotal);
        document.getElementById('kpi-ventas').textContent   = fmt(ventaTotal);
        document.getElementById('kpi-unidades').textContent = fmtNum(unidades);

        // Calcular tendencia comparando primera mitad vs segunda mitad del período
        if (dataB.length >= 2) {
            const mid       = Math.floor(dataB.length / 2);
            const primera   = dataB.slice(0, mid);
            const segunda   = dataB.slice(mid);

            const ganancia1 = primera.reduce((s, d) => s + parseFloat(d.ganancia_total || 0), 0);
            const ganancia2 = segunda.reduce((s, d) => s + parseFloat(d.ganancia_total || 0), 0);
            const ventas1   = primera.reduce((s, d) => s + parseFloat(d.venta_total   || 0), 0);
            const ventas2   = segunda.reduce((s, d) => s + parseFloat(d.venta_total   || 0), 0);
            const transac1  = primera.reduce((s, d) => s + parseFloat(d.num_transacciones || 0), 0);
            const transac2  = segunda.reduce((s, d) => s + parseFloat(d.num_transacciones || 0), 0);

            mostrarTendencia('trend-ganancia', ganancia1, ganancia2);
            mostrarTendencia('trend-ventas',   ventas1,   ventas2);
            mostrarTendencia('trend-unidades', transac1,  transac2);
        } else {
            ['trend-ganancia','trend-ventas','trend-unidades'].forEach(id => {
                document.getElementById(id).textContent = '';
            });
        }
    }

    function mostrarTendencia(elId, valorAnterior, valorActual) {
        const el = document.getElementById(elId);
        if (!el) return;

        if (valorAnterior === 0) {
            el.textContent = '';
            return;
        }

        const pct    = ((valorActual - valorAnterior) / valorAnterior) * 100;
        const abs    = Math.abs(pct).toFixed(1);
        const sube   = pct >= 0;

        el.textContent = sube ? `▲ +${abs}%` : `▼ -${abs}%`;
        el.style.color      = sube ? '#22c55e' : '#ef4444';
        el.style.fontSize   = '.72rem';
        el.style.fontWeight = '700';
        el.style.display    = 'block';
        el.style.marginTop  = '3px';
        el.title = sube
            ? `Creció ${abs}% vs. el período anterior`
            : `Cayó ${abs}% vs. el período anterior`;
    }

    // ============================================================
    // UTILIDADES
    // ============================================================
    function mostrarLoader(show) {
        document.getElementById('loader').style.display = show ? 'flex' : 'none';
    }

    function mostrarError(msg) {
        const el = document.getElementById('alert-error');
        document.getElementById('error-msg').textContent = msg;
        el.style.display = 'block';
    }

    function ocultarError() {
        document.getElementById('alert-error').style.display = 'none';
    }

    function limpiarFiltros() {
        document.getElementById('fecha_inicio').value  = '';
        document.getElementById('fecha_fin').value     = '';
        document.getElementById('id_sucursal').value   = '';
        document.getElementById('id_categoria').value  = '';
        cargarDashboard();
    }

    function truncar(str, max) {
        return str.length > max ? str.substring(0, max) + '…' : str;
    }

    // ============================================================
    // EXPORTAR CSV desde la tabla heatmap
    // ============================================================
    function exportarCSV() {
        const tabla = document.getElementById('tabla-heatmap');
        if (!tabla || tabla.style.display === 'none') {
            alert('No hay datos en la tabla para exportar. Aplica los filtros primero.');
            return;
        }

        const filas = [];

        // Encabezados
        const ths = tabla.querySelectorAll('thead th');
        filas.push([...ths].map(th => `"${th.textContent.trim()}"`).join(','));

        // Filas de datos
        tabla.querySelectorAll('tbody tr').forEach(tr => {
            const celdas = tr.querySelectorAll('td');
            const fila = [...celdas].map(td => {
                // Limpiar moneda: quitar RD$, espacios, separadores de miles
                const raw = td.textContent
                    .replace(/[RD$\s]/g, '')
                    .replace(/,/g, '')
                    .trim();
                // Si es número puro, no lo envuelvas en comillas
                const num = parseFloat(raw);
                if (!isNaN(num) && raw !== '' && raw !== '—') return num;
                return `"${td.textContent.trim()}"`;
            });
            filas.push(fila.join(','));
        });

        // BOM UTF-8 para que Excel abra acentos correctamente
        const bom = '\uFEFF';
        const csv = bom + filas.join('\r\n');

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;

        // Nombre con fecha y filtros aplicados
        const hoy  = new Date().toISOString().split('T')[0];
        const suc  = document.getElementById('id_sucursal').selectedOptions[0]?.text.replace(/[^\w]/g,'_') || 'todas';
        const cat  = document.getElementById('id_categoria').selectedOptions[0]?.text.replace(/[^\w]/g,'_') || 'todas';
        a.download = `reporte_ventas_${hoy}_${suc}_${cat}.csv`;

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // ============================================================
    // INICIALIZACIÓN
    // ============================================================
    document.addEventListener('DOMContentLoaded', async () => {
        await cargarCatalogos();
        await cargarDashboard();
    });
</script>

</body>
</html>
