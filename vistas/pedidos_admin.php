<?php 
// Incluir archivo de conexión
require_once "./modelo/Utils.php"; // Incluir archivo de utilidades


// Obtener la conexión PDO
$conn = Utils::conexion(); // Instanciar la conexión

try {
    // Obtener los pedidos pendientes
    $sql = "SELECT p.pedido_id, p.usuario_id, u.usuario_nombre, p.direccion, p.metodo_pago, p.fecha_pedido
            FROM pedidos p
            JOIN usuario u ON p.usuario_id = u.usuario_id
            WHERE p.estado = 'Pendiente'
            ORDER BY p.fecha_pedido DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el detalle de cada pedido y calcular el total
    foreach ($pedidos as $key => $pedido) { // Cambia la referencia a un índice
        $pedido_id = $pedido['pedido_id'];
        $sql_detalle = "SELECT pd.producto_id, pr.producto_nombre, pd.cantidad, pr.producto_precio, pr.producto_foto
                        FROM detalle_pedido pd
                        JOIN producto pr ON pd.producto_id = pr.producto_id
                        WHERE pd.pedido_id = ?";
        $stmt_detalle = $conn->prepare($sql_detalle);
        $stmt_detalle->execute([$pedido_id]);
        $pedidos[$key]['detalle'] = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC); // Accede a $pedidos con el índice

        // Calcular el total del pedido
        $total = 0;
        foreach ($pedidos[$key]['detalle'] as $detalle) {
            $total += $detalle['cantidad'] * $detalle['producto_precio'];
        }
        $pedidos[$key]['total'] = $total;
    }

} catch (PDOException $e) {
    echo '<div class="message_error">Error al obtener los pedidos: ' . $e->getMessage() . '</div>';
    exit();
}
?>


<body>
    <div class="container custom-container">
        <h1 class="title custom-title">Gestión de Pedidos</h1>

        <?php if (count($pedidos) > 0): ?>
            <table class="table is-striped is-bordered is-hoverable">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Dirección</th>
                        <th>Método de Pago</th>
                        <th>Fecha del Pedido</th>
                        <th>Detalles</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['metodo_pago']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                            <td>
                                <ul>
                                    <?php if (isset($pedido['detalle']) && count($pedido['detalle']) > 0): ?>
                                        <?php foreach ($pedido['detalle'] as $detalle): ?>
                                            <li>
                                                <?php echo htmlspecialchars($detalle['producto_nombre']) . " - " . htmlspecialchars($detalle['cantidad']) . " x $" . number_format($detalle['producto_precio'], 2) . " (Total: $" . number_format($detalle['cantidad'] * $detalle['producto_precio'], 3) . ")"; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li>No hay detalles disponibles.</li>
                                    <?php endif; ?>
                                </ul>
                            </td>
                            <td><?php echo number_format($pedido['total'], 3); ?></td>
                            <td>
                                <form method="post" action="./php/aprobar_pedido.php">
                                    <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido['pedido_id']); ?>">
                                    <button type="submit" class="button is-success">Aprobar Pedido</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="notification is-info">
                No hay pedidos para mostrar.
            </div>
        <?php endif; ?>
    </div>

    <?php require_once "./inc/footer.php"; ?>
</body>
</html>















