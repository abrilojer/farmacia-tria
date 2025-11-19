<?php
header("Content-Type: application/json");
include_once '../config/database.php';

$pedido_id = $_GET['pedido_id'];

$db = (new Database())->getConnection();

// Información del pedido
$query = "SELECT * FROM pedidos WHERE id_pedido = :pedido_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':pedido_id', $pedido_id);
$stmt->execute();
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

// Productos del pedido
$query = "SELECT dp.*, p.nombre_producto
          FROM detalle_pedidos dp
          INNER JOIN productos p ON dp.id_producto = p.id_producto
          WHERE dp.id_pedido = :pedido_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':pedido_id', $pedido_id);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Información del pago
$query = "SELECT pag.*, mp.nombre_metodo as metodo_pago
          FROM pagos pag
          LEFT JOIN metodos_pago mp ON pag.id_metodo_pago = mp.id_metodo_pago
          WHERE pag.id_pedido = :pedido_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':pedido_id', $pedido_id);
$stmt->execute();
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'pedido' => $pedido,
    'productos' => $productos,
    'pago' => $pago ?: ['metodo_pago' => 'N/A', 'dni' => 'N/A']
]);
?>