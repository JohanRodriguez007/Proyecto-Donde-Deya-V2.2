<?php
// Incluir archivo de conexión
require_once "./modelo/Utils.php"; // Incluir archivo de utilidades

// Configurar la localización para que los meses se muestren en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain', 'Spanish');

// Obtener la conexión PDO
$conn = Utils::conexion(); // Instanciar la conexión

// Definir el número de registros por página
$registros = 15; // Cambia esto según tu necesidad
$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Página actual
$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0; // Calcular el inicio

try {
    // Obtener el total de ventas para paginación
    $sql_total = "SELECT COUNT(venta_id) FROM ventas";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->execute();
    $total_ventas_count = (int)$stmt_total->fetchColumn(); // Total de registros
    
    // Obtener las ventas agrupadas por día con paginación
    $sql = "SELECT v.venta_id, v.pedido_id, v.usuario_id, u.usuario_nombre, v.direccion, v.metodo_pago, DATE(v.fecha_pedido) AS fecha_pedido, v.total
            FROM ventas v
            JOIN usuario u ON v.usuario_id = u.usuario_id
            ORDER BY fecha_pedido DESC
            LIMIT :inicio, :registros";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->bindParam(':registros', $registros, PDO::PARAM_INT);
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener el valor total de todas las ventas
    $sql_total_sum = "SELECT SUM(total) AS total_ventas FROM ventas";
    $stmt_total_sum = $conn->prepare($sql_total_sum);
    $stmt_total_sum->execute();
    $resultado_total = $stmt_total_sum->fetch(PDO::FETCH_ASSOC);
    
    // Obtener el valor total de ventas
    $total_ventas = $resultado_total['total_ventas'];

    // Agrupar las ventas por día y calcular el total de cada día
    $ventas_por_fecha = [];
    foreach ($ventas as $venta) {
        $fecha = $venta['fecha_pedido']; // Ya solo contiene la fecha sin la hora
        if (!isset($ventas_por_fecha[$fecha])) {
            $ventas_por_fecha[$fecha] = [
                'ventas' => [],
                'total_ventas_dia' => 0
            ];
        }
        $ventas_por_fecha[$fecha]['ventas'][] = $venta;
        $ventas_por_fecha[$fecha]['total_ventas_dia'] += $venta['total'];
    }

} catch (PDOException $e) {
    echo '<div class="message_error">Error al obtener las ventas: ' . $e->getMessage() . '</div>';
    exit();
}

// Calcular el número total de páginas
$Npaginas = ceil($total_ventas_count / $registros);
?>

<body>
    <div class="container custom-container">
        <h1 class="title custom-title">Ventas</h1>

        <!-- Mostrar el valor total de las ventas -->
        <div class="notification is-primary">
            <strong>Valor Total de las Ventas:</strong> $<?php echo number_format($total_ventas, 3); ?>
        </div>

        <?php if (!empty($ventas_por_fecha)): ?>
            <?php foreach ($ventas_por_fecha as $fecha => $data): ?>
                <?php
                // Convertir la fecha al formato "7 de octubre de 2024"
                $fecha_formateada = strftime('%e de %B de %Y', strtotime($fecha));
                ?>
                <h2 class="subtitle custom-subtitle">Ventas del <?php echo htmlspecialchars($fecha_formateada); ?></h2>
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
                        <?php foreach ($data['ventas'] as $venta): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($venta['usuario_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($venta['direccion']); ?></td>
                                <td><?php echo htmlspecialchars($venta['metodo_pago']); ?></td>
                                <td><?php echo htmlspecialchars($venta['fecha_pedido']); ?></td>
                                <td>$<?php echo number_format($venta['total'], 3); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="has-text-right"><strong>Total de ventas para <?php echo htmlspecialchars($fecha_formateada); ?>:</strong></td>
                            <td><strong>$<?php echo number_format($data['total_ventas_dia'], 3); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="notification is-info">
                No hay ventas para mostrar.
            </div>
        <?php endif; ?>

        <!-- Generar el paginador -->
        <?php if ($Npaginas > 1): ?>
            <nav class="pagination" role="navigation" aria-label="pagination">
                <a class="pagination-previous" href="<?php echo "index.php?vista=ventas&page=" . max(1, $pagina - 1); ?>">Anterior</a>
                <a class="pagination-next" href="<?php echo "index.php?vista=ventas&page=" . min($Npaginas, $pagina + 1); ?>">Siguiente</a>
                <ul class="pagination-list">
                    <?php for ($i = 1; $i <= $Npaginas; $i++): ?>
                        <li>
                            <a class="pagination-link <?php echo ($i == $pagina) ? 'is-current' : ''; ?>" href="<?php echo "index.php?vista=ventas&page=$i"; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    
    <?php require_once "./inc/footer_V2.php"; ?>
</body>
</html>








