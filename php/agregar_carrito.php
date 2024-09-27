<?php
// Incluir archivo de conexión y sesión
require_once '../modelo/Utils.php';

// Iniciar sesión
session_name("INV");
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php?vista=login");
    exit();
}

// Obtener datos del formulario
$producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
$vista_actual = isset($_POST['vista_actual']) ? $_POST['vista_actual'] : 'vinos'; // Vista predeterminada
$usuario_id = $_SESSION['id'];

// Verificar que los datos son válidos
if ($producto_id <= 0 || $cantidad <= 0) {
    $_SESSION['error'] = "Datos inválidos para agregar al carrito.";
    header("Location: ../index.php?vista=$vista_actual");
    exit();
}

// Obtener la conexión PDO instanciando a la clase()
$conn = Utils::conexion();

try {
    $conn->beginTransaction();

    // Primero, verificar si el producto ya está en el carrito del usuario
    $sql = "SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario_id, $producto_id]);
    $producto_en_carrito = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto_en_carrito) {
        // Si el producto ya está en el carrito, actualizar la cantidad
        $nueva_cantidad = $producto_en_carrito['cantidad'] + $cantidad;

        // Actualizar la cantidad en el carrito
        $sql_actualizar = "UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?";
        $stmt_actualizar = $conn->prepare($sql_actualizar);
        $stmt_actualizar->execute([$nueva_cantidad, $usuario_id, $producto_id]);
    } else {
        // Si el producto no está en el carrito, insertarlo
        $sql_insertar = "INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)";
        $stmt_insertar = $conn->prepare($sql_insertar);
        $stmt_insertar->execute([$usuario_id, $producto_id, $cantidad]);
    }

    // Restar del stock
    $sql_update_stock = "UPDATE producto SET producto_stock = producto_stock - ? WHERE producto_id = ?";
    $stmt_update_stock = $conn->prepare($sql_update_stock);
    $stmt_update_stock->execute([$cantidad, $producto_id]);

    // Confirmar transacción
    $conn->commit();

    // Guardar mensaje de éxito en la sesión
    $_SESSION['success'] = "Producto agregado al carrito.";
    header("Location: ../index.php?vista=$vista_actual");
    exit();
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollBack();
    $_SESSION['error'] = "Error al agregar el producto al carrito: " . $e->getMessage();
    header("Location: ../index.php?vista=$vista_actual");
    exit();
}
?>






















