<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// LOG para debug
file_put_contents('debug_login.txt', "=== LOGIN REQUEST ===\n", FILE_APPEND);
file_put_contents('debug_login.txt', "Método: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        file_put_contents('debug_login.txt', "ERROR: No conexión BD\n", FILE_APPEND);
        http_response_code(503);
        echo json_encode(array("message" => "Error de conexión a la base de datos."));
        exit();
    }
    
    // Detectar FormData o JSON
    $isFormData = !empty($_POST);
    
    if ($isFormData) {
        file_put_contents('debug_login.txt', "Recibido como FormData\n", FILE_APPEND);
        $correo = isset($_POST['correo']) ? $_POST['correo'] : null;
        $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : null;
        
        $data = (object) array(
            'correo' => $correo,
            'contrasena' => $contrasena
        );
    } else {
        file_put_contents('debug_login.txt', "Recibido como JSON\n", FILE_APPEND);
        $input = file_get_contents("php://input");
        file_put_contents('debug_login.txt', "Input: $input\n", FILE_APPEND);
        $data = json_decode($input);
    }
    
    if (!empty($data->correo) && !empty($data->contrasena)) {
        file_put_contents('debug_login.txt', "Buscando usuario: {$data->correo}\n", FILE_APPEND);
        
        $query = "SELECT id_usuario, correo, contrasena FROM usuarios WHERE correo = :correo AND activo = 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":correo", $data->correo);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            file_put_contents('debug_login.txt', "Usuario encontrado, verificando contraseña\n", FILE_APPEND);
            
            if (password_verify($data->contrasena, $row['contrasena'])) {
                $_SESSION['usuario_id'] = $row['id_usuario'];
                $_SESSION['usuario_correo'] = $row['correo'];
                
                file_put_contents('debug_login.txt', "✅ Login exitoso\n", FILE_APPEND);
                
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Inicio de sesión exitoso.",
                    "usuario_id" => $row['id_usuario'],
                    "correo" => $row['correo']
                ));
            } else {
                file_put_contents('debug_login.txt', "❌ Contraseña incorrecta\n", FILE_APPEND);
                http_response_code(401);
                echo json_encode(array("message" => "Contraseña incorrecta."));
            }
        } else {
            file_put_contents('debug_login.txt', "❌ Usuario no encontrado\n", FILE_APPEND);
            http_response_code(404);
            echo json_encode(array("message" => "Usuario no encontrado."));
        }
    } else {
        file_put_contents('debug_login.txt', "❌ Datos incompletos\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode(array("message" => "Datos incompletos."));
    }
} else {
    file_put_contents('debug_login.txt', "❌ Método no permitido\n", FILE_APPEND);
    http_response_code(405);
    echo json_encode(array("message" => "Método no permitido."));
}

file_put_contents('debug_login.txt', "=== FIN LOGIN ===\n\n", FILE_APPEND);
?>