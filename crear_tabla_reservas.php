<?php
// Script para crear la tabla de reservas y estructuras relacionadas
$is_ajax = isset($_POST['action']) && $_POST['action'] == 'create_tables';

if ($is_ajax) {
    header('Content-Type: application/json; charset=utf-8');
} else {
    header('Content-Type: text/html; charset=utf-8');
}

require_once "model/conexion.php";

try {
    $conexion = Conexion::conectar();
    $resultados = [];
    
    // Verificar si la tabla ya existe
    $stmt = $conexion->prepare("SELECT to_regclass('public.servicios_reservas')");
    $stmt->execute();
    $tabla_existe = $stmt->fetchColumn();
    
    if ($tabla_existe) {
        $resultados[] = "La tabla servicios_reservas ya existe.";
        if (!$is_ajax) echo "<h3 style='color:orange;'>La tabla servicios_reservas ya existe.</h3>";
    } else {
        // Crear la tabla de reservas
        $sql_crear_tabla = "
        CREATE TABLE servicios_reservas (
            reserva_id SERIAL PRIMARY KEY,
            servicio_id INTEGER,
            doctor_id INTEGER,
            paciente_id INTEGER,
            agenda_id INTEGER,
            fecha_reserva DATE NOT NULL,
            hora_inicio TIME NOT NULL,
            hora_fin TIME NOT NULL,
            observaciones TEXT,
            reserva_estado VARCHAR(20) DEFAULT 'PENDIENTE',
            sala_id INTEGER,
            tarifa_id INTEGER,
            precio_final NUMERIC(10,2),
            business_id INTEGER,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INTEGER,
            updated_at TIMESTAMP,
            updated_by INTEGER
        );
        
        COMMENT ON TABLE servicios_reservas IS 'Tabla para almacenar las reservas de servicios médicos';
        COMMENT ON COLUMN servicios_reservas.reserva_estado IS 'Estados posibles: PENDIENTE, CONFIRMADA, CANCELADA, COMPLETADA';
        ";
        
        $conexion->exec($sql_crear_tabla);
        $resultados[] = "Tabla servicios_reservas creada correctamente.";
        if (!$is_ajax) echo "<h3 style='color:green;'>Tabla servicios_reservas creada correctamente.</h3>";
    }
    
    // Crear tabla de bloqueos de horarios si no existe
    $stmt = $conexion->prepare("SELECT to_regclass('public.agendas_bloqueos')");
    $stmt->execute();
    $tabla_existe = $stmt->fetchColumn();
    
    if ($tabla_existe) {
        $resultados[] = "La tabla agendas_bloqueos ya existe.";
        if (!$is_ajax) echo "<h3 style='color:orange;'>La tabla agendas_bloqueos ya existe.</h3>";
    } else {
        $sql_crear_bloqueos = "
        CREATE TABLE agendas_bloqueos (
            bloqueo_id SERIAL PRIMARY KEY,
            agenda_id INTEGER,
            doctor_id INTEGER,
            fecha_inicio DATE NOT NULL,
            fecha_fin DATE NOT NULL,
            hora_inicio TIME,
            hora_fin TIME,
            motivo TEXT,
            bloqueo_estado BOOLEAN DEFAULT TRUE,
            business_id INTEGER,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INTEGER
        );
        
        COMMENT ON TABLE agendas_bloqueos IS 'Tabla para almacenar bloqueos de horarios en agendas';
        ";
        
        $conexion->exec($sql_crear_bloqueos);
        $resultados[] = "Tabla agendas_bloqueos creada correctamente.";
        if (!$is_ajax) echo "<h3 style='color:green;'>Tabla agendas_bloqueos creada correctamente.</h3>";
    }
    
    // Crear índices para mejorar el rendimiento
    $indices = [
        "CREATE INDEX IF NOT EXISTS idx_reservas_fecha ON servicios_reservas (fecha_reserva);",
        "CREATE INDEX IF NOT EXISTS idx_reservas_doctor ON servicios_reservas (doctor_id);",
        "CREATE INDEX IF NOT EXISTS idx_reservas_paciente ON servicios_reservas (paciente_id);",
        "CREATE INDEX IF NOT EXISTS idx_reservas_servicio ON servicios_reservas (servicio_id);",
        "CREATE INDEX IF NOT EXISTS idx_reservas_estado ON servicios_reservas (reserva_estado);"
    ];
    
    foreach ($indices as $indice) {
        $conexion->exec($indice);
    }
    $resultados[] = "Índices creados correctamente.";
    if (!$is_ajax) echo "<h3 style='color:green;'>Índices creados correctamente.</h3>";
    
    $mensaje_final = "Estructura de base de datos para reservas configurada exitosamente.";
    $resultados[] = $mensaje_final;
    
    if ($is_ajax) {
        echo json_encode([
            'success' => true,
            'message' => implode("\n", $resultados)
        ]);
    } else {
        echo "<p>{$mensaje_final}</p>";
        echo "<p><a href='crear_tabla_reservas_ui.php' class='btn btn-primary'>Volver a la página de instalación</a></p>";
        echo "<p><a href='servicios' class='btn btn-success'>Ir a Gestión de Servicios</a></p>";
    }
    
} catch (PDOException $e) {
    $error_message = "Error al crear las tablas: " . $e->getMessage();
    
    if ($is_ajax) {
        echo json_encode([
            'success' => false,
            'message' => $error_message
        ]);
    } else {
        echo "<h3 style='color:red;'>Error al crear las tablas:</h3>";
        echo "<pre>" . $e->getMessage() . "</pre>";
        echo "<p><a href='crear_tabla_reservas_ui.php' class='btn btn-primary'>Volver a intentar</a></p>";
    }
}

if (!$is_ajax) {
?>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        line-height: 1.6;
    }
    h3 {
        margin-bottom: 10px;
    }
    pre {
        background-color: #f4f4f4;
        padding: 10px;
        border-radius: 5px;
        overflow: auto;
    }
    .btn {
        display: inline-block;
        padding: 8px 16px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin-top: 20px;
        margin-right: 10px;
    }
    .btn-success {
        background-color: #28a745;
    }
    .btn:hover {
        background-color: #0056b3;
    }
    .btn-success:hover {
        background-color: #218838;
    }
</style>
<?php
}
?>
