<?php
/**
 * Práctica 1 (UD1) — Arranque del entorno de desarrollo con Docker
 * Módulo: 0613 · Desarrollo Web en Entorno Servidor
 * Alumno: Alejandro Segundo Ganoza Leiro
 */

// Parámetros de conexión a la base de datos (con valores por defecto coincidentes con docker-compose.yml)
$dbHost = getenv('DB_HOST') ?: 'db';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'dwes_db';
$dbUser = getenv('DB_USER') ?: 'daw_user';
$dbPass = getenv('DB_PASSWORD') ?: 'daw_password';

$dbConnected = false;
$dbVersion = null;
$dbError = null;

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 3,
    ]);
    $dbConnected = true;
    $dbVersion = $pdo->query('SELECT VERSION()')->fetchColumn();
} catch (PDOException $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
}

$hasPdoMysql = extension_loaded('pdo_mysql');
$phpVersion = phpversion();
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Nginx';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Práctica 1 — Entorno de Desarrollo Docker</title>
    <style>
        :root {
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #38bdf8;
            --success-bg: rgba(34, 197, 94, 0.15);
            --success-border: #22c55e;
            --success-text: #4ade80;
            --danger-bg: rgba(239, 68, 68, 0.15);
            --danger-border: #ef4444;
            --danger-text: #f87171;
            --border-color: #334155;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            padding: 2.5rem 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            width: 100%;
            max-width: 960px;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .badge-daw {
            display: inline-block;
            background: #0284c7;
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            margin-bottom: 0.75rem;
        }
        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            margin-bottom: 0.5rem;
        }
        .header p {
            color: var(--text-muted);
            font-size: 1rem;
        }
        .author-box {
            display: inline-flex;
            gap: 1.5rem;
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: #cbd5e1;
            background: #1e293b;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
            border-color: var(--accent);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .card-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.15rem;
            font-weight: 600;
        }
        .status-pill {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .status-ok {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid var(--success-border);
        }
        .status-error {
            background: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid var(--danger-border);
        }
        .card-body {
            color: var(--text-muted);
            font-size: 0.925rem;
            line-height: 1.6;
        }
        .card-body strong {
            color: var(--text-main);
        }
        .meta-list {
            list-style: none;
            margin-top: 0.75rem;
        }
        .meta-list li {
            padding: 0.25rem 0;
            border-bottom: 1px dashed #334155;
            display: flex;
            justify-content: space-between;
        }
        .meta-list li:last-child {
            border-bottom: none;
        }
        .summary-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        .summary-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .code-pill {
            background: #0f172a;
            color: #38bdf8;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.85rem;
        }
        .error-message {
            margin-top: 0.75rem;
            padding: 0.75rem;
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
            border-radius: 6px;
            font-size: 0.85rem;
            word-break: break-all;
        }
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <span class="badge-daw">0613 · DWES · 2º DAW</span>
            <h1>Práctica 1 (UD1) — Entorno Docker</h1>
            <p>Arquitectura de desarrollo aislada con tres contenedores comunicados por red interna</p>
            <div class="author-box">
                <span><strong>Alumno:</strong> Alejandro Segundo Ganoza Leiro</span>
                <span><strong>Email:</strong> agl0073@alu.medac.es</span>
            </div>
        </header>

        <div class="grid">
            <!-- Contenedor 1: Web (Nginx) -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🌐</span>
                        <span>web (nginx)</span>
                    </div>
                    <span class="status-pill status-ok">● Activo</span>
                </div>
                <div class="card-body">
                    <p>Servidor web que recibe peticiones HTTP y las despacha vía FastCGI.</p>
                    <ul class="meta-list">
                        <li><span>Puerto host:</span> <span class="code-pill">8080</span></li>
                        <li><span>Puerto contenedor:</span> <span class="code-pill">80</span></li>
                        <li><span>Software:</span> <span><?= htmlspecialchars($serverSoftware) ?></span></li>
                        <li><span>Reenvío FastCGI:</span> <span class="code-pill">php:9000</span></li>
                    </ul>
                </div>
            </div>

            <!-- Contenedor 2: PHP (PHP-FPM) -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🐘</span>
                        <span>php (php-fpm)</span>
                    </div>
                    <span class="status-pill status-ok">● Activo</span>
                </div>
                <div class="card-body">
                    <p>Intérprete de PHP configurado con soporte para extensiones MySQL.</p>
                    <ul class="meta-list">
                        <li><span>Versión de PHP:</span> <span class="code-pill">v<?= htmlspecialchars($phpVersion) ?></span></li>
                        <li><span>Extensión PDO:</span> <span class="<?= extension_loaded('pdo') ? 'status-ok' : 'status-error' ?>"><?= extension_loaded('pdo') ? 'Instalada' : 'No detectada' ?></span></li>
                        <li><span>Driver pdo_mysql:</span> <span class="<?= $hasPdoMysql ? 'status-ok' : 'status-error' ?>"><?= $hasPdoMysql ? 'Instalado' : 'Falta extensión' ?></span></li>
                        <li><span>Directorio raíz:</span> <span class="code-pill">/var/www/html</span></li>
                    </ul>
                </div>
            </div>

            <!-- Contenedor 3: Base de Datos (MySQL) -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>🗄️</span>
                        <span>db (MySQL)</span>
                    </div>
                    <span class="status-pill <?= $dbConnected ? 'status-ok' : 'status-error' ?>">
                        ● <?= $dbConnected ? 'Conectado (PDO)' : 'Error de Conexión' ?>
                    </span>
                </div>
                <div class="card-body">
                    <p>Servidor MySQL para persistencia de datos relacionales.</p>
                    <ul class="meta-list">
                        <li><span>Host / Puerto:</span> <span class="code-pill"><?= htmlspecialchars($dbHost) ?>:<?= htmlspecialchars($dbPort) ?></span></li>
                        <li><span>Base de datos:</span> <span class="code-pill"><?= htmlspecialchars($dbName) ?></span></li>
                        <li><span>Usuario:</span> <span class="code-pill"><?= htmlspecialchars($dbUser) ?></span></li>
                        <li><span>Versión MySQL:</span> <span><?= $dbConnected ? htmlspecialchars($dbVersion) : 'No disponible' ?></span></li>
                    </ul>

                    <?php if ($dbConnected): ?>
                        <div style="margin-top: 0.75rem; color: var(--success-text); font-size: 0.85rem;">
                            ✓ Conexión PDO establecida satisfactoriamente desde PHP.
                        </div>
                    <?php else: ?>
                        <div class="error-message">
                            <strong>Fallo de conexión:</strong> <?= htmlspecialchars($dbError) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="summary-card">
            <h3>🚀 Estado global de la orquestación Docker</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
                <?php if ($dbConnected && $hasPdoMysql): ?>
                    <strong style="color: var(--success-text);">¡Entorno configurado correctamente!</strong> 
                    Los tres contenedores están comunicados entre sí a través de la red de Docker. Nginx recibe en el puerto 8080, PHP 8.3 procesa la solicitud mediante PHP-FPM y PDO conecta con el contenedor MySQL sin incidencias.
                <?php else: ?>
                    <strong style="color: var(--danger-text);">Atención:</strong> Uno o más componentes no han completado la vinculación. Revisa los logs de Docker Compose.
                <?php endif; ?>
            </p>
        </div>

        <footer class="footer">
            Entorno generado para la Práctica 1 — 0613 Desarrollo Web en Entorno Servidor
        </footer>
    </div>
</body>
</html>
