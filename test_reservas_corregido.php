<?php
/**
 * Script para probar la visualización corregida de reservas
 * Este script verifica la funcionalidad usando la estructura real de la BD donde:
 * - No existe la tabla pacientes, se usa directamente rh_person
 * - No existe servicios_medicos, se usa rs_servicios
 */
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Cabecera HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Visualización Corregida de Reservas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        .debug-info {
            background-color: #f8f9fa;
            border-left: 4px solid #6c757d;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
        }
        .test-section {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Test de Visualización Corregida de Reservas</h1>';

// Obtener la fecha de hoy o la fecha proporcionada
$fecha = $_GET['fecha'] ?? date('Y-m-d');

echo '<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h2>Reservas para la fecha: ' . $fecha . '</h2>
        <form method="GET" class="mt-2">
            <div class="input-group">
                <input type="date" name="fecha" class="form-control" value="' . $fecha . '">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-light">Cambiar fecha</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body">';

try {
    // Obtener reservas para la fecha seleccionada
    $reservas = ControladorServicios::ctrObtenerReservasPorFecha($fecha);
    
    if (empty($reservas)) {
        echo '<div class="alert alert-warning">No se encontraron reservas para esta fecha.</div>';
    } else {
        echo '<h3>Se encontraron ' . count($reservas) . ' reservas:</h3>';
        
        echo '<table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Horario</th>
                    <th>Doctor</th>
                    <th>Paciente</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($reservas as $reserva) {
            // Formatear la hora para mostrar (HH:MM)
            $horaInicio = $reserva['hora_inicio'] ? substr($reserva['hora_inicio'], 0, 5) : '';
            $horaFin = $reserva['hora_fin'] ? substr($reserva['hora_fin'], 0, 5) : '';
            
            // Determinar clase para el estado
            $claseEstado = '';
            $estado = strtoupper($reserva['estado'] ?? 'PENDIENTE');
            
            switch ($estado) {
                case 'PENDIENTE': $claseEstado = 'badge-warning'; break;
                case 'CONFIRMADA': $claseEstado = 'badge-success'; break;
                case 'CANCELADA': $claseEstado = 'badge-danger'; break;
                case 'COMPLETADA': $claseEstado = 'badge-info'; break;
                default: $claseEstado = 'badge-secondary';
            }
            
            echo '<tr>
                <td>' . $reserva['reserva_id'] . '</td>
                <td>' . $horaInicio . ' - ' . $horaFin . '</td>
                <td>' . htmlspecialchars($reserva['doctor_nombre'] ?? 'No disponible') . '</td>
                <td>' . htmlspecialchars($reserva['paciente_nombre'] ?? 'No disponible') . '</td>
                <td>' . htmlspecialchars($reserva['servicio_nombre'] ?? 'No disponible') . '</td>
                <td><span class="badge ' . $claseEstado . '">' . $estado . '</span></td>
            </tr>';
        }
        
        echo '</tbody></table>';
        
        // Mostrar detalle de la primera reserva para debug
        echo '<div class="test-section">
            <h4>Detalle técnico de la primera reserva:</h4>
            <pre class="debug-info">' . json_encode($reservas[0], JSON_PRETTY_PRINT) . '</pre>
        </div>';
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
}

echo '</div></div>';

// Función para verificar existencia de una tabla y sus campos
function verificarTabla($db, $tabla, $camposClave = []) {
    $resultado = [
        'existe' => false,
        'campos' => []
    ];
    
    try {
        // Verificar si la tabla existe
        $stmt = $db->prepare("SELECT to_regclass('public." . $tabla . "')");
        $stmt->execute();
        $resultado['existe'] = $stmt->fetchColumn() !== null;
        
        if ($resultado['existe']) {
            // Contar registros
            $stmt = $db->prepare("SELECT COUNT(*) FROM " . $tabla);
            $stmt->execute();
            $resultado['total_registros'] = $stmt->fetchColumn();
            
            // Obtener estructura de columnas
            $stmt = $db->prepare("
                SELECT column_name, data_type, character_maximum_length
                FROM information_schema.columns
                WHERE table_name = :tabla
                ORDER BY ordinal_position
            ");
            $stmt->bindParam(':tabla', $tabla, PDO::PARAM_STR);
            $stmt->execute();
            $resultado['campos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Verificar campos clave
            if (!empty($camposClave)) {
                $nombresCampos = array_column($resultado['campos'], 'column_name');
                $resultado['campos_faltantes'] = array_diff($camposClave, $nombresCampos);
                $resultado['tiene_campos_clave'] = count($resultado['campos_faltantes']) === 0;
            }
            
            // Obtener muestra de datos
            if ($resultado['total_registros'] > 0) {
                $stmt = $db->prepare("SELECT * FROM " . $tabla . " LIMIT 2");
                $stmt->execute();
                $resultado['muestra'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        $resultado['error'] = $e->getMessage();
    }
    
    return $resultado;
}

echo '<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h2>Verificación de la estructura de la base de datos</h2>
    </div>
    <div class="card-body">';

try {
    $db = Conexion::conectar();
    
    // Definir las tablas a verificar con sus campos clave
    $tablasVerificar = [
        'servicios_reservas' => ['reserva_id', 'doctor_id', 'paciente_id', 'servicio_id', 'fecha_reserva'],
        'rs_servicios' => ['servicio_id', 'nombre', 'descripcion'],
        'rh_doctors' => ['doctor_id', 'person_id'],
        'rh_person' => ['person_id', 'first_name', 'last_name']
    ];
    
    foreach ($tablasVerificar as $tabla => $camposClave) {
        $infoTabla = verificarTabla($db, $tabla, $camposClave);
        
        echo '<div class="test-section">
            <h3>' . $tabla . '</h3>';
        
        if ($infoTabla['existe']) {
            echo '<div class="alert alert-success">
                <strong>✓ La tabla existe</strong> - ' . $infoTabla['total_registros'] . ' registros encontrados
            </div>';
            
            // Mostrar campos
            echo '<h4>Estructura de la tabla:</h4>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Columna</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($infoTabla['campos'] as $campo) {
                $esCamposClave = in_array($campo['column_name'], $camposClave);
                $clase = $esCamposClave ? 'table-success' : '';
                
                echo '<tr class="' . $clase . '">
                    <td>' . $campo['column_name'] . '</td>
                    <td>' . $campo['data_type'] . ($campo['character_maximum_length'] ? '(' . $campo['character_maximum_length'] . ')' : '') . '</td>
                    <td>' . ($esCamposClave ? 'Campo clave' : '') . '</td>
                </tr>';
            }
            
            echo '</tbody></table>';
            
            // Mostrar advertencia si faltan campos clave
            if (!empty($infoTabla['campos_faltantes'])) {
                echo '<div class="alert alert-warning">
                    <strong>⚠ Advertencia:</strong> Faltan los siguientes campos clave:
                    <ul>';
                
                foreach ($infoTabla['campos_faltantes'] as $campoFaltante) {
                    echo '<li>' . $campoFaltante . '</li>';
                }
                
                echo '</ul></div>';
            }
            
            // Mostrar muestra de datos
            if (!empty($infoTabla['muestra'])) {
                echo '<h4>Muestra de datos:</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>';
                
                // Encabezados
                foreach (array_keys($infoTabla['muestra'][0]) as $columna) {
                    echo '<th>' . $columna . '</th>';
                }
                
                echo '</tr></thead><tbody>';
                
                // Datos
                foreach ($infoTabla['muestra'] as $fila) {
                    echo '<tr>';
                    foreach ($fila as $valor) {
                        echo '<td>' . (is_null($valor) ? '<em>NULL</em>' : htmlspecialchars((string)$valor)) . '</td>';
                    }
                    echo '</tr>';
                }
                
                echo '</tbody></table>
                </div>';
            }
        } else {
            echo '<div class="alert alert-danger">
                <strong>❌ La tabla no existe</strong>
            </div>';
            
            if (!empty($infoTabla['error'])) {
                echo '<div class="alert alert-danger">
                    <strong>Error:</strong> ' . $infoTabla['error'] . '
                </div>';
            }
        }
        
        echo '</div>'; // test-section
    }
    
    // Probar consulta SQL directa
    echo '<div class="test-section">
        <h3>Consulta SQL Directa</h3>';
    
    // Consulta SQL para obtener pacientes
    echo '<h4>Test de consulta directa a rh_person:</h4>';
    try {
        $stmt = $db->prepare("SELECT person_id, first_name, last_name FROM rh_person LIMIT 5");
        $stmt->execute();
        $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($personas) > 0) {
            echo '<div class="alert alert-success">
                <strong>✓ Éxito:</strong> Se encontraron ' . count($personas) . ' personas.
            </div>';
            
            echo '<table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($personas as $persona) {
                echo '<tr>
                    <td>' . $persona['person_id'] . '</td>
                    <td>' . htmlspecialchars($persona['first_name']) . '</td>
                    <td>' . htmlspecialchars($persona['last_name']) . '</td>
                </tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-warning">
                <strong>⚠ Advertencia:</strong> No se encontraron registros en rh_person.
            </div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">
            <strong>❌ Error:</strong> ' . $e->getMessage() . '
        </div>';
    }
    
    // Consulta SQL para obtener servicios
    echo '<h4>Test de consulta directa a rs_servicios:</h4>';
    try {
        $stmt = $db->prepare("SELECT servicio_id, nombre FROM rs_servicios LIMIT 5");
        $stmt->execute();
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($servicios) > 0) {
            echo '<div class="alert alert-success">
                <strong>✓ Éxito:</strong> Se encontraron ' . count($servicios) . ' servicios.
            </div>';
            
            echo '<table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($servicios as $servicio) {
                echo '<tr>
                    <td>' . $servicio['servicio_id'] . '</td>
                    <td>' . htmlspecialchars($servicio['nombre']) . '</td>
                </tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-warning">
                <strong>⚠ Advertencia:</strong> No se encontraron registros en rs_servicios.
            </div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">
            <strong>❌ Error:</strong> ' . $e->getMessage() . '
        </div>';
    }
    
    echo '</div>'; // test-section
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">
        <strong>Error de conexión con la base de datos:</strong> ' . $e->getMessage() . '
    </div>';
}

echo '</div></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
