<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// LOG
file_put_contents('debug_pedido.txt', "=== CREAR PEDIDO ===\n", FILE_APPEND);

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        file_put_contents('debug_pedido.txt', "ERROR: No conexión BD\n", FILE_APPEND);
        http_response_code(503);
        echo json_encode(array("message" => "Error de conexión."));
        exit();
    }
    
    $input = file_get_contents("php://input");
    file_put_contents('debug_pedido.txt', "Input: $input\n", FILE_APPEND);
    
    $data = json_decode($input);
    
    file_put_contents('debug_pedido.txt', "Data: " . print_r($data, true) . "\n", FILE_APPEND);
    
    if (!empty($data->usuario_id) && !empty($data->items) && !empty($data->total)) {
        try {
            $db->beginTransaction();
            
            file_put_contents('debug_pedido.txt', "Iniciando transacción...\n", FILE_APPEND);
            
            // Crear pedido
            $query = "INSERT INTO pedidos (id_usuario, total, estado_pedido) 
                     VALUES (:usuario_id, :total, 'pendiente')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":usuario_id", $data->usuario_id);
            $stmt->bindParam(":total", $data->total);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al crear pedido: " . print_r($stmt->errorInfo(), true));
            }
            
            $pedido_id = $db->lastInsertId();
            file_put_contents('debug_pedido.txt', "Pedido creado con ID: $pedido_id\n", FILE_APPEND);
            
            // Insertar detalles
            $query_detalle = "INSERT INTO detalle_pedidos 
                             (id_pedido, id_producto, cantidad, precio_unitario, subtotal) 
                             VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
            $stmt_detalle = $db->prepare($query_detalle);
            
            foreach ($data->items as $item) {
                $subtotal = $item->precio * $item->cantidad;
                
                file_put_contents('debug_pedido.txt', "Insertando item: Producto {$item->id_producto}, Cantidad {$item->cantidad}\n", FILE_APPEND);
                
                $stmt_detalle->bindParam(":pedido_id", $pedido_id);
                $stmt_detalle->bindParam(":producto_id", $item->id_producto);
                $stmt_detalle->bindParam(":cantidad", $item->cantidad);
                $stmt_detalle->bindParam(":precio_unitario", $item->precio);
                $stmt_detalle->bindParam(":subtotal", $subtotal);
                
                if (!$stmt_detalle->execute()) {
                    throw new Exception("Error al insertar detalle: " . print_r($stmt_detalle->errorInfo(), true));
                }
                
                // Actualizar stock
                $query_stock = "UPDATE productos SET stock = stock - :cantidad 
                               WHERE id_producto = :producto_id AND stock >= :cantidad";
                $stmt_stock = $db->prepare($query_stock);
                $stmt_stock->bindParam(":cantidad", $item->cantidad);
                $stmt_stock->bindParam(":producto_id", $item->id_producto);
                
                if (!$stmt_stock->execute()) {
                    throw new Exception("Error al actualizar stock: " . print_r($stmt_stock->errorInfo(), true));
                }
                
                if ($stmt_stock->rowCount() == 0) {
                    throw new Exception("Stock insuficiente para producto ID: " . $item->id_producto);
                }
            }
            
            $db->commit();
            file_put_contents('debug_pedido.txt', "✅ Pedido completado exitosamente\n", FILE_APPEND);
            
            http_response_code(201);
            echo json_encode(array(
                "message" => "Pedido creado exitosamente.",
                "pedido_id" => $pedido_id
            ));
            
        } catch (Exception $e) {
            $db->rollBack();
            file_put_contents('debug_pedido.txt', "❌ ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            http_response_code(503);
            echo json_encode(array("message" => "Error al crear el pedido: " . $e->getMessage()));
        }
    } else {
        file_put_contents('debug_pedido.txt', "❌ Datos incompletos\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode(array("message" => "Datos incompletos."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Método no permitido."));
}

file_put_contents('debug_pedido.txt', "=== FIN PEDIDO ===\n\n", FILE_APPEND);
?>