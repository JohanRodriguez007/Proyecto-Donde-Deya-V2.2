<div class="container is-fluid mb-6">
    <h1 class="title">Productos</h1>
    <h2 class="subtitle">Lista de productos</h2>
</div>

<div class="container pb-6 pt-6 has-background-white custom-container">
    <?php
        require_once "./modelo/Utils.php"; // Incluir archivo de utilidades
        $conn = Utils::conexion(); // Instanciar la conexión

        # Eliminar producto #
        if (isset($_GET['product_id_del'])) {
            require_once "./php/producto_eliminar.php";
        }

        if (!isset($_GET['page'])) {
            $pagina = 1;
        } else {
            $pagina = (int)$_GET['page'];
            if ($pagina <= 1) {
                $pagina = 1;
            }
        }

        $categoria_id = (isset($_GET['category_id'])) ? $_GET['category_id'] : 0;

        $pagina = Utils::limpiar_cadena($pagina);
        $url = "index.php?vista=product_list&page="; /* <== */
        $registros = 15;
        $busqueda = "";

        # Calcular el valor total del inventario #
        $totalInventario = 0;
        $productos = $conn->query("SELECT producto_precio, producto_stock FROM producto");
        if ($productos->rowCount() > 0) {
            foreach ($productos as $producto) {
                $totalInventario += $producto['producto_precio'] * $producto['producto_stock'];
            }
        }
        
        echo '<div class="notification is-info">El valor total de su inventario es: $' . number_format($totalInventario, 3, ',', '.') . '</div>';

        # Paginador producto #
        require_once "./php/producto_lista.php";
    ?>
</div>

<?php require_once "./inc/footer_V2.php"; ?>
