<?php
/**
 * Endpoint para enviar un PDF de consulta por WhatsApp
 * 
 * Este script recibe una solicitud AJAX con el ID de la consulta y el número
 * de WhatsApp, genera la URL del PDF y lo envía utilizando la función de envío
 * definida en enviar_pdf_whatsapp.php
 */

// Incluir el archivo con la función de envío
require_once 'enviar_pdf_whatsapp.php';
require_once 'model/consultas.model.php';
require_once 'check_public_url.php'; // Nueva utilidad para verificar URLs

// Headers para devolver JSON
header('Content-Type: application/json');

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    exit;
}

// Registrar todos los datos recibidos para depuración
file_put_contents(
    'logs/whatsapp_envios_' . date('Y_m') . '.log',
    date('Y-m-d H:i:s') . " | Datos recibidos: " . print_r($_POST, true) . "\n",
    FILE_APPEND
);

// Obtener los parámetros de la solicitud
$id_consulta = isset($_POST['id_consulta']) ? intval($_POST['id_consulta']) : 0;
$telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';

// ID de consulta para pruebas directas (solo cuando se llama sin parámetros)
$id_consulta_test = 19; // Cambiar esto según la consulta que desees probar

// Si no hay ID de consulta en POST pero el script se llamó directamente, usar el ID de prueba
if (empty($id_consulta) && basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__) && empty($_POST)) {
    $id_consulta = $id_consulta_test;
    
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | MODO PRUEBA DIRECTA: Usando ID de consulta test: $id_consulta\n",
        FILE_APPEND
    );
    
    // También podemos definir un teléfono de prueba
    if (empty($telefono)) {
        $telefono = '595982313358';
    }
}

// Validar el ID de consulta
if (empty($id_consulta)) {
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | ERROR: No se proporcionó ID de consulta o es inválido\n",
        FILE_APPEND
    );
    
    echo json_encode([
        'success' => false,
        'error' => 'No se proporcionó el ID de consulta',
        'received_data' => $_POST
    ]);
    exit;
}

// Validar el número de teléfono
if (empty($telefono)) {
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | ERROR: No se proporcionó número de teléfono\n",
        FILE_APPEND
    );
    
    echo json_encode([
        'success' => false,
        'error' => 'No se proporcionó el número de teléfono',
        'received_data' => $_POST
    ]);
    exit;
}

// Limpiar y formatear el número de teléfono
$telefono = preg_replace('/[^0-9]/', '', $telefono);

// Registro de información para depuración
file_put_contents(
    'logs/whatsapp_envios_' . date('Y_m') . '.log',
    date('Y-m-d H:i:s') . " | Número de teléfono limpiado: {$telefono} (longitud: " . strlen($telefono) . ")\n",
    FILE_APPEND
);

// Verificar que el número de teléfono tenga el formato correcto
if (strlen($telefono) < 10) {
    echo json_encode([
        'success' => false,
        'error' => 'El número de teléfono no tiene un formato válido'
    ]);
    exit;
}

try {    // Desactivar modo de prueba para enviar realmente los mensajes
    define('WHATSAPP_TEST_MODE', false); // Si es true, simula éxito sin enviar realmente
    
    // Obtener información de la consulta
    $consultaModel = new ModelConsulta();
    
    // Registrar en log el intento
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | Intentando obtener consulta ID: $id_consulta\n",
        FILE_APPEND
    );
    
    $consulta = $consultaModel->obtenerConsulta($id_consulta);
    
    // Log del resultado de la consulta para depuración
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | Resultado consulta: " . ($consulta ? "ENCONTRADA" : "NO ENCONTRADA") . "\n",
        FILE_APPEND
    );
    
    if (!$consulta) {
        echo json_encode([
            'success' => false,
            'error' => 'No se encontró la consulta con el ID proporcionado'
        ]);
        exit;
    }    // Generar la URL del PDF
    // Usamos SERVER_NAME en lugar de HTTP_HOST para asegurar que se use el nombre del servidor real
    $baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                "://" . $_SERVER['SERVER_NAME'];
    
    // Asegurarse de que el puerto esté incluido si no es el estándar
    if (isset($_SERVER['SERVER_PORT']) && !in_array($_SERVER['SERVER_PORT'], ['80', '443'])) {
        $baseURL .= ":" . $_SERVER['SERVER_PORT'];
    }
    
    // Construimos la URL para que apunte directamente al PDF
    $scriptPath = dirname($_SERVER['PHP_SELF']);
    if ($scriptPath == '/' || $scriptPath == '\\') $scriptPath = '';
    
    // URL completa al PDF
    $pdfUrlOriginal = $baseURL . $scriptPath . "/generar_pdf_consulta.php?id=" . $id_consulta;
    
    // Registrar la URL original para depuración
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | URL original generada: $pdfUrlOriginal\n",
        FILE_APPEND
    );
      // Detectar si estamos en un entorno local o de desarrollo
    $isLocalEnvironment = strpos($baseURL, 'localhost') !== false || 
                          strpos($baseURL, '127.0.0.1') !== false || 
                          strpos($baseURL, '.test') !== false || 
                          strpos($baseURL, '.local') !== false;
    
    // Comprobar si se ha solicitado explícitamente usar un PDF de prueba
    $usarPdfPrueba = (isset($_POST['usar_pdf_prueba']) && $_POST['usar_pdf_prueba'] == 'si');
    
    // Si es un entorno local o se ha solicitado explícitamente usar un PDF de prueba
    if ($isLocalEnvironment || $usarPdfPrueba) {
        // Usar un PDF público de prueba que sabemos que funciona con la API
        $pdfUrl = "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf";
        
        $razon = $usarPdfPrueba ? "SOLICITADO EXPLÍCITAMENTE" : "ENTORNO LOCAL/DESARROLLO DETECTADO";
        
        file_put_contents(
            'logs/whatsapp_envios_' . date('Y_m') . '.log',
            date('Y-m-d H:i:s') . " | $razon: Cambiando a URL pública de prueba: $pdfUrl\n",
            FILE_APPEND
        );
    } else {
        // Si es un entorno de producción, usar la URL real
        $pdfUrl = $pdfUrlOriginal;
    }
    
    // Registrar la URL generada para depuración
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | URL del PDF generada: $pdfUrl\n",
        FILE_APPEND
    );    // Verificar accesibilidad de la URL usando nuestra utilidad
    $urlCheck = checkPublicUrl($pdfUrl);
    $urlAccesible = $urlCheck['accessible_locally'] ?? false;
    
    // Registrar el resultado de la verificación
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | Verificación de URL: " . 
        ($urlAccesible ? "ACCESIBLE LOCALMENTE" : "NO ACCESIBLE LOCALMENTE") . "\n",
        FILE_APPEND
    );
      // Si no es accesible ni siquiera localmente, probablemente hay un problema con la generación del PDF
    if (!$urlAccesible && !$isLocalEnvironment) {
        file_put_contents(
            'logs/whatsapp_envios_' . date('Y_m') . '.log',
            date('Y-m-d H:i:s') . " | ADVERTENCIA: URL del PDF no es accesible ni siquiera localmente: $pdfUrl\n",
            FILE_APPEND
        );
    }
    
    // Registrar más información sobre la URL
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | Estado de accesibilidad: " . ($urlAccesible ? "ACCESIBLE" : "NO ACCESIBLE") . "\n",
        FILE_APPEND
    );
    
    // Si la URL no es accesible, intentar usar una URL alternativa
    if (!$urlAccesible) {
        // Podemos usar una URL pública de un servicio temporal de archivos
        // O generar el PDF y guardarlo en un directorio público
        
        $pdfDir = __DIR__ . "/pdf_consultas/";
        $pdfFilename = "consulta_{$id_consulta}_" . date('YmdHis') . ".pdf";
        $pdfPath = $pdfDir . $pdfFilename;
        
        // Verificar si existe el directorio, si no, crearlo
        if (!file_exists($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }
        
        // Intentar generar el PDF directamente
        ob_start();
        include(__DIR__ . "/generar_pdf_consulta.php");
        $pdfContent = ob_get_clean();
        
        // Guardar el PDF en el servidor
        file_put_contents($pdfPath, $pdfContent);
        
        // Crear nueva URL al archivo guardado
        $pdfUrl = $baseURL . $scriptPath . "/pdf_consultas/{$pdfFilename}";
        
        file_put_contents(
            'logs/whatsapp_envios_' . date('Y_m') . '.log',
            date('Y-m-d H:i:s') . " | URL alternativa generada: $pdfUrl\n",
            FILE_APPEND
        );
    }    // Crear una descripción para el mensaje
    // Log de depuración para ver la estructura del objeto consulta
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | Estructura de consulta: " . print_r($consulta, true) . "\n",
        FILE_APPEND
    );
    
    // Usar el campo nombre_paciente que viene de la consulta SQL
    $nombrePaciente = $consulta['nombre_paciente'] ?? 'Paciente';
    
    // La fecha puede estar en varios formatos dependiendo de la consulta SQL
    $fechaConsulta = null;
    if (isset($consulta['fecha_consulta'])) {
        $fechaConsulta = date('d/m/Y', strtotime($consulta['fecha_consulta']));
    } elseif (isset($consulta['fecha_hora'])) {
        $fechaConsulta = date('d/m/Y', strtotime($consulta['fecha_hora']));
    } elseif (isset($consulta['fecha'])) {
        $fechaConsulta = date('d/m/Y', strtotime($consulta['fecha']));
    } else {
        $fechaConsulta = date('d/m/Y'); // Fecha actual si no hay fecha en la consulta
    }
    
    // Base de la descripción
    $descripcion = "Consulta médica de $nombrePaciente del $fechaConsulta";
    
    // Si estamos usando una URL de prueba, indicarlo en la descripción
    if ($isLocalEnvironment) {
        $descripcion = "[PRUEBA] " . $descripcion . " (PDF de prueba)";
    }
    
    // Registrar log de intento de envío
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | Enviando PDF de consulta $id_consulta al número $telefono\n",
        FILE_APPEND
    );    // Deshabilitar completamente el modo de prueba y enviar siempre el mensaje real
    // Enviar el PDF por WhatsApp
    $resultado = enviarPDFPorWhatsApp($telefono, $pdfUrl, $descripcion);
      // Registrar el resultado en el log
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | Resultado: " . ($resultado['success'] ? 'ÉXITO' : 'ERROR') . 
        " | " . ($resultado['success'] ? (isset($resultado['test_mode']) && $resultado['test_mode'] ? 'Simulación exitosa' : 'Enviado correctamente') : $resultado['error']) . "\n",
        FILE_APPEND
    );
    
    // Devolver la respuesta
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'PDF enviado correctamente por WhatsApp'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $resultado['error']
        ]);
    }
    
} catch (Exception $e) {
    // Registrar el error en el log
    file_put_contents(
        'logs/whatsapp_envios_' . date('Y_m') . '.log',
        date('Y-m-d H:i:s') . " | ERROR: " . $e->getMessage() . "\n",
        FILE_APPEND
    );
    
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}
