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
    // Obtener el ID del pedido y la acción (aprobar o rechazar) desde la solicitud POST
    $pedido_id = isset($_POST['pedido_id']) ? $_POST['pedido_id'] : null;
    $accion = isset($_POST['accion']) ? $_POST['accion'] : null;

    // Verificar que el pedido_id y la acción son válidos
    if (!$pedido_id || !$accion) {
        throw new Exception('ID del pedido o acción no válidos.');
    }

    // Comenzar la transacción
    $conn->beginTransaction();

    // Obtener detalles del pedido
    $sql = "SELECT p.*, u.usuario_nombre, u.usuario_email FROM pedidos p
            JOIN usuario u ON p.usuario_id = u.usuario_id
            WHERE p.pedido_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new Exception('Pedido no encontrado.');
    }

    // Verificar si la acción es aprobar o rechazar el pedido
    if ($accion === 'aprobar') {
        // Actualizar el estado del pedido a 'Aprobado'
        $sql = "UPDATE pedidos SET estado = 'Aprobado' WHERE pedido_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$pedido_id]);

        $asunto = 'Tu Pedido ha sido Aprobado';
        $mensaje = "<h2>Estimado/a {$pedido['usuario_nombre']},</h2>
                    <p>Tu pedido ha sido aprobado y está en camino.</p>";
    } elseif ($accion === 'rechazar') {
        // Actualizar el estado del pedido a 'Rechazado'
        $sql = "UPDATE pedidos SET estado = 'Rechazado' WHERE pedido_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$pedido_id]);

        $asunto = 'Tu Pedido ha sido Rechazado';
        $mensaje = "<h2>Estimado/a {$pedido['usuario_nombre']},</h2>
                    <p>Lamentamos informarte que tu pedido ha sido rechazado.</p>";

        // Obtener detalles de los productos del pedido para restablecer el stock
        $sql_detalle = "SELECT pd.producto_id, pd.cantidad FROM detalle_pedido pd
                        WHERE pd.pedido_id = ?";
        $stmt_detalle = $conn->prepare($sql_detalle);
        $stmt_detalle->execute([$pedido_id]);
        $detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

        // Restablecer el stock de los productos
        foreach ($detalles as $detalle) {
            // Obtener el stock actual del producto
            $sql_stock = "SELECT producto_stock FROM producto WHERE producto_id = ?";
            $stmt_stock = $conn->prepare($sql_stock);
            $stmt_stock->execute([$detalle['producto_id']]);
            $producto = $stmt_stock->fetch(PDO::FETCH_ASSOC);

            // Calcular el nuevo stock
            $nuevo_stock = $producto['producto_stock'] + $detalle['cantidad'];

            // Actualizar el stock del producto
            $sql_update_stock = "UPDATE producto SET producto_stock = ? WHERE producto_id = ?";
            $stmt_update_stock = $conn->prepare($sql_update_stock);
            $stmt_update_stock->execute([$nuevo_stock, $detalle['producto_id']]);
        }
    } else {
        throw new Exception('Acción no válida.');
    }

    // Obtener detalles de los productos del pedido
    $sql_detalle = "SELECT pd.producto_id, pr.producto_nombre, pd.cantidad, pd.total, pr.producto_foto
                    FROM detalle_pedido pd
                    JOIN producto pr ON pd.producto_id = pr.producto_id
                    WHERE pd.pedido_id = ?";
    $stmt_detalle = $conn->prepare($sql_detalle);
    $stmt_detalle->execute([$pedido_id]);
    $detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

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
        $mail->Subject = $asunto;
        $mail->Body = $mensaje . "
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
    echo 'Error al procesar el pedido: ' . $e->getMessage();
}

// Finalizar el almacenamiento en búfer de salida
ob_end_flush();
?>











