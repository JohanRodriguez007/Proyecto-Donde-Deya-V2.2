<?php
// Incluir archivo de conexión y sesión
require_once '../modelo/Utils.php'; // Ahora incluye Utils.php
require_once "../controlador/SessionController.php"; // Incluir el archivo Utils.php

$sessionController = new SessionController();
$sessionController->startSession(); // Iniciar la sesión

// Depuración: Mostrar contenido de la sesión
echo "<pre>";
echo "Sesión actual: ";
print_r($_SESSION);
echo "</pre>";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    echo '<div class="message_error">No se ha iniciado sesión correctamente.</div>';
    header("Location: ../index.php?vista=login");
    exit();
}

// Obtener datos del formulario
$carrito_id = isset($_POST['carrito_id']) ? intval($_POST['carrito_id']) : 0;
$usuario_id = $_SESSION['id'];

// Verificar que el ID del carrito es válido
if ($carrito_id <= 0) {
    echo '<div class="message_error">ID de carrito inválido.</div>';
    exit();
}

// Obtener la conexión PDO desde la función conexion()
$conn = Utils::conexion();

// Preparar y ejecutar la consulta SQL para obtener la cantidad del producto en el carrito
$sql_cantidad = "SELECT cantidad, producto_id FROM carrito WHERE carrito_id = ? AND usuario_id = ?";
$stmt_cantidad = $conn->prepare($sql_cantidad);
$stmt_cantidad->execute([$carrito_id, $usuario_id]);
$producto_en_carrito = $stmt_cantidad->fetch(PDO::FETCH_ASSOC);

if (!$producto_en_carrito) {
    echo '<div class="message_error">No se encontró el producto en el carrito o no pertenece al usuario.</div>';
    exit();
}

// Obtener la cantidad y el ID del producto
$cantidad = $producto_en_carrito['cantidad'];
$producto_id = $producto_en_carrito['producto_id'];

// Preparar y ejecutar la consulta SQL para eliminar del carrito
$sql_eliminar = "DELETE FROM carrito WHERE carrito_id = ? AND usuario_id = ?";
$stmt_eliminar = $conn->prepare($sql_eliminar);

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Ejecutar eliminación
    $stmt_eliminar->execute([$carrito_id, $usuario_id]);

    if ($stmt_eliminar->rowCount() > 0) {
        // Sumar al stock del producto
        $sql_actualizar_stock = "UPDATE producto SET producto_stock = producto_stock + ? WHERE producto_id = ?";
        $stmt_actualizar_stock = $conn->prepare($sql_actualizar_stock);
        $stmt_actualizar_stock->execute([$cantidad, $producto_id]);

        // Confirmar transacción
        $conn->commit();

        echo '<div class="message_success">Producto eliminado del carrito y stock actualizado exitosamente.</div>';
    } else {
        echo '<div class="message_error">No se encontró el producto en el carrito o no pertenece al usuario.</div>';
    }
} catch (PDOException $e) {
    // Revertir cambios en caso de error
    $conn->rollBack();
    echo '<div class="message_error">Error al eliminar del carrito: ' . $e->getMessage() . '</div>';
}

// Redirigir al carrito o a otra vista
header("Location: ../index.php?vista=carrito");
exit();
?>







