<?php
/**
 * Archivo para depurar la respuesta de slots y mostrar información detallada
 * Útil para diagnosticar problemas con el formato de los horarios
 */

// Requerir archivos necesarios
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Obtener parámetros
$servicioId = isset($_GET['servicio_id']) ? intval($_GET['servicio_id']) : null;
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Validar que tengamos los parámetros necesarios
if (!$servicioId || !$doctorId) {
    echo "<h3>Error: Se requieren los parámetros servicio_id y doctor_id</h3>";
    echo "<p>Ejemplo de uso: ajax_slots_debug.php?servicio_id=1&doctor_id=14&fecha=2025-05-28</p>";
    exit;
}

// Obtener los slots
$slots = ControladorServicios::ctrGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);

// Mostrar información de diagnóstico
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depuración de Slots</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
        }
        .field-missing {
            color: red;
            font-weight: bold;
        }
        .field-present {
            color: green;
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="container-fluid py-4">
    <h1>Depuración de Slots</h1>
    
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Parámetros de consulta</div>
                <div class="card-body">
                    <p><strong>Servicio ID:</strong> <?= $servicioId ?></p>
                    <p><strong>Doctor ID:</strong> <?= $doctorId ?></p>
                    <p><strong>Fecha:</strong> <?= $fecha ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Respuesta completa</div>
                <div class="card-body">
                    <pre><?= json_encode(['status' => 'success', 'data' => $slots], JSON_PRETTY_PRINT) ?></pre>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-3">
        <div class="card-header">
            <h3>Análisis de Campos</h3>
            <p class="mb-0">Verificación de campos necesarios para la visualización de slots</p>
        </div>
        <div class="card-body">
            <?php if (empty($slots)): ?>
                <div class="alert alert-danger">
                    No se encontraron slots disponibles para los parámetros especificados.
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-6">
                        <h4>Campos críticos para la visualización de horarios:</h4>
                        <ul class="list-group mb-3">
                            <?php 
                            $firstSlot = $slots[0];
                            $requiredFields = [
                                'horario_id' => ['horario_id', 'id'],
                                'hora_inicio' => ['hora_inicio', 'inicio', 'start_time'],
                                'hora_fin' => ['hora_fin', 'fin', 'end_time'],
                                'sala_nombre' => ['sala_nombre', 'sala']
                            ];
                            
                            foreach ($requiredFields as $fieldLabel => $possibleFields): 
                                $fieldFound = false;
                                $actualField = null;
                                
                                foreach ($possibleFields as $field) {
                                    if (isset($firstSlot[$field])) {
                                        $fieldFound = true;
                                        $actualField = $field;
                                        break;
                                    }
                                }
                            ?>
                                <li class="list-group-item">
                                    <span class="<?= $fieldFound ? 'field-present' : 'field-missing' ?>">
                                        <?= $fieldLabel ?>: 
                                        <?php if ($fieldFound): ?>
                                            <strong>PRESENTE</strong> (como '<?= $actualField ?>': <?= $firstSlot[$actualField] ?>)
                                        <?php else: ?>
                                            <strong>AUSENTE</strong> (buscado como: <?= implode(', ', $possibleFields) ?>)
                                        <?php endif; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h4>Todos los campos disponibles en el primer slot:</h4>
                        <ul class="list-group">
                            <?php foreach ($firstSlot as $key => $value): ?>
                                <li class="list-group-item">
                                    <?= $key ?>: <?= is_array($value) ? json_encode($value) : $value ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>Tabla de Slots</h3>
        </div>
        <div class="card-body table-responsive">
            <?php if (empty($slots)): ?>
                <div class="alert alert-warning">No hay slots para mostrar.</div>
            <?php else: ?>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <?php 
                            // Generar encabezados dinámicamente a partir del primer slot
                            foreach (array_keys($slots[0]) as $header): 
                            ?>
                                <th><?= $header ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slots as $index => $slot): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <?php foreach ($slot as $key => $value): ?>
                                    <td>
                                        <?php
                                        // Formatear valores para mejor visualización
                                        if (is_array($value)) {
                                            echo json_encode($value);
                                        } else if (is_null($value)) {
                                            echo '<em class="text-muted">NULL</em>';
                                        } else if ($value === '') {
                                            echo '<em class="text-muted">EMPTY</em>';
                                        } else {
                                            echo htmlspecialchars($value);
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
