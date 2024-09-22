<?php
// Incluir archivo de conexión y sesión
require_once './modelo/Utils.php';  // Incluir archivo que contiene la función conexion()
require_once "./controlador/SessionController.php"; // Incluir el archivo Utils.php

$sessionController = new SessionController();
$sessionController->startSession(); // Iniciar la sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php?vista=login");
    exit();
}

$usuario_id = $_SESSION['id']; // Asegurarse de que el usuario_id esté bien definido

// Obtener la conexión PDO
$conn = Utils::conexion();  // Usar la función conexion() de Utils.php

try {
    // Iniciar una transacción
    $conn->beginTransaction();

    // Obtener los productos del carrito del usuario
    $sql = "SELECT carrito.producto_id, carrito.cantidad, producto.producto_stock
            FROM carrito
            JOIN producto ON carrito.producto_id = producto.producto_id
            WHERE carrito.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario_id]);
    $productos_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($productos_carrito) > 0) {
        // Recorrer los productos y actualizar el stock
        foreach ($productos_carrito as $producto) {
            $producto_id = $producto['producto_id'];
            $cantidad_carrito = $producto['cantidad'];
            $stock_actual = $producto['producto_stock'];

            // Verificar si hay suficiente stock
            if ($stock_actual >= $cantidad_carrito) {
                // Restar la cantidad comprada del stock
                $nuevo_stock = $stock_actual - $cantidad_carrito;

                // Depuración: Verificar la actualización del stock
                echo "Producto ID: $producto_id, Stock actual: $stock_actual, Cantidad comprada: $cantidad_carrito, Nuevo stock: $nuevo_stock<br>";

                $sql_actualizar_stock = "UPDATE producto SET producto_stock = ? WHERE producto_id = ?";
                $stmt_actualizar_stock = $conn->prepare($sql_actualizar_stock);
                $stmt_actualizar_stock->execute([$nuevo_stock, $producto_id]);

                // Depuración: Verificar si la actualización fue exitosa
                if ($stmt_actualizar_stock->rowCount() > 0) {
                    echo "Stock actualizado para el producto ID: $producto_id<br>";
                } else {
                    echo "No se pudo actualizar el stock para el producto ID: $producto_id<br>";
                }
            } else {
                // Si no hay suficiente stock, cancelar la compra y mostrar un mensaje
                echo '<div class="message_error">No hay suficiente stock para el producto con ID: ' . $producto_id . '</div>';
                // Cancelar la transacción
                $conn->rollBack();
                exit();
            }
        }

        // Vaciar el carrito después de la compra exitosa
        $sql_vaciar_carrito = "DELETE FROM carrito WHERE usuario_id = ?";
        $stmt_vaciar_carrito = $conn->prepare($sql_vaciar_carrito);
        $stmt_vaciar_carrito->execute([$usuario_id]);

        // Confirmar la transacción
        $conn->commit();

        echo '<div class="message_success">Compra realizada con éxito. El stock ha sido actualizado.</div>';

        // Redirigir a la página de éxito
        header("Location: ../index.php?vista=compra_exitosa");
        exit();
    } else {
        echo '<div class="message_error">El carrito está vacío.</div>';
    }
} catch (PDOException $e) {
    // En caso de error, cancelar la transacción
    $conn->rollBack();
    echo '<div class="message_error">Error en la compra: ' . $e->getMessage() . '</div>';
    exit();
}

?>

