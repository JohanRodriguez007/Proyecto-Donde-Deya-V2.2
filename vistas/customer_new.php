<?php 
require_once "./inc/header.php"; 
require_once './modelo/Utils.php'; // Aseguramos que se incluya la clase Utils

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
require './PHPMailer/src/Exception.php';

//-----------------------------------------------------
// Funciones Para Validar
//-----------------------------------------------------
function validar_requerido(string $texto): bool
{
    return !(trim($texto) == '');
}

function validar_email(string $texto): bool
{
    return filter_var($texto, FILTER_VALIDATE_EMAIL);
}

function validar_primera_mayuscula(string $texto): bool
{
    return ctype_upper(substr($texto, 0, 1));
}

//-----------------------------------------------------
// Variables
//-----------------------------------------------------
$errores = [];
$nombre = isset($_REQUEST['nombre']) ? $_REQUEST['nombre'] : '';
$apellido = isset($_REQUEST['apellido']) ? $_REQUEST['apellido'] : '';
$usuario = isset($_REQUEST['usuario']) ? $_REQUEST['usuario'] : '';
$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';

// Comprobamos si nos llegan los datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //-----------------------------------------------------
    // Validaciones
    //-----------------------------------------------------
    // Email
    if (!validar_requerido($email)) {
        $errores[] = 'Campo Email obligatorio.';
    }

    if (!validar_email($email)) {
        $errores[] = 'El campo Email no tiene un formato válido.';
    }

    // Nombre
    if (!validar_requerido($nombre)) {
        $errores[] = 'El campo Nombre es Obligatorio.';
    }

    // Apellido
    if (!validar_requerido($apellido)) {
        $errores[] = 'El campo Apellido es Obligatorio.';
    }

    // Usuario
    if (!validar_requerido($usuario)) {
        $errores[] = 'El campo Usuario es Obligatorio.';
    } elseif (!validar_primera_mayuscula($usuario)) {
        $errores[] = 'El nombre de usuario debe comenzar con una letra mayúscula.';
    }

    // Contraseña
    if (!validar_requerido($password)) {
        $errores[] = 'El campo Contraseña es Obligatorio.';
    }

    /* Verificar que no existe en la base de datos el mismo email */
    // Conecta con base de datos usando la clase Utils
    $conn = Utils::conexion(); // Usar la función de conexión

    // Cuenta cuantos emails existen
    $miConsulta = $conn->prepare('SELECT COUNT(*) as length FROM usuario WHERE usuario_email = :email;');
    // Ejecuta la búsqueda
    $miConsulta->execute(['email' => $email]);
    // Recoge los resultados
    $resultado = $miConsulta->fetch();

    // Comprueba si existe
    if ((int) $resultado['length'] > 0) {
        $errores[] = 'La dirección de Email ya está registrada';
    }

    // Validar que el usuario no existe (sin sensibilidad a mayúsculas)
    $miConsultaUsuario = $conn->prepare('SELECT COUNT(*) as length FROM usuario WHERE BINARY usuario_usuario = :usuario;');
    $miConsultaUsuario->execute(['usuario' => $usuario]);
    $resultadoUsuario = $miConsultaUsuario->fetch();
    
    if ((int) $resultadoUsuario['length'] > 0) {
        $errores[] = 'El nombre de usuario ya está en uso.';
    }

    //-----------------------------------------------------
    // Crear cuenta
    //-----------------------------------------------------
    if (count($errores) === 0) {
        /* Registro En La Base De Datos */

        // Prepara INSERT
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $miNuevoRegistro = $conn->prepare('INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_usuario, usuario_email, usuario_clave, usuario_rol, usuario_activo, usuario_token) VALUES (:nombre, :apellido, :usuario, :email, :password, :rol, :activo, :token);');
        // Ejecuta el nuevo registro en la base de datos
        $miNuevoRegistro->execute([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'usuario' => $usuario,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'rol' => 0,
            'activo' => 0,
            'token' => $token
        ]);

        /* Envío De Email Con Token */

        // Configuración de PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Nivel de depuración
            $mail->isSMTP(); // Usar SMTP
            $mail->Host = 'smtp.gmail.com'; // Servidor SMTP
            $mail->SMTPAuth = true; // Habilitar autenticación SMTP
            $mail->Username = 'depruebac310@gmail.com'; // Usuario SMTP
            $mail->Password = 'vjyeitfrmywcqgoz'; // Contraseña SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar cifrado TLS
            $mail->Port = 587; // Puerto TCP para TLS

            // Remitente y destinatarios
            $mail->setFrom('depruebac310@gmail.com', 'Cigarreria Donde Deya');
            $mail->addAddress($email);
            
            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Active su Usuario';
            $emailEncode = urlencode($email);
            $tokenEncode = urlencode($token);
            $mail->Body = "
            Hola!<br>
            Gracias por registrarte.<br>
            Para activar tu cuenta, por favor haz clic en el siguiente enlace:<br>
            <a href='http://localhost/Proyecto-Donde-Deya-V2.2-main/index.php?vista=activate_customer&email=$emailEncode&token=$tokenEncode'>Activar cuenta</a>";

            $mail->send();
            echo 'Correo enviado';
        } catch (Exception $e) {
            echo 'Mensaje: ' . $mail->ErrorInfo;
        }

        /* Redirección a login.php con GET para informar del envío del email */
        header('Location: index.php?vista=login');
        die();
    }
}
?>
<body>
    <div class="container mt-5">
        <!-- Mostramos errores por HTML -->
        <?php if (!empty($errores)): ?>
        <div class="notification is-danger">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Formulario -->
        <form action="" method="post" class="box">
            <h2 class="title is-4">Registro</h2>

            <div class="field">
                <label class="label">Nombre</label>
                <div class="control">
                    <input class="input" type="text" name="nombre" placeholder="Introduce tu nombre" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Apellido</label>
                <div class="control">
                    <input class="input" type="text" name="apellido" placeholder="Introduce tu apellido" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Usuario</label>
                <div class="control">
                    <input class="input" type="text" name="usuario" placeholder="Crea un nombre de usuario" required>
                </div>
            </div>

            <div class="field">
                <label class="label">E-mail</label>
                <div class="control">
                    <input class="input" type="email" name="email" placeholder="Introduce tu correo electrónico" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Contraseña</label>
                <div class="control">
                    <input class="input" type="password" name="password" placeholder="Crea una contraseña" required>
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <button class="button is-primary" type="submit">Registrarse</button>
                </div>
            </div>
        </form>
    </div>
</body>

<?php require_once "./inc/footer.php"; ?>





