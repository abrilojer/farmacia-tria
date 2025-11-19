<?php
header("Content-Type: application/json");
include_once '../config/database.php';

$db = (new Database())->getConnection();

// Total ventas
$stmt = $db->query("SELECT COALESCE(SUM(total), 0) as total FROM pedidos");
$totalVentas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total pedidos
$stmt = $db->query("SELECT COUNT(*) as total FROM pedidos");
$totalPedidos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total productos
$stmt = $db->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
$totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total usuarios
$stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
$totalUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

echo json_encode([
    'total_ventas' => $totalVentas,
    'total_pedidos' => $totalPedidos,
    'total_productos' => $totalProductos,
    'total_usuarios' => $totalUsuarios
]);
?>