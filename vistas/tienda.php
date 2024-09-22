<?php
$usuarioLogueado = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;
?>

<body>

    <div class="d-flex justify-content-end position-fixed top-0 end-0 m-3 bg-light rounded shadow p-2">
         <?php if ($usuarioLogueado): ?>
             <!-- Recuadro oculto que se muestra al pasar el mouse -->
                <div class="user-info">
                <span class="me-2">Bienvenido, <?php echo htmlspecialchars($usuarioLogueado); ?></span>
                <div class="user-menu">
                <a href="index.php?vista=logout" class="btn btn-danger btn-sm rounded shadow">Cerrar sesión</a>
            </div>
        </div>
        <a href="index.php?vista=carrito" class="btn btn-primary btn-sm rounded shadow">Carrito</a>
    <?php else: ?>
        <!-- Mostrar los botones de login y registro si no está logueado -->
        <a href="index.php?vista=login" class="btn btn-primary btn-sm me-2 rounded shadow">Iniciar Sesión</a>
        <a href="index.php?vista=customer_new" class="btn btn-primary btn-sm me-2 rounded shadow">Registrarse</a>
    <?php endif; ?>
</div>

        <div class="container-icon">
            <div class="title">Sobre Nosotros...</div>                      
        </div>
 
        <div class="container-slider">
    <div class="slider">
        <!-- Radio buttons to navigate slides -->
        <input type="radio" name="slider" id="slide-uno" checked>
        <input type="radio" name="slider" id="slide-dos">
        <input type="radio" name="slider" id="slide-tres">

        <!-- Buttons to navigate slides -->
        <div class="buttons">
            <label for="slide-uno"></label>
            <label for="slide-dos"></label>
            <label for="slide-tres"></label>
        </div>

        <!-- Slider content -->
        <div class="content-slider">
            <!-- First slide (Keep as is) -->
            <div class="primer-slide">
                <h1></h1>
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
                <p>Direccion: Calle 127 abis No 90b-30</p>
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
                <p>Contacto: 311 391 1473</p>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>
    

      <footer class="personal-info">
       
        <div class="container-info">

            <div class="info">

                <p>Direccion: Calle 127 abis No 90b-30</p>
                <p>Contacto: 311 391 1473</p>
                <p>Correo: linita5marcelita@gmail.com</p>
                <p>&copy;2024 Cigarrería Donde Deya, Derechos Reservados</p>


            </div>
        </div>

        
    </footer>

    <script src="comprar-productos.js"></script>
       
</body>
</html>