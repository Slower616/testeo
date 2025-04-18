<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'asesoramiento_judicial';
$username = 'root';
$password = '';

try {
    // Crear conexión PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configurar el modo de error de PDO para lanzar excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar el modo de fetch por defecto a asociativo
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Crear tabla de consultas si no existe
    $conn->exec("CREATE TABLE IF NOT EXISTS consultas (
        id_consulta INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefono VARCHAR(20),
        id_area INT NOT NULL,
        descripcion TEXT NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        atendida BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (id_area) REFERENCES areas_practica(id_area)
    )");
} catch(PDOException $e) {
    // En caso de error, registrar el error pero no mostrar detalles sensibles al usuario
    error_log("Error de conexión: " . $e->getMessage());
    
    // Crear una conexión simulada para desarrollo
    if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
        // Para desarrollo local, crear una conexión simulada
        $conn = new stdClass();
        $conn->query = function($sql) {
            return new stdClass();
        };
        $conn->prepare = function($sql) {
            return new stdClass();
        };
    } else {
        // Para producción, mostrar un mensaje genérico
        die("Lo sentimos, estamos experimentando problemas técnicos. Por favor, inténtelo de nuevo más tarde.");
    }
}

// Función para obtener el enlace de WhatsApp
function getWhatsAppLink($data = []) {
    $phone = '584163117367';
    $message = "Hola, me interesa obtener información sobre servicios legales.";
    
    if (!empty($data)) {
        $message = "Hola, soy " . ($data['nombre'] ?? '') . ". ";
        $message .= "Me interesa obtener información sobre " . ($data['area'] ?? 'servicios legales') . ". ";
        $message .= "Mi consulta: " . ($data['descripcion'] ?? '');
    }
    
    return "https://wa.me/" . $phone . "?text=" . urlencode($message);
}

// Función para enviar correo de consulta
function enviarCorreoConsulta($data) {
    $to = "yumili3369@yahoo.com.ve";
    $subject = "Nueva consulta legal - " . ($data['area'] ?? 'General');
    
    $message = "Se ha recibido una nueva consulta legal:\n\n";
    $message .= "Nombre: " . ($data['nombre'] ?? 'No especificado') . "\n";
    $message .= "Email: " . ($data['email'] ?? 'No especificado') . "\n";
    $message .= "Teléfono: " . ($data['telefono'] ?? 'No especificado') . "\n";
    $message .= "Área: " . ($data['area'] ?? 'No especificada') . "\n\n";
    $message .= "Descripción:\n" . ($data['descripcion'] ?? 'No especificada') . "\n";
    
    $headers = "From: " . ($data['email'] ?? 'noreply@estudiojuridicoacuna.com') . "\r\n";
    $headers .= "Reply-To: " . ($data['email'] ?? 'noreply@estudiojuridicoacuna.com') . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Intentar enviar el correo
    return mail($to, $subject, $message, $headers);
}

// Función para obtener el enlace de WhatsApp para consulta
function getWhatsAppConsultationLink($data) {
    $phone = '584163117367';
    $message = "Hola, soy " . ($data['nombre'] ?? '') . ". ";
    $message .= "Me interesa obtener información sobre " . ($data['area'] ?? 'servicios legales') . ". ";
    $message .= "Mi consulta: " . ($data['descripcion'] ?? '');
    
    return "https://wa.me/" . $phone . "?text=" . urlencode($message);
}
?>