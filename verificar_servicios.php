<?php
// Script para verificar y mostrar los servicios disponibles

require_once 'config/config.php';
require_once 'model/conexion.php';
require_once 'model/servicios.model.php';
require_once 'controller/servicios.controller.php';

// Función para verificar si existe una tabla
function tabla_existe($nombre_tabla) {
    $stmt = Conexion::conectar()->prepare("SELECT to_regclass('public.$nombre_tabla')");
    $stmt->execute();
    return $stmt->fetchColumn() !== null;
}

// Verificar tablas de servicios
$tablas_a_verificar = [
    'servicios',
    'servicios_medicos', 
    'servicios_reservas'
];

echo "<h1>Verificación de Tablas de Servicios</h1>";
echo "<ul>";

foreach ($tablas_a_verificar as $tabla) {
    if (tabla_existe($tabla)) {
        echo "<li><strong>$tabla</strong>: Existe</li>";
    } else {
        echo "<li><strong>$tabla</strong>: No existe</li>";
    }
}

echo "</ul>";

// Verificar servicios disponibles
$tabla_servicios_existente = tabla_existe('servicios');
$tabla_servicios_medicos_existente = tabla_existe('servicios_medicos');

echo "<h2>Servicios Disponibles</h2>";

if ($tabla_servicios_existente || $tabla_servicios_medicos_existente) {
    try {
        $conn = Conexion::conectar();
        
        if ($tabla_servicios_existente) {
            $sql = "SELECT * FROM servicios LIMIT 10";
            $stmt = $conn->query($sql);
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($servicios) > 0) {
                echo "<h3>De tabla 'servicios':</h3>";
                echo "<table border='1'>";
                echo "<tr>";
                foreach (array_keys($servicios[0]) as $columna) {
                    echo "<th>$columna</th>";
                }
                echo "</tr>";
                
                foreach ($servicios as $servicio) {
                    echo "<tr>";
                    foreach ($servicio as $valor) {
                        echo "<td>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>La tabla 'servicios' no tiene registros.</p>";
            }
        }
        
        if ($tabla_servicios_medicos_existente) {
            $sql = "SELECT * FROM servicios_medicos LIMIT 10";
            $stmt = $conn->query($sql);
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($servicios) > 0) {
                echo "<h3>De tabla 'servicios_medicos':</h3>";
                echo "<table border='1'>";
                echo "<tr>";
                foreach (array_keys($servicios[0]) as $columna) {
                    echo "<th>$columna</th>";
                }
                echo "</tr>";
                
                foreach ($servicios as $servicio) {
                    echo "<tr>";
                    foreach ($servicio as $valor) {
                        echo "<td>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>La tabla 'servicios_medicos' no tiene registros.</p>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>Error al consultar servicios: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>No existe ninguna tabla de servicios. Se necesita crear una tabla que almacene los servicios para poder mostrar sus nombres.</p>";
    
    // Mostrar estructura recomendada
    echo "<h3>Estructura recomendada para tabla de servicios:</h3>";
    echo "<pre>
CREATE TABLE servicios (
    servicio_id SERIAL PRIMARY KEY,
    servicio_nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    duracion INTEGER, -- duración en minutos
    activo BOOLEAN DEFAULT true,
    business_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
    </pre>";
}

// Verificar las reservas
echo "<h2>Reservas Existentes</h2>";
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

if (tabla_existe('servicios_reservas')) {
    try {
        $sql = "SELECT * FROM servicios_reservas WHERE fecha_reserva = :fecha";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($reservas) > 0) {
            echo "<p>Se encontraron " . count($reservas) . " reservas para la fecha $fecha</p>";
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th>ID</th>";
            echo "<th>Doctor ID</th>";
            echo "<th>Paciente ID</th>";
            echo "<th>Servicio ID</th>";
            echo "<th>Fecha</th>";
            echo "<th>Hora</th>";
            echo "<th>Estado</th>";
            echo "</tr>";
            
            foreach ($reservas as $r) {
                echo "<tr>";
                echo "<td>" . $r['reserva_id'] . "</td>";
                echo "<td>" . $r['doctor_id'] . "</td>";
                echo "<td>" . $r['paciente_id'] . "</td>";
                echo "<td>" . $r['servicio_id'] . "</td>";
                echo "<td>" . $r['fecha_reserva'] . "</td>";
                echo "<td>" . substr($r['hora_inicio'], 0, 5) . " - " . substr($r['hora_fin'], 0, 5) . "</td>";
                echo "<td>" . ($r['reserva_estado'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No hay reservas para la fecha $fecha</p>";
        }
        
        // Formulario para buscar por otra fecha
        echo "<form method='get'>";
        echo "<p><label>Ver reservas de otra fecha: <input type='date' name='fecha' value='$fecha'></label>";
        echo "<button type='submit'>Buscar</button></p>";
        echo "</form>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>Error al consultar reservas: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>La tabla de reservas no existe.</p>";
}

// Script para crear tabla de servicios básica si falta
echo "<h2>Asistente para Crear Tabla de Servicios</h2>";

if (!$tabla_servicios_existente && !$tabla_servicios_medicos_existente) {
    echo "<p>No se encontró ninguna tabla de servicios. ¿Desea crear una tabla básica de servicios?</p>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='crear_tabla' value='1'>";
    echo "<button type='submit'>Crear Tabla de Servicios</button>";
    echo "</form>";
    
    if (isset($_POST['crear_tabla'])) {
        try {
            $sql = "
            CREATE TABLE IF NOT EXISTS servicios (
                servicio_id SERIAL PRIMARY KEY,
                servicio_nombre VARCHAR(100) NOT NULL,
                descripcion TEXT,
                duracion INTEGER,
                activo BOOLEAN DEFAULT true,
                business_id INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $conn = Conexion::conectar();
            $conn->exec($sql);
            
            echo "<p style='color: green;'>¡Tabla de servicios creada con éxito!</p>";
            echo "<p>Ahora puede agregar algunos servicios básicos:</p>";
            
            echo "<form method='post'>";
            echo "<input type='hidden' name='agregar_servicios' value='1'>";
            echo "<div style='margin-bottom: 10px;'>";
            echo "<input type='text' name='nombre[]' placeholder='Nombre del servicio' required>";
            echo "<input type='text' name='descripcion[]' placeholder='Descripción'>";
            echo "<input type='number' name='duracion[]' placeholder='Duración (min)' value='30'>";
            echo "</div>";
            
            echo "<div style='margin-bottom: 10px;'>";
            echo "<input type='text' name='nombre[]' placeholder='Nombre del servicio'>";
            echo "<input type='text' name='descripcion[]' placeholder='Descripción'>";
            echo "<input type='number' name='duracion[]' placeholder='Duración (min)' value='30'>";
            echo "</div>";
            
            echo "<button type='submit'>Agregar Servicios</button>";
            echo "</form>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error al crear tabla: " . $e->getMessage() . "</p>";
        }
    }
    
    if (isset($_POST['agregar_servicios'])) {
        try {
            $conn = Conexion::conectar();
            $stmt = $conn->prepare("INSERT INTO servicios (servicio_nombre, descripcion, duracion) VALUES (:nombre, :descripcion, :duracion)");
            
            $count = 0;
            for ($i = 0; $i < count($_POST['nombre']); $i++) {
                if (!empty($_POST['nombre'][$i])) {
                    $stmt->bindParam(':nombre', $_POST['nombre'][$i]);
                    $stmt->bindParam(':descripcion', $_POST['descripcion'][$i]);
                    $duracion = !empty($_POST['duracion'][$i]) ? intval($_POST['duracion'][$i]) : 30;
                    $stmt->bindParam(':duracion', $duracion);
                    $stmt->execute();
                    $count++;
                }
            }
            
            echo "<p style='color: green;'>¡$count servicios agregados con éxito!</p>";
            echo "<p><a href='?'>Recargar página para ver los servicios</a></p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error al agregar servicios: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p>Ya existe una tabla de servicios en el sistema.</p>";
}

// Script para agregar servicio_nombre a consulta de reservas si falta
echo "<h2>Mejorar Consulta de Reservas</h2>";

echo "<p>Para asegurar que las reservas muestren el nombre del servicio, se puede modificar la consulta de reservas para que incluya el nombre del servicio.</p>";
echo "<form method='post'>";
echo "<input type='hidden' name='mejorar_consulta' value='1'>";
echo "<button type='submit'>Mejorar Consulta de Reservas</button>";
echo "</form>";

if (isset($_POST['mejorar_consulta'])) {
    // Crear una vista para unificar servicios
    try {
        $conn = Conexion::conectar();
        
        $sqlVista = "
            CREATE OR REPLACE VIEW servicios_vista AS
            SELECT servicio_id, servicio_nombre, descripcion
            FROM (
                SELECT servicio_id, servicio_nombre, descripcion 
                FROM servicios
                WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'servicios')
                UNION ALL
                SELECT servicio_id, servicio_nombre, descripcion 
                FROM servicios_medicos
                WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'servicios_medicos')
            ) s;
        ";
        
        $conn->exec($sqlVista);
        echo "<p style='color: green;'>¡Se ha creado una vista unificada de servicios!</p>";
        
        echo "<p>Ahora al consultar reservas, se incluirá el nombre del servicio correctamente.</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error al crear vista de servicios: " . $e->getMessage() . "</p>";
        
        // Plan B: Crear tabla de servicios básica si no existe
        if (strpos($e->getMessage(), 'does not exist') !== false) {
            try {
                $sql = "
                CREATE TABLE IF NOT EXISTS servicios (
                    servicio_id SERIAL PRIMARY KEY,
                    servicio_nombre VARCHAR(100) NOT NULL,
                    descripcion TEXT,
                    duracion INTEGER,
                    activo BOOLEAN DEFAULT true,
                    business_id INTEGER,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                $conn->exec($sql);
                
                echo "<p style='color: green;'>¡Se ha creado una tabla básica de servicios!</p>";
            } catch (PDOException $e2) {
                echo "<p style='color: red;'>Error al crear tabla básica: " . $e2->getMessage() . "</p>";
            }
        }
    }
}

?>
