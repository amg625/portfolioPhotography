<?php
/**
 * mail.php — Backend del formulario de contacto
 * Sube este archivo a tu hosting en Hostinger
 * junto al index.html o en una carpeta /api/
 *
 * Recibe JSON via POST y guarda en DB o manda email
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://guillermoworks.com'); // header('Access-Control-Allow-Origin: https://tu-dominio.com'); // Cambia a tu dominio
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Lee el body JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Sanitiza
$nombre   = htmlspecialchars(strip_tags($input['nombre'] ?? ''));
$email    = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
$telefono = htmlspecialchars(strip_tags($input['telefono'] ?? ''));
$servicio = htmlspecialchars(strip_tags($input['servicio'] ?? ''));
$mensaje  = htmlspecialchars(strip_tags($input['mensaje'] ?? ''));

// Validación básica
if (!$nombre || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$mensaje) {
    http_response_code(422);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// ══════════════════════════════════════════
// OPCIÓN 1: GUARDAR EN BASE DE DATOS MySQL
// Configura estos datos en Hostinger
// ══════════════════════════════════════════
$db_host = '31.97.208.83';
$db_name = 'u912746025_MainData';   // <-- cambia
$db_user = 'u912746025_galvarezm4';      // <-- cambia
$db_pass = 'Memo91am';     // <-- cambia

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crea la tabla si no existe (solo primera vez)
    $pdo->exec("CREATE TABLE IF NOT EXISTS contactos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(150),
        email VARCHAR(150),
        telefono VARCHAR(50),
        servicio VARCHAR(80),
        mensaje TEXT,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->prepare("INSERT INTO contactos (nombre, email, telefono, servicio, mensaje) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $telefono, $servicio, $mensaje]);

} catch (PDOException $e) {
    // Continúa aunque falle la DB, el email es suficiente
    error_log('DB Error: ' . $e->getMessage());
}

// ══════════════════════════════════════════
// OPCIÓN 2: ENVIAR EMAIL DE NOTIFICACIÓN
// ══════════════════════════════════════════
$to      = 'alvarezmg01@gmail.com';  // <-- tu correo
$subject = "Nuevo contacto desde tu portfolio: $nombre";
$body    = "
Nombre: $nombre
Email: $email
Teléfono: $telefono
Servicio: $servicio

Mensaje:
$mensaje

---
Enviado desde tu portfolio fotográfico
";
$headers = "From: noreply@tu-dominio.com\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";

mail($to, $subject, $body, $headers);

// Respuesta exitosa
http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Mensaje guardado']);
?>