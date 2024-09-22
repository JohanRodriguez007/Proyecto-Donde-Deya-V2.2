<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Iniciar el almacenamiento en búfer de salida
ob_start();

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

// Incluir archivo de conexión
require_once '../modelo/Utils.php'; // Ajusta la ruta según sea necesario

// Obtener la conexión PDO
$conn = Utils::conexion();

try {
    // Obtener el ID del pedido desde la solicitud POST
    $pedido_id = $_POST['pedido_id'];

    // Comenzar la transacción
    $conn->beginTransaction();

    // Obtener detalles del pedido
    $sql = "SELECT p.*, u.usuario_nombre, u.usuario_email FROM pedidos p
            JOIN usuario u ON p.usuario_id = u.usuario_id
            WHERE p.pedido_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    // Actualizar el estado del pedido en la base de datos
    $sql = "UPDATE pedidos SET estado = 'Aprobado' WHERE pedido_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pedido_id]);

    // Obtener detalles de los productos
    $sql_detalle = "SELECT pd.producto_id, pr.producto_nombre, pd.cantidad, pd.total, pr.producto_stock, pr.producto_precio, pr.producto_foto
                    FROM detalle_pedido pd
                    JOIN producto pr ON pd.producto_id = pr.producto_id
                    WHERE pd.pedido_id = ?";
    $stmt_detalle = $conn->prepare($sql_detalle);
    $stmt_detalle->execute([$pedido_id]);
    $detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

    // Reducir el stock de cada producto, verificando que el stock sea suficiente
    foreach ($detalles as $detalle) {
        $producto_id = $detalle['producto_id'];
        $cantidad = $detalle['cantidad'];
        $stock_actual = $detalle['producto_stock'];

        if ($stock_actual >= $cantidad) {
            // Actualizar el stock en la tabla producto
            $sqlUpdateStock = "UPDATE producto SET producto_stock = producto_stock - :cantidad WHERE producto_id = :producto_id";
            $stmtUpdateStock = $conn->prepare($sqlUpdateStock);
            $stmtUpdateStock->bindParam(':cantidad', $cantidad);
            $stmtUpdateStock->bindParam(':producto_id', $producto_id);
            $stmtUpdateStock->execute();
        } else {
            // Si no hay suficiente stock, deshacer la transacción y mostrar error
            throw new Exception("No hay suficiente stock para el producto: {$detalle['producto_nombre']}. Stock actual: {$stock_actual}, Cantidad solicitada: {$cantidad}");
        }
    }

    // Insertar en la tabla ventas
    $sql_ventas = "INSERT INTO ventas (pedido_id, usuario_id, fecha_pedido, direccion, metodo_pago, total)
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_ventas = $conn->prepare($sql_ventas);
    $stmt_ventas->execute([
        $pedido_id,
        $pedido['usuario_id'],
        $pedido['fecha_pedido'],
        $pedido['direccion'],
        $pedido['metodo_pago'],
        $pedido['total']
    ]);

    // Confirmar la transacción
    $conn->commit();

    // Enviar correo al cliente
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF; 
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'depruebac310@gmail.com';
        $mail->Password = 'vjyeitfrmywcqgoz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('depruebac310@gmail.com', 'Cigarreria Donde Deya');
        $mail->addAddress($pedido['usuario_email']);

        $mail->isHTML(true);
        $mail->Subject = 'Tu Pedido ha sido Aprobado';
        $mail->Body = "<h2>Estimado/a {$pedido['usuario_nombre']},</h2>
                       <p>Tu pedido ha sido aprobado y está en camino.</p>
                       <h3>Detalles del Pedido:</h3>
                       <p><strong>Dirección:</strong> {$pedido['direccion']}</p>
                       <p><strong>Método de Pago:</strong> {$pedido['metodo_pago']}</p>
                       <p><strong>Fecha del Pedido:</strong> {$pedido['fecha_pedido']}</p>
                       <p><strong>Total del Pedido:</strong> $" . number_format($pedido['total'], 3) . "</p>
                       <h4>Detalles de los Productos:</h4>
                       <ul>";
        foreach ($detalles as $detalle) {
            $mail->Body .= "<li>{$detalle['producto_nombre']} - Cantidad: {$detalle['cantidad']} - Total: $" . number_format($detalle['total'], 3) . "</li>";
        }
        $mail->Body .= "</ul>
                        <p>Gracias por tu compra.</p>
                        <p>Saludos,</p>
                        <p>Cigarreria Donde Deya</p>";

        $mail->send();

        // Redirigir a la vista de pedidos_admin
        header('Location: ../index.php?vista=pedidos_admin');
        exit();
    } catch (Exception $e) {
        echo 'Error al enviar el correo: ' . $mail->ErrorInfo;
    }

} catch (Exception $e) {
    // Si algo falla, deshacer la transacción
    $conn->rollBack();
    echo 'Error al aprobar el pedido: ' . $e->getMessage();
}

// Finalizar el almacenamiento en búfer de salida
ob_end_flush();
?>







