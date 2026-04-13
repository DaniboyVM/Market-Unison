<?php
// ============================================================
// ARCHIVO: back-end/getProduct.php
//  Este archivo sirve para hacer la busqueda por un producto
// en especifico y devolverlo en JSON, lo usa product.js que
// a su vez lo usa products.html
// ============================================================
header('Content-Type: application/json');

// 1. Obtener el ID del producto desde la URL (GET)
$id_publicacion = isset($_GET['id']) ? $_GET['id'] : null;

if ($id_publicacion === null) {
    echo json_encode(["error" => "No se proporcionó un ID de producto."]);
    exit;
}

// 2. Datos de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketplace";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $conn->connect_error]);
    exit;
}

// 3. Preparar la consulta SQL con JOIN (JOINeamos la tabla de usuarios para traer el vendedor)
// Usamos prepared statements para mayor seguridad
// Esta instrucción en SQL hace algo que se llama Consultas Relacionales
// (TODO u.departamento AS contacto_vendedor hay que hacer un ajuste a eso )
$sql = "SELECT p.*, p.id_usuario AS id_vendedor, u.nombre AS nombre_vendedor, u.departamento AS contacto_vendedor
        FROM publicacion p
        JOIN usuario u ON p.id_usuario = u.id_usuario
        WHERE p.id_publicacion = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_publicacion); // "i" significa que el parámetro es un entero
$stmt->execute();
$result = $stmt->get_result();

// 4. Procesar el resultado y devolver JSON
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Suponemos que tienes una columna 'imagenes' en tu base de datos con URLs separadas por comas.
    // Si no, podemos adaptarlo más adelante. Por ahora lo manejamos como una cadena simple.
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Producto no encontrado."]);
}

$stmt->close();
$conn->close();
?>