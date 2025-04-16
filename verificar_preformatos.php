<?php
/**
 * Script para verificar e insertar preformatos de ejemplo en la base de datos PostgreSQL
 */

require_once "model/conexion.php";

try {
    // Conectar a la base de datos PostgreSQL
    $conn = Conexion::conectar();
    
    echo "<h2>Verificación de preformatos</h2>";
    
    // Verificar si ya existen preformatos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM preformatos");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Preformatos existentes: " . $result['total'] . "</p>";
    
    // Si no hay preformatos, insertar algunos ejemplos
    if ($result['total'] == 0) {
        echo "<p>No se encontraron preformatos. Insertando ejemplos...</p>";
        
        // Preformatos de consulta
        $preformatosConsulta = [
            [
                'nombre' => 'Consulta General',
                'contenido' => "MOTIVO DE CONSULTA:\n\nANTECEDENTES:\n\nEXAMEN FÍSICO:\n\nDIAGNÓSTICO:\n\nTRATAMIENTO:",
                'tipo' => 'consulta'
            ],
            [
                'nombre' => 'Consulta Pediátrica',
                'contenido' => "MOTIVO DE CONSULTA:\n\nANTECEDENTES:\n\nPESO: \nTALLA: \nFC: \nFR: \nT°: \n\nEXAMEN FÍSICO:\n\nDIAGNÓSTICO:\n\nTRATAMIENTO:",
                'tipo' => 'consulta'
            ],
            [
                'nombre' => 'Control Prenatal',
                'contenido' => "MOTIVO DE CONSULTA: Control prenatal\n\nFUM: \nEG: \nFPP: \n\nPESO: \nTA: \nAU: \nFCF: \n\nEXAMEN FÍSICO:\n\nDIAGNÓSTICO:\n\nINDICACIONES:",
                'tipo' => 'consulta'
            ]
        ];
        
        // Preformatos de receta
        $preformatosReceta = [
            [
                'nombre' => 'Antibiótico General',
                'contenido' => "1. Amoxicilina 500mg - 1 cápsula cada 8 horas por 7 días\n2. Paracetamol 500mg - 1 tableta cada 8 horas si hay dolor o fiebre\n3. Abundantes líquidos",
                'tipo' => 'receta'
            ],
            [
                'nombre' => 'Analgésicos',
                'contenido' => "1. Ibuprofeno 400mg - 1 tableta cada 8 horas por 5 días\n2. Paracetamol 500mg - 1 tableta cada 8 horas alternando con ibuprofeno\n3. Reposo relativo",
                'tipo' => 'receta'
            ],
            [
                'nombre' => 'Antialérgico',
                'contenido' => "1. Loratadina 10mg - 1 tableta cada 24 horas por 7 días\n2. Evitar alérgenos conocidos\n3. Mantener ambientes ventilados",
                'tipo' => 'receta'
            ]
        ];
        
        // Iniciar transacción
        $conn->beginTransaction();
        
        try {
            // Preparar la consulta SQL para PostgreSQL
            $stmt = $conn->prepare("INSERT INTO preformatos (nombre, contenido, tipo, creado_por, activo) VALUES (:nombre, :contenido, :tipo, 1, true)");
            
            // Insertar preformatos de consulta
            foreach ($preformatosConsulta as $preformato) {
                $stmt->bindParam(':nombre', $preformato['nombre']);
                $stmt->bindParam(':contenido', $preformato['contenido']);
                $stmt->bindParam(':tipo', $preformato['tipo']);
                $stmt->execute();
            }
            
            // Insertar preformatos de receta
            foreach ($preformatosReceta as $preformato) {
                $stmt->bindParam(':nombre', $preformato['nombre']);
                $stmt->bindParam(':contenido', $preformato['contenido']);
                $stmt->bindParam(':tipo', $preformato['tipo']);
                $stmt->execute();
            }
            
            // Confirmar la transacción
            $conn->commit();
            
            echo "<p>Se han insertado " . (count($preformatosConsulta) + count($preformatosReceta)) . " preformatos de ejemplo.</p>";
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $conn->rollBack();
            throw $e;
        }
    } else {
        echo "<p>Ya existen preformatos en la base de datos.</p>";
        
        // Mostrar los preformatos existentes
        $stmt = $conn->prepare("SELECT id_preformato, nombre, tipo FROM preformatos WHERE activo = true ORDER BY tipo, nombre");
        $stmt->execute();
        $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Preformatos existentes:</h3>";
        echo "<ul>";
        foreach ($preformatos as $preformato) {
            echo "<li>ID: " . $preformato['id_preformato'] . " - " . $preformato['nombre'] . " (" . $preformato['tipo'] . ")</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Error</h2>";
    echo "<p>Error al conectar con la base de datos: " . $e->getMessage() . "</p>";
    error_log("[" . date('Y-m-d H:i:s') . "] Error en verificar_preformatos.php: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
}
?>

<p><a href="index.php">Volver al inicio</a></p>