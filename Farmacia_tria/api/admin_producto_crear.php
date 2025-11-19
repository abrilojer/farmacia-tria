<?php
header("Content-Type: application/json");
include_once '../config/database.php';

$db = (new Database())->getConnection();
$data = json_decode(file_get_contents("php://input"));

$query = "INSERT INTO productos (nombre_producto, descripcion, precio, stock, id_categoria, imagen_url)
          VALUES (:nombre, :descripcion, :precio, :stock, :categoria, :imagen)";

$stmt = $db->prepare($query);
$stmt->execute([
    ':nombre' => $data->nombre,
    ':descripcion' => $data->descripcion,
    ':precio' => $data->precio,
    ':stock' => $data->stock,
    ':categoria' => $data->categoria,
    ':imagen' => $data->imagen
]);

echo json_encode(['message' => 'Producto creado']);
?>