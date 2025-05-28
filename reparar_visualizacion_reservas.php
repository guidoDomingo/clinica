<?php
/**
 * Script para verificar y reparar el problema de visualización de reservas
 */
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

try {
    $db = Conexion::conectar();
    
    // 1. Verificar la estructura de la tabla de reservas
    echo "<h1>Verificación y reparación del sistema de reservas</h1>";
    
    $resultado = $db->query("
        SELECT EXISTS (
            SELECT FROM information_schema.tables WHERE table_name = 'servicios_reservas'
        ) as tabla_existe
    ")->fetch(PDO::FETCH_ASSOC);
    
    if (!$resultado['tabla_existe']) {
        die("<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>
            La tabla servicios_reservas no existe. Por favor, cree primero esta tabla.
        </div>");
    }
    
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 15px; border-radius: 5px;'>
        La tabla servicios_reservas existe.
    </div>";
    
    // 2. Verificar la estructura de pacientes y doctors
    $estructuraPacientes = $db->query("
        SELECT 
            EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'pacientes') as tabla_existe,
            EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'pacientes' AND column_name = 'paciente_id') as tiene_id,
            EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'pacientes' AND column_name = 'person_id') as tiene_person_id
    ")->fetch(PDO::FETCH_ASSOC);
    
    $estructuraDoctores = $db->query("
        SELECT 
            EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'rh_doctors') as tabla_existe,
            EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'rh_doctors' AND column_name = 'doctor_id') as tiene_id,
            EXISTS (SELECT FROM information_schema.columns WHERE table_name = 'rh_doctors' AND column_name = 'person_id') as tiene_person_id
    ")->fetch(PDO::FETCH_ASSOC);
    
    if (!$estructuraPacientes['tabla_existe'] || !$estructuraPacientes['tiene_id'] || !$estructuraPacientes['tiene_person_id']) {
        echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin-bottom: 15px; border-radius: 5px;'>
            <h3>Problemas detectados con la tabla de pacientes:</h3>
            <ul>";
        if (!$estructuraPacientes['tabla_existe']) echo "<li>La tabla 'pacientes' no existe</li>";
        if (!$estructuraPacientes['tiene_id']) echo "<li>La tabla no tiene columna 'paciente_id'</li>";
        if (!$estructuraPacientes['tiene_person_id']) echo "<li>La tabla no tiene columna 'person_id'</li>";
        echo "</ul></div>";
    }
    
    if (!$estructuraDoctores['tabla_existe'] || !$estructuraDoctores['tiene_id'] || !$estructuraDoctores['tiene_person_id']) {
        echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin-bottom: 15px; border-radius: 5px;'>
            <h3>Problemas detectados con la tabla de doctores:</h3>
            <ul>";
        if (!$estructuraDoctores['tabla_existe']) echo "<li>La tabla 'rh_doctors' no existe</li>";
        if (!$estructuraDoctores['tiene_id']) echo "<li>La tabla no tiene columna 'doctor_id'</li>";
        if (!$estructuraDoctores['tiene_person_id']) echo "<li>La tabla no tiene columna 'person_id'</li>";
        echo "</ul></div>";
    }
    
    // 3. Crear una versión temporal de la función mejorada para obtener reservas
    echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; margin-bottom: 15px; border-radius: 5px;'>
        <h2>Creando función temporal para obtener reservas</h2>
    </div>";
    
    function obtenerReservasPorFechaOptimizado($fecha, $doctorId = null) {
        try {
            $db = Conexion::conectar();
            
            // Consulta SQL con múltiples opciones de JOIN para manejar diferentes estructuras de base de datos
            $sql = "
                WITH paciente_info AS (
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
                        COALESCE(p.nombre, '') as nombre_paciente
                    FROM 
                        pacientes p
                    WHERE 
                        p.person_id IS NULL AND p.nombre IS NOT NULL
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
                    r.observaciones,
                    r.reserva_estado as estado,
                    r.sala_id,
                    COALESCE(sm.servicio_nombre, 'Servicio no especificado') as servicio_nombre,
                    COALESCE(pi.nombre_paciente, 'Paciente ID: ' || r.paciente_id) as paciente_nombre,
                    COALESCE(di.nombre_doctor, 'Doctor ID: ' || r.doctor_id) as doctor_nombre,
                    COALESCE(s.sala_nombre, 'Sin sala') as sala_nombre
                FROM 
                    servicios_reservas r
                LEFT JOIN 
                    servicios_medicos sm ON r.servicio_id = sm.servicio_id
                LEFT JOIN 
                    paciente_info pi ON r.paciente_id = pi.paciente_id
                LEFT JOIN 
                    doctor_info di ON r.doctor_id = di.doctor_id
                LEFT JOIN 
                    salas s ON r.sala_id = s.sala_id
                WHERE 
                    r.fecha_reserva = :fecha";
                
            if ($doctorId !== null) {
                $sql .= " AND r.doctor_id = :doctor_id";
            }
            
            $sql .= " ORDER BY r.hora_inicio ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            
            if ($doctorId !== null) {
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $reservas;
            
        } catch (PDOException $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px 0; border-radius: 5px;'>
                Error en la consulta SQL: " . $e->getMessage() . "
            </div>";
            return [];
        }
    }
    
    // 4. Probar la función mejorada
    echo "<h2>Prueba con la función original</h2>";
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
    $doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;
    
    $reservasOriginal = ModelServicios::mdlObtenerReservasPorFecha($fecha, $doctorId);
    echo "<div>Se encontraron " . count($reservasOriginal) . " reservas con la función original para la fecha " . $fecha . "</div>";
    
    echo "<h2>Prueba con la función optimizada</h2>";
    $reservasOptimizadas = obtenerReservasPorFechaOptimizado($fecha, $doctorId);
    echo "<div>Se encontraron " . count($reservasOptimizadas) . " reservas con la función optimizada para la fecha " . $fecha . "</div>";
    
    // 5. Mostrar ambos resultados para comparación
    echo "<div style='display: flex;'>";
    
    // Reservas con función original
    echo "<div style='flex: 1; margin-right: 10px;'>";
    echo "<h3>Resultado función original:</h3>";
    
    if (count($reservasOriginal) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Hora</th><th>Doctor</th><th>Paciente</th><th>Estado</th></tr>";
        
        foreach ($reservasOriginal as $reserva) {
            $horario = substr($reserva['hora_inicio'], 0, 5) . " - " . substr($reserva['hora_fin'], 0, 5);
            $doctorNombre = isset($reserva['doctor_nombre']) ? $reserva['doctor_nombre'] : 'No especificado';
            $pacienteNombre = isset($reserva['paciente_nombre']) ? $reserva['paciente_nombre'] : 'No especificado';
            $estado = isset($reserva['estado']) ? $reserva['estado'] : (isset($reserva['reserva_estado']) ? $reserva['reserva_estado'] : 'No especificado');
            
            echo "<tr>";
            echo "<td>{$reserva['reserva_id']}</td>";
            echo "<td>{$horario}</td>";
            echo "<td>{$doctorNombre}</td>";
            echo "<td>{$pacienteNombre}</td>";
            echo "<td>{$estado}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<div style='padding: 10px; background-color: #f8f9fa; border-radius: 5px;'>
            No se encontraron reservas con la función original.
        </div>";
    }
    echo "</div>";
    
    // Reservas con función optimizada
    echo "<div style='flex: 1; margin-left: 10px;'>";
    echo "<h3>Resultado función optimizada:</h3>";
    
    if (count($reservasOptimizadas) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Hora</th><th>Doctor</th><th>Paciente</th><th>Estado</th></tr>";
        
        foreach ($reservasOptimizadas as $reserva) {
            $horario = substr($reserva['hora_inicio'], 0, 5) . " - " . substr($reserva['hora_fin'], 0, 5);
            $doctorNombre = isset($reserva['doctor_nombre']) ? $reserva['doctor_nombre'] : 'No especificado';
            $pacienteNombre = isset($reserva['paciente_nombre']) ? $reserva['paciente_nombre'] : 'No especificado';
            $estado = isset($reserva['estado']) ? $reserva['estado'] : (isset($reserva['reserva_estado']) ? $reserva['reserva_estado'] : 'No especificado');
            
            echo "<tr>";
            echo "<td>{$reserva['reserva_id']}</td>";
            echo "<td>{$horario}</td>";
            echo "<td>{$doctorNombre}</td>";
            echo "<td>{$pacienteNombre}</td>";
            echo "<td>{$estado}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<div style='padding: 10px; background-color: #f8f9fa; border-radius: 5px;'>
            No se encontraron reservas con la función optimizada.
        </div>";
    }
    echo "</div>";
    echo "</div>";
    
    // 6. Mostrar el formulario para aplicar los cambios
    echo "<div style='margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;'>";
    echo "<h2>Aplicar arreglos al sistema</h2>";
    
    if (isset($_GET['aplicar']) && $_GET['aplicar'] == 'true') {
        // Código para aplicar los cambios al archivo del modelo
        $modeloFile = 'model/servicios.model.php';
        $modeloContent = file_get_contents($modeloFile);
        
        // Verificar si podemos identificar la función actual
        if (strpos($modeloContent, 'static public function mdlObtenerReservasPorFecha') !== false) {
            // Crear backup
            $backupFile = 'model/servicios.model.php.bak.' . date('YmdHis');
            file_put_contents($backupFile, $modeloContent);
            
            $newFunction = '    static public function mdlObtenerReservasPorFecha($fecha, $doctorId = null, $estado = null) {
        try {
            error_log("mdlObtenerReservasPorFecha: Fecha=$fecha, DoctorID=" . ($doctorId ?? "null") . ", Estado=" . ($estado ?? "null"), 3, \'c:/laragon/www/clinica/logs/reservas.log\');
            
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass(\'public.servicios_reservas\')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                error_log("mdlObtenerReservasPorFecha: La tabla servicios_reservas no existe", 3, \'c:/laragon/www/clinica/logs/reservas.log\');
                return [];
            }
            
            // Consulta SQL con múltiples opciones de JOIN para manejar diferentes estructuras de base de datos
            $sql = "
                WITH paciente_info AS (
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
                        COALESCE(p.nombre, \'\') as nombre_paciente
                    FROM 
                        pacientes p
                    WHERE 
                        p.person_id IS NULL AND p.nombre IS NOT NULL
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
                    r.observaciones,
                    r.reserva_estado as estado,
                    r.sala_id,
                    COALESCE(sm.servicio_nombre, \'Servicio no especificado\') as servicio_nombre,
                    COALESCE(pi.nombre_paciente, \'Paciente ID: \' || r.paciente_id) as paciente_nombre,
                    COALESCE(di.nombre_doctor, \'Doctor ID: \' || r.doctor_id) as doctor_nombre,
                    COALESCE(s.sala_nombre, \'Sin sala\') as sala_nombre
                FROM 
                    servicios_reservas r
                LEFT JOIN 
                    servicios_medicos sm ON r.servicio_id = sm.servicio_id
                LEFT JOIN 
                    paciente_info pi ON r.paciente_id = pi.paciente_id
                LEFT JOIN 
                    doctor_info di ON r.doctor_id = di.doctor_id
                LEFT JOIN 
                    salas s ON r.sala_id = s.sala_id
                WHERE 
                    r.fecha_reserva = :fecha";
                
            if ($doctorId !== null) {
                $sql .= " AND r.doctor_id = :doctor_id";
            }
            
            if ($estado !== null) {
                $sql .= " AND r.reserva_estado = :estado";
            }
            
            // Ordenar por hora
            $sql .= " ORDER BY r.hora_inicio ASC";
            
            error_log("mdlObtenerReservasPorFecha: SQL=$sql", 3, \'c:/laragon/www/clinica/logs/reservas.log\');
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            
            if ($doctorId !== null) {
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            }
            
            if ($estado !== null) {
                $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerReservasPorFecha: Se encontraron " . count($reservas) . " reservas", 3, \'c:/laragon/www/clinica/logs/reservas.log\');
            if (count($reservas) > 0) {
                error_log("mdlObtenerReservasPorFecha: Primera reserva: " . json_encode($reservas[0]), 3, \'c:/laragon/www/clinica/logs/reservas.log\');
            }
            
            return $reservas;
            
        } catch (PDOException $e) {
            error_log("Error al obtener reservas por fecha: " . $e->getMessage(), 3, \'c:/laragon/www/clinica/logs/reservas.log\');
            return [];
        }
    }';
            
            // Reemplazar la función antigua con la nueva
            $pattern = '/static public function mdlObtenerReservasPorFecha\([^\{]+\{[^}]+return \$stmt->fetchAll\(PDO::FETCH_ASSOC\);[\s\n]+\s+\} catch[^\}]+\}\s+\}/s';
            $modeloContentNuevo = preg_replace($pattern, $newFunction, $modeloContent);
            
            if ($modeloContentNuevo !== $modeloContent) {
                file_put_contents($modeloFile, $modeloContentNuevo);
                
                echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 15px; border-radius: 5px;'>
                    <strong>¡ÉXITO!</strong> Se ha actualizado la función mdlObtenerReservasPorFecha en el archivo {$modeloFile}.<br>
                    Se ha creado un backup del archivo original en {$backupFile}
                </div>";
            } else {
                echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 15px; border-radius: 5px;'>
                    <strong>ERROR:</strong> No se pudo identificar correctamente la función mdlObtenerReservasPorFecha para reemplazarla.
                </div>";
            }
        } else {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 15px; border-radius: 5px;'>
                <strong>ERROR:</strong> No se pudo encontrar la función mdlObtenerReservasPorFecha en el archivo {$modeloFile}.
            </div>";
        }
    } else {
        echo "<p>Si desea aplicar la versión optimizada de la función que obtiene reservas, haga clic en el siguiente botón:</p>";
        echo "<a href='?aplicar=true" . (isset($_GET['fecha']) ? "&fecha=" . $_GET['fecha'] : "") . 
             (isset($_GET['doctor_id']) ? "&doctor_id=" . $_GET['doctor_id'] : "") . "' 
             style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>
             Aplicar corrección
             </a>";
        echo "<p><strong>Nota:</strong> Se creará un backup del archivo original antes de realizar cualquier cambio.</p>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>
        <h2>Error al procesar:</h2>
        <p>{$e->getMessage()}</p>
    </div>";
}
