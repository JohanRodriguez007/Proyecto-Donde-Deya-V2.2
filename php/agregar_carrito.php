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
    header("Location: ../index.php?vista=$vista_actual&error=Datos inválidos para agregar al carrito.");
    exit();
}

// Obtener la conexión PDO instanciando a la clase()
$conn = Utils::conexion();

// Verificar la cantidad disponible en stock
$sql_stock = "SELECT producto_stock FROM producto WHERE producto_id = ?";
$stmt_stock = $conn->prepare($sql_stock);
$stmt_stock->execute([$producto_id]);
$stock = $stmt_stock->fetchColumn();

if ($cantidad > $stock) {
    header("Location: ../index.php?vista=$vista_actual&error=La cantidad solicitada excede el stock disponible.");
    exit();
}

// Primero, verificar si el producto ya está en el carrito del usuario
$sql = "SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$usuario_id, $producto_id]);
$producto_en_carrito = $stmt->fetch(PDO::FETCH_ASSOC);

if ($producto_en_carrito) {
    // Si el producto ya está en el carrito, actualizar la cantidad
    $nueva_cantidad = $producto_en_carrito['cantidad'] + $cantidad;

    // Verificar nuevamente el stock disponible para la nueva cantidad
    if ($nueva_cantidad > $stock) {
        header("Location: ../index.php?vista=$vista_actual&error=La cantidad total en el carrito excede el stock disponible.");
        exit();
    }

    $sql_actualizar = "UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?";
    $stmt_actualizar = $conn->prepare($sql_actualizar);
    $stmt_actualizar->execute([$nueva_cantidad, $usuario_id, $producto_id]);
} else {
    // Si el producto no está en el carrito, insertarlo
    $sql_insertar = "INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)";
    $stmt_insertar = $conn->prepare($sql_insertar);
    $stmt_insertar->execute([$usuario_id, $producto_id, $cantidad]);
}

// Redirigir a la vista desde la cual se realizó la solicitud con mensaje de éxito
header("Location: ../index.php?vista=$vista_actual&success=Producto agregado al carrito.");
exit();
?>









