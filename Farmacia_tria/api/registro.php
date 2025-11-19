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

file_put_contents('debug_registro.txt', "=== REGISTRO ===\n", FILE_APPEND);

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        file_put_contents('debug_registro.txt', "ERROR: No conexión BD\n", FILE_APPEND);
        http_response_code(503);
        echo json_encode(array("message" => "Error de conexión a la base de datos."));
        exit();
    }
    
    // Detectar FormData o JSON
    $isFormData = !empty($_POST);
    
    if ($isFormData) {
        $correo = isset($_POST['correo']) ? $_POST['correo'] : null;
        $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : null;
        $data = (object) array('correo' => $correo, 'contrasena' => $contrasena);
    } else {
        $input = file_get_contents("php://input");
        $data = json_decode($input);
    }
    
    file_put_contents('debug_registro.txt', "Correo: " . ($data->correo ?? 'null') . "\n", FILE_APPEND);
    
    if (!empty($data->correo) && !empty($data->contrasena)) {
        try {
            // Verificar si el correo ya existe
            $query = "SELECT id_usuario FROM usuarios WHERE correo = :correo";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":correo", $data->correo);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                file_put_contents('debug_registro.txt', "❌ Correo ya existe\n", FILE_APPEND);
                http_response_code(400);
                echo json_encode(array("message" => "El correo ya está registrado."));
                exit();
            }
            
            // Crear usuario
            $contrasena_hash = password_hash($data->contrasena, PASSWORD_BCRYPT);
            
            $query = "INSERT INTO usuarios (correo, contrasena) VALUES (:correo, :contrasena)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":correo", $data->correo);
            $stmt->bindParam(":contrasena", $contrasena_hash);
            
            if ($stmt->execute()) {
                $id = $db->lastInsertId();
                file_put_contents('debug_registro.txt', "✅ Usuario creado ID: $id\n", FILE_APPEND);
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Usuario registrado exitosamente.",
                    "id" => $id
                ));
            } else {
                throw new Exception("Error al ejecutar INSERT");
            }
            
        } catch (Exception $e) {
            file_put_contents('debug_registro.txt', "❌ Excepción: " . $e->getMessage() . "\n", FILE_APPEND);
            http_response_code(503);
            echo json_encode(array("message" => "Error al registrar el usuario."));
        }
    } else {
        file_put_contents('debug_registro.txt', "❌ Datos incompletos\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode(array("message" => "Datos incompletos."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Método no permitido."));
}

file_put_contents('debug_registro.txt', "=== FIN ===\n\n", FILE_APPEND);
?>