<?php
// Incluir archivo de conexión
require_once "./modelo/Utils.php"; // Incluir archivo de utilidades

// Obtener la conexión PDO
$conn = Utils::conexion(); // Instanciar la conexión

try {
    // Obtener las ventas
    $sql = "SELECT v.venta_id, v.pedido_id, v.usuario_id, u.usuario_nombre, v.direccion, v.metodo_pago, v.fecha_pedido, v.total
            FROM ventas v
            JOIN usuario u ON v.usuario_id = u.usuario_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para obtener el total de todas las ventas
    $sql_total = "SELECT SUM(total) AS total_ventas FROM ventas";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->execute();
    $resultado_total = $stmt_total->fetch(PDO::FETCH_ASSOC);
    
    // Obtener el valor total de ventas
    $total_ventas = $resultado_total['total_ventas'];

} catch (PDOException $e) {
    echo '<div class="message_error">Error al obtener las ventas: ' . $e->getMessage() . '</div>';
    exit();
}
?>

<body>
    <div class="container custom-container">
        <h1 class="title custom-title">Ventas</h1>

        <!-- Mostrar el valor total de las ventas -->
        <div class="notification is-primary">
            <strong>Valor Total de las Ventas:</strong> $<?php echo number_format($total_ventas, 3); ?>
        </div>

        <?php if (count($ventas) > 0): ?>
            <table class="table is-striped is-bordered is-hoverable">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Dirección</th>
                        <th>Método de Pago</th>
                        <th>Fecha del Pedido</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $venta): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($venta['usuario_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($venta['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($venta['metodo_pago']); ?></td>
                            <td><?php echo htmlspecialchars($venta['fecha_pedido']); ?></td>
                            <td><?php echo number_format($venta['total'], 3); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="notification is-info">
                No hay ventas para mostrar.
            </div>
        <?php endif; ?>
    </div>
    
    <?php require_once "./inc/footer.php"; ?>
    
</body>
</html>

