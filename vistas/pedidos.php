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
    $tipo_via = $_POST['tipo_via'] ?? null;
    $numero_via_principal = $_POST['numero_via_principal'] ?? null;
    $numero_via_secundaria = $_POST['numero_via_secundaria'] ?? null;
    $numero_via_complemento = $_POST['numero_via_complemento'] ?? null;
    $localidad = $_POST['localidad'] ?? null;
    $barrio = $_POST['barrio'] ?? null;
    $detalles = $_POST['detalles'] ?? null;
    $metodo_pago = $_POST['metodo_pago'] ?? null;
    $captura_transferencia = null;

    // Validar y procesar la imagen si el método de pago es Transferencia Nequi
    if ($metodo_pago === 'Transferencia Nequi' && isset($_FILES['captura_transferencia']) && $_FILES['captura_transferencia']['error'] === 0) {
        $nombre_archivo = $_FILES['captura_transferencia']['name'];
        $ruta_temporal = $_FILES['captura_transferencia']['tmp_name'];
        $ext_archivo = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
        $nombre_archivo_guardado = uniqid('nequi_', true) . '.' . $ext_archivo;
        $directorio_destino = './uploads/capturas/';

        // Crear directorio si no existe
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        // Validar el tipo de archivo (JPEG o PNG)
        $tipo_archivo = mime_content_type($ruta_temporal);
        if ($tipo_archivo !== 'image/jpeg' && $tipo_archivo !== 'image/png') {
            $mensaje = 'El archivo debe ser una imagen JPEG o PNG.';
        } else {
            // Validar el tamaño del archivo (por ejemplo, máximo 2MB)
            if ($_FILES['captura_transferencia']['size'] > 2 * 1024 * 1024) {
                $mensaje = 'El tamaño del archivo no puede exceder los 2MB.';
            } else {
                // Mover el archivo al directorio destino
                $ruta_destino = $directorio_destino . $nombre_archivo_guardado;
                if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
                    $captura_transferencia = $nombre_archivo_guardado;
                } else {
                    $mensaje = 'Error al subir la captura de transferencia.';
                }
            }
        }
    } elseif ($metodo_pago === 'Transferencia Nequi') {
        $mensaje = 'Por favor, sube una captura de la transferencia para continuar.';
    }

    // Construir la dirección concatenando las partes seleccionadas
    $direccion = "$tipo_via $numero_via_principal No $numero_via_secundaria - $numero_via_complemento, $localidad $barrio, $detalles";

    // Validar que los campos no estén vacíos
    if ($tipo_via && $numero_via_principal && $numero_via_secundaria && $numero_via_complemento && $localidad && $barrio && $detalles && $metodo_pago && ($metodo_pago !== 'Transferencia Nequi' || $captura_transferencia)) {
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
            $sql_pedido = "INSERT INTO pedidos (usuario_id, direccion, metodo_pago, captura_transferencia) VALUES (?, ?, ?, ?)";
            $stmt_pedido = $conn->prepare($sql_pedido);
            $stmt_pedido->execute([$usuario_id, $direccion, $metodo_pago, $captura_transferencia]);

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

<div class="d-flex justify-content-end position-absolute top-0 end-0 m-3 bg-light rounded shadow p-2">
    <div class="user-info">
        <span class="me-2">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
        <div class="user-menu">
            <a href="index.php?vista=logout" class="btn btn-danger btn-sm">Cerrar sesión</a>
        </div>
    </div>
</div>

<!-- Formulario de dirección con múltiples campos para Bogotá -->
<form method="post" enctype="multipart/form-data" class="container mt-4">
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
                <input type="text" name="numero_via_complemento" id="numero_via_complemento" class="form-control" placeholder="Complemento (si aplica)">
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="localidad">Localidad:</label>
        <select name="localidad" id="localidad" class="form-control" required>
            <option value="Suba">Suba</option>
            <option value="Engativá">Engativá</option>
            <option value="Barrios Unidos">Barrios Unidos</option>
            <option value="Chapinero">Chapinero</option>
            <option value="Teusaquillo">Teusaquillo</option>
            <option value="Santafé">Santafé</option>
        </select>
    </div>

    <div class="form-group mb-3">
        <label for="barrio">Barrio:</label>
        <input type="text" name="barrio" id="barrio" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label for="detalles">Detalles:</label>
        <textarea name="detalles" id="detalles" class="form-control" rows="3" required></textarea>
    </div>

    <div class="form-group mb-3">
        <label for="metodo_pago">Método de pago:</label>
        <select name="metodo_pago" id="metodo_pago" class="form-control" required>
            <option value="Efectivo">Efectivo</option>
            <option value="Transferencia Nequi">Transferencia Nequi</option>
        </select>
    </div>

    <div class="form-group mb-3" id="div_captura" style="display: none;">
        <label for="captura_transferencia">Captura de transferencia:</label>
        <input type="file" name="captura_transferencia" id="captura_transferencia" accept="image/*" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Realizar Pedido</button>
</form>

<script>
    // Mostrar/ocultar campo de captura de transferencia según el método de pago seleccionado
    document.getElementById('metodo_pago').addEventListener('change', function() {
        document.getElementById('div_captura').style.display = this.value === 'Transferencia Nequi' ? 'block' : 'none';
    });
</script>

<?php
} // Fin del formulario
?>
<?php require_once "./inc/footer_V2.php"; ?>












