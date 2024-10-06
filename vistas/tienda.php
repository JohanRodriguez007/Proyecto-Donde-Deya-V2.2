<?php
$usuarioLogueado = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;
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

<div class="container">
    <div class="title">Sobre Nosotros...</div>
</div>

<div class="container-slider">
    <div class="slider">
        <input type="radio" name="slider" id="slide-uno" checked>
        <input type="radio" name="slider" id="slide-dos">
        <input type="radio" name="slider" id="slide-tres">
        <div class="buttons">
            <label for="slide-uno"></label>
            <label for="slide-dos"></label>
            <label for="slide-tres"></label>
        </div>
        <div class="content-slider">
            <div class="primer-slide text-container">
                <h1>¡Bienvenido a Donde Deya!</h1>
            </div>

            <div class="segundo-slide">
                <div class="product-info">
                    <h1>Nuestros Productos</h1>
                    <div class="product-list">
                        <div class="product-item">
                            <img src="./css/imagenes_referencia/Referencia1.jpg" alt="Cerveza" class="product-image">
                            <div class="product-details">
                                <p class="product-name">Cerveza</p>
                                <p class="product-description">Cerveza Nacional</p>
                            </div>
                        </div>
                        <div class="product-item">
                            <img src="./css/imagenes_referencia/Referencia-Whisky4.png" alt="Whiskey" class="product-image">
                            <div class="product-details">
                                <p class="product-name">Whiskey</p>
                                <p class="product-description">Whiskey de alta calidad</p>
                            </div>
                        </div>
                        <div class="product-item">
                            <img src="./css/imagenes_referencia/Referencia-Vino1.png" alt="Vino" class="product-image">
                            <div class="product-details">
                                <p class="product-name">Vino</p>
                                <p class="product-description">Vino Selecto</p>
                            </div>
                        </div>
                        <div class="product-item">
                            <img src="./css/imagenes_referencia/Referencia-aguardiente6.png" alt="Aguardiente" class="product-image">
                            <div class="product-details">
                                <p class="product-name">Aguardiente</p>
                                <p class="product-description">Aguardiente tradicional</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tercer-slide">
                <div class="contact-info">
                    <h1>Contacto</h1>
                    <div class="contact-details">
                        <div class="contact-item">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="contact-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7z" />
                            </svg>
                            <p>Dirección: Calle 127 abis No 90b-30</p>
                        </div>
                        <div class="contact-item">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="contact-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4z" />
                            </svg>
                            <p>Correo: linita5marcelita@gmail.com</p>
                        </div>
                        <div class="contact-item">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="contact-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4z" />
                            </svg>
                            <p>Teléfono: 123456789</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
