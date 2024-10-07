<?php
// Incluir archivo de utilidades
require_once './modelo/Utils.php';

/*== Almacenando datos ==*/
$usuario = Utils::limpiar_cadena($_POST['login_usuario']);
$clave = Utils::limpiar_cadena($_POST['login_clave']);

/*== Verificando campos obligatorios ==*/
if ($usuario == "" || $clave == "") {
    echo '
        <div class="message_error">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            <p style="color:red;">No has llenado todos los campos que son obligatorios</p>
        </div>
    ';
    exit();
}

/*== Verificando integridad de los datos ==*/
if (Utils::verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    echo '
        <div class="message_error">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            <p style="color:red;">El USUARIO no coincide con el formato solicitado</p>
        </div>
    ';
    exit();
}

if (Utils::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave)) {
    echo '
        <div class="message_error">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            <p style="color:red;">La clave no coincide con el formato solicitado</p>
        </div>
    ';
    exit();
}

/*== Verificando usuario en la base de datos ==*/
$check_user = Utils::conexion();
$check_user = $check_user->query("SELECT * FROM usuario WHERE usuario_usuario='$usuario'");
if ($check_user->rowCount() == 1) {

    $check_user = $check_user->fetch();

    if ($check_user['usuario_usuario'] == $usuario && password_verify($clave, $check_user['usuario_clave'])) {

        // Iniciar sesión
        $_SESSION['id'] = $check_user['usuario_id'];
        $_SESSION['nombre'] = $check_user['usuario_nombre'];
        $_SESSION['apellido'] = $check_user['usuario_apellido'];
        $_SESSION['usuario'] = $check_user['usuario_usuario'];

        // Obtener la vista actual del formulario
        $vista_actual = isset($_POST['vista_actual']) ? $_POST['vista_actual'] : 'tienda';

        // Verificar si es administrador o cliente
        if ($check_user['usuario_rol'] == 1) { // Administrador
            if (headers_sent()) {
                echo "<script> window.location.href='index.php?vista=home'; </script>";
            } else {
                header("Location: index.php?vista=home");
            }
        } else { // Cliente
            // Redirigir a la vista actual en lugar de la tienda
            if (headers_sent()) {
                echo "<script> window.location.href='index.php?vista=$vista_actual'; </script>";
            } else {
                header("Location: index.php?vista=$vista_actual");
            }
        }

    } else {
        echo '
            <div class="message_error">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                <p style="color:red;">Usuario o clave incorrectos</p>
            </div>
        ';
    }
} else {
    echo '
        <div class="message_error">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            <p style="color:red;">Usuario o clave incorrectos</p>
        </div>
    ';
}
$check_user = null;
?>

