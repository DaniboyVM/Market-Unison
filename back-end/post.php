<?php
// ============================================================
// ARCHIVO: back-end/post.php
// ============================================================
session_start();
header('Content-Type: application/json');

// 1. VERIFICAMOS EL CADENERO (Si no hay sesión, pa' fuera)
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "Debes iniciar sesión para publicar."]);
    exit;
}

// 2. CONEXIÓN A LA BASE DE DATOS
$conn = new mysqli("localhost", "root", "", "marketplace"); //

if ($conn->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos."]);
    exit;
}

// 3. RECIBIMOS LOS DATOS DE TEXTO
$id_vendedor = $_SESSION['id_usuario'];
$titulo = $conn->real_escape_string($_POST['titulo']);
$precio = (float)$_POST['precio'];
$categoria = $conn->real_escape_string($_POST['categoria']);
$descripcion = isset($_POST['descripcion']) ? $conn->real_escape_string($_POST['descripcion']) : "";

// 4. LÓGICA DE LA IMAGEN
// Verificamos si mandaron una imagen y si no hubo errores en la subida
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

    // Carpeta destino (asegúrate de haberla creado en el Paso 1)
    $carpeta_destino = "../uploads/";

    // Agarramos el nombre original del archivo (ej. foto_perro.png)
    $nombre_original = basename($_FILES['imagen']['name']);

    // EL TRUCO PRO: Le agregamos un código único al inicio para que si dos personas
    // suben una foto llamada "foto.jpg", no se borren entre sí.
    $nombre_final = uniqid() . "_" . $nombre_original;
    $ruta_completa = $carpeta_destino . $nombre_final;

    // Movemos el archivo de la memoria temporal a tu carpeta real
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {

        // 5. GUARDAMOS EN LA BASE DE DATOS
        // Ojo: Solo guardamos la RUTA de la imagen ("../uploads/nombre_final.jpg")
        $sql = "INSERT INTO publicacion (titulo, descripcion, precio, categoria, imagen, id_usuario)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdssi", $titulo, $descripcion, $precio, $categoria, $ruta_completa, $id_vendedor);

        if ($stmt->execute()) {
            // ¡Todo salió perfecto! Le avisamos a tu JavaScript
            echo json_encode(["exito" => true, "mensaje" => "Producto publicado."]);
        } else {
            echo json_encode(["error" => "Error al guardar en la base de datos."]);
        }

        $stmt->close();

    } else {
        echo json_encode(["error" => "Hubo un problema al guardar la imagen en el servidor."]);
    }
} else {
    echo json_encode(["error" => "Debes subir una imagen válida."]);
}

$conn->close();
?>