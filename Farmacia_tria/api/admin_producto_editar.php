<?php
header("Content-Type: application/json");
include_once '../config/database.php';

$db = (new Database())->getConnection();
$data = json_decode(file_get_contents("php://input"));
$id = $_GET['id'];

$query = "UPDATE productos SET 
          nombre_producto = :nombre,
          descripcion = :descripcion,
          precio = :precio,
          stock = :stock,
          id_categoria = :categoria,
          imagen_url = :imagen
          WHERE id_producto = :id";

$stmt = $db->prepare($query);
$stmt->execute([
    ':nombre' => $data->nombre,
    ':descripcion' => $data->descripcion,
    ':precio' => $data->precio,
    ':stock' => $data->stock,
    ':categoria' => $data->categoria,
    ':imagen' => $data->imagen,
    ':id' => $id
]);

echo json_encode(['message' => 'Producto actualizado']);
?>