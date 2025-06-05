<?php
/**
 * Controlador AJAX para operaciones CRUD de servicios (rs_servicios)
 */

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = dirname(__FILE__, 2); // Obtiene la ruta del directorio raíz (dos niveles arriba)
require_once $rutaBase . "/model/conexion.php";
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

class AjaxRsServicios {
    
    /**
     * Listar todos los servicios
     */
    public static function listarServicios() {
        $servicios = ModelServicios::mdlObtenerTodosRsServicios();
        echo json_encode($servicios);
    }
    
    /**
     * Listar tipos de servicio
     */
    public static function listarTiposServicio() {
        $tipos = ModelServicios::mdlObtenerTiposRsServicio();
        echo json_encode($tipos);
    }
    
    /**
     * Crear nuevo servicio
     */
    public static function crearServicio($datos) {
        $respuesta = ModelServicios::mdlCrearRsServicio($datos);
        echo json_encode($respuesta);
    }
    
    /**
     * Actualizar servicio existente
     */
    public static function actualizarServicio($datos) {
        $respuesta = ModelServicios::mdlActualizarRsServicio($datos);
        echo json_encode($respuesta);
    }
    
    /**
     * Eliminar servicio
     */
    public static function eliminarServicio($id) {
        $respuesta = ModelServicios::mdlEliminarRsServicio($id);
        echo json_encode($respuesta);
        return $respuesta;
    }
      /**
     * Crear nuevo tipo de servicio
     */
    public static function crearTipoServicio($nombre) {
        $respuesta = ModelServicios::mdlCrearTipoRsServicio($nombre);
        echo json_encode($respuesta);
    }
    
    /**
     * Actualizar tipo de servicio
     */
    public static function actualizarTipoServicio($id, $nombre) {
        $respuesta = ModelServicios::mdlActualizarTipoRsServicio($id, $nombre);
        echo json_encode($respuesta);
    }
    
    /**
     * Eliminar tipo de servicio
     */
    public static function eliminarTipoServicio($id) {
        $respuesta = ModelServicios::mdlEliminarTipoRsServicio($id);
        echo json_encode($respuesta);
    }
    
    /**
     * Filtrar servicios
     */
    public static function filtrarServicios($filtros) {
        $servicios = ModelServicios::mdlFiltrarRsServicios($filtros);
        echo json_encode($servicios);
    }
}

// Procesar la petición AJAX
if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $ajax = new AjaxRsServicios();
    
    switch ($accion) {
        case 'listar':
            $ajax::listarServicios();
            break;
            
        case 'listarTipos':
            $ajax::listarTiposServicio();
            break;
            
        case 'crear':
            if (isset($_POST['servicio'])) {
                $ajax::crearServicio($_POST['servicio']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
            }
            break;
            
        case 'actualizar':
            if (isset($_POST['servicio'])) {
                $ajax::actualizarServicio($_POST['servicio']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
            }
            break;
            
        case 'eliminar':
            if (isset($_POST['id'])) {
                $ajax::eliminarServicio($_POST['id']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'ID no especificado']);
            }
            break;
            
        case 'crearTipo':
            if (isset($_POST['nombre'])) {
                $ajax::crearTipoServicio($_POST['nombre']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'Nombre no especificado']);
            }
            break;
            
        case 'actualizarTipo':
            if (isset($_POST['id']) && isset($_POST['nombre'])) {
                $ajax::actualizarTipoServicio($_POST['id'], $_POST['nombre']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
            }
            break;
            
        case 'eliminarTipo':
            if (isset($_POST['id'])) {
                $ajax::eliminarTipoServicio($_POST['id']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'ID no especificado']);
            }
            break;
              case 'filtrar':
            $filtros = [
                'codigo' => isset($_POST['codigo']) ? $_POST['codigo'] : '',
                'descripcion' => isset($_POST['descripcion']) ? $_POST['descripcion'] : '',
                'tipo' => isset($_POST['tipo']) ? $_POST['tipo'] : '0'
            ];
            
            // Log para depuración
            error_log('Filtrando servicios con: ' . json_encode($filtros));
            
            $ajax::filtrarServicios($filtros);
            break;
            
        default:
            echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida']);
            break;
    }
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'No se especificó ninguna acción']);
}
