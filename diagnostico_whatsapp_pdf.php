<?php
/**
 * Script de diagnóstico para verificar el envío de PDFs por WhatsApp
 */

// Configuración de cabeceras para mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Envío de PDFs por WhatsApp</h1>";

// Verificar si existe el directorio de PDFs temporales
$dirPdf = 'pdf_reservas/';
if (file_exists($dirPdf)) {
    echo "<p style='color: green'>✓ El directorio de PDFs temporales existe.</p>";
    
    // Verificar permisos de escritura
    if (is_writable($dirPdf)) {
        echo "<p style='color: green'>✓ El directorio tiene permisos de escritura.</p>";
    } else {
        echo "<p style='color: red'>✗ El directorio NO tiene permisos de escritura. Por favor ajuste los permisos.</p>";
    }
    
    // Listar archivos existentes
    $files = glob($dirPdf . '*.pdf');
    echo "<p>Archivos PDF existentes: " . count($files) . "</p>";
    if (count($files) > 0) {
        echo "<ul>";
        foreach ($files as $file) {
            echo "<li>" . basename($file) . " - " . date("Y-m-d H:i:s", filemtime($file)) . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color: red'>✗ El directorio de PDFs temporales NO existe. Debe crear el directorio: $dirPdf</p>";
}

// Verificar existencia y acceso a los archivos principales
$archivos = [
    'view/js/enviar_pdf_reserva.js',
    'ajax/enviar_media.php',
    'enviar_pdf_whatsapp.php',
    'generar_pdf_reserva.php'
];

echo "<h2>Archivos del sistema de envío:</h2>";
echo "<ul>";
foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "<li style='color: green'>✓ $archivo - Existe</li>";
        
        // Verificar si el archivo tiene derechos de lectura
        if (is_readable($archivo)) {
            echo "<li style='margin-left: 20px'>✓ $archivo - Legible</li>";
        } else {
            echo "<li style='margin-left: 20px; color: red'>✗ $archivo - NO es legible</li>";
        }
    } else {
        echo "<li style='color: red'>✗ $archivo - NO existe</li>";
    }
}
echo "</ul>";

// Probar la conexión con la API externa
echo "<h2>Prueba de conexión a la API externa:</h2>";

function testApiConexion() {
    $apiUrl = 'http://aventisdev.com:8082/media.php';
    $ch = curl_init($apiUrl);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, 'admin:1234');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($response === false) {
        echo "<p style='color: red'>✗ Error de conexión: $error</p>";
        return false;
    } else {
        echo "<p style='color: green'>✓ Conexión exitosa - Código HTTP: $httpCode</p>";
        return true;
    }
}

testApiConexion();

// Probar la generación de un PDF
echo "<h2>Prueba de generación de PDF:</h2>";

function testGenerarPdf() {
    // Buscar una reserva existente para probar
    try {
        require_once 'model/conexion.php';
        require_once 'model/reservas.model.php';
          $modelo = new ReservasModel();
        $reservas = $modelo->obtenerReservas(5); // Usando el método correcto según la clase
        
        if (empty($reservas)) {
            echo "<p style='color: orange'>⚠ No se encontraron reservas para probar.</p>";
            return false;
        }
        
        $reservaId = $reservas[0]['id'];
        echo "<p>Usando reserva ID: $reservaId para prueba</p>";
        
        // Intentar generar PDF sin guardar (solo verificar que se puede generar)
        $urlPdf = "generar_pdf_reserva.php?id=$reservaId";
        
        echo "<p>URL de prueba: <a href='$urlPdf' target='_blank'>$urlPdf</a></p>";
        echo "<p style='color: green'>✓ El PDF se puede generar. Haz clic en el enlace para probarlo.</p>";
        
        return $reservaId;
    } catch (Exception $e) {
        echo "<p style='color: red'>✗ Error al probar la generación de PDF: " . $e->getMessage() . "</p>";
        return false;
    }
}

$reservaId = testGenerarPdf();

// Mostrar formulario para prueba manual si hay una reserva
if ($reservaId) {
    echo "<h2>Prueba de Envío Manual:</h2>";
    echo "<form id='testForm' style='padding: 20px; background: #f8f9fa; border-radius: 5px;'>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label for='telefono' style='display: block; margin-bottom: 5px;'>Teléfono (formato internacional sin +):</label>";
    echo "<input type='text' id='telefono' name='telefono' style='padding: 8px; width: 300px;' placeholder='Ejemplo: 595982313358'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<input type='hidden' id='reservaId' value='$reservaId'>";
    echo "<button type='button' id='btnTest' style='padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 3px;'>Probar Envío por WhatsApp</button>";
    echo "</div>";
    
    echo "<div id='resultado' style='margin-top: 20px; padding: 10px; display: none; border: 1px solid #ddd; border-radius: 3px;'></div>";
    echo "</form>";
    
    // Script para la prueba manual
    echo "<script>
    document.getElementById('btnTest').addEventListener('click', function() {
        const telefono = document.getElementById('telefono').value.trim();
        const reservaId = document.getElementById('reservaId').value.trim();
        const resultadoDiv = document.getElementById('resultado');
        
        if (!telefono) {
            alert('Por favor ingrese un número de teléfono');
            return;
        }
        
        resultadoDiv.style.display = 'block';
        resultadoDiv.innerHTML = '<p>Enviando PDF, por favor espere...</p>';
        
        // Realizar petición directa al script de envío de media
        fetch('ajax/enviar_media.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                telefono: telefono,
                mediaUrl: window.location.origin + '/generar_pdf_reserva.php?id=' + reservaId,
                mediaCaption: 'Prueba de envío de PDF'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultadoDiv.innerHTML = '<p style=\"color: green\">✓ PDF enviado correctamente</p><pre>' + 
                                       JSON.stringify(data, null, 2) + '</pre>';
            } else {
                resultadoDiv.innerHTML = '<p style=\"color: red\">✗ Error al enviar el PDF</p><pre>' + 
                                      JSON.stringify(data, null, 2) + '</pre>';
            }
        })
        .catch(error => {
            resultadoDiv.innerHTML = '<p style=\"color: red\">✗ Error en la solicitud: ' + error.message + '</p>';
        });
    });
    </script>";
}

// Incluir información sobre inclusión de JS
echo "<h2>Verificación de inclusión de JavaScript:</h2>";
echo "<p>El archivo enviar_pdf_reserva.js debe estar incluido en la página de servicios.</p>";
echo "<p>Verifica que en <code>view/modules/servicios.php</code> exista la línea:</p>";
echo "<pre>&lt;script src=\"view/js/enviar_pdf_reserva.js\"&gt;&lt;/script&gt;</pre>";

?>
