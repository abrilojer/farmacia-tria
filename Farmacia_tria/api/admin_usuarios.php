<?php
header("Content-Type: application/json");
include_once '../config/database.php';

$db = (new Database())->getConnection();

$stmt = $db->query("SELECT * FROM usuarios ORDER BY fecha_registro DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>