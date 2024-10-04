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
    // Capturar los datos del formulario de dirección
    $tipo_via = isset($_POST['tipo_via']) ? $_POST['tipo_via'] : null;
    $numero_via_principal = isset($_POST['numero_via_principal']) ? $_POST['numero_via_principal'] : null;
    $numero_via_secundaria = isset($_POST['numero_via_secundaria']) ? $_POST['numero_via_secundaria'] : null;
    $numero_via_complemento = isset($_POST['numero_via_complemento']) ? $_POST['numero_via_complemento'] : null;
    $localidad = isset($_POST['localidad']) ? $_POST['localidad'] : null;
    $barrio = isset($_POST['barrio']) ? $_POST['barrio'] : null;
    $detalles = isset($_POST['detalles']) ? $_POST['detalles'] : null;
    $metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : null;

    // Construir la dirección concatenando las partes seleccionadas
    $direccion = "$tipo_via $numero_via_principal No $numero_via_secundaria - $numero_via_complemento, $localidad $barrio, $detalles";

    // Validar que los campos no estén vacíos
    if ($tipo_via && $numero_via_principal && $numero_via_secundaria && $numero_via_complemento && $localidad && $barrio && $detalles &&  $metodo_pago) {
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

            // Insertar el pedido en la tabla pedidos
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

<div class="d-flex justify-content-end position-fixed top-0 end-0 m-3 bg-light rounded shadow p-2">
    <div class="user-info">
        <span class="me-2">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
        <div class="user-menu">
            <a href="index.php?vista=logout" class="btn btn-danger btn-sm">Cerrar sesión</a>
        </div>
    </div>
</div>

<!-- Formulario de dirección con múltiples campos para Bogotá -->
<form method="post" class="container mt-4">
    <div class="form-group mb-3">
        <label for="tipo_via">Tipo de Vía:</label>
        <select name="tipo_via" id="tipo_via" class="form-control" required>
            <option value="Calle">Calle</option>
            <option value="Carrera">Carrera</option>
            <option value="Avenida">Avenida</option>
            <option value="Diagonal">Diagonal</option>
            <option value="Transversal">Transversal</option>
        </select>
    </div>

    <div class="form-group mb-3">
        <label for="numero_via_principal">Número principal:</label>
        <input type="text" name="numero_via_principal" id="numero_via_principal" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label for="numero_via_secundaria">Número</label>
        <div class="row">
            <div class="col-md-3 mb-3">
                <input type="text" name="numero_via_secundaria" id="numero_via_secundaria" class="form-control" required>
            </div>
            <div class="col-md-3 mb-3">
                <input type="text" name="numero_via_complemento" id="numero_via_complemento" class="form-control" required>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="localidad">Localidad:</label>
        <select name="localidad" id="localidad" class="form-control" required>
            <option value="Chapinero">Chapinero</option>
            <option value="Usaquén">Usaquén</option>
            <option value="Suba">Suba</option>
            <option value="Kennedy">Kennedy</option>
            <option value="Teusaquillo">Teusaquillo</option>
            <option value="Engativá">Engativá</option>
            <!-- Agregar más localidades de Bogotá -->
        </select>
    </div>

    <div class="form-group mb-3">
        <label for="barrio">Barrio:</label>
        <input type="text" name="barrio" id="barrio" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label for="detalles">Detalles Adicionales:</label>
        <input type="text" name="detalles" id="detalles" class="form-control" required>
    </div>

    <div class="form-group mb-3">
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









