<?php
/**
 * Panel de administración de los registros de envíos de WhatsApp
 */

// Control de acceso - Solo administradores
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Inicializar variables
$logFiles = [];
$selectedLog = '';
$logContent = '';
$filterSuccess = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Directorio de logs
$logDir = __DIR__ . '/../logs';

// Crear directorio si no existe
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// Obtener lista de archivos de log
$files = glob($logDir . '/whatsapp_envios_*.log');
rsort($files); // Del más reciente al más antiguo

foreach ($files as $file) {
    $logFiles[] = basename($file);
}

// Si hay archivos y no se ha seleccionado uno, seleccionar el más reciente
if (count($logFiles) > 0 && empty($selectedLog)) {
    $selectedLog = isset($_GET['log']) ? $_GET['log'] : $logFiles[0];
}

// Leer contenido del log seleccionado
if (!empty($selectedLog)) {
    $logPath = $logDir . '/' . $selectedLog;
    
    if (file_exists($logPath)) {
        $allLines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $filteredLines = [];
        
        // Aplicar filtros
        foreach ($allLines as $line) {
            $includeBySuccess = true;
            if ($filterSuccess === 'success') {
                $includeBySuccess = strpos($line, '[ÉXITO]') !== false;
            } elseif ($filterSuccess === 'error') {
                $includeBySuccess = strpos($line, '[ERROR]') !== false;
            }
            
            $includeBySearch = true;
            if (!empty($searchTerm)) {
                $includeBySearch = stripos($line, $searchTerm) !== false;
            }
            
            if ($includeBySuccess && $includeBySearch) {
                $filteredLines[] = $line;
            }
        }
        
        $logContent = implode("\n", $filteredLines);
    }
}

// Formato para mostrar datos
function formatLogLine($line) {
    // Colorear según éxito/error
    if (strpos($line, '[ÉXITO]') !== false) {
        return '<div class="log-line success">' . htmlspecialchars($line) . '</div>';
    } elseif (strpos($line, '[ERROR]') !== false) {
        return '<div class="log-line error">' . htmlspecialchars($line) . '</div>';
    } else {
        return '<div class="log-line">' . htmlspecialchars($line) . '</div>';
    }
}

// Título de la página
$pageTitle = 'Registro de Envíos por WhatsApp';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Clínica</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../assets/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    
    <style>
        .log-container {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            padding: 15px;
            max-height: 600px;
            overflow-y: auto;
            font-family: monospace;
            white-space: pre-wrap;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .log-line {
            margin-bottom: 5px;
            padding: 3px;
            border-bottom: 1px dotted #eee;
        }
        
        .log-line.success {
            background-color: #e7f7e7;
            color: #155724;
        }
        
        .log-line.error {
            background-color: #ffeaea;
            color: #721c24;
        }
        
        .filter-row {
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Incluir el header y el sidebar -->
        <?php include_once '../view/modules/header.php'; ?>
        <?php include_once '../view/modules/sidebar.php'; ?>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><?= $pageTitle ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                                <li class="breadcrumb-item active"><?= $pageTitle ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Filtros y selección de archivo -->
                    <div class="row filter-row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="get" class="form-inline">
                                        <!-- Selector de archivo -->
                                        <div class="form-group mr-3">
                                            <label for="log" class="mr-2">Archivo:</label>
                                            <select name="log" id="log" class="form-control" onchange="this.form.submit()">
                                                <?php foreach ($logFiles as $logFile): ?>
                                                <option value="<?= $logFile ?>" <?= $selectedLog === $logFile ? 'selected' : '' ?>>
                                                    <?= $logFile ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <!-- Filtro por resultado -->
                                        <div class="form-group mr-3">
                                            <label for="filter" class="mr-2">Mostrar:</label>
                                            <select name="filter" id="filter" class="form-control" onchange="this.form.submit()">
                                                <option value="all" <?= $filterSuccess === 'all' ? 'selected' : '' ?>>Todos</option>
                                                <option value="success" <?= $filterSuccess === 'success' ? 'selected' : '' ?>>Exitosos</option>
                                                <option value="error" <?= $filterSuccess === 'error' ? 'selected' : '' ?>>Errores</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Búsqueda -->
                                        <div class="form-group mr-3">
                                            <input type="text" name="search" id="search" class="form-control" placeholder="Buscar..." value="<?= htmlspecialchars($searchTerm) ?>">
                                            <button type="submit" class="btn btn-primary ml-2">
                                                <i class="fas fa-search"></i> Buscar
                                            </button>
                                        </div>
                                        
                                        <!-- Botón para limpiar filtros -->
                                        <a href="?log=<?= $selectedLog ?>" class="btn btn-secondary">
                                            <i class="fas fa-sync"></i> Limpiar filtros
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenido del log -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-file-alt"></i> 
                                        <?= $selectedLog ?>
                                    </h3>
                                    
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <?php if (empty($logFiles)): ?>
                                    <div class="alert alert-info">
                                        No hay archivos de registro disponibles.
                                    </div>
                                    <?php elseif (empty($logContent)): ?>
                                    <div class="alert alert-warning">
                                        No hay registros que coincidan con los filtros o el archivo está vacío.
                                    </div>
                                    <?php else: ?>
                                    <div class="log-container">
                                        <?php 
                                        $lines = explode("\n", $logContent);
                                        foreach ($lines as $line) {
                                            echo formatLogLine($line);
                                        }
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Explicación del formato -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-info-circle"></i> Ayuda
                                    </h3>
                                    
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <h5>Formato de los registros</h5>
                                    <p>Cada línea del registro contiene la siguiente información:</p>
                                    <ul>
                                        <li><strong>Fecha y hora:</strong> Cuando se realizó el envío</li>
                                        <li><strong>Estado:</strong> ÉXITO o ERROR según el resultado</li>
                                        <li><strong>ReservaID:</strong> Identificador de la reserva</li>
                                        <li><strong>Teléfono:</strong> Número al que se envió</li>
                                        <li><strong>URL:</strong> Dirección del PDF enviado</li>
                                        <li><strong>IP:</strong> Dirección IP del remitente</li>
                                        <li><strong>Error:</strong> Descripción en caso de error</li>
                                        <li><strong>UA:</strong> Navegador utilizado</li>
                                    </ul>
                                    
                                    <h5>Significado de los colores</h5>
                                    <ul>
                                        <li><span class="badge badge-success">Verde</span>: Envío exitoso</li>
                                        <li><span class="badge badge-danger">Rojo</span>: Error en el envío</li>
                                    </ul>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        Los registros se guardan automáticamente en la carpeta <code>logs/</code> y se organizan por mes.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <!-- Footer -->
        <?php include_once '../view/modules/footer.php'; ?>
    </div>
    
    <!-- jQuery -->
    <script src="../assets/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../assets/js/adminlte.min.js"></script>
</body>
</html>
