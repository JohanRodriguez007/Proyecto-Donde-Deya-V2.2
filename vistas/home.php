<?php

// Verificar si la sesión está activa
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    // Redirigir a la tienda si la sesión no está activa
    header("Location: index.php?vista=tienda&timeout=1");
    exit();
}
?>

<div class="container is-fluid">
  <main>
    <h1 class="title">Home</h1>
    <h2 class="subtitle">¡Bienvenido <?php echo htmlspecialchars($_SESSION['nombre']." ".$_SESSION['apellido']); ?>!</h2>
    <figure class="image is-fluid">
        <img src="./css/imagenes_referencia/tienda.jpg" style="max-width: 580px; max-height: 960px; object-fit: cover; margin: 20px;">
    </figure>
  </main>
</div>
<?php require_once "./inc/footer_V2.php"; ?>

