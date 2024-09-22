<?php
require_once 'Utils.php';

class ProductoModel extends Utils {
    private $pdo;

    public function __construct() {
        $this->pdo = self::conexion(); // Usar la función de conexión
    }

    public function listarProductosPorCategoria($categoria_id) {
        $sql = "SELECT producto_codigo, producto_nombre, producto_precio, producto_stock, producto_foto, producto_id FROM producto WHERE categoria_id = :categoria_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':categoria_id', $categoria_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
