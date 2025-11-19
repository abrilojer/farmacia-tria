<?php
error_reporting(0);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include_once '../config/database.php';

try {
    $correo = $_POST['correo'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    
    if (!$correo || !$codigo) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit();
    }

    $db = (new Database())->getConnection();

    // Buscar código válido
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

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Código válido']);
    } else {
        // Verificar si existe pero expiró
        $stmt = $db->prepare("SELECT * FROM codigos_recuperacion 
                              WHERE correo = :correo 
                              AND codigo = :codigo 
                              ORDER BY fecha_creacion DESC 
                              LIMIT 1");
        $stmt->execute([':correo' => $correo, ':codigo' => $codigo]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row['expiracion'] < date('Y-m-d H:i:s')) {
                echo json_encode(['success' => false, 'message' => 'Código expirado']);
            } else if ($row['usado'] == 1) {
                echo json_encode(['success' => false, 'message' => 'Código ya usado']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Código incorrecto']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Código incorrecto']);
        }
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>