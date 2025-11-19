<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;

if ($categoria) {
    $query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, 
                     p.imagen_url, p.stock, c.nombre_categoria
              FROM productos p
              INNER JOIN categorias c ON p.id_categoria = c.id_categoria
              WHERE p.activo = 1 AND c.nombre_categoria = :categoria
              ORDER BY p.nombre_producto";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":categoria", $categoria);
} else {
    $query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, 
                     p.imagen_url, p.stock, c.nombre_categoria
              FROM productos p
              INNER JOIN categorias c ON p.id_categoria = c.id_categoria
              WHERE p.activo = 1
              ORDER BY c.nombre_categoria, p.nombre_producto";
    $stmt = $db->prepare($query);
}

$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($productos) > 0) {
    http_response_code(200);
    echo json_encode($productos);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No se encontraron productos."));
}
?>