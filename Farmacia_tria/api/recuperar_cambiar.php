<?php
error_reporting(0);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include_once '../config/database.php';

try {
    $correo = $_POST['correo'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    $nueva_password = $_POST['nueva_password'] ?? '';
    
    if (!$correo || !$codigo || !$nueva_password) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit();
    }

    $db = (new Database())->getConnection();

    // Verificar código nuevamente
    $stmt = $db->prepare("SELECT * FROM codigos_recuperacion 
                          WHERE correo = :correo 
                          AND codigo = :codigo 
                          AND usado = 0 
                          AND expiracion > NOW()
                          ORDER BY fecha_creacion DESC 
                          LIMIT 1");
    $stmt->execute([
        ':correo' => $correo,
        ':codigo' => $codigo
    ]);

    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Código inválido o expirado']);
        exit();
    }

    // Hash de la nueva contraseña
    $password_hash = password_hash($nueva_password, PASSWORD_BCRYPT);

    // Actualizar contraseña
    $stmt = $db->prepare("UPDATE usuarios SET contrasena = :password WHERE correo = :correo");
    $stmt->execute([
        ':password' => $password_hash,
        ':correo' => $correo
    ]);

    // Marcar código como usado
    $stmt = $db->prepare("UPDATE codigos_recuperacion SET usado = 1 WHERE correo = :correo AND codigo = :codigo");
    $stmt->execute([
        ':correo' => $correo,
        ':codigo' => $codigo
    ]);

    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>