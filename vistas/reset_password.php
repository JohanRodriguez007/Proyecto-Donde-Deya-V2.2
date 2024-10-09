<?php
require_once "./inc/header.php";
require_once './modelo/Utils.php'; // Asegúrate de incluir la clase Utils

$errores = [];
$email = isset($_GET['email']) ? $_GET['email'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
$nueva_password = isset($_REQUEST['nueva_password']) ? $_REQUEST['nueva_password'] : '';
$password_restablecida = false; // Para controlar si la contraseña ha sido restablecida

try {
    // Obtener la conexión a la base de datos usando la clase Utils
    $miPDO = Utils::conexion();

    // Obtener la contraseña actual de la base de datos
    $miConsulta = $miPDO->prepare('SELECT usuario_clave FROM usuario WHERE usuario_email = :email;');
    $miConsulta->execute(['email' => $email]);
    $resultado = $miConsulta->fetch();

    $password_actual = $resultado ? $resultado['usuario_clave'] : '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validar nueva contraseña
        if (empty($nueva_password)) {
            $errores[] = 'El campo Nueva Contraseña es obligatorio.';
        }

        // Verificar que la nueva contraseña no sea la misma que la actual
        if (password_verify($nueva_password, $password_actual)) {
            $errores[] = 'La nueva contraseña no puede ser la misma que la actual.';
        }

        if (count($errores) === 0) {
            // Verificar el token y su expiración
            $miConsulta = $miPDO->prepare('SELECT usuario_id, usuario_token_expiration FROM usuario WHERE usuario_email = :email AND usuario_token = :token;');
            $miConsulta->execute(['email' => $email, 'token' => $token]);
            $resultado = $miConsulta->fetch();

            if ($resultado) {
                $tokenExpiration = strtotime($resultado['usuario_token_expiration']);
                $currentDateTime = time();

                // Verificar si el token ha expirado
                if ($currentDateTime > $tokenExpiration) {
                    $errores[] = 'El token ha expirado. Por favor solicita un nuevo enlace para restablecer tu contraseña.';
                } else {
                    // Actualizar la contraseña
                    $miActualizacion = $miPDO->prepare('UPDATE usuario SET usuario_clave = :password, usuario_token = NULL, usuario_token_expiration = NULL WHERE usuario_email = :email;');
                    $miActualizacion->execute([
                        'password' => password_hash($nueva_password, PASSWORD_BCRYPT),
                        'email' => $email
                    ]);
                    $password_restablecida = true; // Cambiar a verdadero si la contraseña se restableció con éxito
                    echo '<div class="notification is-success is-light">Tu contraseña ha sido restablecida con éxito.</div>';
                }
            } else {
                echo '<div class="notification is-danger is-light">Token o email inválido.</div>';
            }
        }
    }
} catch (PDOException $e) {
    // Manejo de errores de conexión
    echo '<div class="notification is-danger is-light">Error en la conexión a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<body>
    <div class="container mt-5">
        <?php if (!empty($errores)): ?>
        <div class="notification is-danger">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form action="" method="post" class="box" <?php echo $password_restablecida ? 'disabled' : ''; ?>>
            <h2 class="title is-4">Restablecer Contraseña</h2>

            <div class="field">
                <label class="label">Nueva Contraseña</label>
                <div class="control">
                    <input class="input" type="password" name="nueva_password" placeholder="Introduce una nueva contraseña" required <?php echo $password_restablecida ? 'disabled' : ''; ?>>
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <button class="button is-primary" type="submit" <?php echo $password_restablecida ? 'disabled' : ''; ?>>Restablecer Contraseña</button>
                </div>
            </div>
        </form>
    </div>
</body>

<?php require_once "./inc/footer_V2.php"; ?>




