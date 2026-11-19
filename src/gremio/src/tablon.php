<?php
/**
 * =====================================================================
 *  tablon.php · Tablón de misiones · PLANTILLA DEL ALUMNO
 * =====================================================================
 *  PARTE 1 (UD3): recorrer el array de misiones y pintar cada tarjeta.
 *  PARTE 2 (UD4): filtro por GET y aceptación por POST.
 *  PARTE 3 (UD5): página solo para miembros; contador de visitas en sesión.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/funciones.php';
require_once __DIR__ . '/inc/sesion.php';

iniciarSesion();
$aventurero = requiereLogin();
$misiones   = require __DIR__ . '/inc/datos_misiones.php';
$_SESSION['zurron'] ??= [];

// TODO (Parte 3): suma 1 a $_SESSION['visitas'] en cada carga del tablón.
$_SESSION['visitas'] = ($_SESSION['visitas'] ?? 0) + 1;

// ---- POST: aceptar una misión --------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $mision = buscarMision($misiones, $id);

    if ($mision === null) {
        mensajeFlash('Esa misión no existe en el tablón.');
    } elseif (in_array($id, $_SESSION['zurron'], true)) {
        mensajeFlash('Ya tienes esa misión en tu zurrón.');
    } else {
        $_SESSION['zurron'][] = $id;
        mensajeFlash('¡Misión «' . $mision['titulo'] . '» aceptada!');
    }

    header('Location: tablon.php');
    exit;
}

// ---- GET: filtros ---------------------------------------------------------
$rango     = leerRangoFiltro($_GET);
$oroMinimo = leerOroMinimo($_GET);
$visibles  = filtrarMisiones($misiones, $rango, $oroMinimo);
$flash     = leerFlash();

$titulo = 'Tablón de misiones';
require __DIR__ . '/inc/cabecera.php';
?>
<h1>Tablón de misiones</h1>
<p class="nota">Entrada al gremio: <?= e($_SESSION['entrada'] ?? '—') ?> · Visitas a esta sala: <?= e((string)($_SESSION['visitas'] ?? 1)) ?></p>
<?php if ($flash !== null): ?><p class="aviso"><?= e($flash) ?></p><?php endif; ?>

<form method="get" action="tablon.php" class="filtros">
    <label>Rango
        <select name="rango">
            <option value="">Todos</option>
            <?php foreach (RANGOS as $r): ?>
                <option value="<?= $r ?>" <?= $r === $rango ? 'selected' : '' ?>><?= $r ?> · <?= etiquetaRango($r) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Recompensa mínima
        <input type="number" name="oro" min="0" max="10000" step="10" value="<?= $oroMinimo ?>">
    </label>
    <button type="submit">Filtrar</button>
</form>

<p><?= count($visibles) ?> de <?= count($misiones) ?> misiones.</p>

<div class="misiones">
<?php
if (empty($visibles)) {
    echo '<p>Ninguna misión cumple el filtro.</p>';
} else {
    foreach ($visibles as $mision) {
        $dias = $mision['dias'] === 1 ? '1 día' : $mision['dias'] . ' días';
        $enZurron = in_array($mision['id'], $_SESSION['zurron'], true);

        echo '<article class="mision ' . e(claseRango($mision['rango'])) . '">';
        echo   '<span class="rango">' . e($mision['rango']) . ' · ' . e(etiquetaRango($mision['rango'])) . '</span>';
        echo   '<h2>' . e($mision['titulo']) . '</h2>';
        echo   '<p class="nota">' . e($mision['zona']) . ' · ' . e($dias) . '</p>';
        echo   '<p>' . e($mision['descripcion']) . '</p>';
        echo   '<p class="oro">' . e(formatearOro($mision['recompensa'])) . '</p>';

        if ($enZurron) {
            echo '<p class="hecho">✔ En tu zurrón</p>';
        } else {
            echo '<form method="post" action="tablon.php">';
            echo   '<input type="hidden" name="id" value="' . e($mision['id']) . '">';
            echo   '<button type="submit">Aceptar misión</button>';
            echo '</form>';
        }

        echo '</article>';
    }
}
?>
</div>
<?php require __DIR__ . '/inc/pie.php'; ?>
