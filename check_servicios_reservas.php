<?php
/**
 * Script para verificar la tabla servicios_reservas y su estructura
 */
require_once "model/conexion.php";

try {
    $db = Conexion::conectar();
    
    // Verificar si la tabla existe
    $stmt = $db->prepare("SELECT to_regclass('public.servicios_reservas') IS NOT NULL as existe");
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h1>Verificación de la tabla servicios_reservas</h1>";
    
    if ($resultado['existe']) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>
            La tabla servicios_reservas existe en la base de datos.
        </div>";
        
        // Obtener información sobre las columnas
        $stmt = $db->prepare("
            SELECT 
                column_name,
                data_type,
                is_nullable,
                column_default
            FROM 
                information_schema.columns
            WHERE 
                table_schema = 'public' AND table_name = 'servicios_reservas'
            ORDER BY 
                ordinal_position
        ");
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Estructura de la tabla</h2>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
            <thead style='background-color: #f8f9fa;'>
                <tr>
                    <th>Columna</th>
                    <th>Tipo</th>
                    <th>Puede ser NULL</th>
                    <th>Valor por defecto</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($columnas as $columna) {
            echo "<tr>
                <td>{$columna['column_name']}</td>
                <td>{$columna['data_type']}</td>
                <td>{$columna['is_nullable']}</td>
                <td>{$columna['column_default']}</td>
            </tr>";
        }
        
        echo "</tbody></table>";
        
        // Verificar las restricciones (constraints)
        $stmt = $db->prepare("
            SELECT
                tc.constraint_name, 
                tc.constraint_type,
                kcu.column_name,
                tc.table_name,
                ccu.table_name AS referenced_table,
                ccu.column_name AS referenced_column
            FROM 
                information_schema.table_constraints tc
            LEFT JOIN 
                information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name
            LEFT JOIN 
                information_schema.constraint_column_usage ccu ON tc.constraint_name = ccu.constraint_name
            WHERE 
                tc.constraint_schema = 'public' AND tc.table_name = 'servicios_reservas'
            ORDER BY 
                tc.constraint_name, kcu.column_name
        ");
        $stmt->execute();
        $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($constraints) {
            echo "<h2>Restricciones de la tabla</h2>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                <thead style='background-color: #f8f9fa;'>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Columna</th>
                        <th>Tabla referenciada</th>
                        <th>Columna referenciada</th>
                    </tr>
                </thead>
                <tbody>";
            
            foreach ($constraints as $constraint) {
                echo "<tr>
                    <td>{$constraint['constraint_name']}</td>
                    <td>{$constraint['constraint_type']}</td>
                    <td>{$constraint['column_name']}</td>
                    <td>{$constraint['referenced_table']}</td>
                    <td>{$constraint['referenced_column']}</td>
                </tr>";
            }
            
            echo "</tbody></table>";
        } else {
            echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                No se encontraron restricciones para la tabla servicios_reservas.
            </div>";
        }
        
        // Verificar si hay datos en la tabla
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM servicios_reservas");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        echo "<h2>Datos en la tabla</h2>";
        echo "<p>Total de registros: <strong>{$count}</strong></p>";
        
        if ($count > 0) {
            // Mostrar algunos ejemplos de datos
            $stmt = $db->prepare("SELECT * FROM servicios_reservas ORDER BY reserva_id DESC LIMIT 5");
            $stmt->execute();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Últimos 5 registros</h3>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                <thead style='background-color: #f8f9fa;'><tr>";
            
            // Encabezados
            foreach (array_keys($datos[0]) as $key) {
                echo "<th>{$key}</th>";
            }
            
            echo "</tr></thead><tbody>";
            
            // Filas de datos
            foreach ($datos as $fila) {
                echo "<tr>";
                foreach ($fila as $valor) {
                    echo "<td>" . (is_null($valor) ? '<em>NULL</em>' : $valor) . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</tbody></table>";
        } else {
            echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin-top: 20px; border-radius: 5px;'>
                La tabla no contiene registros.
            </div>";
        }
        
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>
            <h2>¡Error! La tabla servicios_reservas no existe en la base de datos.</h2>
            <p>Es necesario crear esta tabla antes de poder guardar reservas.</p>
        </div>";
        
        // Mostrar un script SQL para crear la tabla
        echo "<h2>Script para crear la tabla</h2>";
        echo "<pre style='background-color: #f8f9fa; padding: 15px; border-radius: 5px;'>
CREATE TABLE servicios_reservas (
    reserva_id SERIAL PRIMARY KEY,
    servicio_id INTEGER NOT NULL,
    doctor_id INTEGER NOT NULL,
    paciente_id INTEGER NOT NULL,
    fecha_reserva DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    observaciones TEXT,
    reserva_estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
    business_id INTEGER NOT NULL,
    created_by INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_servicios_reservas_servicio FOREIGN KEY (servicio_id) REFERENCES servicios_medicos(servicio_id),
    CONSTRAINT fk_servicios_reservas_doctor FOREIGN KEY (doctor_id) REFERENCES rh_doctors(doctor_id),
    CONSTRAINT fk_servicios_reservas_paciente FOREIGN KEY (paciente_id) REFERENCES pacientes(paciente_id)
);
</pre>";
    }
    
    // Verificar que las tablas referenciadas existan
    echo "<h2>Verificación de tablas relacionadas</h2>";
    $tablasRelacionadas = [
        'servicios_medicos' => 'servicio_id',
        'rh_doctors' => 'doctor_id',
        'pacientes' => 'paciente_id'
    ];
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
        <thead style='background-color: #f8f9fa;'>
            <tr>
                <th>Tabla</th>
                <th>Columna clave</th>
                <th>¿Existe tabla?</th>
                <th>¿Existe columna?</th>
            </tr>
        </thead>
        <tbody>";
    
    foreach ($tablasRelacionadas as $tabla => $columna) {
        // Verificar si la tabla existe
        $stmt = $db->prepare("SELECT to_regclass('public.{$tabla}') IS NOT NULL as existe_tabla");
        $stmt->execute();
        $existeTabla = $stmt->fetchColumn();
        
        // Verificar si la columna existe en esa tabla
        $existeColumna = false;
        if ($existeTabla) {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM information_schema.columns 
                WHERE table_schema = 'public' AND table_name = :tabla AND column_name = :columna
            ");
            $stmt->bindParam(':tabla', $tabla, PDO::PARAM_STR);
            $stmt->bindParam(':columna', $columna, PDO::PARAM_STR);
            $stmt->execute();
            $existeColumna = $stmt->fetchColumn() > 0;
        }
        
        echo "<tr>
            <td>{$tabla}</td>
            <td>{$columna}</td>
            <td>" . ($existeTabla ? "<span style='color: green;'>SÍ</span>" : "<span style='color: red;'>NO</span>") . "</td>
            <td>" . ($existeColumna ? "<span style='color: green;'>SÍ</span>" : "<span style='color: red;'>NO</span>") . "</td>
        </tr>";
    }
    
    echo "</tbody></table>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>
        <h2>Error al verificar la tabla:</h2>
        <p>{$e->getMessage()}</p>
    </div>";
}
