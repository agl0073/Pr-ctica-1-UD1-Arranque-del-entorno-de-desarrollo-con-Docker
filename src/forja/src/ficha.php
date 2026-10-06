<?php
/**
 * =====================================================================
 *  ficha.php · Forja de Héroes · UD2 · Reto evaluable
 * =====================================================================
 *  Autor: Alejandro Segundo Ganoza Leiro · Variante: C · Pícara
 *
 *  Estructura OBLIGATORIA (siguiendo el caso guiado ARES-7):
 *   1) Directiva strict_types
 *   2) Carga de inc/heroe.php mediante require_once y __DIR__
 *   3) CÁLCULOS: en este bloque superior, sin imprimir HTML
 *   4) PRESENTACIÓN: marcado HTML con valores incrustados
 *
 *  REGLAS: Prohibido if, else, switch, match, bucles, arrays propios
 *  y funciones propias. Toda la lógica se resuelve mediante operadores,
 *  operador ternario (? :) y operadores ?? y ?:.
 */

// 1. Directiva de tipos estrictos
declare(strict_types=1);

// 2. Carga del fichero de configuración y datos del héroe
require_once __DIR__ . '/inc/heroe.php';

// ---------------------------------------------------------------------
// 3. CÁLCULOS (R2 a R7)
// ---------------------------------------------------------------------

// --- R2 · Conversión explícita de tipos (casting) --------------------
$fuerza       = (int) $fuerzaTxt;
$destreza     = (int) $destrezaTxt;
$inteligencia = (int) $inteligenciaTxt;
$constitucion = (int) $constitucionTxt;
$experiencia  = (int) $experienciaTxt;
$vidaActual   = (int) $vidaActualTxt;
$oro          = (float) $oroTxt;

// --- R3 · Progresión del héroe ---------------------------------------
$nivel       = intdiv($experiencia, XP_POR_NIVEL) + 1;
$xpEnNivel   = $experiencia % XP_POR_NIVEL;
$xpParaSubir = XP_POR_NIVEL - $xpEnNivel;
$pctNivel    = ($xpEnNivel / XP_POR_NIVEL) * 100;

// --- R4 · Vida y combate ---------------------------------------------
$vidaMax = VIDA_BASE + ($constitucion * $nivel * MULT_VIDA);
$pctVida = ($vidaActual / $vidaMax) * 100;

// Fórmulas de la variante C (Pícara):
// Daño: destreza × 2 + nivel^1,5
$danio = ($destreza * 2) + ($nivel ** 1.5);

// Estadística especial: Turno de emboscada = (nivel × destreza) mód 7 + 1
$estadisticaEspecial = (($nivel * $destreza) % 7) + 1;
$etiquetaEspecial    = 'Turno de emboscada';
$valorEspecialTexto  = $estadisticaEspecial . '.º turno';

// Poder: daño × nivel, redondeado y convertido a entero
$poder = (int) round($danio * $nivel);

// Comparación espacial (<=>) frente al poder del rival
$comparacion = $poder <=> PODER_RIVAL;

// Veredicto táctico mediante operador ternario anidado con paréntesis
$veredicto = ($comparacion === 1)
    ? 'Ventaja: ¡a la carga!'
    : (($comparacion === 0)
        ? 'Empate: combate igualado'
        : 'Desventaja: mejor retirarse');

// --- R5 · Estado y decisiones tácticas -------------------------------
// Estado: si vidaActual > 0 -> (< 35 ? 'En peligro' : 'En pie') : 'K.O.'
$estado = ($vidaActual > 0)
    ? (($pctVida < 35) ? 'En peligro' : 'En pie')
    : 'K.O.';

$puedeAscender  = ($nivel >= 5 && $pctVida >= 50) ? 'Sí' : 'No';
$necesitaPocion = ($pctVida < 40 || !$esVeterano) ? 'Sí' : 'No';
$insignia       = $esVeterano ? 'VETERANO' : 'NOVATO';

// --- R6 · Barras de progreso con bloques Unicode ---------------------
$bloquesVidaLlenos = (int) round(($pctVida / 100) * BLOQUES_BARRA);
$bloquesVidaVacios = BLOQUES_BARRA - $bloquesVidaLlenos;
$barraVida = str_repeat('█', $bloquesVidaLlenos) . str_repeat('░', $bloquesVidaVacios);

$bloquesXpLlenos = (int) round(($pctNivel / 100) * BLOQUES_BARRA);
$bloquesXpVacios = BLOQUES_BARRA - $bloquesXpLlenos;
$barraXp = str_repeat('█', $bloquesXpLlenos) . str_repeat('░', $bloquesXpVacios);

// --- R7 · Textos y formateo de salida --------------------------------
$nombreSeguro       = htmlspecialchars($nombreHeroe, ENT_QUOTES, 'UTF-8');
$apodoSeguro        = $apodo ?? 'Sin apodo';
$lemaSeguro         = $lema ?: 'Sin lema (todavía)';
$oroFormateado      = number_format($oro, 2, ',', '.') . ' mo';
$danioFormateado    = number_format($danio, 1, ',', '.');
$pctVidaFormateado  = number_format($pctVida, 1, ',', '.');
$pctNivelFormateado = number_format($pctNivel, 1, ',', '.');

// Registro de combate construido con concatenación (.=) en al menos 3 pasos
$horaRegistro = date('H:i:s');
$registroCombate  = "[$horaRegistro] La combatiente $nombreSeguro (nivel $nivel) ";
$registroCombate .= "se dispone a batirse en duelo con " . NOMBRE_RIVAL . " (poder " . PODER_RIVAL . "). ";
$registroCombate .= "Resultado del análisis táctico: $veredicto.";

// Crónica de tres líneas mediante sintaxis heredoc interpolando variables
$claseHeroe = CLASE_HEROE;
$cronica = <<<CRONICA
La pícara $nombreSeguro se desliza entre sombras ejecutando tácticas de $claseHeroe (nivel $nivel).
Le faltan $xpParaSubir puntos de experiencia para consolidar su salto al siguiente nivel.
Permanece actualmente en estado "$estado", preparada para golpear con precisión en el combate.
CRONICA;

// Datos de pie de página
$fechaGeneracion = date('d/m/Y H:i:s');
$phpVersion      = PHP_VERSION;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ficha de <?= $nombreSeguro ?></title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<main>
    <!-- R1: Insignia VETERANO/NOVATO con la forma larga <?php echo ...; ?> -->
    <span class="insignia"><?php echo $insignia; ?></span>

    <!-- Salidas restantes con la forma corta <?= ... ?> -->
    <h1><?= $nombreSeguro ?></h1>
    <p class="subtitulo"><?= CLASE_HEROE ?> · «<?= $apodoSeguro ?>» · <?= $lemaSeguro ?></p>

    <h2>Estadísticas</h2>
    <div class="rejilla">
        <div class="stat"><div class="etq">Nivel</div><div class="val"><?= $nivel ?></div></div>
        <div class="stat"><div class="etq">Vida</div><div class="val"><?= $vidaActual ?> / <?= $vidaMax ?></div></div>
        <div class="stat"><div class="etq">Daño</div><div class="val"><?= $danioFormateado ?></div></div>
        <div class="stat"><div class="etq"><?= $etiquetaEspecial ?></div><div class="val"><?= $valorEspecialTexto ?></div></div>
        <div class="stat"><div class="etq">Poder</div><div class="val"><?= $poder ?></div></div>
        <div class="stat"><div class="etq">Oro</div><div class="val"><?= $oroFormateado ?></div></div>
    </div>
    <table>
        <tr><th>Fuerza</th><th>Destreza</th><th>Inteligencia</th><th>Constitución</th></tr>
        <tr><td><?= $fuerza ?></td><td><?= $destreza ?></td><td><?= $inteligencia ?></td><td><?= $constitucion ?></td></tr>
    </table>

    <h2>Progreso</h2>
    <p class="barra vida">VIDA <?= $barraVida ?> · <?= $pctVidaFormateado ?> %</p>
    <p class="barra xp">XP&nbsp;&nbsp; <?= $barraXp ?> · <?= $xpEnNivel ?>/<?= XP_POR_NIVEL ?> (faltan <?= $xpParaSubir ?>)</p>

    <h2>Estado y decisiones</h2>
    <table>
        <tr><td>Estado</td><td><?= $estado ?></td></tr>
        <tr><td>¿Puede ascender de rango? (nivel ≥ 5 y vida ≥ 50 %)</td><td><?= $puedeAscender ?></td></tr>
        <tr><td>¿Necesita poción? (vida &lt; 40 % o no veterano)</td><td><?= $necesitaPocion ?></td></tr>
        <tr><td>Rival: <?= NOMBRE_RIVAL ?> (poder <?= PODER_RIVAL ?>)</td><td><?= $comparacion ?> → <?= $veredicto ?></td></tr>
    </table>

    <h2>Crónica</h2>
    <pre><?= $cronica ?></pre>
    <p><em><?= $registroCombate ?></em></p>

    <h2>Modo depuración</h2>
    <pre>Tipo antes ($experienciaTxt): <?= get_debug_type($experienciaTxt) . "\n" ?>Tipo después ($experiencia): <?= get_debug_type($experiencia) . "\n\n" ?>var_dump($oro): <?php var_dump($oro); ?>
var_dump($pctVida): <?php var_dump($pctVida); ?>
var_dump($esVeterano): <?php var_dump($esVeterano); ?>
var_dump($apodo): <?php var_dump($apodo); ?>

Comparación débil ($experienciaTxt == $experiencia): <?php var_dump($experienciaTxt == $experiencia); ?>
Comparación estricta ($experienciaTxt === $experiencia): <?php var_dump($experienciaTxt === $experiencia); ?></pre>

    <footer>Generado el <?= $fechaGeneracion ?> · PHP <?= $phpVersion ?> ·
        <a href="diagnostico.php">Diagnóstico del servidor</a></footer>
</main>
</body>
</html>
