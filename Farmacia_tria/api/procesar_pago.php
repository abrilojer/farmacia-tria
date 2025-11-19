<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// LOG
file_put_contents('debug_pago.txt', "=== PROCESAR PAGO ===\n", FILE_APPEND);

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        file_put_contents('debug_pago.txt', "ERROR: No conexión BD\n", FILE_APPEND);
        http_response_code(503);
        echo json_encode(array("message" => "Error de conexión."));
        exit();
    }
    
    $input = file_get_contents("php://input");
    file_put_contents('debug_pago.txt', "Input: $input\n", FILE_APPEND);
    
    $data = json_decode($input);
    
    if (!empty($data->pedido_id) && !empty($data->metodo_pago) && !empty($data->dni)) {
        // Obtener ID del método de pago
        $query_metodo = "SELECT id_metodo_pago FROM metodos_pago WHERE nombre_metodo = :metodo";
        $stmt_metodo = $db->prepare($query_metodo);
        $stmt_metodo->bindParam(":metodo", $data->metodo_pago);
        $stmt_metodo->execute();
        $metodo = $stmt_metodo->fetch(PDO::FETCH_ASSOC);
        
        if ($metodo) {
            $codigo = isset($data->codigo) ? $data->codigo : null;
            
            file_put_contents('debug_pago.txt', "Procesando pago para pedido: {$data->pedido_id}\n", FILE_APPEND);
            
            $query = "INSERT INTO pagos (id_pedido, id_metodo_pago, dni, codigo_transaccion, estado_pago) 
                     VALUES (:pedido_id, :metodo_id, :dni, :codigo, 'aprobado')";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(":pedido_id", $data->pedido_id);
            $stmt->bindParam(":metodo_id", $metodo['id_metodo_pago']);
            $stmt->bindParam(":dni", $data->dni);
            $stmt->bindParam(":codigo", $codigo);
            
            if ($stmt->execute()) {
                // Actualizar estado del pedido
                $query_update = "UPDATE pedidos SET estado_pedido = 'procesando' 
                                WHERE id_pedido = :pedido_id";
                $stmt_update = $db->prepare($query_update);
                $stmt_update->bindParam(":pedido_id", $data->pedido_id);
                $stmt_update->execute();
                
                file_put_contents('debug_pago.txt', "✅ Pago procesado exitosamente\n", FILE_APPEND);
                
                http_response_code(201);
                echo json_encode(array("message" => "Pago procesado exitosamente."));
            } else {
                file_put_contents('debug_pago.txt', "❌ Error al insertar pago\n", FILE_APPEND);
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo procesar el pago."));
            }
        } else {
            file_put_contents('debug_pago.txt', "❌ Método de pago no válido: {$data->metodo_pago}\n", FILE_APPEND);
            http_response_code(404);
            echo json_encode(array("message" => "Método de pago no válido."));
        }
    } else {
        file_put_contents('debug_pago.txt', "❌ Datos incompletos\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode(array("message" => "Datos incompletos."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Método no permitido."));
}

file_put_contents('debug_pago.txt', "=== FIN PAGO ===\n\n", FILE_APPEND);
?>