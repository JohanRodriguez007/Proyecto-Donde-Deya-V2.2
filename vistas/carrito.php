<?php
require './modelo/Utils.php'; // Incluir archivo de utilidades

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    header("Location: index.php?vista=login");
    exit();
}

$usuario_id = $_SESSION['id'];

// Obtener la conexión PDO
$conn = Utils::conexion(); // Cambiado a usar la función de Utils

// Preparar y ejecutar la consulta SQL para obtener los productos del carrito
$sql = "SELECT carrito.carrito_id, producto.producto_id, producto.producto_nombre, producto.producto_precio, carrito.cantidad, producto.producto_foto, producto.producto_stock
        FROM carrito
        JOIN producto ON carrito.producto_id = producto.producto_id
        WHERE carrito.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$usuario_id]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la URL de la vista anterior
$prev_page = isset($_SESSION['prev_page']) ? $_SESSION['prev_page'] : 'index.php';
?>

<div class="d-flex justify-content-end position-absolute top-0 end-0 m-3 bg-light rounded shadow p-2" 
     style="right: 10px; top: 10px;">
    <!-- Recuadro de bienvenida solo si el usuario está logueado -->
    <div class="user-info">
        <span class="me-2">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
        <div class="user-menu">
            <a href="index.php?vista=logout" class="btn btn-danger btn-sm">Cerrar sesión</a>
        </div>
    </div>
</div>

<main>
    <div class="container">
        <h1>Carrito de Compras</h1>

        <!-- Mostrar mensajes de error o éxito -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']); // Limpiar el mensaje después de mostrarlo
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success']); 
                unset($_SESSION['success']); // Limpiar el mensaje después de mostrarlo
                ?>
            </div>
        <?php endif; ?>

        <?php if (count($resultado) > 0) { ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_carrito = 0;
                    foreach($resultado as $row) {
                        $nombre = $row['producto_nombre'];
                        $precio = $row['producto_precio'];
                        $cantidad = $row['cantidad'];
                        $foto = $row['producto_foto'];
                        $carrito_id = $row['carrito_id'];
                        $producto_id = $row['producto_id']; // Agregado para enviar al formulario
                        $stock = $row['producto_stock']; // Stock del producto
                        $total_producto = $precio * $cantidad;

                        // Construir la ruta completa de la imagen
                        $imagen = "./img/producto/" . $foto;

                        // Verificar si la imagen existe
                        if (!file_exists($imagen)) {
                            $imagen = "./img/no-photo.png"; // Imagen predeterminada
                        }

                        // Mostrar el producto en la tabla
                        echo '<tr>';
                        echo '<td><img src="' . $imagen . '" alt="' . htmlspecialchars($nombre) . '" style="width: 50px; height: 50px;"></td>';
                        echo '<td>' . htmlspecialchars($nombre) . '</td>';
                        echo '<td>$' . number_format($precio, 3) . '</td>';
                        echo '<td>' . htmlspecialchars($cantidad) . '</td>';
                        echo '<td>$' . number_format($total_producto, 3) . '</td>';
                        echo '<td>
                                <form method="post" action="./php/eliminar_carrito.php" style="display: inline;">
                                    <input type="hidden" name="carrito_id" value="' . htmlspecialchars($carrito_id) . '">
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                                <form method="post" action="./php/aumentar_cantidad.php" style="display: inline;">
                                    <input type="hidden" name="producto_id" value="' . htmlspecialchars($producto_id) . '"> <!-- Enviar producto_id -->
                                    <input type="hidden" name="vista_actual" value="carrito">
                                    <button type="submit" class="btn btn-success text-white" style="font-size: 1rem;">+</button>
                                </form>
                              </td>';
                        echo '</tr>';

                        $total_carrito += $total_producto;
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total del Carrito:</strong></td>
                        <td colspan="2">$<?php echo number_format($total_carrito, 3); ?></td>
                    </tr>
                </tfoot>
            </table>
            <div class="text-end mt-3">
                <form method="post" action="./index.php?vista=pedidos">
                    <button type="submit" class="btn btn-success">Realizar Pedido</button>
                </form>
            </div>
        <?php } else { ?>
            <p>Tu carrito está vacío.</p>
        <?php } ?>
        <!-- Botón para regresar a la vista anterior -->
        <div class="text-end mt-3">
            <a href="<?php echo $prev_page; ?>" class="btn btn-secondary">Regresar</a>
        </div>
    </div>
</main>

<?php
require './inc/footer.php'; // Incluir archivo de pie de página
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>













