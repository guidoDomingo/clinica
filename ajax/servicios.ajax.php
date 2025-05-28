<?php
/**
 * Archivo para procesar peticiones AJAX relacionadas con servicios médicos
 */

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = dirname(__FILE__, 2); // Obtiene la ruta del directorio raíz (dos niveles arriba)
require_once $rutaBase . "/controller/servicios.controller.php";
require_once $rutaBase . "/model/servicios.model.php";

// Configurar cabeceras para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');
date_default_timezone_set('America/Caracas');

// Iniciar sesión si no está iniciada
if (!isset($_SESSION)) {
    session_start();
}

// Procesar la acción solicitada
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'obtenerCategorias':
            $categorias = ControladorServicios::ctrObtenerCategorias();
            echo json_encode([
                "status" => "success",
                "data" => $categorias
            ]);
            break;
            
        case 'obtenerServicios':
            $categoriaId = isset($_POST['categoria_id']) ? $_POST['categoria_id'] : null;
            $servicios = ControladorServicios::ctrObtenerServicios($categoriaId);
            echo json_encode([
                "status" => "success",
                "data" => $servicios
            ]);
            break;
            
        case 'obtenerServicioPorId':
            if (isset($_POST['servicio_id'])) {
                $servicioId = $_POST['servicio_id'];
                $datos = ControladorServicios::ctrObtenerServicioPorId($servicioId);
                echo json_encode([
                    "status" => "success",
                    "data" => $datos
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID de servicio no proporcionado"
                ]);
            }
            break;
            
        case 'obtenerHorariosMedico':
            if (isset($_POST['doctor_id'])) {
                $doctorId = $_POST['doctor_id'];
                $horarios = ControladorServicios::ctrObtenerHorariosMedico($doctorId);
                echo json_encode([
                    "status" => "success",
                    "data" => $horarios
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID de doctor no proporcionado"
                ]);
            }
            break;
            
        case 'generarSlotsDisponibles':
            if (isset($_POST['servicio_id']) && isset($_POST['doctor_id']) && isset($_POST['fecha'])) {
                $servicioId = $_POST['servicio_id'];
                $doctorId = $_POST['doctor_id'];
                $fecha = $_POST['fecha'];
                
                $slots = ControladorServicios::ctrGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
                echo json_encode([
                    "status" => "success",
                    "data" => $slots
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Faltan parámetros requeridos"
                ]);
            }
            break;
            
        case 'obtenerMedicosPorFecha':
            if (isset($_POST['fecha'])) {
                $fecha = $_POST['fecha'];
                $medicos = ControladorServicios::ctrObtenerMedicosDisponiblesPorFecha($fecha);
                echo json_encode([
                    "status" => "success",
                    "data" => $medicos
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Fecha no proporcionada"
                ]);
            }
            break;
            
        case 'obtenerServiciosPorFechaMedico':
            if (isset($_POST['fecha']) && isset($_POST['doctor_id'])) {
                $fecha = $_POST['fecha'];
                $doctorId = $_POST['doctor_id'];
                $servicios = ControladorServicios::ctrObtenerServiciosPorFechaMedico($fecha, $doctorId);
                echo json_encode([
                    "status" => "success",
                    "data" => $servicios
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Faltan parámetros requeridos"
                ]);
            }
            break;
            
        case 'obtenerReservas':
            $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
            $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
            $estado = isset($_POST['estado']) ? $_POST['estado'] : null;
            
            $reservas = ControladorServicios::ctrObtenerReservasPorFecha($fecha, $doctorId, $estado);
            echo json_encode([
                "status" => "success",
                "data" => $reservas
            ]);
            break;
            
        case 'crearReserva':
            // Recopilar datos desde la petición POST
            $datos = [
                'servicio_id' => $_POST['servicio_id'] ?? null,
                'doctor_id' => $_POST['doctor_id'] ?? null,
                'paciente_id' => $_POST['persona_id'] ?? null,
                'fecha_reserva' => $_POST['fecha_reserva'] ?? null,
                'hora_inicio' => $_POST['hora_inicio'] ?? null,
                'hora_fin' => $_POST['hora_fin'] ?? null
            ];
            
            // Datos opcionales
            if (isset($_POST['agenda_id'])) $datos['agenda_id'] = $_POST['agenda_id'];
            if (isset($_POST['observaciones'])) $datos['observaciones'] = $_POST['observaciones'];
            if (isset($_POST['sala_id'])) $datos['sala_id'] = $_POST['sala_id'];
            if (isset($_POST['tarifa_id'])) $datos['tarifa_id'] = $_POST['tarifa_id'];
            if (isset($_POST['precio_final'])) $datos['precio_final'] = $_POST['precio_final'];
            
            // Datos de usuario
            if (isset($_SESSION['user_id'])) {
                $datos['created_by'] = $_SESSION['user_id'];
            }
            if (isset($_SESSION['business_id'])) {
                $datos['business_id'] = $_SESSION['business_id'];
            }
            
            $resultado = ControladorServicios::ctrCrearReserva($datos);
            echo json_encode($resultado);
            break;
            
        case 'cambiarEstadoReserva':
            if (isset($_POST['reserva_id']) && isset($_POST['estado'])) {
                $reservaId = $_POST['reserva_id'];
                $estado = $_POST['estado'];
                
                $resultado = ControladorServicios::ctrCambiarEstadoReserva($reservaId, $estado);
                echo json_encode($resultado);
            } else {
                echo json_encode([
                    "error" => true,
                    "mensaje" => "Faltan parámetros requeridos"
                ]);
            }
            break;        case 'buscarPaciente':
            if (isset($_POST['termino'])) {
                $termino = $_POST['termino'];
                $pacientes = ControladorServicios::ctrBuscarPaciente($termino);
                
                // Debug info
                error_log("Búsqueda de paciente: " . $termino . " - Resultados: " . count($pacientes));
                
                echo json_encode([
                    "status" => "success",
                    "data" => $pacientes
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Término de búsqueda no proporcionado"
                ]);
            }
            break;
            
        case 'guardarReserva':
            if (isset($_POST['doctor_id']) && isset($_POST['servicio_id']) && isset($_POST['paciente_id']) && 
                isset($_POST['fecha']) && isset($_POST['hora_inicio']) && isset($_POST['hora_fin'])) {
                
                // Capturar datos de la reserva
                $datos = [
                    'doctor_id' => intval($_POST['doctor_id']),
                    'servicio_id' => intval($_POST['servicio_id']),
                    'paciente_id' => intval($_POST['paciente_id']),
                    'fecha' => $_POST['fecha'],
                    'hora_inicio' => $_POST['hora_inicio'],
                    'hora_fin' => $_POST['hora_fin'],
                    'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : ''
                ];
                
                try {
                    // Guardar la reserva
                    $resultado = ControladorServicios::ctrGuardarReserva($datos);
                    
                    if ($resultado) {
                        echo json_encode([
                            "status" => "success",
                            "message" => "Reserva guardada exitosamente",
                            "reserva_id" => $resultado
                        ]);
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => "No se pudo guardar la reserva"
                        ]);
                    }
                } catch (Exception $e) {
                    error_log("Error al guardar reserva: " . $e->getMessage());
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al guardar la reserva: " . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Faltan datos requeridos para guardar la reserva"
                ]);
            }
            break;
            
        default:
            echo json_encode([
                "status" => "error",
                "message" => "Acción no reconocida: " . $action
            ]);
            break;
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No se especificó una acción"
    ]);
}
