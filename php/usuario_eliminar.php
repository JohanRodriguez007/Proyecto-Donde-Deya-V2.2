<?php
require_once "./modelo/Utils.php"; // Incluir el archivo Utils.php

// Almacenando datos
$user_id_del = Utils::limpiar_cadena($_GET['user_id_del']);

// Verificando usuario
$check_usuario = Utils::conexion();
$check_usuario = $check_usuario->query("SELECT usuario_id FROM usuario WHERE usuario_id='$user_id_del'");

if ($check_usuario->rowCount() == 1) {
    // Verificando si hay pedidos relacionados
    $check_pedidos = Utils::conexion();
    $check_pedidos = $check_pedidos->query("SELECT usuario_id FROM pedidos WHERE usuario_id='$user_id_del' LIMIT 1");

    if ($check_pedidos->rowCount() > 0) {
        echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                No podemos eliminar el usuario ya que tiene pedidos registrados por él
            </div>
        ';
    } else {
        // Verificando si hay productos relacionados
        $check_productos = Utils::conexion();
        $check_productos = $check_productos->query("SELECT usuario_id FROM producto WHERE usuario_id='$user_id_del' LIMIT 1");

        if ($check_productos->rowCount() <= 0) {
            $eliminar_usuario = Utils::conexion();
            $eliminar_usuario = $eliminar_usuario->prepare("DELETE FROM usuario WHERE usuario_id=:id");

            if ($eliminar_usuario->execute([":id" => $user_id_del])) {
                echo '
                    <div class="notification is-info is-light">
                        <strong>¡USUARIO ELIMINADO!</strong><br>
                        Los datos del usuario se eliminaron con éxito
                    </div>
                ';
            } else {
                echo '
                    <div class="notification is-danger is-light">
                        <strong>¡Ocurrio un error inesperado!</strong><br>
                        No se pudo eliminar el usuario, por favor intente nuevamente
                    </div>
                ';
            }
            $eliminar_usuario = null;
        } else {
            echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    No podemos eliminar el usuario ya que tiene productos registrados por él
                </div>
            ';
        }
        $check_productos = null;
    }
    $check_pedidos = null;
} else {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El USUARIO que intenta eliminar no existe
        </div>
    ';
}
$check_usuario = null;
?>

