<?php
// ══════════════════════════════════════════
// SEGURIDAD — Lo primero antes de todo
// ══════════════════════════════════════════

// Solo POST, bloquea acceso directo desde navegador
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    die('Acceso denegado');
}

// Solo acepta peticiones desde tu dominio
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== 'https://guillermoworks.com') {
    http_response_code(403);
    die('Origen no permitido');
}

// Rate limiting — máximo 5 envíos por IP por hora
session_start();
$ip  = $_SERVER['REMOTE_ADDR'];
$key = 'rate_' . md5($ip);
$_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
if ($_SESSION[$key] > 5) {
    http_response_code(429);
    die(json_encode(['error' => 'Demasiados intentos, intenta más tarde']));
}

// ══════════════════════════════════════════
// HEADERS
// ══════════════════════════════════════════
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://guillermoworks.com');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// ══════════════════════════════════════════
// VARIABLES DE ENTORNO
// ══════════════════════════════════════════
$env_path = __DIR__ . '/.env';
if (!file_exists($env_path)) {
    http_response_code(500);
    die(json_encode(['error' => 'Configuración no encontrada']));
}
$env = parse_ini_file($env_path);

$db_host = $env['DB_HOST'];
$db_name = $env['DB_NAME'];
$db_user = $env['DB_USER'];
$db_pass = $env['DB_PASS'];
$to      = $env['MAIL_TO'];

// ══════════════════════════════════════════
// LEER Y VALIDAR DATOS DEL FORMULARIO
// ══════════════════════════════════════════
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    die(json_encode(['error' => 'Datos inválidos']));
}

$nombre   = htmlspecialchars(strip_tags($input['nombre']   ?? ''));
$email    = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
$telefono = htmlspecialchars(strip_tags($input['telefono'] ?? ''));
$servicio = htmlspecialchars(strip_tags($input['servicio'] ?? ''));
$mensaje  = htmlspecialchars(strip_tags($input['mensaje']  ?? ''));

if (!$nombre || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$mensaje) {
    http_response_code(422);
    die(json_encode(['error' => 'Faltan campos requeridos']));
}

// ══════════════════════════════════════════
// GUARDAR EN BASE DE DATOS
// ══════════════════════════════════════════
try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8",
        $db_user,
        $db_pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare(
        "INSERT INTO contactos (nombre, email, telefono, servicio, mensaje)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$nombre, $email, $telefono, $servicio, $mensaje]);

} catch (PDOException $e) {
    error_log('DB Error: ' . $e->getMessage());
    // Continúa aunque falle la BD, el email es suficiente
}

// ══════════════════════════════════════════
// ENVIAR EMAIL DE NOTIFICACIÓN
// ══════════════════════════════════════════
$subject = "Nuevo contacto desde guillermoworks.com — $nombre";
$body    = "
Nombre:   $nombre
Email:    $email
Teléfono: $telefono
Servicio: $servicio

Mensaje:
$mensaje

---
Enviado desde guillermoworks.com
";
$headers = "From: noreply@guillermoworks.com\r\n" .
           "Reply-To: $email\r\n" .
           "Content-Type: text/plain; charset=UTF-8";

mail($to, $subject, $body, $headers);

// ══════════════════════════════════════════
// RESPUESTA EXITOSA
// ══════════════════════════════════════════
http_response_code(200);
echo json_encode(['success' => true]);
?>