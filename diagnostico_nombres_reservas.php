<?php
/**
 * Script para diagnosticar y corregir problemas de nombres en reservas
 * Este script ayuda a identificar qué tablas y relaciones están disponibles para mostrar nombres
 * de doctores, pacientes y servicios en lugar de IDs
 */
require_once "model/conexion.php";

try {
    $db = Conexion::conectar();
    
    // Cabecera HTML
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Diagnóstico de Nombres en Reservas</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
        <style>
            .sql-code {
                background-color: #f8f9fa;
                border-radius: 4px;
                padding: 15px;
                font-family: monospace;
                white-space: pre-wrap;
                margin: 10px 0;
                border-left: 4px solid #007bff;
            }
            .success-block {
                background-color: #d4edda;
                border-radius: 4px;
                padding: 15px;
                margin: 10px 0;
                border-left: 4px solid #28a745;
            }
            .warning-block {
                background-color: #fff3cd;
                border-radius: 4px;
                padding: 15px;
                margin: 10px 0;
                border-left: 4px solid #ffc107;
            }
            .error-block {
                background-color: #f8d7da;
                border-radius: 4px;
                padding: 15px;
                margin: 10px 0;
                border-left: 4px solid #dc3545;
            }
        </style>
    </head>
    <body>
        <div class="container mt-4 mb-5">
            <h1>Diagnóstico de Nombres en Reservas</h1>
            <p class="lead">Este script analiza la estructura de la base de datos para determinar cómo obtener los nombres de doctores, pacientes y servicios.</p>';
    
    // Obtener información sobre las tablas principales
    $tablas = [
        'servicios_reservas' => [
            'descripcion' => 'Tabla principal de reservas',
            'campos_clave' => ['reserva_id', 'doctor_id', 'paciente_id', 'servicio_id', 'fecha_reserva', 'hora_inicio', 'hora_fin', 'reserva_estado']
        ],
        'servicios_medicos' => [
            'descripcion' => 'Tabla de servicios médicos',
            'campos_clave' => ['servicio_id', 'servicio_nombre', 'servicio_descripcion']
        ],
        'rh_doctors' => [
            'descripcion' => 'Tabla de doctores',
            'campos_clave' => ['doctor_id', 'person_id', 'nombre', 'doctor_nombre']
        ],
        'pacientes' => [
            'descripcion' => 'Tabla de pacientes',
            'campos_clave' => ['paciente_id', 'person_id', 'nombre', 'paciente_nombre']
        ],
        'rh_person' => [
            'descripcion' => 'Tabla de personas (información común para doctores y pacientes)',
            'campos_clave' => ['person_id', 'first_name', 'last_name']
        ]
    ];
    
    echo '<div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2>Análisis de Tablas Principales</h2>
        </div>
        <div class="card-body">';
    
    // Verificar cada tabla y sus campos
    foreach ($tablas as $tabla => $info) {
        echo '<h3>' . $tabla . ' - ' . $info['descripcion'] . '</h3>';
        
        // Verificar si la tabla existe
        $stmt = $db->prepare("SELECT to_regclass('public." . $tabla . "')");
        $stmt->execute();
        $tablaExiste = $stmt->fetchColumn();
        
        if (!$tablaExiste) {
            echo '<div class="error-block">
                <strong>Error:</strong> La tabla "' . $tabla . '" no existe en la base de datos.
            </div>';
            continue;
        }
        
        // Obtener estructura de la tabla
        $stmt = $db->prepare("
            SELECT column_name, data_type, character_maximum_length
            FROM information_schema.columns
            WHERE table_name = :tabla
            ORDER BY ordinal_position
        ");
        $stmt->bindParam(':tabla', $tabla, PDO::PARAM_STR);
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<div class="success-block">
            <strong>La tabla existe</strong> y tiene ' . count($columnas) . ' columnas.
        </div>';
        
        // Mostrar las columnas
        echo '<table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Columna</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';
            
        $columnasPorNombre = [];
        foreach ($columnas as $columna) {
            $columnasPorNombre[$columna['column_name']] = $columna;
            
            $esColumnaClave = in_array($columna['column_name'], $info['campos_clave']);
            $clase = $esColumnaClave ? 'table-success' : '';
            $estado = $esColumnaClave ? 'Campo clave' : '';
            
            echo '<tr class="' . $clase . '">
                <td>' . $columna['column_name'] . '</td>
                <td>' . $columna['data_type'] . ($columna['character_maximum_length'] ? '(' . $columna['character_maximum_length'] . ')' : '') . '</td>
                <td>' . $estado . '</td>
            </tr>';
        }
        
        echo '</tbody></table>';
        
        // Verificar campos clave faltantes
        $camposFaltantes = array_diff($info['campos_clave'], array_keys($columnasPorNombre));
        if (!empty($camposFaltantes)) {
            echo '<div class="warning-block">
                <strong>Advertencia:</strong> Faltan los siguientes campos clave en esta tabla:
                <ul>';
            
            foreach ($camposFaltantes as $campo) {
                echo '<li>' . $campo . '</li>';
            }
            
            echo '</ul>
            </div>';
        }
        
        // Contar registros
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM " . $tabla);
            $stmt->execute();
            $conteo = $stmt->fetchColumn();
            
            echo '<div class="info-block alert alert-info">
                <strong>Registros:</strong> ' . $conteo . ' registros en total.
            </div>';
            
            // Mostrar algunos ejemplos
            if ($conteo > 0) {
                $stmt = $db->prepare("SELECT * FROM " . $tabla . " LIMIT 3");
                $stmt->execute();
                $ejemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<h4>Ejemplos de registros:</h4>';
                echo '<div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>';
                
                // Encabezados de tabla
                foreach (array_keys($ejemplos[0]) as $columna) {
                    echo '<th>' . $columna . '</th>';
                }
                
                echo '</tr>
                        </thead>
                        <tbody>';
                
                // Filas de datos
                foreach ($ejemplos as $ejemplo) {
                    echo '<tr>';
                    foreach ($ejemplo as $valor) {
                        echo '<td>' . (is_null($valor) ? '<em>NULL</em>' : htmlspecialchars($valor)) . '</td>';
                    }
                    echo '</tr>';
                }
                
                echo '</tbody>
                    </table>
                </div>';
            }
        } catch (PDOException $e) {
            echo '<div class="error-block">
                <strong>Error al contar registros:</strong> ' . $e->getMessage() . '
            </div>';
        }
    }
    
    echo '</div>
    </div>';
    
    // Mostrar la consulta SQL optimizada
    echo '<div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h2>Consulta SQL Optimizada</h2>
        </div>
        <div class="card-body">
            <p>Se ha generado la siguiente consulta SQL para obtener nombres completos en vez de solo IDs:</p>
            
            <div class="sql-code">WITH paciente_info AS (
    -- Intento 1: Join directo con rh_person
    SELECT 
        p.paciente_id, 
        COALESCE(rp.first_name, \'\') || \' \' || COALESCE(rp.last_name, \'\') as nombre_paciente
    FROM 
        pacientes p
    LEFT JOIN 
        rh_person rp ON p.person_id = rp.person_id
    
    UNION ALL
    
    -- Intento 2: Join con campo nombre directo en pacientes
    SELECT 
        p.paciente_id,
        COALESCE(p.nombre, p.paciente_nombre, \'\') as nombre_paciente
    FROM 
        pacientes p
    WHERE 
        p.person_id IS NULL AND (p.nombre IS NOT NULL OR p.paciente_nombre IS NOT NULL)
),
doctor_info AS (
    -- Intento 1: Join con rh_person
    SELECT 
        d.doctor_id,
        COALESCE(rp.first_name, \'\') || \' \' || COALESCE(rp.last_name, \'\') as nombre_doctor
    FROM 
        rh_doctors d
    LEFT JOIN 
        rh_person rp ON d.person_id = rp.person_id
    
    UNION ALL
    
    -- Intento 2: Join con campo nombre directo en doctors
    SELECT 
        d.doctor_id,
        COALESCE(d.nombre, d.doctor_nombre, \'\') as nombre_doctor
    FROM 
        rh_doctors d
    WHERE 
        d.person_id IS NULL AND (d.nombre IS NOT NULL OR d.doctor_nombre IS NOT NULL)
)

SELECT 
    r.reserva_id,
    r.servicio_id,
    r.doctor_id,
    r.paciente_id,
    r.fecha_reserva,
    r.hora_inicio,
    r.hora_fin,
    r.reserva_estado as estado,
    r.observaciones,
    r.business_id,
    r.created_at,
    r.updated_at,
    r.agenda_id,
    r.sala_id,
    r.tarifa_id,
    COALESCE(di.nombre_doctor, \'Doctor \' || r.doctor_id) as doctor_nombre,
    COALESCE(pi.nombre_paciente, \'Paciente \' || r.paciente_id) as paciente_nombre,
    COALESCE(sm.servicio_nombre, \'Servicio \' || r.servicio_id) as servicio_nombre
FROM servicios_reservas r
LEFT JOIN servicios_medicos sm ON r.servicio_id = sm.servicio_id
LEFT JOIN paciente_info pi ON r.paciente_id = pi.paciente_id
LEFT JOIN doctor_info di ON r.doctor_id = di.doctor_id
WHERE r.fecha_reserva = :fecha
ORDER BY r.hora_inicio ASC</div>
            
            <h3>Características de la consulta:</h3>
            <ul class="list-group">
                <li class="list-group-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <strong>Robustez:</strong> Maneja diferentes estructuras de base de datos mediante CTEs (Common Table Expressions)
                </li>
                <li class="list-group-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <strong>Flexibilidad:</strong> Busca nombres en múltiples campos (person_id relacional o campo nombre directo)
                </li>
                <li class="list-group-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <strong>Fallback:</strong> Si no encuentra el nombre, muestra "Doctor X", "Paciente Y", etc. con el ID
                </li>
                <li class="list-group-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <strong>LEFT JOINs:</strong> Asegura que siempre se muestren las reservas aunque falte información relacionada
                </li>
            </ul>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h2>Prueba la consulta</h2>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="form-group">
                    <label for="fecha">Seleccione una fecha:</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" value="' . (isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d')) . '">
                </div>
                <button type="submit" class="btn btn-primary">Probar consulta</button>
            </form>';
            
    // Si se especificó una fecha, ejecutar la consulta
    if (isset($_GET['fecha'])) {
        $fecha = $_GET['fecha'];
        
        try {
            // Preparar la consulta
            $sql = "WITH paciente_info AS (
                -- Intento 1: Join directo con rh_person
                SELECT 
                    p.paciente_id, 
                    COALESCE(rp.first_name, '') || ' ' || COALESCE(rp.last_name, '') as nombre_paciente
                FROM 
                    pacientes p
                LEFT JOIN 
                    rh_person rp ON p.person_id = rp.person_id
                
                UNION ALL
                
                -- Intento 2: Join con campo nombre directo en pacientes
                SELECT 
                    p.paciente_id,
                    COALESCE(p.nombre, p.paciente_nombre, '') as nombre_paciente
                FROM 
                    pacientes p
                WHERE 
                    p.person_id IS NULL AND (p.nombre IS NOT NULL OR p.paciente_nombre IS NOT NULL)
            ),
            doctor_info AS (
                -- Intento 1: Join con rh_person
                SELECT 
                    d.doctor_id,
                    COALESCE(rp.first_name, '') || ' ' || COALESCE(rp.last_name, '') as nombre_doctor
                FROM 
                    rh_doctors d
                LEFT JOIN 
                    rh_person rp ON d.person_id = rp.person_id
                
                UNION ALL
                
                -- Intento 2: Join con campo nombre directo en doctors
                SELECT 
                    d.doctor_id,
                    COALESCE(d.nombre, d.doctor_nombre, '') as nombre_doctor
                FROM 
                    rh_doctors d
                WHERE 
                    d.person_id IS NULL AND (d.nombre IS NOT NULL OR d.doctor_nombre IS NOT NULL)
            )
            
            SELECT 
                r.reserva_id,
                r.servicio_id,
                r.doctor_id,
                r.paciente_id,
                r.fecha_reserva,
                r.hora_inicio,
                r.hora_fin,
                r.reserva_estado as estado,
                COALESCE(di.nombre_doctor, 'Doctor ' || r.doctor_id) as doctor_nombre,
                COALESCE(pi.nombre_paciente, 'Paciente ' || r.paciente_id) as paciente_nombre,
                COALESCE(sm.servicio_nombre, 'Servicio ' || r.servicio_id) as servicio_nombre
            FROM servicios_reservas r
            LEFT JOIN servicios_medicos sm ON r.servicio_id = sm.servicio_id
            LEFT JOIN paciente_info pi ON r.paciente_id = pi.paciente_id
            LEFT JOIN doctor_info di ON r.doctor_id = di.doctor_id
            WHERE r.fecha_reserva = :fecha
            ORDER BY r.hora_inicio ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($reservas)) {
                echo '<div class="alert alert-warning">
                    No se encontraron reservas para la fecha ' . $fecha . '.
                </div>';
            } else {
                echo '<h3>Resultados para la fecha: ' . $fecha . '</h3>';
                echo '<div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hora</th>
                                <th>Doctor</th>
                                <th>Paciente</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>';
                
                foreach ($reservas as $reserva) {
                    // Estado visual
                    $claseEstado = '';
                    $estado = (isset($reserva['estado']) ? strtoupper($reserva['estado']) : 'PENDIENTE');
                    
                    switch ($estado) {
                        case 'PENDIENTE': $claseEstado = 'badge-warning'; break;
                        case 'CONFIRMADA': $claseEstado = 'badge-success'; break;
                        case 'CANCELADA': $claseEstado = 'badge-danger'; break;
                        case 'COMPLETADA': $claseEstado = 'badge-info'; break;
                        default: $claseEstado = 'badge-secondary';
                    }
                    
                    echo '<tr>
                        <td>' . $reserva['reserva_id'] . '</td>
                        <td>' . substr($reserva['hora_inicio'], 0, 5) . ' - ' . substr($reserva['hora_fin'], 0, 5) . '</td>
                        <td>' . htmlspecialchars($reserva['doctor_nombre']) . '</td>
                        <td>' . htmlspecialchars($reserva['paciente_nombre']) . '</td>
                        <td>' . htmlspecialchars($reserva['servicio_nombre']) . '</td>
                        <td><span class="badge ' . $claseEstado . '">' . $estado . '</span></td>
                    </tr>';
                }
                
                echo '</tbody>
                    </table>
                </div>';
                
                // Mostrar detalles técnicos de la primera reserva
                echo '<h4>Detalles técnicos del primer resultado:</h4>';
                echo '<pre class="bg-light p-3">' . json_encode($reservas[0], JSON_PRETTY_PRINT) . '</pre>';
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">
                <strong>Error al ejecutar la consulta:</strong> ' . $e->getMessage() . '
            </div>';
        }
    }
    
    echo '</div>
    </div>
    
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>';
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger m-5">
        <h3>Error de conexión con la base de datos</h3>
        <p>' . $e->getMessage() . '</p>
    </div>';
}
