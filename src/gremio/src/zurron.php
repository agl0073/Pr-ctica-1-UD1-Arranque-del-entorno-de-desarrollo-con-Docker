<?php
/**
 * =====================================================================
 *  zurron.php · Mi zurrón: misiones aceptadas · PLANTILLA DEL ALUMNO
 * =====================================================================
 *  PARTE 2 (listar y total) · PARTE 3 (los datos viven en $_SESSION)
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/funciones.php';
require_once __DIR__ . '/inc/sesion.php';

iniciarSesion();
$aventurero = requiereLogin();
$misiones   = require __DIR__ . '/inc/datos_misiones.php';
$_SESSION['zurron'] ??= [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $pos = array_search($id, $_SESSION['zurron'], true);
    if ($pos !== false) {
        unset($_SESSION['zurron'][$pos]);
        $_SESSION['zurron'] = array_values($_SESSION['zurron']);
        mensajeFlash('Misión abandonada.');
    }
    header('Location: zurron.php');
    exit;
}

$ids   = $_SESSION['zurron'];
$total = totalRecompensa($misiones, $ids);
$flash = leerFlash();

$titulo = 'Mi zurrón';
require __DIR__ . '/inc/cabecera.php';
?>
<h1>Mi zurrón</h1>
<?php if ($flash !== null): ?><p class="aviso"><?= e($flash) ?></p><?php endif; ?>

<?php
if (empty($ids)) {
    echo '<p>Tu zurrón está vacío. <a href="tablon.php">Vuelve al tablón</a> para aceptar misiones.</p>';
} else {
    echo '<table>';
    echo '<thead><tr><th>Misión</th><th>Rango</th><th>Recompensa</th><th></th></tr></thead>';
    echo '<tbody>';
    foreach ($ids as $id) {
        $mision = buscarMision($misiones, $id);
        if ($mision === null) continue;
        echo '<tr>';
        echo '<td>' . e($mision['titulo']) . '</td>';
        echo '<td>' . e($mision['rango']) . ' · ' . e(etiquetaRango($mision['rango'])) . '</td>';
        echo '<td>' . e(formatearOro($mision['recompensa'])) . '</td>';
        echo '<td>';
        echo '<form method="post" action="zurron.php">';
        echo '<input type="hidden" name="id" value="' . e($mision['id']) . '">';
        echo '<button type="submit" class="secundario">Abandonar</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '<tfoot><tr>';
    echo '<td colspan="2"><strong>Total</strong></td>';
    echo '<td><strong>' . e(formatearOro($total)) . '</strong></td>';
    echo '<td></td>';
    echo '</tr></tfoot>';
    echo '</table>';
}
?>
<p class="nota">El zurrón vive en la sesión: si sales del gremio, se vacía.</p>
<?php require __DIR__ . '/inc/pie.php'; ?>
