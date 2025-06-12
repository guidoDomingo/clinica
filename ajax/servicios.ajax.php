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

               //agregar un log para verificar los datos recibidos
                error_log("AJAX generarSlotsDisponibles: ServicioID=$servicioId, DoctorID=$doctorId, Fecha=$fecha", 3, 'c:/laragon/www/clinica/logs/slots.log');
                
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
            $doctorId = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null;
            $estado = isset($_POST['estado']) ? $_POST['estado'] : null;
            
            error_log("AJAX obtenerReservas: Fecha=$fecha, DoctorID=" . ($doctorId ?? "null") . ", Estado=" . ($estado ?? "null"), 3, 'c:/laragon/www/clinica/logs/reservas.log');
            
            try {
                $reservas = ControladorServicios::ctrObtenerReservasPorFecha($fecha, $doctorId, $estado);
                
                error_log("AJAX obtenerReservas: Se encontraron " . count($reservas) . " reservas", 3, 'c:/laragon/www/clinica/logs/reservas.log');
                if (count($reservas) > 0) {
                    error_log("AJAX obtenerReservas: Primera reserva: " . json_encode($reservas[0]), 3, 'c:/laragon/www/clinica/logs/reservas.log');
                } else {
                    error_log("AJAX obtenerReservas: No se encontraron reservas para esta fecha", 3, 'c:/laragon/www/clinica/logs/reservas.log');
                }
                
                echo json_encode([
                    "status" => "success",
                    "data" => $reservas
                ]);
            } catch (Exception $e) {
                error_log("AJAX obtenerReservas ERROR: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al obtener reservas: " . $e->getMessage(),
                    "data" => []
                ]);
            }
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
            if (isset($_POST['reserva_id']) && isset($_POST['nuevo_estado'])) {
                $reservaId = $_POST['reserva_id'];
                $estado = $_POST['nuevo_estado'];
                
                $resultado = ControladorServicios::ctrCambiarEstadoReserva($reservaId, $estado);
                
                // Adaptar la respuesta al formato esperado por el cliente
                echo json_encode([
                    "status" => $resultado["error"] ? "error" : "success",
                    "mensaje" => $resultado["mensaje"]
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "mensaje" => "Faltan parámetros requeridos"
                ]);
            }
            break;
            
        case 'buscarPaciente':
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
            
        case 'buscarPacientePorId':
            if (isset($_POST['paciente_id'])) {
                $pacienteId = $_POST['paciente_id'];
                $paciente = ControladorServicios::ctrBuscarPacientePorId($pacienteId);
                
                // Debug info
                error_log("Búsqueda de paciente por ID: " . $pacienteId . " - Resultados: " . count($paciente));
                
                echo json_encode([
                    "status" => "success",
                    "data" => $paciente
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "ID de paciente no proporcionado"
                ]);
            }
            break;
            
        case 'guardarReserva':
            if (isset($_POST['doctor_id']) && isset($_POST['servicio_id']) && isset($_POST['paciente_id']) && 
                isset($_POST['fecha_reserva']) && isset($_POST['hora_inicio']) && isset($_POST['hora_fin'])) {
                
                // Capturar datos de la reserva
                $datos = [
                    'doctor_id' => intval($_POST['doctor_id']),
                    'servicio_id' => intval($_POST['servicio_id']),
                    'paciente_id' => intval($_POST['paciente_id']),
                    'fecha_reserva' => $_POST['fecha_reserva'],
                    'hora_inicio' => $_POST['hora_inicio'],
                    'hora_fin' => $_POST['hora_fin'],
                    'seguro_id' => $_POST['seguro_id'],
                    'observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : ''
                ];
                
                // Agregar campos opcionales
                if (isset($_POST['agenda_id']) && !empty($_POST['agenda_id'])) {
                    $datos['agenda_id'] = intval($_POST['agenda_id']);
                }
                
                if (isset($_POST['tarifa_id']) && !empty($_POST['tarifa_id'])) {
                    $datos['tarifa_id'] = intval($_POST['tarifa_id']);
                }
                
                if (isset($_POST['sala_id']) && !empty($_POST['sala_id'])) {
                    $datos['sala_id'] = intval($_POST['sala_id']);
                }
                
                // Registrar intento de guardar reserva
                error_log("AJAX guardarReserva: Datos recibidos = " . json_encode($datos), 3, 'c:/laragon/www/clinica/logs/reservas.log');
                
                try {
                    // Guardar la reserva
                    $resultado = ControladorServicios::ctrGuardarReserva($datos);
                    
                    if ($resultado) {
                        echo json_encode([
                            "status" => "success",
                            "message" => "Reserva guardada exitosamente",
                            "reserva_id" => $resultado
                        ]);
                        error_log("AJAX guardarReserva: Reserva creada con ID " . $resultado, 3, 'c:/laragon/www/clinica/logs/reservas.log');
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => "No se pudo guardar la reserva. Verifique que no haya conflictos de horarios."
                        ]);
                        error_log("AJAX guardarReserva: No se pudo guardar la reserva (resultado=false)", 3, 'c:/laragon/www/clinica/logs/reservas.log');
                    }
                } catch (Exception $e) {
                    error_log("AJAX guardarReserva: Excepción - " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/reservas.log');
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al guardar la reserva: " . $e->getMessage()
                    ]);
                }
            } else {
                $camposFaltantes = [];
                $camposRequeridos = ['doctor_id', 'servicio_id', 'paciente_id', 'fecha_reserva', 'hora_inicio', 'hora_fin'];
                
                foreach ($camposRequeridos as $campo) {
                    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
                        $camposFaltantes[] = $campo;
                    }
                }
                
                $mensaje = "Faltan datos requeridos para guardar la reserva: " . implode(", ", $camposFaltantes);
                error_log("AJAX guardarReserva: " . $mensaje, 3, 'c:/laragon/www/clinica/logs/reservas.log');
                
                echo json_encode([
                    "status" => "error",
                    "message" => $mensaje,
                    "campos_faltantes" => $camposFaltantes
                ]);
            }
            break;
            
        case 'obtenerProveedoresSeguro':
            try {
                $proveedores = ControladorServicios::ctrObtenerProveedoresSeguro();
                echo json_encode([
                    "status" => "success",
                    "data" => $proveedores
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al obtener proveedores de seguro: " . $e->getMessage()
                ]);
            }
            break;
              case 'buscarReservas':
            // Debug para verificar los datos recibidos
            error_log("AJAX buscarReservas: POST=" . json_encode($_POST), 3, 'c:/laragon/www/clinica/logs/reservas.log');
            
            // Procesar todos los parámetros de filtro
            $fecha = isset($_POST['fecha']) && !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
            
            // Asegurar que doctorId sea tratado correctamente como entero o null
            $doctorId = null;
            if (isset($_POST['doctor_id']) && $_POST['doctor_id'] !== '0' && $_POST['doctor_id'] !== '') {
                $doctorId = intval($_POST['doctor_id']);
                error_log("AJAX buscarReservas: doctor_id recibido={$_POST['doctor_id']}, convertido a int={$doctorId}", 3, 'c:/laragon/www/clinica/logs/reservas.log');
            }
            
            // Procesar filtro de estado
            $estado = null;
            if (isset($_POST['estado']) && $_POST['estado'] !== '0' && $_POST['estado'] !== '') {
                $estado = trim($_POST['estado']);
            }
            
            // Procesar filtro de paciente
            $paciente = null;
            if (isset($_POST['paciente']) && !empty($_POST['paciente'])) {
                $paciente = trim($_POST['paciente']);
            }
            
            error_log("AJAX buscarReservas (procesado): Fecha=" . ($fecha ?? "null") . 
                      ", DoctorID=" . ($doctorId ?? "null") . " (tipo: " . gettype($doctorId) . ")" .
                      ", Estado=" . ($estado ?? "null") . 
                      ", Paciente=" . ($paciente ?? "null"), 
                      3, 'c:/laragon/www/clinica/logs/reservas.log');
            
            try {
                // Verificación previa de registros (opcional)
                if ($doctorId !== null) {
                    $db = Conexion::conectar();
                    $check = $db->prepare("SELECT COUNT(*) FROM servicios_reservas WHERE doctor_id = ?");
                    $check->execute([$doctorId]);
                    $count = $check->fetchColumn();
                    error_log("AJAX buscarReservas: Verificación previa - Existen {$count} reservas con doctor_id={$doctorId}", 3, 'c:/laragon/www/clinica/logs/reservas.log');
                }
                
                // Obtener reservas según los filtros
                $reservas = ControladorServicios::ctrBuscarReservas($fecha, $doctorId, $estado, $paciente);
                
                // Enviar respuesta con información de filtros para depuración
                echo json_encode([
                    "status" => "success",
                    "data" => $reservas,
                    "filtros" => [
                        "fecha" => $fecha,
                        "doctor_id" => $doctorId,
                        "estado" => $estado,
                        "paciente" => $paciente
                    ]
                ]);
            } catch (Exception $e) {
                error_log("AJAX buscarReservas ERROR: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "mensaje" => "Error al buscar reservas: " . $e->getMessage(),
                    "debug_info" => [
                        "fecha" => $fecha,
                        "doctor_id" => $doctorId,
                        "estado" => $estado,
                        "paciente" => $paciente
                    ]
                ]);
            }
            break;
            
        case 'obtenerMedicos':
            try {
                $medicos = ControladorServicios::ctrObtenerMedicos();
                
                echo json_encode([
                    "status" => "success",
                    "data" => $medicos
                ]);
            } catch (Exception $e) {
                error_log("AJAX obtenerMedicos ERROR: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/reservas.log');
                echo json_encode([
                    "status" => "error",
                    "mensaje" => "Error al obtener médicos: " . $e->getMessage()
                ]);
            }
            break;
              case 'obtenerHorariosDisponibles':
            if (isset($_POST['doctor_id']) && isset($_POST['fecha'])) {
                // El servicio_id ahora es opcional
                $servicioId = isset($_POST['servicio_id']) ? $_POST['servicio_id'] : 0;
                $doctorId = $_POST['doctor_id'];
                $fecha = $_POST['fecha'];
                
                error_log("AJAX obtenerHorariosDisponibles: ServicioID=$servicioId, DoctorID=$doctorId, Fecha=$fecha", 3, 'c:/laragon/www/clinica/logs/slots.log');
                
                try {
                    // Llamar al método del controlador
                    $horarios = ControladorServicios::ctrObtenerHorariosDisponibles($servicioId, $doctorId, $fecha);
                    
                    // Si no hay horarios para el servicio específico, intentar obtener cualquier horario del doctor
                    if (empty($horarios) && $servicioId > 0) {
                        error_log("No se encontraron horarios para el servicio específico. Buscando cualquier horario del doctor.", 3, 'c:/laragon/www/clinica/logs/slots.log');
                        $horarios = ControladorServicios::ctrObtenerHorariosDisponibles(0, $doctorId, $fecha);
                    }
                    
                    // Si aún no hay horarios, crear horarios predeterminados para demo
                    if (empty($horarios)) {
                        $horarios = [];
                    }
                    
                    echo json_encode([
                        "status" => "success",
                        "data" => $horarios
                    ]);
                } catch (Exception $e) {
                    error_log("AJAX obtenerHorariosDisponibles ERROR: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/slots.log');
                    echo json_encode([
                        "status" => "error",
                        "message" => "Error al obtener horarios disponibles: " . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Faltan parámetros requeridos: doctor_id y fecha son obligatorios"
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
