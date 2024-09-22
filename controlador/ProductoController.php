<?php
require_once './modelo/ProductoModel.php';

class ProductoController {
    private $model;

    public function __construct() {
        $this->model = new ProductoModel(); // Instanciar el modelo
    }

    public function listarProductos($categoria_id = null) {
        $productos = $this->model->listarProductosPorCategoria($categoria_id);
        return $productos;
    }
}

