<?php
/**
 * Función para registrar envíos de WhatsApp en un archivo de log
 * 
 * @param array $data Los datos del envío
 * @return bool True si el log se guardó correctamente
 */
function logWhatsAppEnvio($data) {
    // Directorio de logs
    $logDir = __DIR__ . '/../logs';
    
    // Crear directorio si no existe
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Nombre del archivo de log (un archivo por mes)
    $logFile = $logDir . '/whatsapp_envios_' . date('Y_m') . '.log';
    
    // Preparar datos para el log
    $timestamp = date('Y-m-d H:i:s');
    $telefono = isset($data['telefono']) ? $data['telefono'] : 'no-telefono';
    $reservaId = isset($data['reservaId']) ? $data['reservaId'] : 'no-id';
    $mediaUrl = isset($data['mediaUrl']) ? $data['mediaUrl'] : 'no-url';
    $success = isset($data['success']) ? ($data['success'] ? 'ÉXITO' : 'ERROR') : 'DESCONOCIDO';
    $errorMsg = isset($data['error']) ? $data['error'] : '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
    
    // Formar la entrada del log
    $logEntry = sprintf(
        "[%s] [%s] ReservaID: %s, Teléfono: %s, URL: %s, IP: %s, Error: %s, UA: %s\n",
        $timestamp,
        $success,
        $reservaId,
        $telefono,
        $mediaUrl,
        $ip,
        $errorMsg,
        $userAgent
    );
    
    // Escribir en el archivo de log
    $result = file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    return ($result !== false);
}
