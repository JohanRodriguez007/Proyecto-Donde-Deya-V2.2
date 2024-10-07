<?php
require_once "./inc/header.php";

// Incluir el archivo Utils para la conexión a la base de datos
require_once './modelo/Utils.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
require './PHPMailer/src/Exception.php';

$errores = [];
$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
$correo_enviado = false; // Variable para controlar si el correo se ha enviado

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar email
    if (empty($email)) {
        $errores[] = 'El campo Email es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El formato del Email no es válido.';
    }

    if (count($errores) === 0) {
        // Conexión a la base de datos usando Utils
        $conn = Utils::conexion();
        
        // Comprobar si el email existe en la base de datos
        $miConsulta = $conn->prepare('SELECT COUNT(*) as length FROM usuario WHERE usuario_email = :email;');
        $miConsulta->execute(['email' => $email]);
        $resultado = $miConsulta->fetch();

        if ((int)$resultado['length'] === 0) {
            $errores[] = 'El email no está registrado.';
        } else {
            // Generar token y fecha de expiración
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Actualizar el token y la fecha de expiración en la base de datos
            $miActualizacion = $conn->prepare('UPDATE usuario SET usuario_token = :token, usuario_token_expiration = :expiration WHERE usuario_email = :email;');
            $miActualizacion->execute([
                'token' => $token,
                'expiration' => $expiration,
                'email' => $email
            ]);

            // Configuración de PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug = 0; // Desactivar la depuración
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'depruebac310@gmail.com'; // Cambia esto a tu correo
                $mail->Password = 'vjyeitfrmywcqgoz'; // Cambia esto a tu contraseña
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('depruebac310@gmail.com', 'Cigarreria Donde Deya');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Recupera tu contraseña';
                $tokenEncode = urlencode($token);
                $mail->Body = "
                Hola,<br>
                Para restablecer tu contraseña, por favor haz clic en el siguiente enlace:<br>
                <a href='http://localhost/Proyecto-Donde-Deya-V2.2-main/index.php?vista=reset_password&email=$email&token=$tokenEncode'>Restablecer contraseña</a>";

                $mail->send();
                $correo_enviado = true; // Cambiar la variable a true si el correo se envía correctamente
            } catch (Exception $e) {
                $errores[] = 'Mensaje: ' . $mail->ErrorInfo;
            }
        }
    }
}
?>

<body>
    <div class="container mt-5">
        <?php if ($correo_enviado): ?>
            <div class="notification is-success is-light">Correo de recuperación enviado. Por favor, revisa tu bandeja de entrada.</div>
        <?php else: ?>
            <?php if (!empty($errores)): ?>
            <div class="notification is-danger">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form action="" method="post" class="box">
                <h2 class="title is-4">Recuperación de Contraseña</h2>

                <div class="field">
                    <label class="label">E-mail</label>
                    <div class="control">
                        <input class="input" type="email" name="email" placeholder="Introduce tu correo electrónico" required>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <button class="button is-primary" type="submit">Enviar enlace de recuperación</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

<?php require_once "./inc/footer.php"; ?>



