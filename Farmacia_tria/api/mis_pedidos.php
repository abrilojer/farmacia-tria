<?php
header("Content-Type: application/json");
include_once '../config/database.php';

$usuario_id = $_GET['usuario_id'];

$db = (new Database())->getConnection();

$query = "SELECT p.*, mp.nombre_metodo as metodo_pago
          FROM pedidos p
          LEFT JOIN pagos pag ON p.id_pedido = pag.id_pedido
          LEFT JOIN metodos_pago mp ON pag.id_metodo_pago = mp.id_metodo_pago
          WHERE p.id_usuario = :usuario_id
          ORDER BY p.fecha_pedido DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':usuario_id', $usuario_id);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>