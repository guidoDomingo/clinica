<?php
/**
 * Script para verificar y asegurar la existencia de la tabla rs_servicios
 * Esta tabla es necesaria para mostrar los nombres de los servicios en las reservas
 */
require_once "model/conexion.php";

// Cabecera HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de tabla rs_servicios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Verificación y Creación de Tablas de Servicios</h1>';

try {
    $db = Conexion::conectar();
    
    // 1. Verificar si existe la tabla rs_servicios
    $stmt = $db->prepare("SELECT to_regclass('public.rs_servicios')");
    $stmt->execute();
    $tablaExiste = $stmt->fetchColumn();
    
    if ($tablaExiste) {
        echo '<div class="alert alert-success">
            <strong>✓</strong> La tabla rs_servicios ya existe.
        </div>';
        
        // Verificar la estructura
        $stmt = $db->prepare("
            SELECT column_name, data_type, character_maximum_length
            FROM information_schema.columns
            WHERE table_name = 'rs_servicios'
            ORDER BY ordinal_position
        ");
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<div class="card mb-4">
            <div class="card-header">
                <h3>Estructura actual de rs_servicios</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Columna</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($columnas as $columna) {
            echo '<tr>
                <td>' . $columna['column_name'] . '</td>
                <td>' . $columna['data_type'] . ($columna['character_maximum_length'] ? '(' . $columna['character_maximum_length'] . ')' : '') . '</td>
            </tr>';
        }
        
        echo '</tbody></table></div></div>';
        
        // Contar registros
        $stmt = $db->prepare("SELECT COUNT(*) FROM rs_servicios");
        $stmt->execute();
        $totalRegistros = $stmt->fetchColumn();
        
        echo '<div class="alert alert-info">
            La tabla tiene ' . $totalRegistros . ' registros.
        </div>';
        
        // Mostrar algunos servicios
        if ($totalRegistros > 0) {
            $stmt = $db->prepare("SELECT * FROM rs_servicios LIMIT 10");
            $stmt->execute();
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<div class="card mb-4">
                <div class="card-header">
                    <h3>Servicios existentes (muestra)</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>';
            
            foreach (array_keys($servicios[0]) as $columna) {
                echo '<th>' . $columna . '</th>';
            }
            
            echo '</tr></thead><tbody>';
            
            foreach ($servicios as $servicio) {
                echo '<tr>';
                foreach ($servicio as $valor) {
                    echo '<td>' . (is_null($valor) ? '<em>NULL</em>' : htmlspecialchars((string)$valor)) . '</td>';
                }
                echo '</tr>';
            }
            
            echo '</tbody></table></div></div>';
        }
    } else {
        echo '<div class="alert alert-warning">
            <strong>⚠</strong> La tabla rs_servicios no existe. Intentando crearla...
        </div>';
        
        // Crear la tabla
        $db->exec("
            CREATE TABLE rs_servicios (
                servicio_id SERIAL PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL,
                descripcion TEXT,
                duracion INTEGER NOT NULL DEFAULT 30,
                precio NUMERIC(10,2),
                categoria_id INTEGER,
                business_id INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE
            )
        ");
        
        echo '<div class="alert alert-success">
            <strong>✓</strong> La tabla rs_servicios ha sido creada exitosamente.
        </div>';
        
        // Verificar si existen servicios en servicios_reservas
        $stmt = $db->prepare("
            SELECT DISTINCT servicio_id FROM servicios_reservas
            WHERE servicio_id IS NOT NULL
        ");
        $stmt->execute();
        $serviciosId = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($serviciosId) > 0) {
            echo '<div class="alert alert-info">
                Se encontraron ' . count($serviciosId) . ' servicios usados en reservas.
                Añadiendo estos servicios a rs_servicios...
            </div>';
            
            // Insertar servicios encontrados
            foreach ($serviciosId as $servicioId) {
                $stmt = $db->prepare("
                    INSERT INTO rs_servicios (servicio_id, nombre, descripcion)
                    VALUES (:id, :nombre, :desc)
                ");
                $stmt->execute([
                    ':id' => $servicioId,
                    ':nombre' => 'Servicio ' . $servicioId,
                    ':desc' => 'Servicio migrado automáticamente'
                ]);
            }
            
            echo '<div class="alert alert-success">
                <strong>✓</strong> Se han añadido servicios básicos a la tabla.
            </div>';
        } else {
            echo '<div class="alert alert-info">
                No se encontraron servicios en uso. Añadiendo algunos servicios de ejemplo...
            </div>';
            
            // Añadir servicios de ejemplo
            $serviciosEjemplo = [
                ['nombre' => 'Consulta General', 'descripcion' => 'Consulta médica general', 'duracion' => 30, 'precio' => 50.00],
                ['nombre' => 'Consulta Especializada', 'descripcion' => 'Consulta con especialista', 'duracion' => 45, 'precio' => 80.00],
                ['nombre' => 'Control de Rutina', 'descripcion' => 'Control médico de rutina', 'duracion' => 20, 'precio' => 40.00]
            ];
            
            foreach ($serviciosEjemplo as $index => $servicio) {
                $stmt = $db->prepare("
                    INSERT INTO rs_servicios (servicio_id, nombre, descripcion, duracion, precio)
                    VALUES (:id, :nombre, :desc, :duracion, :precio)
                ");
                $stmt->execute([
                    ':id' => $index + 1,
                    ':nombre' => $servicio['nombre'],
                    ':desc' => $servicio['descripcion'],
                    ':duracion' => $servicio['duracion'],
                    ':precio' => $servicio['precio']
                ]);
            }
            
            echo '<div class="alert alert-success">
                <strong>✓</strong> Se han añadido servicios de ejemplo a la tabla.
            </div>';
        }
        
        // Mostrar servicios añadidos
        $stmt = $db->prepare("SELECT * FROM rs_servicios");
        $stmt->execute();
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<div class="card mb-4">
            <div class="card-header">
                <h3>Servicios creados</h3>
            </div>
            <div class="card-body">';
        
        if (count($servicios) > 0) {
            echo '<table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>';
            
            foreach (array_keys($servicios[0]) as $columna) {
                echo '<th>' . $columna . '</th>';
            }
            
            echo '</tr></thead><tbody>';
            
            foreach ($servicios as $servicio) {
                echo '<tr>';
                foreach ($servicio as $valor) {
                    echo '<td>' . (is_null($valor) ? '<em>NULL</em>' : htmlspecialchars((string)$valor)) . '</td>';
                }
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-warning">
                No se pudieron añadir servicios a la tabla.
            </div>';
        }
        
        echo '</div></div>';
    }
    
    // 2. Verificar que la información de doctores y pacientes esté correctamente vinculada
    echo '<h2 class="mt-4">Verificación de relaciones entre tablas</h2>';
    
    // Verificar relaciones entre doctor_id y rh_person
    $stmt = $db->prepare("
        SELECT r.reserva_id, r.doctor_id, d.person_id, 
               p.first_name || ' ' || p.last_name AS nombre_doctor
        FROM servicios_reservas r
        LEFT JOIN rh_doctors d ON r.doctor_id = d.doctor_id
        LEFT JOIN rh_person p ON d.person_id = p.person_id
        WHERE r.doctor_id IS NOT NULL
        LIMIT 5
    ");
    $stmt->execute();
    $relacionesDoctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<div class="card mb-4">
        <div class="card-header">
            <h3>Relación doctores-personas (muestra)</h3>
        </div>
        <div class="card-body">';
    
    if (count($relacionesDoctores) > 0) {
        echo '<table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Reserva ID</th>
                    <th>Doctor ID</th>
                    <th>Person ID</th>
                    <th>Nombre completo</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($relacionesDoctores as $relacion) {
            echo '<tr>
                <td>' . $relacion['reserva_id'] . '</td>
                <td>' . $relacion['doctor_id'] . '</td>
                <td>' . ($relacion['person_id'] ?? '<span class="text-danger">Falta vínculo</span>') . '</td>
                <td>' . ($relacion['nombre_doctor'] ? htmlspecialchars($relacion['nombre_doctor']) : '<span class="text-danger">Sin nombre</span>') . '</td>
            </tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning">
            No se encontraron relaciones entre doctores y reservas.
        </div>';
    }
    
    echo '</div></div>';
    
    // Verificar relaciones entre paciente_id y rh_person
    $stmt = $db->prepare("
        SELECT r.reserva_id, r.paciente_id, 
               p.first_name || ' ' || p.last_name AS nombre_paciente
        FROM servicios_reservas r
        LEFT JOIN rh_person p ON r.paciente_id = p.person_id
        WHERE r.paciente_id IS NOT NULL
        LIMIT 5
    ");
    $stmt->execute();
    $relacionesPacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<div class="card mb-4">
        <div class="card-header">
            <h3>Relación pacientes-personas (muestra)</h3>
        </div>
        <div class="card-body">';
    
    if (count($relacionesPacientes) > 0) {
        echo '<table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Reserva ID</th>
                    <th>Paciente ID</th>
                    <th>Nombre completo</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($relacionesPacientes as $relacion) {
            echo '<tr>
                <td>' . $relacion['reserva_id'] . '</td>
                <td>' . $relacion['paciente_id'] . '</td>
                <td>' . ($relacion['nombre_paciente'] ? htmlspecialchars($relacion['nombre_paciente']) : '<span class="text-danger">Sin nombre</span>') . '</td>
            </tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning">
            No se encontraron relaciones entre pacientes y reservas.
        </div>';
    }
    
    echo '</div></div>';
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">
        <h3>Error</h3>
        <p>' . $e->getMessage() . '</p>
    </div>';
}

echo '</div>
</body>
</html>';
