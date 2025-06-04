<?php
/**
 * Script para probar la validación de horarios pasados
 * Este script simula diferentes escenarios para comprobar que los horarios pasados
 * no se muestren correctamente en el sistema.
 */

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = __DIR__; // Directorio actual
require_once $rutaBase . "/controller/servicios.controller.php";
require_once $rutaBase . "/model/servicios.model.php";

// Verificar tiempo del servidor
date_default_timezone_set('America/Caracas');
$ahora = new DateTime();
$fechaHoy = $ahora->format('Y-m-d');
$horaActual = $ahora->format('H:i:s');

echo "=== TEST DE VALIDACIÓN DE HORARIOS PASADOS ===\n";
echo "Fecha y hora del servidor: " . $ahora->format('Y-m-d H:i:s') . "\n";
echo "Zona horaria: " . date_default_timezone_get() . "\n";
echo "------------------------------------------------\n\n";

// Parámetros para la prueba
$servicioId = 1;
$doctorId = 13; // ID del doctor Antonio Galeano

// CASO 1: Probar con fecha de hoy
echo "CASO 1: SLOTS PARA HOY (" . $fechaHoy . ")\n";
echo "------------------------------------------------\n";
$slotsPrueba1 = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fechaHoy);
echo "Slots generados para hoy: " . count($slotsPrueba1) . "\n";
if (count($slotsPrueba1) > 0) {
    foreach ($slotsPrueba1 as $i => $slot) {
        $slotDateTime = new DateTime($fechaHoy . ' ' . $slot['hora_inicio']);
        $diferenciaMinutos = ($slotDateTime->getTimestamp() - $ahora->getTimestamp()) / 60;
        
        echo sprintf(
            "Slot %d: %s - %s | Diferencia con hora actual: %.1f minutos | %s\n",
            $i + 1,
            substr($slot['hora_inicio'], 0, 5),
            substr($slot['hora_fin'], 0, 5),
            $diferenciaMinutos,
            $diferenciaMinutos > 0 ? "FUTURO (✓)" : "PASADO (✗)"
        );
    }
} else {
    echo "No hay slots disponibles para hoy (esto es esperado si todos los horarios ya pasaron)\n";
}

// CASO 2: Probar con fecha futura
$fechaManiana = (new DateTime('tomorrow'))->format('Y-m-d');
echo "\nCASO 2: SLOTS PARA MAÑANA (" . $fechaManiana . ")\n";
echo "------------------------------------------------\n";
$slotsPrueba2 = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fechaManiana);
echo "Slots generados para mañana: " . count($slotsPrueba2) . "\n";
if (count($slotsPrueba2) > 0) {
    echo "Primer slot: " . substr($slotsPrueba2[0]['hora_inicio'], 0, 5) . " - " . substr($slotsPrueba2[0]['hora_fin'], 0, 5) . "\n";
    echo "Último slot: " . substr($slotsPrueba2[count($slotsPrueba2) - 1]['hora_inicio'], 0, 5) . " - " . substr($slotsPrueba2[count($slotsPrueba2) - 1]['hora_fin'], 0, 5) . "\n";
} else {
    echo "No hay slots configurados para mañana\n";
}

// CASO 3: Mostrar información de franjas horarias comunes
echo "\nCASO 3: INFORMACIÓN DE FRANJAS HORARIAS\n";
echo "------------------------------------------------\n";

// Definir algunas franjas horarias típicas para análisis
$franjasHorarias = [
    ['08:00:00', '09:00:00', 'Mañana temprano'],
    ['12:00:00', '14:00:00', 'Mediodía'],
    ['18:00:00', '20:00:00', 'Tarde'],
    ['20:00:00', '22:00:00', 'Noche']
];

// Comprobar si slots existentes caen en estas franjas
echo "Análisis de franjas horarias para hoy:\n";

foreach ($franjasHorarias as $franja) {
    $inicio = new DateTime($fechaHoy . ' ' . $franja[0]);
    $fin = new DateTime($fechaHoy . ' ' . $franja[1]);
    $nombre = $franja[2];
    
    $slotsEnFranja = 0;
    $slotsDisponiblesEnFranja = [];
    
    foreach ($slotsPrueba1 as $slot) {
        $slotInicio = new DateTime($fechaHoy . ' ' . $slot['hora_inicio']);
        $slotFin = new DateTime($fechaHoy . ' ' . $slot['hora_fin']);
        
        // Si el slot está dentro de esta franja
        if (($slotInicio >= $inicio && $slotInicio < $fin) || 
            ($slotFin > $inicio && $slotFin <= $fin) ||
            ($slotInicio <= $inicio && $slotFin >= $fin)) {
            $slotsEnFranja++;
            $slotsDisponiblesEnFranja[] = substr($slot['hora_inicio'], 0, 5) . '-' . substr($slot['hora_fin'], 0, 5);
        }
    }
    
    echo "Franja " . $nombre . " (" . substr($franja[0], 0, 5) . " - " . substr($franja[1], 0, 5) . "): ";
    echo $slotsEnFranja . " slots disponibles\n";
    
    if ($slotsEnFranja > 0) {
        echo "   Slots: " . implode(", ", $slotsDisponiblesEnFranja) . "\n";
    }
    
    // Verificar si esta franja ya pasó, está en curso o es futura
    $estado = "futura";
    if ($ahora > $fin) {
        $estado = "pasada";
    } elseif ($ahora >= $inicio && $ahora <= $fin) {
        $estado = "en curso";
    }    echo "   Estado de la franja: " . $estado . "\n";
}

echo "\n=== TEST COMPLETADO ===\n";
