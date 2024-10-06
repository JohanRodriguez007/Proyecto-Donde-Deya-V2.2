<?php
require_once './controlador/ProductoController.php'; 

// Verificar si el usuario está logueado
$usuarioLogueado = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;

// Crear instancia del controlador de productos
$productoController = new ProductoController();
$resultado = $productoController->listarProductos(5); 

$mensaje_error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$mensaje_exito = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>

<div class="d-flex justify-content-end position-fixed top-0 end-0 m-3 bg-light rounded shadow p-2">
    <?php if ($usuarioLogueado): ?>
        <div class="user-info">
            <span class="me-2">Bienvenido, <?php echo htmlspecialchars($usuarioLogueado); ?></span>
            <div class="user-menu">
                <a href="index.php?vista=logout" class="btn btn-danger btn-sm rounded shadow">Cerrar sesión</a>
            </div>
        </div>
        <a href="index.php?vista=carrito" class="btn btn-primary btn-sm rounded shadow">Carrito</a>
    <?php else: ?>
        <button class="btn btn-primary btn-sm me-2 rounded shadow" data-bs-toggle="modal" data-bs-target="#loginModal">Iniciar Sesión</button>
        <a href="index.php?vista=customer_new" class="btn btn-primary btn-sm me-2 rounded shadow">Registrarse</a>
    <?php endif; ?>
</div>

<!-- Modal de inicio de sesión -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Iniciar Sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" autocomplete="off">
                    <input type="hidden" name="vista_actual" value="<?php echo isset($_GET['vista']) ? htmlspecialchars($_GET['vista']) : 'tienda'; ?>">
                    <div class="mb-3">
                        <label for="login_usuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="login_usuario" name="login_usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required>
                    </div>
                    <div class="mb-3">
                        <label for="login_clave" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="login_clave" name="login_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                    </div>
                    <?php
                    if (isset($_POST['login_usuario']) && isset($_POST['login_clave'])) {
                        require_once "./modelo/Utils.php"; // Cargar Utils para usar la función de conexión
                        require_once "./php/iniciar_sesion.php"; // Incluir el archivo que maneja el inicio de sesión
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
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
                    <img src="<?php echo $imagen; ?>" alt="<?php echo $nombre; ?>" class="card-img-top">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $nombre; ?></h5>
                        <p class="card-text">$<?php echo number_format($precio, 3); ?></p>
                        <p class="card-text">Unidades Disponibles: <?php echo $stock; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex justify-content-center align-items-center">
                                <form method="post" action="php/agregar_carrito.php" class="d-flex" onsubmit="return verificarLogin();">
                                    <input type="hidden" name="vista_actual" value="vinos">
                                    <input type="hidden" name="producto_id" value="<?php echo $row['producto_id']; ?>">
                                    <input type="number" name="cantidad" min="1" max="<?php echo $row['producto_stock']; ?>" value="1" required class="form-control mx-2" style="width: 80px;">
                                    <button type="submit" class="btn btn-success">Agregar al Carrito</button>
                                </form>
                                <script>
                                    function verificarLogin() {
                                        if (!<?php echo json_encode($usuarioLogueado); ?>) {
                                            // Mostrar el modal si el usuario no está logueado
                                            var myModal = new bootstrap.Modal(document.getElementById('loginModal'));
                                            myModal.show();
                                            return false; // Evitar el envío del formulario
                                        }
                                        return true; // Permitir el envío del formulario
                                    }
                                </script>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>






