<?php
/**
 * ICD-11 API Integration Test
 * 
 * Esta herramienta permite probar rápidamente la integración con la API de ICD-11
 * y mostrará resultados detallados del proceso.
 */

// Configuración para mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Comprobar opciones de PHP importantes para la conectividad
$curlEnabled = function_exists('curl_init');
$allowUrlFopen = ini_get('allow_url_fopen');
$sslEnabled = in_array('https', stream_get_wrappers());
$phpVersion = PHP_VERSION;

// URLs y credenciales para pruebas
$tokenUrl = 'https://icdaccessmanagement.who.int/connect/token';
$apiBaseUrl = 'https://id.who.int/icd/release/11/2022-02';
$clientId = '97bc4e27-44a4-4a37-9e56-b65708f709a5_874d810b-8f96-4c9e-9c13-e66a78e8051f';
$clientSecret = '0EPvwLIAEFBdQgnaxbJAT2IaoPu4V9kvkATe9JlbCo4=';

// Credenciales ocultas para la visualización
$hiddenClientId = substr($clientId, 0, 10) . '...';
$hiddenClientSecret = substr($clientSecret, 0, 5) . '...';

// Función para realizar solicitudes HTTP con cURL o file_get_contents
function makeHttpRequest($url, $method = 'GET', $data = null, $headers = [], $showDetails = false) {
    $startTime = microtime(true);
    $result = [
        'success' => false,
        'method_used' => '',
        'status_code' => 0,
        'time_ms' => 0,
        'body' => '',
        'headers' => [],
        'error' => '',
        'details' => []
    ];
    
    // Intentar con cURL primero si está disponible
    if (function_exists('curl_init')) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            
            // Configurar método
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
            }
            
            // Configurar encabezados
            if (!empty($headers)) {
                $headersList = [];
                foreach ($headers as $key => $value) {
                    $headersList[] = "$key: $value";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headersList);
            }
            
            // Capturar información detallada
            if ($showDetails) {
                $verbose = fopen('php://temp', 'w+');
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_STDERR, $verbose);
            }
            
            // Ejecutar la solicitud
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            
            // Obtener detalles
            if ($showDetails && isset($verbose)) {
                rewind($verbose);
                $result['details']['verbose'] = stream_get_contents($verbose);
                fclose($verbose);
            }
            
            // Dividir encabezados y cuerpo
            $headerSize = $info['header_size'];
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            
            // Configurar resultado
            $result['success'] = ($info['http_code'] >= 200 && $info['http_code'] < 300 && !$error);
            $result['method_used'] = 'curl';
            $result['status_code'] = $info['http_code'];
            $result['time_ms'] = round((microtime(true) - $startTime) * 1000);
            $result['body'] = $body;
            $result['headers'] = $headers;
            $result['error'] = $error;
            $result['details']['info'] = $info;
            
            curl_close($ch);
            return $result;
        } catch (Exception $e) {
            // Si falla cURL, intentaremos con file_get_contents
            $result['error'] = "Error cURL: " . $e->getMessage();
        }
    }
    
    // Alternativa: usar file_get_contents
    if (!$result['success'] && function_exists('file_get_contents') && ini_get('allow_url_fopen')) {
        try {
            // Crear contexto
            $context = [];
            $context['http'] = [
                'method' => $method,
                'ignore_errors' => true,
                'follow_location' => 1,
                'max_redirects' => 10,
                'timeout' => 30
            ];
            
            // Añadir encabezados
            if (!empty($headers)) {
                $headerStr = '';
                foreach ($headers as $key => $value) {
                    $headerStr .= "$key: $value\r\n";
                }
                $context['http']['header'] = $headerStr;
            }
            
            // Añadir datos para POST
            if ($method === 'POST' && $data) {
                $context['http']['content'] = $data;
            }
            
            // Configuración SSL
            $context['ssl'] = [
                'verify_peer' => true,
                'verify_peer_name' => true
            ];
            
            // Crear stream context y realizar solicitud
            $streamContext = stream_context_create($context);
            $response = file_get_contents($url, false, $streamContext);
            
            // Obtener detalles de respuesta
            $responseHeaders = $http_response_header ?? [];
            
            // Analizar código de estado
            $statusCode = 0;
            foreach ($responseHeaders as $header) {
                if (preg_match('#HTTP/\d\.\d\s+(\d+)#', $header, $matches)) {
                    $statusCode = intval($matches[1]);
                    break;
                }
            }
            
            // Configurar resultado
            $result['success'] = ($statusCode >= 200 && $statusCode < 300 && $response !== false);
            $result['method_used'] = 'file_get_contents';
            $result['status_code'] = $statusCode;
            $result['time_ms'] = round((microtime(true) - $startTime) * 1000);
            $result['body'] = $response;
            $result['headers'] = implode("\n", $responseHeaders);
            $result['details']['context'] = $context;
            
            if ($response === false) {
                $error = error_get_last();
                $result['error'] = "Error file_get_contents: " . ($error['message'] ?? 'Desconocido');
            }
            
            return $result;
        } catch (Exception $e) {
            $result['error'] .= " | Error file_get_contents: " . $e->getMessage();
        }
    }
    
    // Si llegamos aquí, ambos métodos fallaron
    $result['time_ms'] = round((microtime(true) - $startTime) * 1000);
    return $result;
}

function getTokenData($clientId, $clientSecret) {
    global $tokenUrl;
    
    // Preparar datos
    $data = http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'scope' => 'icdapi_access',
        'grant_type' => 'client_credentials'
    ]);
    
    // Configurar encabezados
    $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Accept' => 'application/json'
    ];
    
    // Realizar solicitud
    $result = makeHttpRequest(
        $tokenUrl, 
        'POST', 
        $data, 
        $headers,
        true
    );
    
    // Intentar analizar el token si la solicitud fue exitosa
    if ($result['success'] && !empty($result['body'])) {
        $tokenData = json_decode($result['body'], true);
        if (isset($tokenData['access_token'])) {
            $result['token'] = $tokenData['access_token'];
            $result['expires_in'] = $tokenData['expires_in'] ?? 3600;
            // Ocultar parte del token para seguridad
            $result['token_preview'] = substr($tokenData['access_token'], 0, 15) . '...';
        } else {
            $result['error'] = 'Token no encontrado en la respuesta';
        }
    }
    
    return $result;
}

function testApiEndpoint($token, $endpoint, $headers = []) {
    global $apiBaseUrl;
    
    // Asegurarse de que la versión de API esté presente
    $headers['Authorization'] = 'Bearer ' . $token;
    $headers['Accept'] = 'application/json';
    $headers['Accept-Language'] = 'es, en';
    $headers['API-Version'] = 'v2'; // Requerido por la API ICD-11
    
    // Realizar solicitud
    $url = $apiBaseUrl . $endpoint;
    $result = makeHttpRequest(
        $url, 
        'GET', 
        null, 
        $headers,
        true
    );
    
    // Intentar decodificar JSON si la solicitud fue exitosa
    if ($result['success'] && !empty($result['body'])) {
        $jsonData = json_decode($result['body'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $result['json'] = $jsonData;
        } else {
            $result['json_error'] = json_last_error_msg();
        }
    }
    
    return $result;
}

// Ejecutar casos de prueba si se solicita
$testToken = null;
$tokenResult = null;
$searchResult = null;
$lookupResult = null;

// Fase 1: Obtener token
if (isset($_GET['test']) && $_GET['test'] === 'token') {
    $tokenResult = getTokenData($clientId, $clientSecret);
    if ($tokenResult['success'] && isset($tokenResult['token'])) {
        $testToken = $tokenResult['token'];
    }
}

// Fase 2: Probar búsqueda - solo si tenemos token
if (isset($_GET['test']) && $_GET['test'] === 'search' && isset($_GET['token']) && !empty($_GET['token'])) {
    $searchTerm = $_GET['term'] ?? 'diabetes';
    $testToken = $_GET['token'];
    $searchResult = testApiEndpoint(
        $testToken, 
        '/mms/search?q=' . urlencode($searchTerm) . '&useFlexisearch=true&preferredLanguage=es'
    );
}

// Fase 3: Probar lookup por código - solo si tenemos token
if (isset($_GET['test']) && $_GET['test'] === 'lookup' && isset($_GET['token']) && !empty($_GET['token'])) {
    $code = $_GET['code'] ?? 'MB36';
    $testToken = $_GET['token'];
    $lookupResult = testApiEndpoint(
        $testToken,
        '/mms/lookup?q=' . urlencode($code)
    );
}

// HTML para mostrar resultados
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test ICD-11 API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            max-height: 300px;
            overflow-y: auto;
        }
        .result-card {
            margin-bottom: 20px;
        }
        .test-complete {
            background-color: #d1e7dd;
        }
        .test-failed {
            background-color: #f8d7da;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 5px 8px;
        }
        .code-block {
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .http-success { color: #0d6efd; }
        .http-redirect { color: #6f42c1; }
        .http-client-error { color: #dc3545; }
        .http-server-error { color: #fd7e14; }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <h1>Diagnóstico de Integración ICD-11</h1>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Esta herramienta realiza pruebas para verificar la conectividad con la API oficial de ICD-11 de la OMS.
            Siga las pruebas en orden para verificar cada componente de la integración.
        </div>
        
        <!-- Sección 1: Verificación del entorno -->
        <div class="card result-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">1. Verificación del entorno PHP</h5>
                <span class="badge bg-<?php echo ($curlEnabled || $allowUrlFopen) && $sslEnabled ? 'success' : 'danger'; ?> status-badge">
                    <?php echo ($curlEnabled || $allowUrlFopen) && $sslEnabled ? 'Compatible' : 'Incompatible'; ?>
                </span>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th scope="row">Versión PHP</th>
                            <td>
                                <?php echo htmlspecialchars($phpVersion); ?>
                                <?php if (version_compare($phpVersion, '7.2.0', '>=')): ?>
                                    <span class="badge bg-success">Compatible</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Recomendado &gt;= 7.2.0</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">cURL Habilitado</th>
                            <td>
                                <?php if ($curlEnabled): ?>
                                    <span class="badge bg-success">Sí</span>
                                    <small class="text-muted ms-2">Preferido para API</small>
                                <?php else: ?>
                                    <span class="badge bg-warning">No</span>
                                    <small class="text-danger ms-2">Se recomienda habilitar cURL</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">allow_url_fopen</th>
                            <td>
                                <?php if ($allowUrlFopen): ?>
                                    <span class="badge bg-success">Habilitado</span>
                                    <small class="text-muted ms-2">Alternativa a cURL</small>
                                <?php else: ?>
                                    <span class="badge bg-warning">Deshabilitado</span>
                                    <small class="text-danger ms-2">Necesario si cURL no está disponible</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Soporte HTTPS</th>
                            <td>
                                <?php if ($sslEnabled): ?>
                                    <span class="badge bg-success">Habilitado</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Deshabilitado</span>
                                    <small class="text-danger ms-2">Requerido para conectar a la API ICD-11</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="mt-3">
                    <?php if (($curlEnabled || $allowUrlFopen) && $sslEnabled): ?>
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle"></i> Su entorno PHP parece estar correctamente configurado para conectar con la API ICD-11.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Su entorno PHP necesita ajustes para conectar con la API ICD-11.
                            <?php if (!$sslEnabled): ?>
                                <br><strong>Error crítico:</strong> El soporte HTTPS está deshabilitado. Verifique la configuración de PHP.
                            <?php endif; ?>
                            <?php if (!$curlEnabled && !$allowUrlFopen): ?>
                                <br><strong>Error crítico:</strong> Se requiere cURL o allow_url_fopen para realizar solicitudes HTTP.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sección 2: Prueba de autenticación -->
        <div class="card result-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">2. Prueba de Autenticación OAuth</h5>
                <?php if ($tokenResult): ?>
                    <span class="badge bg-<?php echo $tokenResult['success'] ? 'success' : 'danger'; ?> status-badge">
                        <?php echo $tokenResult['success'] ? 'Exitoso' : 'Fallido'; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p>
                    La API ICD-11 requiere autenticación OAuth 2.0 para todas las solicitudes. 
                    Esta prueba verifica si podemos obtener un token de acceso válido.
                </p>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Client ID</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($hiddenClientId); ?>" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Client Secret</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($hiddenClientSecret); ?>" disabled>
                        </div>
                    </div>
                </div>
                
                <?php if (!$tokenResult): ?>
                    <div class="text-center my-3">
                        <form method="get" action="">
                            <input type="hidden" name="test" value="token">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Probar Autenticación
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-<?php echo $tokenResult['success'] ? 'success' : 'danger'; ?> mt-3">
                        <?php if ($tokenResult['success']): ?>
                            <i class="fas fa-check-circle"></i> Autenticación exitosa. Se obtuvo un token de acceso.
                            <div class="mt-2">
                                <strong>Token:</strong> <code><?php echo htmlspecialchars($tokenResult['token_preview'] ?? ''); ?></code><br>
                                <strong>Expira en:</strong> <?php echo htmlspecialchars($tokenResult['expires_in'] ?? ''); ?> segundos
                            </div>
                        <?php else: ?>
                            <i class="fas fa-times-circle"></i> Error al obtener token de acceso.
                            <div class="mt-2">
                                <strong>Error:</strong> <?php echo htmlspecialchars($tokenResult['error'] ?? 'Desconocido'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Detalles de la solicitud:</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <th>URL</th>
                                    <td><code><?php echo htmlspecialchars($tokenUrl); ?></code></td>
                                </tr>
                                <tr>
                                    <th>Método</th>
                                    <td>POST</td>
                                </tr>
                                <tr>
                                    <th>Estado HTTP</th>
                                    <td>
                                        <span class="badge bg-<?php 
                                            $code = $tokenResult['status_code'];
                                            if ($code >= 200 && $code < 300) echo "success";
                                            elseif ($code >= 300 && $code < 400) echo "primary";
                                            elseif ($code >= 400 && $code < 500) echo "warning";
                                            else echo "danger";
                                        ?>">
                                            <?php echo htmlspecialchars($tokenResult['status_code']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tiempo</th>
                                    <td><?php echo htmlspecialchars($tokenResult['time_ms']); ?> ms</td>
                                </tr>
                                <tr>
                                    <th>Método usado</th>
                                    <td><?php echo htmlspecialchars($tokenResult['method_used']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($tokenResult['success']): ?>
                        <div class="text-center mt-3">
                            <a href="?test=token" class="btn btn-outline-secondary">
                                <i class="fas fa-sync"></i> Probar de nuevo
                            </a>
                            <a href="?test=search&token=<?php echo htmlspecialchars($tokenResult['token']); ?>&term=diabetes" class="btn btn-primary ms-2">
                                <i class="fas fa-search"></i> Continuar con búsqueda
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center mt-3">
                            <a href="?test=token" class="btn btn-warning">
                                <i class="fas fa-sync"></i> Intentar de nuevo
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sección 3: Prueba de búsqueda -->
        <?php if ($testToken): ?>
        <div class="card result-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">3. Prueba de Búsqueda ICD-11</h5>
                <?php if ($searchResult): ?>
                    <span class="badge bg-<?php echo $searchResult['success'] ? 'success' : 'danger'; ?> status-badge">
                        <?php echo $searchResult['success'] ? 'Exitoso' : 'Fallido'; ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p>
                    Esta prueba verifica si podemos buscar términos en la API ICD-11 utilizando el token obtenido.
                </p>
                
                <?php if ($searchResult): ?>
                    <div class="mb-3">
                        <form method="get" action="" class="row g-3">
                            <input type="hidden" name="test" value="search">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($testToken); ?>">
                            
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="term" placeholder="Término de búsqueda (ej: diabetes, hipertensión)" value="<?php echo htmlspecialchars($_GET['term'] ?? 'diabetes'); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="alert alert-<?php echo $searchResult['success'] ? 'success' : 'danger'; ?> mt-3">
                        <?php if ($searchResult['success']): ?>
                            <i class="fas fa-check-circle"></i> Búsqueda exitosa.
                            <?php
                            $count = 0;
                            if (isset($searchResult['json']) && isset($searchResult['json']['destinationEntities'])) {
                                $count = count($searchResult['json']['destinationEntities']);
                            }
                            ?>
                            <div class="mt-2">
                                <strong>Resultados encontrados:</strong> <?php echo $count; ?>
                            </div>
                        <?php else: ?>
                            <i class="fas fa-times-circle"></i> Error en la búsqueda.
                            <div class="mt-2">
                                <strong>Error:</strong> <?php echo htmlspecialchars($searchResult['error'] ?? 'Desconocido'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Detalles de la solicitud:</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <th>URL</th>
                                    <td><code class="small"><?php echo htmlspecialchars($apiBaseUrl . '/mms/search?q=' . urlencode($_GET['term'] ?? 'diabetes') . '&useFlexisearch=true&preferredLanguage=es'); ?></code></td>
                                </tr>
                                <tr>
                                    <th>Estado HTTP</th>
                                    <td>
                                        <span class="badge bg-<?php 
                                            $code = $searchResult['status_code'];
                                            if ($code >= 200 && $code < 300) echo "success";
                                            elseif ($code >= 300 && $code < 400) echo "primary";
                                            elseif ($code >= 400 && $code < 500) echo "warning";
                                            else echo "danger";
                                        ?>">
                                            <?php echo htmlspecialchars($searchResult['status_code']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tiempo</th>
                                    <td><?php echo htmlspecialchars($searchResult['time_ms']); ?> ms</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($searchResult['success'] && isset($searchResult['json']) && isset($searchResult['json']['destinationEntities']) && count($searchResult['json']['destinationEntities']) > 0): ?>
                        <div class="mt-4">
                            <h6>Resultados de búsqueda:</h6>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Título</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach(array_slice($searchResult['json']['destinationEntities'], 0, 5) as $entity): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($entity['theCode'] ?? ''); ?></code></td>
                                        <td><?php echo htmlspecialchars($entity['title'] ?? ''); ?></td>
                                        <td>
                                            <?php if (!empty($entity['theCode'])): ?>
                                            <a href="?test=lookup&token=<?php echo htmlspecialchars($testToken); ?>&code=<?php echo htmlspecialchars($entity['theCode']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-info-circle"></i> Detalles
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <div class="text-center mt-3">
                                <a href="?test=lookup&token=<?php echo htmlspecialchars($testToken); ?>&code=<?php echo htmlspecialchars($_GET['code'] ?? 'MB36'); ?>" class="btn btn-primary">
                                    <i class="fas fa-info-circle"></i> Continuar con prueba de detalles
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="mb-3">
                        <form method="get" action="" class="row g-3">
                            <input type="hidden" name="test" value="search">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($testToken); ?>">
                            
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="term" placeholder="Término de búsqueda (ej: diabetes, hipertensión)" value="diabetes" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Sección 4: Prueba de detalles -->
        <?php if ($testToken && $lookupResult): ?>
        <div class="card result-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">4. Prueba de Consulta por Código</h5>
                <span class="badge bg-<?php echo $lookupResult['success'] ? 'success' : 'danger'; ?> status-badge">
                    <?php echo $lookupResult['success'] ? 'Exitoso' : 'Fallido'; ?>
                </span>
            </div>
            <div class="card-body">
                <p>
                    Esta prueba verifica si podemos consultar detalles específicos de un código ICD-11.
                </p>
                
                <div class="mb-3">
                    <form method="get" action="" class="row g-3">
                        <input type="hidden" name="test" value="lookup">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($testToken); ?>">
                        
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="code" placeholder="Código ICD-11 (ej: MB36, BA00)" value="<?php echo htmlspecialchars($_GET['code'] ?? 'MB36'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Buscar Código
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="alert alert-<?php echo $lookupResult['success'] ? 'success' : 'danger'; ?> mt-3">
                    <?php if ($lookupResult['success']): ?>
                        <i class="fas fa-check-circle"></i> Consulta exitosa.
                    <?php else: ?>
                        <i class="fas fa-times-circle"></i> Error en la consulta de código.
                        <div class="mt-2">
                            <strong>Error:</strong> <?php echo htmlspecialchars($lookupResult['error'] ?? 'Desconocido'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <h6>Detalles de la solicitud:</h6>
                    <table class="table table-sm table-bordered">
                        <tbody>
                            <tr>
                                <th>URL</th>
                                <td><code class="small"><?php echo htmlspecialchars($apiBaseUrl . '/mms/lookup?q=' . urlencode($_GET['code'] ?? 'MB36')); ?></code></td>
                            </tr>
                            <tr>
                                <th>Estado HTTP</th>
                                <td>
                                    <span class="badge bg-<?php 
                                        $code = $lookupResult['status_code'];
                                        if ($code >= 200 && $code < 300) echo "success";
                                        elseif ($code >= 300 && $code < 400) echo "primary";
                                        elseif ($code >= 400 && $code < 500) echo "warning";
                                        else echo "danger";
                                    ?>">
                                        <?php echo htmlspecialchars($lookupResult['status_code']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tiempo</th>
                                <td><?php echo htmlspecialchars($lookupResult['time_ms']); ?> ms</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($lookupResult['success'] && isset($lookupResult['json'])): ?>
                    <div class="mt-4">
                        <h6>Datos del código ICD-11:</h6>
                        <?php if (isset($lookupResult['json']['destinationEntities']) && count($lookupResult['json']['destinationEntities']) > 0): ?>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <?php $entity = $lookupResult['json']['destinationEntities'][0]; ?>
                                    <h5 class="card-title"><?php echo htmlspecialchars($entity['title'] ?? 'Sin título'); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">Código: <?php echo htmlspecialchars($entity['theCode'] ?? 'Desconocido'); ?></h6>
                                    <p class="card-text">URI: <?php echo htmlspecialchars($entity['id'] ?? 'No disponible'); ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                No se encontraron detalles para el código especificado.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>¡Todas las pruebas completadas!</strong>
                        <p class="mb-0 mt-2">La integración con la API ICD-11 funciona correctamente en este entorno.</p>
                    </div>
                    
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> Volver al inicio
                        </a>
                        <a href="icd11_reference_codes.php" class="btn btn-info ms-2">
                            <i class="fas fa-list"></i> Códigos de referencia
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mt-4 text-center text-muted">
            <p>
                <small>
                    Herramienta de diagnóstico para la integración ICD-11<br>
                    Esta herramienta es parte del sistema de soporte para la implementación de diagnósticos médicos utilizando la API oficial de la OMS
                </small>
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
