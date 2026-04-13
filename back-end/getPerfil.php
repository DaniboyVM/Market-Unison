<?php
// ============================================================
// ARCHIVO: back-end/getPerfil.php
// Devuelve los datos del perfil del usuario en sesión
// ============================================================
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["exito" => false, "error" => "No hay sesión activa."]);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$servername  = "localhost";
$db_user     = "root";
$db_password = "";
$dbname      = "marketplace";

$conn = new mysqli($servername, $db_user, $db_password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["exito" => false, "error" => "Error de conexión."]);
    exit;
}

// Datos del usuario
$stmt = $conn->prepare("SELECT nombre, correo, departamento, horario, tipo_usuario, foto_de_perfil FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["exito" => false, "error" => "Usuario no encontrado."]);
    $stmt->close(); $conn->close(); exit;
}

$u = $res->fetch_assoc();
$stmt->close();

// Foto de perfil en base64 si existe
$foto = null;
if (!empty($u['foto_de_perfil'])) {
    $foto = 'data:image/jpeg;base64,' . base64_encode($u['foto_de_perfil']);
}

// Publicaciones del usuario
$stmtPub = $conn->prepare("SELECT id_publicacion, titulo, precio, categoria, imagen FROM publicacion WHERE id_usuario = ? ORDER BY id_publicacion DESC");
$stmtPub->bind_param("i", $id_usuario);
$stmtPub->execute();
$resPub = $stmtPub->get_result();

$publicaciones = [];
while ($p = $resPub->fetch_assoc()) {
    $publicaciones[] = $p;
}
$stmtPub->close();

// Reseñas recibidas (sobre las publicaciones del usuario)
$stmtRes = $conn->prepare("
    SELECT r.calificacion, r.comentario, u.nombre AS revisor, r.id_resena
    FROM resena r
    JOIN publicacion p ON r.id_publicacion = p.id_publicacion
    JOIN usuario u ON r.id_usuario = u.id_usuario
    WHERE p.id_usuario = ?
    ORDER BY r.id_resena DESC
    LIMIT 5
");
$stmtRes->bind_param("i", $id_usuario);
$stmtRes->execute();
$resReseñas = $stmtRes->get_result();

$resenas = [];
while ($r = $resReseñas->fetch_assoc()) {
    $resenas[] = $r;
}
$stmtRes->close();

// Promedio de calificación
$stmtProm = $conn->prepare("
    SELECT AVG(r.calificacion) AS promedio, COUNT(*) AS total
    FROM resena r
    JOIN publicacion p ON r.id_publicacion = p.id_publicacion
    WHERE p.id_usuario = ?
");
$stmtProm->bind_param("i", $id_usuario);
$stmtProm->execute();
$resProm = $stmtProm->get_result()->fetch_assoc();
$stmtProm->close();

$conn->close();

echo json_encode([
    "exito"        => true,
    "nombre"       => $u['nombre'],
    "correo"       => $u['correo'],
    "departamento" => $u['departamento'],
    "horario"      => $u['horario'],
    "tipo_usuario" => $u['tipo_usuario'],
    "foto"         => $foto,
    "publicaciones"=> $publicaciones,
    "resenas"      => $resenas,
    "promedio"     => round($resProm['promedio'] ?? 0, 1),
    "total_resenas"=> (int)($resProm['total'] ?? 0)
]);
?>
