<?php
require_once "../modelo/Utils.php"; // Incluir el archivo Utils.php
require_once "../controlador/SessionController.php";

$sessionController = new SessionController();
$sessionController->startSession(); // Iniciar la sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Error de sesión!</strong><br>
            Debes iniciar sesión para poder editar el usuario.
        </div>
    ';
    exit();
}

// Almacenando id
$id = Utils::limpiar_cadena($_POST['usuario_id']);

// Verificando usuario
$check_usuario = Utils::conexion();
$check_usuario = $check_usuario->query("SELECT * FROM usuario WHERE usuario_id='$id'");

if ($check_usuario->rowCount() <= 0) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El usuario no existe en el sistema
        </div>
    ';
    exit();
} else {
    $datos = $check_usuario->fetch();
}
$check_usuario = null;

// Almacenando datos del administrador
$admin_usuario = Utils::limpiar_cadena($_POST['administrador_usuario']);
$admin_clave = Utils::limpiar_cadena($_POST['administrador_clave']);

// Verificando campos obligatorios del administrador
if ($admin_usuario == "" || $admin_clave == "") {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            No ha llenado los campos que corresponden a su USUARIO o CLAVE
        </div>
    ';
    exit();
}

// Verificando integridad de los datos (admin)
if (Utils::verificar_datos("[a-zA-Z0-9]{4,20}", $admin_usuario)) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            Su USUARIO no coincide con el formato solicitado
        </div>
    ';
    exit();
}

if (Utils::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $admin_clave)) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            Su CLAVE no coincide con el formato solicitado
        </div>
    ';
    exit();
}

// Verificando el administrador en DB
$check_admin = Utils::conexion();
$check_admin = $check_admin->query("SELECT usuario_usuario, usuario_clave FROM usuario WHERE usuario_usuario='$admin_usuario' AND usuario_id='" . $_SESSION['id'] . "'");
if ($check_admin->rowCount() == 1) {
    $check_admin = $check_admin->fetch();
    if ($check_admin['usuario_usuario'] != $admin_usuario || !password_verify($admin_clave, $check_admin['usuario_clave'])) {
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                USUARIO o CLAVE de administrador incorrectos
            </div>
        ';
        exit();
    }
} else {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            USUARIO o CLAVE de administrador incorrectos
        </div>
    ';
    exit();
}
$check_admin = null;

// Almacenando datos del usuario
$nombre = Utils::limpiar_cadena($_POST['usuario_nombre']);
$apellido = Utils::limpiar_cadena($_POST['usuario_apellido']);
$usuario = Utils::limpiar_cadena($_POST['usuario_usuario']);
$email = Utils::limpiar_cadena($_POST['usuario_email']);
$clave_1 = Utils::limpiar_cadena($_POST['usuario_clave_1']);
$clave_2 = Utils::limpiar_cadena($_POST['usuario_clave_2']);

// Verificando campos obligatorios del usuario
if ($nombre == "" || $apellido == "" || $usuario == "") {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            No has llenado todos los campos que son obligatorios
        </div>
    ';
    exit();
}

// Verificando integridad de los datos (usuario)
if (Utils::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $nombre)) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El NOMBRE no coincide con el formato solicitado
        </div>
    ';
    exit();
}

if (Utils::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $apellido)) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El APELLIDO no coincide con el formato solicitado
        </div>
    ';
    exit();
}

if (Utils::verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El USUARIO no coincide con el formato solicitado
        </div>
    ';
    exit();
}

// Verificando email
if ($email != "" && $email != $datos['usuario_email']) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check_email = Utils::conexion();
        $check_email = $check_email->query("SELECT usuario_email FROM usuario WHERE usuario_email='$email'");
        if ($check_email->rowCount() > 0) {
            echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    El correo electrónico ingresado ya se encuentra registrado, por favor elija otro
                </div>
            ';
            exit();
        }
        $check_email = null;
    } else {
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                Ha ingresado un correo electrónico no valido
            </div>
        ';
        exit();
    }
}

// Verificando usuario
if ($usuario != $datos['usuario_usuario']) {
    $check_usuario = Utils::conexion();
    $check_usuario = $check_usuario->query("SELECT usuario_usuario FROM usuario WHERE usuario_usuario='$usuario'");
    if ($check_usuario->rowCount() > 0) {
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                El USUARIO ingresado ya se encuentra registrado, por favor elija otro
            </div>
        ';
        exit();
    }
    $check_usuario = null;
}

// Verificando claves
if ($clave_1 != "" || $clave_2 != "") {
    if (Utils::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave_1) || Utils::verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave_2)) {
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                Las CLAVES no coinciden con el formato solicitado
            </div>
        ';
        exit();
    } else {
        if ($clave_1 != $clave_2) {
            echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    Las CLAVES que ha ingresado no coinciden
                </div>
            ';
            exit();
        } else {
            $clave = password_hash($clave_1, PASSWORD_BCRYPT, ["cost" => 10]);
        }
    }
} else {
    $clave = $datos['usuario_clave'];
}

// Actualizar datos
$actualizar_usuario = Utils::conexion();
$actualizar_usuario = $actualizar_usuario->prepare("UPDATE usuario SET usuario_nombre=:nombre, usuario_apellido=:apellido, usuario_usuario=:usuario, usuario_clave=:clave, usuario_email=:email WHERE usuario_id=:id");

$marcadores = [
    ":nombre" => $nombre,
    ":apellido" => $apellido,
    ":usuario" => $usuario,
    ":clave" => $clave,
    ":email" => $email,
    ":id" => $id
];

if ($actualizar_usuario->execute($marcadores)) {
    echo '
        <div class="notification is-info is-light">
            <strong>¡USUARIO ACTUALIZADO!</strong><br>
            El usuario se actualizó con éxito
        </div>
    ';
} else {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            No se pudo actualizar el usuario, por favor intente nuevamente
        </div>
    ';
}
$actualizar_usuario = null;
?>
