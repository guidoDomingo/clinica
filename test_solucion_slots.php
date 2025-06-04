<?php
/**
 * Script para verificar la solución del problema de slots
 */

// Incluir archivos necesarios
$rutaBase = dirname(__FILE__);
require_once $rutaBase . "/controller/servicios.controller.php";
require_once $rutaBase . "/model/servicios.model.php";

// Configurar zona horaria
date_default_timezone_set('America/Caracas');

// Script de prueba para diferentes fechas y horas
echo "====================================================\n";
echo "PRUEBA DE SOLUCIÓN PARA SLOTS DE FECHAS FUTURAS\n";
echo "====================================================\n\n";

// Configuración
$doctorId = 13; // Antonio Galeano
$servicioId = 2; // Cirugía de cataratas

// Establecer la fecha y hora actual
$fechaActual = '2025-06-03'; // Hoy
$horaActual = date('H:i:s'); // Hora actual del sistema

echo "Fecha actual: $fechaActual, Hora actual: $horaActual\n\n";

// Crear dos fechas adicionales para pruebas
$fechaProxima = '2025-06-04'; // Mañana
$fechaFutura = '2025-06-10'; // Próxima semana

// Obtener slots disponibles para el doctor en la fecha actual
echo "1. SLOTS PARA LA FECHA ACTUAL ($fechaActual):\n";
echo "--------------------------------------------\n";
$slotsHoy = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fechaActual);
if (count($slotsHoy) > 0) {
    foreach ($slotsHoy as $i => $slot) {
        echo "Slot " . ($i + 1) . ": " . $slot['hora_inicio'] . " - " . $slot['hora_fin'] . "\n";
    }
} else {
    echo "No hay slots disponibles para hoy (esto es correcto si son más de las 16:00)\n";
}
echo "\n";

// Obtener slots disponibles para el doctor en la fecha próxima
echo "2. SLOTS PARA FECHA PRÓXIMA ($fechaProxima):\n";
echo "--------------------------------------------\n";
$slotsProximos = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fechaProxima);
if (count($slotsProximos) > 0) {
    foreach ($slotsProximos as $i => $slot) {
        echo "Slot " . ($i + 1) . ": " . $slot['hora_inicio'] . " - " . $slot['hora_fin'] . "\n";
    }
} else {
    echo "No hay slots disponibles para el " . $fechaProxima . 
         " (verificar si el doctor tiene agenda ese día)\n";
}
echo "\n";

// Obtener slots disponibles para el doctor en la fecha futura
echo "3. SLOTS PARA FECHA FUTURA ($fechaFutura):\n";
echo "--------------------------------------------\n";
$slotsFuturos = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fechaFutura);
if (count($slotsFuturos) > 0) {
    foreach ($slotsFuturos as $i => $slot) {
        echo "Slot " . ($i + 1) . ": " . $slot['hora_inicio'] . " - " . $slot['hora_fin'] . "\n";
    }
} else {
    echo "No hay slots disponibles para el " . $fechaFutura . 
         " (verificar si el doctor tiene agenda ese día)\n";
}

echo "\n====================================================\n";
echo "CONCLUSIÓN:\n";
echo "- Para el día actual: " . (count($slotsHoy) > 0 ? "Se muestran slots si la hora no ha pasado" : "No se muestran slots si la hora ya pasó") . "\n";
echo "- Para fechas futuras: " . ((count($slotsProximos) > 0 || count($slotsFuturos) > 0) ? "Se muestran todos los slots configurados" : "No hay slots configurados para las fechas probadas") . "\n";
echo "====================================================\n";

// Guardar en archivo de log
error_log("Prueba de solución ejecutada: " . date('Y-m-d H:i:s') . "\n" .
          "Slots para hoy: " . count($slotsHoy) . "\n" .
          "Slots para mañana: " . count($slotsProximos) . "\n" .
          "Slots para fecha futura: " . count($slotsFuturos) . "\n",
          3, 'c:/laragon/www/clinica/logs/tests.log');
