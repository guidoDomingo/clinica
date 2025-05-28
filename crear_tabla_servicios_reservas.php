<?php
/**
 * Script para crear o corregir la tabla servicios_reservas
 */
require_once "model/conexion.php";

try {
    $db = Conexion::conectar();
    
    // Verificar si la tabla existe
    $stmt = $db->prepare("SELECT to_regclass('public.servicios_reservas') IS NOT NULL as existe");
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h1>Creación/reparación de la tabla servicios_reservas</h1>";
    
    if ($resultado['existe']) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>
            La tabla servicios_reservas ya existe.
        </div>";
        
        // Verificar si tiene todos los campos necesarios
        $stmt = $db->prepare("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_schema = 'public' AND table_name = 'servicios_reservas'
        ");
        $stmt->execute();
        $columnasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $columnasFaltantes = [];
        $columnasRequeridas = [
            'reserva_id', 'servicio_id', 'doctor_id', 'paciente_id', 
            'fecha_reserva', 'hora_inicio', 'hora_fin', 'observaciones', 
            'reserva_estado', 'business_id', 'created_by', 'created_at'
        ];
        
        foreach ($columnasRequeridas as $columna) {
            if (!in_array($columna, $columnasExistentes)) {
                $columnasFaltantes[] = $columna;
            }
        }
        
        if (!empty($columnasFaltantes)) {
            echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>
                Faltan las siguientes columnas: " . implode(', ', $columnasFaltantes) . ". Se agregarán ahora.
            </div>";
            
            // Agregar columnas faltantes
            $alterTableSQL = [];
            foreach ($columnasFaltantes as $columna) {
                switch ($columna) {
                    case 'reserva_id':
                        // Si falta la columna reserva_id, mejor recrear la tabla
                        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>
                            Falta la columna reserva_id (clave primaria). Se recomienda recrear la tabla por completo.
                        </div>";
                        break 2; // Salir del switch y del foreach
                    case 'servicio_id':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN servicio_id INTEGER NOT NULL";
                        break;
                    case 'doctor_id':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN doctor_id INTEGER NOT NULL";
                        break;
                    case 'paciente_id':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN paciente_id INTEGER NOT NULL";
                        break;
                    case 'fecha_reserva':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN fecha_reserva DATE NOT NULL";
                        break;
                    case 'hora_inicio':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN hora_inicio TIME NOT NULL";
                        break;
                    case 'hora_fin':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN hora_fin TIME NOT NULL";
                        break;
                    case 'observaciones':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN observaciones TEXT";
                        break;
                    case 'reserva_estado':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN reserva_estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE'";
                        break;
                    case 'business_id':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN business_id INTEGER NOT NULL DEFAULT 1";
                        break;
                    case 'created_by':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN created_by INTEGER NOT NULL DEFAULT 1";
                        break;
                    case 'created_at':
                        $alterTableSQL[] = "ALTER TABLE servicios_reservas ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
                        break;
                }
            }
            
            if (!empty($alterTableSQL)) {
                try {
                    foreach ($alterTableSQL as $sql) {
                        $db->exec($sql);
                        echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 5px 0; border-radius: 5px;'>
                            SQL ejecutado: {$sql}
                        </div>";
                    }
                    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                        Se han agregado todas las columnas faltantes.
                    </div>";
                } catch (PDOException $e) {
                    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                        Error al agregar columnas: {$e->getMessage()}
                    </div>";
                }
            }
        } else {
            echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                La tabla tiene todas las columnas requeridas.
            </div>";
        }
        
        // Verificar las restricciones (constraints) de clave foránea
        $stmt = $db->prepare("
            SELECT 
                tc.constraint_name, 
                kcu.column_name,
                ccu.table_name AS referenced_table,
                ccu.column_name AS referenced_column
            FROM 
                information_schema.table_constraints tc
            JOIN 
                information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name
            JOIN 
                information_schema.constraint_column_usage ccu ON tc.constraint_name = ccu.constraint_name
            WHERE 
                tc.constraint_type = 'FOREIGN KEY' 
                AND tc.table_name = 'servicios_reservas'
        ");
        $stmt->execute();
        $restricciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mapeo de columnas que deberían tener restricciones de clave foránea
        $restriccionesRequeridas = [
            'servicio_id' => ['tabla' => 'servicios_medicos', 'columna' => 'servicio_id'],
            'doctor_id' => ['tabla' => 'rh_doctors', 'columna' => 'doctor_id'],
            'paciente_id' => ['tabla' => 'pacientes', 'columna' => 'paciente_id']
        ];
        
        $restriccionesExistentes = [];
        foreach ($restricciones as $restriccion) {
            $restriccionesExistentes[$restriccion['column_name']] = [
                'tabla' => $restriccion['referenced_table'],
                'columna' => $restriccion['referenced_column']
            ];
        }
        
        $restriccionesFaltantes = [];
        foreach ($restriccionesRequeridas as $columna => $detalles) {
            if (!isset($restriccionesExistentes[$columna])) {
                $restriccionesFaltantes[$columna] = $detalles;
            }
        }
        
        if (!empty($restriccionesFaltantes)) {
            echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                Faltan algunas restricciones de clave foránea. Se intentará agregar...
            </div>";
            
            foreach ($restriccionesFaltantes as $columna => $detalles) {
                // Primero verificar si la tabla referenciada existe
                $stmt = $db->prepare("SELECT to_regclass('public.{$detalles['tabla']}') IS NOT NULL as existe");
                $stmt->execute();
                $tablaExiste = $stmt->fetchColumn();
                
                if (!$tablaExiste) {
                    echo "<div style='background-color: #fff3cd; color: #856404; padding: 10px; margin: 5px 0; border-radius: 5px;'>
                        No se puede agregar restricción para {$columna} porque la tabla {$detalles['tabla']} no existe.
                    </div>";
                    continue;
                }
                
                try {
                    // Agregar la restricción
                    $sql = "ALTER TABLE servicios_reservas ADD CONSTRAINT fk_servicios_reservas_{$columna} 
                            FOREIGN KEY ({$columna}) REFERENCES {$detalles['tabla']}({$detalles['columna']})";
                    $db->exec($sql);
                    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 5px 0; border-radius: 5px;'>
                        Restricción agregada para {$columna} → {$detalles['tabla']}({$detalles['columna']})
                    </div>";
                } catch (PDOException $e) {
                    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 5px 0; border-radius: 5px;'>
                        Error al agregar restricción para {$columna}: {$e->getMessage()}
                    </div>";
                }
            }
        } else {
            echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                Todas las restricciones de clave foránea necesarias están presentes.
            </div>";
        }
        
    } else {
        // La tabla no existe, hay que crearla
        echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>
            La tabla servicios_reservas no existe. Se intentará crear...
        </div>";
        
        try {
            // Crear la tabla
            $db->exec("
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
                    business_id INTEGER NOT NULL DEFAULT 1,
                    created_by INTEGER NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                Se ha creado correctamente la tabla servicios_reservas.
            </div>";
            
            // Verificar si las tablas relacionadas existen antes de agregar restricciones
            $tablasRelacionadas = [
                'servicios_medicos' => 'servicio_id',
                'rh_doctors' => 'doctor_id',
                'pacientes' => 'paciente_id'
            ];
            
            $restriccionesAgregadas = 0;
            
            foreach ($tablasRelacionadas as $tabla => $columna) {
                // Verificar si la tabla existe
                $stmt = $db->prepare("SELECT to_regclass('public.{$tabla}') IS NOT NULL as existe");
                $stmt->execute();
                $tablaExiste = $stmt->fetchColumn();
                
                if ($tablaExiste) {
                    try {
                        // Agregar restricción de clave foránea
                        $nombreColumna = str_replace('s_', '_', strtolower($tabla)) . '_' . $columna;
                        $sql = "ALTER TABLE servicios_reservas 
                                ADD CONSTRAINT fk_servicios_reservas_{$nombreColumna} 
                                FOREIGN KEY ({$columna}) REFERENCES {$tabla}({$columna})";
                        $db->exec($sql);
                        echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 5px 0; border-radius: 5px;'>
                            Restricción agregada para {$columna} → {$tabla}({$columna})
                        </div>";
                        $restriccionesAgregadas++;
                    } catch (PDOException $e) {
                        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 5px 0; border-radius: 5px;'>
                            Error al agregar restricción para {$columna} → {$tabla}: {$e->getMessage()}
                        </div>";
                    }
                } else {
                    echo "<div style='background-color: #fff3cd; color: #856404; padding: 10px; margin: 5px 0; border-radius: 5px;'>
                        No se pudo agregar restricción para {$columna} porque la tabla {$tabla} no existe.
                    </div>";
                }
            }
            
            if ($restriccionesAgregadas == 0) {
                echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <strong>Advertencia:</strong> No se pudo agregar ninguna restricción de clave foránea. 
                    Esto podría afectar la integridad de los datos.
                </div>";
            } else if ($restriccionesAgregadas < count($tablasRelacionadas)) {
                echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <strong>Advertencia:</strong> Solo se pudieron agregar {$restriccionesAgregadas} de " . count($tablasRelacionadas) . " 
                    restricciones de clave foránea. Esto podría afectar la integridad de los datos.
                </div>";
            } else {
                echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    Todas las restricciones de clave foránea fueron agregadas correctamente.
                </div>";
            }
            
        } catch (PDOException $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                Error al crear la tabla: {$e->getMessage()}
            </div>";
        }
    }
    
    echo "<div style='margin-top: 30px;'>
        <a href='check_servicios_reservas.php' class='btn' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
            Verificar tabla servicios_reservas
        </a>
        <a href='index.php' class='btn' style='background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>
            Volver al inicio
        </a>
    </div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>
        <h2>Error al procesar la operación:</h2>
        <p>{$e->getMessage()}</p>
    </div>";
}
