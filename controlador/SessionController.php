<?php
class SessionController {
    private $tiempo_inactividad = 900; // 15 minutos

    public function startSession() {
        session_name("INV");
        session_start();

        // Verificar si existe la variable de tiempo de última actividad
        if (isset($_SESSION['ultimo_acceso'])) {
            $inactividad = time() - $_SESSION['ultimo_acceso'];

            if ($inactividad > $this->tiempo_inactividad) {
                $this->destroySession();
            }
        }

        // Actualizar el tiempo de último acceso
        $_SESSION['ultimo_acceso'] = time();
    }

    private function destroySession() {
        session_unset();
        session_destroy();
        // Redirigir a la vista de tienda en caso de caducidad de sesión
        if (basename($_SERVER['PHP_SELF']) !== 'index.php' || (isset($_GET['vista']) && $_GET['vista'] !== 'tienda')) {
            header("Location: index.php?vista=tienda&timeout=1");
            exit();
        }
    }

    public function checkProtectedView($vista_actual) {
        $vista_protegida = [
            "login", "home", "404", 
            "category_list", "category_new", "category_search", "category_update", "logout", 
            "product_category", "product_img", "product_list", "product_new", "product_search", 
            "product_update", "user_list", "user_new", "user_search", "user_update", "pedidos_admin", "ventas"
        ];

        // Verificar si se intenta acceder a una vista protegida
        if (in_array($vista_actual, $vista_protegida) && !isset($_SESSION['id']) && $vista_actual !== 'login' && $vista_actual !== 'tienda') {
            header("Location: index.php?vista=tienda&timeout=1");
            exit();
        }
    }
}
