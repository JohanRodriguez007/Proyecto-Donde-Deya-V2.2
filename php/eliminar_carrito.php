<?php
// Incluir archivo de conexión y sesión
require_once './modelo/Utils.php'; // Ahora incluye Utils.php
require_once "./controlador/SessionController.php"; // Incluir el archivo Utils.php

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

// Preparar y ejecutar la consulta SQL para eliminar del carrito
$sql = "DELETE FROM carrito WHERE carrito_id = ? AND usuario_id = ?";
$stmt = $conn->prepare($sql);

try {
    $stmt->execute([$carrito_id, $usuario_id]);
    
    if ($stmt->rowCount() > 0) {
        echo '<div class="message_success">Producto eliminado del carrito exitosamente.</div>';
    } else {
        echo '<div class="message_error">No se encontró el producto en el carrito o no pertenece al usuario.</div>';
    }
} catch (PDOException $e) {
    echo '<div class="message_error">Error al eliminar del carrito: ' . $e->getMessage() . '</div>';
}

// Redirigir al carrito o a otra vista
header("Location: ../index.php?vista=carrito");
exit();
?>






