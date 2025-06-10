<?php
/**
 * Script de prueba para enviar PDF por WhatsApp
 * 
 * Este script prueba directamente el envío de un PDF por WhatsApp
 * sin depender de otros componentes del sistema.
 */

// Incluir la función de envío
require_once 'enviar_pdf_whatsapp.php';

// ID de consulta a probar (cambiar según sea necesario)
$id_consulta = 19;

// Número de teléfono de prueba (formato internacional sin +)
$telefono = '595982313358';

// URL de PDF pública para pruebas
$pdfUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';

// Descripción del mensaje
$descripcion = '[PRUEBA] Consulta médica - PDF de prueba';

echo "Iniciando prueba de envío de WhatsApp\n";
echo "-----------------------------------\n";
echo "ID Consulta: $id_consulta\n";
echo "Teléfono: $telefono\n";
echo "URL del PDF: $pdfUrl\n";
echo "Descripción: $descripcion\n";
echo "-----------------------------------\n\n";

// Registrar en el log
file_put_contents(
    'logs/whatsapp_envios_' . date('Y_m') . '.log',
    "\n" . date('Y-m-d H:i:s') . " | PRUEBA DIRECTA: Enviando PDF de prueba al número $telefono\n",
    FILE_APPEND
);

// Intentar enviar el PDF
$resultado = enviarPDFPorWhatsApp($telefono, $pdfUrl, $descripcion);

// Mostrar resultado
echo "Resultado: " . ($resultado['success'] ? 'ÉXITO' : 'ERROR') . "\n";

if (!$resultado['success']) {
    echo "Error: " . $resultado['error'] . "\n";
    
    if (isset($resultado['debug_info'])) {
        echo "\nInformación de depuración:\n";
        print_r($resultado['debug_info']);
    }
    
    if (isset($resultado['response'])) {
        echo "\nRespuesta del servidor:\n";
        print_r($resultado['response']);
    }
} else {
    echo "Mensaje enviado correctamente.\n";
    
    if (isset($resultado['data'])) {
        echo "\nDatos de respuesta:\n";
        print_r($resultado['data']);
    }
}

// Registrar resultado en el log
file_put_contents(
    'logs/whatsapp_envios_' . date('Y_m') . '.log',
    date('Y-m-d H:i:s') . " | PRUEBA DIRECTA: Resultado " . 
    ($resultado['success'] ? 'EXITOSO' : 'FALLIDO - ' . $resultado['error']) . "\n",
    FILE_APPEND
);

echo "\n-----------------------------------\n";
echo "Prueba finalizada. Revisa logs/whatsapp_envios_" . date('Y_m') . ".log para más detalles.\n";

?>
