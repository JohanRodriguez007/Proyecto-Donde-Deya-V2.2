<?php require_once "./inc/header.php"; ?>

<?php

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

//-----------------------------------------------------
// Variables
//-----------------------------------------------------
$errores = [];
$nombre = isset($_REQUEST['nombre']) ? $_REQUEST['nombre'] : '';
$apellido = isset($_REQUEST['apellido']) ? $_REQUEST['apellido'] : '';
$usuario = isset($_REQUEST['usuario']) ? $_REQUEST['usuario'] : '';
$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';

// Comprobamos si nos llega los datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //-----------------------------------------------------
    // Validaciones
    //-----------------------------------------------------
    // Email
    if (!validar_requerido($email)) {
        $errores[] = '<div class="notification is-danger is-light">
                <strong>Campo Email obligatorio.</strong><br>
                El usuario no existe en el sistema
            </div>
        ';
    }

    if (!validar_email($email)) {
        $errores[] = '<div class="notification is-danger is-light">
                <strong>El campo Email no tiene un formato válido.</strong><br>
            </div>
        ';
    }

    // Nombre
    if (!validar_requerido($nombre)) {
        $errores[] = '<div class="notification is-danger is-light">
                <strong>El campo Nombre es Obligatorio.</strong><br>
            </div>
        ';
    }

    // Apellido
    if (!validar_requerido($apellido)) {
        $errores[] = '<div class="notification is-danger is-light">
                <strong>El campo Apellido es Obligatorio.</strong><br>
            </div>
        ';
    }

    // Usuario
    if (!validar_requerido($usuario)) {
        $errores[] = '<div class="notification is-danger is-light">
                <strong>El campo Usuario es Obligatorio.</strong><br>
            </div>
        ';
    }

    // Contraseña
    if (!validar_requerido($password)) {
        $errores[] = '<div class="notification is-danger is-light">
                <strong>El campo Contraseña es Obligatorio.</strong><br>
            </div>
        ';
    }

    /* Verificar que no existe en la base de datos el mismo email */
    // Conecta con base de datos
    $BD ='bd_donde_deya';
    $miPDO = new PDO("mysql:host=127.0.0.1; dbname=$BD;", 'root', '');
    // Cuenta cuantos emails existen
    $miConsulta = $miPDO->prepare('SELECT COUNT(*) as length FROM usuario WHERE usuario_email = :email;');
    // Ejecuta la busqueda
    $miConsulta->execute([
        'email' => $email
    ]);
    // Recoge los resultados
    $resultado = $miConsulta->fetch();
    // Comprueba si existe
    if ((int) $resultado['length'] > 0) {
        $errores[] = '<div class="notification is-danger is-light">
                <strong>La dirección de Email ya está registrada</strong><br>
            </div>
        ';
    }

    //-----------------------------------------------------
    // Crear cuenta
    //-----------------------------------------------------
    if (count($errores) === 0) {
        /* Registro En La Base De Datos */

        // Prepara INSERT
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $miNuevoRegistro = $miPDO->prepare('INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_usuario, usuario_email, usuario_clave, usuario_rol, usuario_activo, usuario_token) VALUES (:nombre, :apellido, :usuario, :email, :password, :rol, :activo, :token);');
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
            <a href='http://localhost/Proyecto-Donde-Deya-V2-master/index.php?vista=activate_customer&email=$emailEncode&token=$tokenEncode'>Activar cuenta</a>";

            $mail->send();
            echo 'Correo enviado';
        } catch (Exception $e) {
            echo 'Mensaje: ' . $mail->ErrorInfo;
        }

        /* Redirección a login.php con GET para informar del envio del email */
        header('Location: index.php?vista=tienda');
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Cliente</title>
    <style>
        form {
            width: 50%; 
            margin: 40px auto; 
            padding: 20px; 
            border: 1px solid #ccc; 
            border-radius: 10px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
        }
        label {
            display: block; 
            margin-bottom: 10px; 
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; 
            padding: 10px; 
            margin-bottom: 20px;
            border: 1px solid #ccc; 
        }
        input[type="submit"] {
            background-color: #4CAF50; 
            color: #fff; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        input[type="submit"]:hover {
            background-color: #3e8e41; 
        }
    </style>
</head>
<body>
    <!-- Mostramos errores por HTML -->
    <?php if (isset($errores)): ?>
    <ul class="errores">
        <?php 
            foreach ($errores as $error) {
                echo '<li>' . $error . '</li>';
            } 
        ?> 
    </ul>
    <?php endif; ?>
    <!-- Formulario -->
    <form action="" method="post">
        <div>
            <!-- Campo de Nombre -->
            <label>
                Nombre
                <input type="text" name="nombre">
            </label>
        </div>
        <div>
            <!-- Campo de Apellido -->
            <label>
                Apellido
                <input type="text" name="apellido">
            </label>
        </div>
        <div>
            <!-- Campo de Usuario -->
            <label>
                Usuario
                <input type="text" name="usuario">
            </label>
        </div>
        <div>
            <!-- Campo de Email -->
            <label>
                E-mail
                <input type="email" name="email">
            </label>
        </div>
        <div>
            <!-- Campo de Contraseña -->
            <label>
                Contraseña
                <input type="password" name="password">
            </label>
        </div>
        <div>
            <!-- Botón submit -->
            <input type="submit" value="Registrarse">
        </div>
    </form>
</body>
</html>

<?php require_once "./inc/footer.php"; ?>

