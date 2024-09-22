<?php
require_once "./modelo/Utils.php"; // Incluir archivo de utilidades

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: index.php?vista=login");
    exit();
}

// Variable para controlar si se debe mostrar el formulario o el mensaje de éxito
$formulario_visible = true;
$mensaje = null;

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar los datos del formulario
    $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : null;
    $metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : null;

    // Validar que los campos no estén vacíos
    if ($direccion && $metodo_pago) {
        $usuario_id = $_SESSION['id'];
        $conn = Utils::conexion(); // Instanciar la conexión
        
        try {
            // Iniciar la transacción
            $conn->beginTransaction();

            // Obtener los productos del carrito del usuario
            $sql_carrito = "SELECT producto_id, cantidad FROM carrito WHERE usuario_id = ?";
            $stmt_carrito = $conn->prepare($sql_carrito);
            $stmt_carrito->execute([$usuario_id]);
            $productos = $stmt_carrito->fetchAll(PDO::FETCH_ASSOC);

            // Insertar el pedido en la tabla pedidos (sin el total)
            $sql_pedido = "INSERT INTO pedidos (usuario_id, direccion, metodo_pago) VALUES (?, ?, ?)";
            $stmt_pedido = $conn->prepare($sql_pedido);
            $stmt_pedido->execute([$usuario_id, $direccion, $metodo_pago]);

            // Obtener el ID del nuevo pedido
            $pedido_id = $conn->lastInsertId();

            $total_pedido = 0;

            // Insertar detalles del pedido en la tabla detalle_pedido y calcular el total
            $sql_detalle = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, total) VALUES (?, ?, ?, ?)";
            $stmt_detalle = $conn->prepare($sql_detalle);

            foreach ($productos as $producto) {
                // Obtener el precio del producto
                $sql_precio = "SELECT producto_precio FROM producto WHERE producto_id = ?";
                $stmt_precio = $conn->prepare($sql_precio);
                $stmt_precio->execute([$producto['producto_id']]);
                $precio_producto = $stmt_precio->fetchColumn();

                $total_producto = $producto['cantidad'] * $precio_producto;
                $total_pedido += $total_producto;

                // Insertar en detalle_pedido
                $stmt_detalle->execute([$pedido_id, $producto['producto_id'], $producto['cantidad'], $total_producto]);
            }

            // Actualizar el total del pedido en la tabla pedidos
            $sql_actualizar_total = "UPDATE pedidos SET total = ? WHERE pedido_id = ?";
            $stmt_actualizar_total = $conn->prepare($sql_actualizar_total);
            $stmt_actualizar_total->execute([$total_pedido, $pedido_id]);

            // Vaciar el carrito después de realizar el pedido
            $sql_vaciar_carrito = "DELETE FROM carrito WHERE usuario_id = ?";
            $stmt_vaciar_carrito = $conn->prepare($sql_vaciar_carrito);
            $stmt_vaciar_carrito->execute([$usuario_id]);

            // Confirmar la transacción
            $conn->commit();

            // Establecer mensaje de éxito y ocultar el formulario
            $mensaje = 'Pedido realizado correctamente. Espera aprobación.';
            $formulario_visible = false;
        } catch (PDOException $e) {
            // Deshacer la transacción en caso de error
            $conn->rollBack();
            $mensaje = 'Error al realizar el pedido: ' . $e->getMessage();
        }
    } else {
        $mensaje = 'Por favor, completa todos los campos.';
    }
}

// Mostrar mensaje si existe
if ($mensaje) {
    echo '<div class="alert alert-info">' . htmlspecialchars($mensaje) . '</div>';
}

// Mostrar el formulario solo si es visible
if ($formulario_visible) {
?>
<form method="post" class="container mt-4">
    <div class="form-group">
        <label for="direccion">Dirección de Envío:</label>
        <input type="text" name="direccion" id="direccion" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="metodo_pago">Método de Pago:</label>
        <select name="metodo_pago" id="metodo_pago" class="form-control" required>
            <option value="Transferencia Nequi">Transferencia Nequi</option>
            <option value="Efectivo">Efectivo</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Realizar Pedido</button>
</form>
<?php
}
?>

<?php
require './inc/footer.php'; // Incluir archivo de pie de página
?>






