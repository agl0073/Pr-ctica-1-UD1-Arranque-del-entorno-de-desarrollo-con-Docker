<?php
/**
 * =====================================================================
 *  login.php · Inicio de sesión contra MySQL · PLANTILLA DEL ALUMNO
 * =====================================================================
 *  PARTE 3 · CE 4.e.1 (autenticación) · CE 4.b (sesión) · CE 4.c (cookie)
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/funciones.php';
require_once __DIR__ . '/inc/sesion.php';
require_once __DIR__ . '/inc/conexion.php';

iniciarSesion();

if (aventureroActual() !== null) {
    header('Location: tablon.php');
    exit;
}

$error    = null;
$usuario  = $_COOKIE[COOKIE_USUARIO] ?? '';
$recordar = $usuario !== '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $clave    = $_POST['clave'] ?? '';
    $recordar = isset($_POST['recordar']);

    if ($usuario === '' || $clave === '') {
        $error = 'Escribe tu usuario y tu contraseña.';
    } else {
        try {
            $pdo = conectar();
            $aventurero = autenticar($pdo, $usuario, $clave);

            if ($aventurero !== null) {
                session_regenerate_id(true);
                $_SESSION['aventurero'] = $aventurero;
                $_SESSION['zurron']     = [];
                $_SESSION['entrada']    = date('H:i');

                if ($recordar) {
                    recordarUsuario($usuario);
                } else {
                    olvidarUsuario();
                }

                mensajeFlash('¡Bienvenido/a al gremio, ' . $aventurero['nombre'] . '!');
                header('Location: tablon.php');
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos.';
            }
        } catch (PDOException $ex) {
            error_log('Error PDO en autenticacion: ' . $ex->getMessage());
            $error = 'No se ha podido conectar con el gremio. Inténtalo más tarde.';
        }
    }
}

$motivo = match ($_GET['motivo'] ?? '') {
    'privado' => 'Esa sala es solo para miembros del gremio. Inicia sesión.',
    'salida'  => 'Has salido del gremio. Tu zurrón se ha vaciado.',
    default   => null,
};

$titulo = 'Entrar';
$aventurero = null;
require __DIR__ . '/inc/cabecera.php';
?>
<section class="tarjeta estrecha">
    <h1>Entrada al gremio</h1>
    <?php if ($motivo !== null): ?><p class="aviso"><?= e($motivo) ?></p><?php endif; ?>
    <?php if ($error !== null): ?><p class="error"><?= e($error) ?></p><?php endif; ?>

    <form method="post" action="login.php">
        <label>Usuario
            <input type="text" name="usuario" value="<?= e($usuario) ?>" maxlength="30" required autofocus>
        </label>
        <label>Contraseña
            <input type="password" name="clave" required>
        </label>
        <label class="casilla">
            <input type="checkbox" name="recordar" <?= $recordar ? 'checked' : '' ?>>
            Recordar mi usuario en este navegador
        </label>
        <button type="submit">Entrar</button>
    </form>
</section>
<?php require __DIR__ . '/inc/pie.php'; ?>
