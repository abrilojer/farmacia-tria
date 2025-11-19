<?php
header("Content-Type: application/json");
include_once '../config/database.php';

$db = (new Database())->getConnection();
$id = $_GET['id'];

$stmt = $db->prepare("UPDATE productos SET activo = 0 WHERE id_producto = :id");
$stmt->execute([':id' => $id]);

echo json_encode(['message' => 'Producto eliminado']);
?>