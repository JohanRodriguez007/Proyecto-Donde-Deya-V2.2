<?php
require_once "../modelo/Utils.php"; // Incluir Utils.php

// Almacenando datos
$nombre = Utils::limpiar_cadena($_POST['usuario_nombre']);
$apellido = Utils::limpiar_cadena($_POST['usuario_apellido']);
$usuario = Utils::limpiar_cadena($_POST['usuario_usuario']);
$email = Utils::limpiar_cadena($_POST['usuario_email']);
$clave_1 = Utils::limpiar_cadena($_POST['usuario_clave_1']);
$clave_2 = Utils::limpiar_cadena($_POST['usuario_clave_2']);
$usuario_rol = 1; // Valor fijo para usuario_rol

// Verificando campos obligatorios
if ($nombre == "" || $apellido == "" || $usuario == "" || $clave_1 == "" || $clave_2 == "") {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrió un error inesperado!</strong><br>
            No has llenado todos los campos que son obligatorios
        </div>
    ';
    exit();
}

// Verificando integridad de los datos (aquí puedes agregar tus validaciones)

// Verificando usuario
$check_usuario = Utils::conexion();
$check_usuario = $check_usuario->query("SELECT usuario_usuario FROM usuario WHERE usuario_usuario='$usuario'");
if ($check_usuario->rowCount() > 0) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrió un error inesperado!</strong><br>
            El USUARIO ingresado ya se encuentra registrado, por favor elija otro
        </div>
    ';
    exit();
}
$check_usuario = null;

// Verificando claves
if ($clave_1 != $clave_2) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrió un error inesperado!</strong><br>
            Las CLAVES que ha ingresado no coinciden
        </div>
    ';
    exit();
} else {
    $clave = password_hash($clave_1, PASSWORD_BCRYPT, ["cost" => 10]);
}

// Guardando datos
$guardar_usuario = Utils::conexion();
$guardar_usuario = $guardar_usuario->prepare("INSERT INTO usuario(usuario_nombre, usuario_apellido, usuario_usuario, usuario_clave, usuario_email, usuario_rol) VALUES(:nombre, :apellido, :usuario, :clave, :email, :rol)");

$marcadores = [
    ":nombre" => $nombre,
    ":apellido" => $apellido,
    ":usuario" => $usuario,
    ":clave" => $clave,
    ":email" => $email,
    ":rol" => $usuario_rol
];

if ($guardar_usuario->execute($marcadores) && $guardar_usuario->rowCount() == 1) {
    echo '
        <div class="notification is-info is-light">
            <strong>¡USUARIO REGISTRADO!</strong><br>
            El usuario se registró con éxito
        </div>
    ';
} else {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrió un error inesperado!</strong><br>
            No se pudo registrar el usuario, por favor intente nuevamente
        </div>
    ';
}
$guardar_usuario = null;
?>

