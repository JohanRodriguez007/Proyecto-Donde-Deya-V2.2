<?php
require_once './controlador/ProductoController.php'; 

// Crear instancia del controlador
$productoController = new ProductoController();
$resultado = $productoController->listarProductos(3); 

$mensaje_error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$mensaje_exito = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<div class="d-flex justify-content-end position-fixed top-0 end-0 m-3">
    <a href="index.php?vista=login" class="btn btn-primary me-2">Iniciar Sesión</a>
    <a href="index.php?vista=customer_new" class="btn btn-primary">Registrarse</a>
    <a href="index.php?vista=carrito" class="btn btn-primary">Carrito</a>
</div>

<main>
<div class="container">
    <!-- Mostrar el mensaje de error si existe -->
    <?php if ($mensaje_error): ?>
        <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
    <?php endif; ?>

    <div class="container">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <?php
            if (count($resultado) > 0) {
                foreach($resultado as $row) {
                    $nombre = $row['producto_nombre'];
                    $precio = $row['producto_precio'];
                    $stock = $row['producto_stock'];
                    $foto = $row['producto_foto'];
                    $producto_id = $row['producto_id'];

                    // Construir la ruta completa de la imagen
                    $imagen = "./img/producto/" . $foto;

                    // Verificar si la imagen existe
                    if (!file_exists($imagen)) {
                        $imagen = "./img/no-photo.png"; // Imagen predeterminada
                    }
            ?>
            <div class="col">
                <div class="card shadow-sm">
                    <img src="<?php echo $imagen; ?>" alt="<?php echo $nombre; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $nombre; ?></h5>
                        <p class="card-text">$<?php echo number_format($precio, 3); ?></p>
                        <p class="card-text">Unidades Disponibles: <?php echo $stock; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <a href="descripción-producto.php?id=<?php echo $producto_id; ?>" class="btn btn-primary">Detalles</a>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <form method="post" action="php/agregar_carrito.php">
                                    <input type="hidden" name="vista_actual" value="cerveza">
                                    <input type="hidden" name="producto_id" value="<?php echo $row['producto_id']; ?>">
                                    <input type="number" name="cantidad" min="1" max="<?php echo $row['producto_stock']; ?>" value="1" required>
                                    <button type="submit" class="btn btn-success">Agregar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<p>No hay productos disponibles.</p>";
            }
            ?>
        </div>
    </div>
</main>

<?php
require './inc/footer.php'; // Incluir archivo de pie de página
?>

<script src="comprar-productos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>




