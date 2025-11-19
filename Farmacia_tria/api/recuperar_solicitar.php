<?php
header("Content-Type: application/json");

$correo = isset($_POST['correo']) ? $_POST['correo'] : '';

if (empty($correo)) {
    die(json_encode(['success' => false, 'message' => 'Correo no proporcionado']));
}

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo");
    $stmt->execute([':correo' => $correo]);

    if ($stmt->rowCount() == 0) {
        die(json_encode(['success' => false, 'message' => 'Usuario no encontrado']));
    }

    $codigo = sprintf("%06d", mt_rand(1, 999999));
    $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmt = $db->prepare("INSERT INTO codigos_recuperacion (correo, codigo, expiracion) VALUES (:correo, :codigo, :expiracion)");
    $stmt->execute([':correo' => $correo, ':codigo' => $codigo, ':expiracion' => $expiracion]);

    die(json_encode(['success' => true, 'message' => 'Código generado', 'codigo' => $codigo]));

} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]));
}