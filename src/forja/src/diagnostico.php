<?php
/**
 * =====================================================================
 *  diagnostico.php · Panel de directivas del servidor          [CE 2.f]
 * =====================================================================
 *  Autor: Alejandro Segundo Ganoza Leiro · Variante: C · Pícara
 *
 *  Requisito R9 del enunciado:
 *   - strict_types
 *   - Leer con ini_get las 6 directivas de forja.ini y comprobar
 *     error_reporting() con E_ALL
 *   - Mostrar qué ficheros .ini adicionales ha cargado PHP
 *   - Cambiar date.timezone con ini_set, mostrar hora antes/después,
 *     valor devuelto y restaurarla con ini_restore
 *   - Intentar cambiar short_open_tag con ini_set y mostrar resultado
 */

declare(strict_types=1);

// --- Experimento 1: Modificación dinámica de date.timezone -----------
$horaAntes = date('H:i:s T');
$retornoTimezone = ini_set('date.timezone', 'America/New_York');
$horaDespues = date('H:i:s T');
ini_restore('date.timezone');
$horaRestaurada = date('H:i:s T');

// --- Experimento 2: Intento de modificación de short_open_tag --------
$valorShortAntes = ini_get('short_open_tag');
// Intentamos desactivarla en tiempo de ejecución:
$retornoShortTag = ini_set('short_open_tag', '0');
$valorShortDespues = ini_get('short_open_tag');

// --- Lectura de ficheros de configuración cargados -------------------
$ficheroPrincipal = php_ini_loaded_file();
$ficherosAdicionales = php_ini_scanned_files();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diagnóstico del servidor · Forja de Héroes</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<main>
    <h1>Diagnóstico del servidor</h1>
    <p class="subtitulo">PHP <?= PHP_VERSION ?> · Sistema operativo: <?= PHP_OS_FAMILY ?> (<?= PHP_OS ?>)</p>

    <h2>Ficheros de configuración cargados</h2>
    <div class="panel">
        <p><strong>Fichero principal (php.ini):</strong> <?= $ficheroPrincipal ?: 'Ninguno (configuración interna predeterminada)' ?></p>
        <p style="margin-top: 0.5rem;"><strong>Ficheros .ini adicionales escaneados (conf.d):</strong></p>
        <pre><?= $ficherosAdicionales ?: 'No se detectaron ficheros adicionales escaneados' ?></pre>
    </div>

    <h2>Directivas configuradas en php/forja.ini</h2>
    <table>
        <tr><th>Directiva</th><th>Valor (ini_get)</th><th>Comprobación y justificación</th></tr>
        <tr>
            <td><code>display_errors</code></td>
            <td><?= ini_get('display_errors') ?: '0' ?></td>
            <td><?= ini_get('display_errors') ? 'Activado (On): muestra errores y excepciones en el navegador durante el desarrollo' : 'Desactivado (Off)' ?></td>
        </tr>
        <tr>
            <td><code>error_reporting</code></td>
            <td><?= error_reporting() ?></td>
            <td><?= (error_reporting() === E_ALL) ? '✓ Coincide con E_ALL (' . E_ALL . '): máxima exigencia y reporte de incidencias' : 'Difiere de E_ALL' ?></td>
        </tr>
        <tr>
            <td><code>date.timezone</code></td>
            <td><?= ini_get('date.timezone') ?></td>
            <td>Zona horaria de referencia (Europe/Madrid) para sincronización horaria</td>
        </tr>
        <tr>
            <td><code>short_open_tag</code></td>
            <td><?= ini_get('short_open_tag') ?: '0' ?></td>
            <td>Habilita etiquetas cortas &lt;? para soporte de plantillas ágiles</td>
        </tr>
        <tr>
            <td><code>expose_php</code></td>
            <td><?= ini_get('expose_php') ?: '0 (Off)' ?></td>
            <td>Oculta la cabecera X-Powered-By como medida de seguridad (hardening)</td>
        </tr>
        <tr>
            <td><code>memory_limit</code></td>
            <td><?= ini_get('memory_limit') ?></td>
            <td>Límite holgado de 256M para evitar desbordamientos de memoria</td>
        </tr>
    </table>

    <h2>Cambio en tiempo de ejecución (ini_set)</h2>
    <table>
        <tr><th>Directiva y acción</th><th>Resultado del experimento</th></tr>
        <tr>
            <td>
                <strong>date.timezone</strong><br>
                Cambio temporal a <em>America/New_York</em>
            </td>
            <td>
                Hora antes (Madrid): <strong><?= $horaAntes ?></strong><br>
                Valor devuelto por ini_set(): <strong><?= var_export($retornoTimezone, true) ?></strong> (devuelve la zona anterior)<br>
                Hora después (New York): <strong><?= $horaDespues ?></strong><br>
                Tras ini_restore() (Madrid): <strong><?= $horaRestaurada ?></strong>
            </td>
        </tr>
        <tr>
            <td>
                <strong>short_open_tag</strong><br>
                Intento de cambio a <em>0 (Off)</em>
            </td>
            <td>
                Valor previo: <strong><?= var_export($valorShortAntes, true) ?></strong><br>
                Valor devuelto por ini_set(): <strong><?= var_export($retornoShortTag, true) ?></strong> (rechazado / false)<br>
                Valor posterior: <strong><?= var_export($valorShortDespues, true) ?></strong> (permanece inalterado al ser INI_PERDIR | INI_SYSTEM)
            </td>
        </tr>
    </table>

    <footer><a href="ficha.php">← Volver a la ficha</a></footer>
</main>
</body>
</html>
