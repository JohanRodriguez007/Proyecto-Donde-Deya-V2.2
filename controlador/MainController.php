<?php
require_once "SessionController.php";

class MainController {
    public function handleRequest() {
        $vista = isset($_GET['vista']) ? $_GET['vista'] : "tienda";
        
        // Comprobar si la vista es una de las de la tienda
        if (in_array($vista, ['tienda', 'cerveza', 'vinos', 'whiskey', 'aguardiente', 'mecato', 'carrito', 'pedidos'])) {
            include_once "./inc/headTienda.php";
        } else {
            include_once "./inc/head.php";
        }

        // Mostrar un mensaje si la sesión ha caducado
        if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
            echo '<div class="message_error"><strong>¡Sesión caducada!</strong><br>Por favor, inicia sesión nuevamente.</div>';
        }

        $vistas_permitidas = [
            "tienda", "cerveza", "vinos", "whiskey", "aguardiente", "mecato",
            "login", "home", "404", "category_list", "category_new", "category_search",
            "category_update", "logout", "product_category", "product_img",
            "product_list", "product_new", "product_search", "product_update",
            "user_list", "user_new", "user_search", "user_update",
            "customer_new", "activate_customer", "carrito", "compra_exitosa",
            "pedidos", "pedidos_admin", "ventas"
        ];

        // Verificar si la vista es válida
        if (!in_array($vista, $vistas_permitidas)) {
            $vista = "404";
        }

        // Incluir la barra de navegación si no es la vista de login
        if ($vista !== "login" && $vista !== "customer_new" && $vista !== "activate_customer" && $vista !== "compra_exitosa") {
            if (in_array($vista, ['tienda', 'cerveza', 'vinos', 'whiskey', 'aguardiente', 'mecato', 'carrito', 'pedidos'])) {
                include_once "./inc/navbarTienda.php";
            } else {
                include_once "./inc/navbar.php";
            }
        }

        // Incluir la vista correspondiente
        $vista_path = "./vistas/" . $vista . ".php";
        if (is_file($vista_path)) {
            include_once $vista_path;
        } else {
            include_once "./vistas/404.php";
        }

        // Incluir el script
        include_once "./inc/script.php";
    }
}
