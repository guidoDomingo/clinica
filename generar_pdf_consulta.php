<?php
/**
 * Generador de PDF para consultas médicas
 * 
 * Este archivo genera un PDF con los datos de una consulta médica específica
 */

// Incluir archivos necesarios
require_once 'config/config.php';
require_once 'vendor/autoload.php';
require_once 'model/conexion.php';
require_once 'model/consultas.model.php';

// Referencias a las clases de Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// Verificar que se recibió el ID de la consulta
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Error: No se proporcionó el ID de la consulta.";
    exit;
}

$id_consulta = $_GET['id'];
// Creamos una instancia de ModelConsulta sin constructor
$consultaModel = new ModelConsulta();
$datos_consulta = $consultaModel->obtenerConsulta($id_consulta);

// Verificar si se encontró la consulta
if (!$datos_consulta) {
    echo "Error: No se encontró la consulta solicitada.";
    exit;
}

// Obtener datos de la persona
$id_persona = $datos_consulta['id_persona'];
$persona = $consultaModel->obtenerDatosPersona($id_persona);

// Mapear campos de la consulta
$motivo = $datos_consulta['txtmotivo'] ?? '';
$descripcion = $datos_consulta['consulta_textarea'] ?? '';
$receta = $datos_consulta['receta_textarea'] ?? '';
$vision_od = $datos_consulta['visionod'] ?? '';
$vision_oi = $datos_consulta['visionoi'] ?? '';
$tension_od = $datos_consulta['tensionod'] ?? '';
$tension_oi = $datos_consulta['tensionoi'] ?? '';
$proximaConsulta = $datos_consulta['proximaconsulta'] ?? '';
$fecha_consulta = $datos_consulta['fecha_registro'] ?? $datos_consulta['fecha_creacion'] ?? date('Y-m-d H:i:s');

// Configurar opciones de Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isFontSubsettingEnabled', true);
$options->set('defaultFont', 'Arial');

// Inicializar Dompdf
$dompdf = new Dompdf($options);
$dompdf->setPaper('letter', 'portrait');

// Iniciar buffer para ir generando el HTML
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Consulta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .fecha-impresion {
            text-align: center;
            font-size: 12px;
            margin-bottom: 20px;
            color: #7f8c8d;
        }
        h2 {
            background-color: #f8f9fa;
            padding: 5px 10px;
            margin-top: 20px;
            color: #2c3e50;
            border-left: 4px solid #3498db;
        }
        .info-paciente, .info-consulta {
            margin-bottom: 15px;
        }
        .etiqueta {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        .tabla-parametros {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .tabla-parametros th, .tabla-parametros td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .tabla-parametros th {
            background-color: #f2f2f2;
        }
        .seccion-texto {
            text-align: justify;
            margin-bottom: 15px;
        }
        .pie-pagina {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>INFORME DE CONSULTA MÉDICA</h1>
    <div class="fecha-impresion">Fecha de impresión: <?php echo date('d/m/Y H:i:s'); ?></div>

    <h2>INFORMACIÓN DEL PACIENTE</h2>
    <div class="info-paciente">
        <p><span class="etiqueta">Nombre completo:</span> <?php echo htmlspecialchars($persona['nombres'] . ' ' . $persona['apellidos']); ?></p>
        <p><span class="etiqueta">Documento:</span> <?php echo htmlspecialchars($persona['documento']); ?></p>
        <?php if (!empty($persona['nro_ficha'])): ?>
        <p><span class="etiqueta">Ficha:</span> <?php echo htmlspecialchars($persona['nro_ficha']); ?></p>
        <?php endif; ?>
        <p><span class="etiqueta">Fecha de nacimiento:</span> <?php echo date('d/m/Y', strtotime($persona['fecha_nacimiento'])); ?></p>
    </div>    <h2>DATOS DE LA CONSULTA</h2>
    <div class="info-consulta">
        <p><span class="etiqueta">Fecha de consulta:</span> <?php echo date('d/m/Y H:i', strtotime($fecha_consulta)); ?></p>
        <?php if (!empty($motivo)): ?>
        <p><span class="etiqueta">Motivo:</span> <?php echo htmlspecialchars($motivo); ?></p>
        <?php endif; ?>
    </div>

    <h2>PARÁMETROS VITALES</h2>
    <table class="tabla-parametros">
        <tr>
            <th>Parámetro</th>
            <th>Valor</th>
            <th>Parámetro</th>
            <th>Valor</th>
        </tr>
        <tr>
            <td>Visión OD</td>
            <td><?php echo htmlspecialchars($vision_od ?: '-'); ?></td>
            <td>Visión OI</td>
            <td><?php echo htmlspecialchars($vision_oi ?: '-'); ?></td>
        </tr>
        <tr>
            <td>Tensión OD</td>
            <td><?php echo htmlspecialchars($tension_od ?: '-'); ?></td>
            <td>Tensión OI</td>
            <td><?php echo htmlspecialchars($tension_oi ?: '-'); ?></td>
        </tr>
    </table>

    <?php if (!empty($descripcion)): ?>
    <h2>DESCRIPCIÓN DE LA CONSULTA</h2>
    <div class="seccion-texto">
        <?php echo $descripcion; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($receta)): ?>
    <h2>RECETA MÉDICA</h2>
    <div class="seccion-texto">
        <?php echo $receta; ?>
    </div>
    <?php endif; ?>

    <?php 
    $diagnosticos = $consultaModel->obtenerDiagnosticos($id_consulta);
    if (!empty($diagnosticos)): 
    ?>
    <h2>DIAGNÓSTICOS (ICD-11)</h2>
    <div>
        <?php foreach ($diagnosticos as $diagnostico): ?>
        <p>
            <span class="etiqueta">Código:</span> <?php echo htmlspecialchars($diagnostico['codigo'] ?: '-'); ?><br>
            <span class="etiqueta">Descripción:</span> <?php echo htmlspecialchars($diagnostico['descripcion'] ?: '-'); ?>
        </p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="pie-pagina">
        <p>Este documento es un informe médico generado automáticamente.</p>
        <p>Consulta generada el: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Generar un nombre único para el archivo PDF
$nombreArchivo = 'consulta_' . $id_consulta . '_' . date('YmdHis') . '.pdf';
$rutaPDF = 'pdf_consultas/' . $nombreArchivo;

// Verificar si el directorio existe, y si no, crearlo
if (!is_dir('pdf_consultas/')) {
    mkdir('pdf_consultas/', 0755, true);
}

// Cargar HTML en Dompdf y renderizar
$dompdf->loadHtml($html);
$dompdf->render();

// Guardar el PDF en el servidor
file_put_contents($rutaPDF, $dompdf->output());

// Devolver el PDF al navegador
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
readfile($rutaPDF);
?>
