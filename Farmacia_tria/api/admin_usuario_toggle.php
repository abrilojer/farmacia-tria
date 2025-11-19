<?php
header("Content-Type: application/json");
include_once '../config/database.php';

$db = (new Database())->getConnection();
$id = $_GET['id'];

$stmt = $db->prepare("UPDATE usuarios SET activo = NOT activo WHERE id_usuario = :id");
$stmt->execute([':id' => $id]);

echo json_encode(['message' => 'Usuario actualizado']);
?>