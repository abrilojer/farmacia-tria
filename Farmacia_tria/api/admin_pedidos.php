<?php
header("Content-Type: application/json");
include_once '../config/database.php';

$db = (new Database())->getConnection();

$query = "SELECT p.*, u.correo as usuario_correo, mp.nombre_metodo as metodo_pago
          FROM pedidos p
          LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
          LEFT JOIN pagos pag ON p.id_pedido = pag.id_pedido
          LEFT JOIN metodos_pago mp ON pag.id_metodo_pago = mp.id_metodo_pago
          ORDER BY p.fecha_pedido DESC";

$stmt = $db->query($query);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>