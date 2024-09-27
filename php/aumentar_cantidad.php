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
$vista_actual = isset($_POST['vista_actual']) ? $_POST['vista_actual'] : 'vinos'; // Vista predeterminada
$usuario_id = $_SESSION['id'];

// Verificar que el producto_id es válido
if ($producto_id <= 0) {
    $_SESSION['error'] = "Datos inválidos para aumentar la cantidad.";
    header("Location: ../index.php?vista=$vista_actual");
    exit();
}

// Obtener la conexión PDO
$conn = Utils::conexion();

// Verificar el stock del producto
$sql_stock = "SELECT producto_stock FROM producto WHERE producto_id = ?";
$stmt_stock = $conn->prepare($sql_stock);
$stmt_stock->execute([$producto_id]);
$producto_stock = $stmt_stock->fetchColumn();

// Verificar si hay suficiente stock para agregar al carrito
if ($producto_stock <= 0) {
    // Si no hay stock disponible, redirigir con mensaje de error
    $_SESSION['error'] = "No puedes agregar más productos. No hay stock disponible.";
    header("Location: ../index.php?vista=carrito");
    exit();
}

try {
    $conn->beginTransaction();

    // Verificar si el producto ya está en el carrito del usuario
    $sql = "SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario_id, $producto_id]);
    $producto_en_carrito = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto_en_carrito) {
        // Si el producto ya está en el carrito, aumentar la cantidad
        $nueva_cantidad = $producto_en_carrito['cantidad'] + 1;

        // Actualizar la cantidad en el carrito
        $sql_actualizar = "UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?";
        $stmt_actualizar = $conn->prepare($sql_actualizar);
        $stmt_actualizar->execute([$nueva_cantidad, $usuario_id, $producto_id]);

        // Restar del stock
        $sql_update_stock = "UPDATE producto SET producto_stock = producto_stock - 1 WHERE producto_id = ?";
        $stmt_update_stock = $conn->prepare($sql_update_stock);
        $stmt_update_stock->execute([$producto_id]);
    }

    // Confirmar transacción
    $conn->commit();

    // Guardar mensaje de éxito en la sesión
    $_SESSION['success'] = "Cantidad aumentada en el carrito.";
    header("Location: ../index.php?vista=$vista_actual");
    exit();
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollBack();
    $_SESSION['error'] = "Error al aumentar la cantidad: " . $e->getMessage();
    header("Location: ../index.php?vista=$vista_actual");
    exit();
}
?>





