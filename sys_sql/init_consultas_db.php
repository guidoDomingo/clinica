<?php
require_once __DIR__ . '/../model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    // Leer y ejecutar el archivo SQL
    $sql = file_get_contents(__DIR__ . '/consultas_estructura.sql');
    $pdo->exec($sql);
    
    echo "Estructura de tablas para consultas médicas inicializada correctamente.\n";
    
    // Insertar algunos motivos comunes de ejemplo
    $motivos = [
        ['Control rutinario', 'Revisión periódica sin síntomas específicos'],
        ['Dolor ocular', 'Paciente presenta dolor en uno o ambos ojos'],
        ['Visión borrosa', 'Dificultad para ver con claridad'],
        ['Irritación', 'Ojos rojos o irritados'],
        ['Actualización de lentes', 'Paciente requiere actualizar su prescripción']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO motivos_comunes (nombre, descripcion, creado_por) VALUES (?, ?, 1)");
    foreach ($motivos as $motivo) {
        $stmt->execute([$motivo[0], $motivo[1]]);
    }
    
    // Insertar preformatos de ejemplo
    $preformatos = [
        ['Consulta estándar', 'Paciente acude a consulta por [motivo]. Se realiza examen completo de la vista incluyendo refracción y evaluación de salud ocular.', 'consulta'],
        ['Receta lentes monofocales', 'Rx:\n- OD: [parámetros]\n- OI: [parámetros]\n\nLentes monofocales con antireflejo.\nUso permanente.', 'receta'],
        ['Receta gotas lubricantes', 'Gotas lubricantes sin conservantes.\nAplicar 1 gota en cada ojo 3 veces al día.\nContinuar por 15 días.', 'receta']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO preformatos (nombre, contenido, tipo, creado_por) VALUES (?, ?, ?, 1)");
    foreach ($preformatos as $preformato) {
        $stmt->execute([$preformato[0], $preformato[1], $preformato[2]]);
    }
    
    echo "Datos iniciales insertados correctamente.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}