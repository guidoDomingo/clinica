<?php
/**
 * Generador de PDF de confirmación de reserva
 * Este script genera un PDF con los datos de la reserva utilizando FPDF
 */

// Incluir clases necesarias
require_once 'lib/fpdf_simple.php'; // Versión simplificada de FPDF
require_once 'model/conexion.php';
require_once 'model/reservas.model.php';

// Verificar que se proporciona un ID de reserva
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID de reserva no especificado');
}

$reservaId = intval($_GET['id']);

try {
    // Obtener datos de la reserva
    $modelo = new ReservasModel();
    $reserva = $modelo->obtenerReservaPorId($reservaId);
    
    if (!$reserva) {
        die('Reserva no encontrada');
    }
    
    // Crear instancia de FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Configuración de fuentes y estilos
    $pdf->SetFont('Arial', 'B', 16);
    
    // Logo de la clínica (ajustar la ruta según la ubicación de tu logo)
    if (file_exists('view/img/logo.png')) {
        $pdf->Image('view/img/logo.png', 10, 10, 30);
    }
    
    // Título
    $pdf->Cell(0, 20, 'CONFIRMACIÓN DE CITA MÉDICA', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    
    // Información de la clínica
    $pdf->Cell(0, 10, 'CLÍNICA MÉDICA', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Dirección: Av. Principal 123, Ciudad', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Teléfono: (123) 456-7890', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Email: info@clinica.com', 0, 1, 'C');
    
    $pdf->Ln(10);
    
    // Información de la reserva
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'DATOS DE LA CITA:', 0, 1);
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(60, 10, 'N° de Reserva:', 0, 0);
    $pdf->Cell(0, 10, $reserva['id'], 0, 1);
    
    $pdf->Cell(60, 10, 'Paciente:', 0, 0);
    $pdf->Cell(0, 10, $reserva['nombre_paciente'], 0, 1);
    
    $pdf->Cell(60, 10, 'Documento:', 0, 0);
    $pdf->Cell(0, 10, $reserva['documento_paciente'], 0, 1);
    
    $pdf->Cell(60, 10, 'Servicio:', 0, 0);
    $pdf->Cell(0, 10, $reserva['servicio'], 0, 1);
    
    $pdf->Cell(60, 10, 'Médico:', 0, 0);
    $pdf->Cell(0, 10, $reserva['nombre_medico'], 0, 1);
    
    $pdf->Cell(60, 10, 'Fecha:', 0, 0);
    $pdf->Cell(0, 10, date('d/m/Y', strtotime($reserva['fecha'])), 0, 1);
    
    $pdf->Cell(60, 10, 'Hora:', 0, 0);
    $pdf->Cell(0, 10, $reserva['hora'], 0, 1);
    
    $pdf->Ln(10);
    
    // Instrucciones para el paciente
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'INSTRUCCIONES:', 0, 1);
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, 'Por favor presentarse 15 minutos antes de la cita programada. Traer su documento de identidad y carnet de seguro médico (si aplica).', 0, 'L');
    
    $pdf->Ln(5);
    $pdf->MultiCell(0, 10, 'En caso de no poder asistir, favor cancelar la cita con al menos 24 horas de anticipación.', 0, 'L');
    
    $pdf->Ln(5);
    $pdf->MultiCell(0, 10, '¡Gracias por confiar en nosotros para el cuidado de su salud!', 0, 'L');
    
    $pdf->Ln(20);
    
    // Pie de página
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'Este documento es una constancia de su cita médica programada.', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Fecha de emisión: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    
    // Salida del PDF
    $pdf->Output('Confirmacion_Cita_' . $reservaId . '.pdf', 'I');
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
