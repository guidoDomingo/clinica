<?php
/**
 * Script para probar la generación de PDF
 * Muestra los datos de la reserva antes de generar el PDF
 */

// Incluir las clases necesarias
require_once 'model/conexion.php';
require_once 'model/reservas.model.php';
require_once 'model/reservas_tolerante.php';

// Obtener el ID de la reserva
$reservaId = isset($_GET['id']) ? intval($_GET['id']) : 20;

echo "<h1>Datos de la Reserva ID: $reservaId</h1>";
echo "<pre>";

// Intentar obtener la reserva con el método estándar
$modelo = new ReservasModel();
$reserva = $modelo->obtenerReservaPorId($reservaId);

if ($reserva) {
    echo "Reserva encontrada con método estándar:\n";
    print_r($reserva);
} else {
    echo "No se encontró la reserva con el método estándar.\n";
    
    // Intentar con el método tolerante
    $reserva = obtenerReservaPorIdTolerante($reservaId);
    
    if ($reserva) {
        echo "Reserva encontrada con método tolerante:\n";
        print_r($reserva);
    } else {
        echo "No se pudo encontrar la reserva con ningún método.\n";
        
        // Verificar si existen reservas
        $conn = Conexion::conectar();
        $query = "SELECT reserva_id FROM servicios_reservas LIMIT 5";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($ids)) {
            echo "\nIDs de reservas disponibles: " . implode(", ", $ids);
            echo "\nIntenta con: <a href='?id=" . $ids[0] . "'>Reserva ID " . $ids[0] . "</a>";
        } else {
            echo "\nNo hay reservas en la base de datos.";
        }
    }
}

echo "</pre>";

// Mostrar enlaces útiles
echo "<hr>";
echo "<p><a href='generar_pdf_reserva.php?id=$reservaId' target='_blank'>Generar PDF para esta reserva</a></p>";
echo "<p><a href='diagnostico_reserva.php?id=$reservaId'>Ver diagnóstico completo</a></p>";
echo "<p><a href='listar_reservas.php'>Ver lista de todas las reservas</a></p>";

?>
