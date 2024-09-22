<?php
require './inc/header.php'; // Incluir encabezado (si es necesario)

if (!isset($_SESSION['id'])) {
    header("Location: index.php?vista=login");
    exit();
}
?>

<div class="container mt-5">
    <div class="alert alert-success text-center" role="alert">
        <h1 class="display-4">¡Compra Exitosa!</h1>
        <p class="lead">Nos pondremos en contacto con usted para coordinar la entrega de su pedido.</p>
        <hr>
        <p class="mb-0">Gracias por su compra. Puede ver más productos en nuestra tienda.</p>
        <a href="index.php?vista=tienda" class="btn btn-primary mt-3">Volver a la tienda</a>
    </div>
</div>

<?php
require './inc/footer.php'; // Incluir pie de página (si es necesario)
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
