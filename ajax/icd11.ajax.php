<?php
/**
 * ICD-11 API Client
 * 
 * Este archivo proporciona un cliente para acceder a la API oficial de la OMS para ICD-11
 * Documentación de la API: https://icd.who.int/docs/icd-api/APIDoc-Version2/
 * 
 * ACTUALIZACIÓN: Se eliminó la verificación estricta de cURL para permitir métodos alternativos
 * como file_get_contents con allow_url_fopen.
 */

// Verificar disponibilidad de métodos HTTP
$curlAvailable = function_exists('curl_init');
$fileGetContentsAvailable = function_exists('file_get_contents') && ini_get('allow_url_fopen');

// Verificar soporte de HTTPS
$httpsAvailable = in_array('https', stream_get_wrappers());

// Si no hay ningún método disponible para realizar peticiones HTTP, mostrar error
if (!$curlAvailable && (!$fileGetContentsAvailable || !$httpsAvailable)) {
    header('Content-Type: application/json');
    
    $message = 'No hay métodos HTTP disponibles en este servidor para conectarse a la API.';
    $instructions = 'Habilite cURL o allow_url_fopen en php.ini';
    
    if (!$httpsAvailable) {
        $message .= ' El soporte para HTTPS no está habilitado.';
        $instructions .= ' y asegúrese de que openssl está habilitado en php.ini';
    }
    
    echo json_encode([
        'success' => false,
        'message' => $message,
        'requirements' => [
            'curl' => 'Extensión cURL (recomendado) o allow_url_fopen necesarios',
            'https' => 'Soporte HTTPS requerido para conexiones seguras',
            'instructions' => $instructions,
            'diagnostic' => [
                'curl_available' => $curlAvailable,
                'file_get_contents_available' => $fileGetContentsAvailable,
                'https_available' => $httpsAvailable,
                'extensions' => get_loaded_extensions(),
                'php_version' => PHP_VERSION,
                'sapi' => php_sapi_name(),
                'stream_wrappers' => stream_get_wrappers()
            ]
        ]
    ]);
    exit;
}

// Registrar qué método se usará para depuración
error_log('ICD11Ajax - Método HTTP disponible: ' . 
    ($curlAvailable ? 'cURL (principal)' : 'file_get_contents (alternativo)'));

class ICD11Ajax {
    // Credenciales de API
    private $clientId = '97bc4e27-44a4-4a37-9e56-b65708f709a5_874d810b-8f96-4c9e-9c13-e66a78e8051f';
    private $clientSecret = '0EPvwLIAEFBdQgnaxbJAT2IaoPu4V9kvkATe9JlbCo4=';
    
    // URLs de la API
    private $tokenUrl = 'https://icdaccessmanagement.who.int/connect/token';
    private $apiBaseUrl = 'https://id.who.int/icd/release/11/2022-02';
    
    // Token de acceso
    private $accessToken = null;
    private $tokenExpiry = 0;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Configurar errores
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Procesar la solicitud
        $this->processRequest();
    }
      /**
     * Procesa la solicitud recibida y envía la respuesta
     */
    private function processRequest() {
        // Verificar que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse([
                'success' => false,
                'message' => 'Método no permitido, solo se acepta POST'
            ], 405);
            return;
        }
        
        // Obtener los datos de la solicitud
        $rawInput = file_get_contents('php://input');
        
        // Registrar entrada para depuración
        error_log('ICD11Ajax - Datos recibidos: ' . $rawInput);
        
        // Intentar decodificar JSON
        $postData = null;
        if (!empty($rawInput)) {
            $postData = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'JSON inválido: ' . json_last_error_msg(),
                    'raw_data' => substr($rawInput, 0, 100) . (strlen($rawInput) > 100 ? '...' : '')
                ], 400);
                return;
            }
        }
        
        // Si no es JSON válido, intentar con $_POST
        if (!$postData) {
            $postData = $_POST;
            // Registrar formato alternativo
            error_log('ICD11Ajax - Usando datos de $_POST: ' . print_r($_POST, true));
        }
        
        // Verificar que se proporcionó una acción
        $action = isset($postData['action']) ? $postData['action'] : null;
        if (!$action) {
            $this->sendResponse([
                'success' => false,
                'message' => 'No se especificó una acción'
            ], 400);
            return;
        }
        
        try {
            // Realizar la acción correspondiente
            switch ($action) {
                case 'searchByCode':
                    $code = isset($postData['code']) ? $postData['code'] : null;
                    if (!$code) {
                        throw new Exception('El código es requerido');
                    }
                    $result = $this->searchByCode($code);
                    $this->sendResponse([
                        'success' => true,
                        'data' => $result
                    ]);
                    break;
                    
                case 'searchByTerm':
                    $term = isset($postData['term']) ? $postData['term'] : null;
                    $language = isset($postData['language']) ? $postData['language'] : 'es';
                    if (!$term) {
                        throw new Exception('El término de búsqueda es requerido');
                    }
                    $result = $this->searchByTerm($term, $language);
                    $this->sendResponse([
                        'success' => true,
                        'data' => $result
                    ]);
                    break;
                    
                case 'getEntityDetails':
                    $uri = isset($postData['uri']) ? $postData['uri'] : null;
                    if (!$uri) {
                        throw new Exception('El URI de la entidad es requerido');
                    }
                    $result = $this->getEntityDetails($uri);
                    $this->sendResponse([
                        'success' => true,
                        'data' => $result
                    ]);
                    break;
                    
                default:
                    $this->sendResponse([
                        'success' => false,
                        'message' => 'Acción no reconocida: ' . $action
                    ], 400);
                    break;
            }
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'details' => $e->getTraceAsString()
            ], 500);
        }
    }    /**
     * Busca una entidad por código ICD-11
     * 
     * @param string $code Código ICD-11 (ejemplo: MD12)
     * @return array Datos de la entidad
     */    private function searchByCode($code) {
        try {
            $token = $this->getAccessToken();
            
            // Depurar el código buscado
            error_log("ICD11Ajax - Buscando código: '$code'");
            
            // Intentar la búsqueda de múltiples maneras
            $searchMethods = [
                // 1. Intento: Búsqueda general por código en el buscador normal (más confiable)
                [
                    'url' => $this->apiBaseUrl . '/mms/search?q=' . urlencode($code) . '&useFlexisearch=true&preferredLanguage=es',
                    'description' => 'búsqueda general'
                ],
                // 2. Intento: Lookup directo usando el endpoint de lookup
                [
                    'url' => $this->apiBaseUrl . '/mms/lookup?q=' . urlencode($code),
                    'description' => 'lookup directo'
                ],
                // 3. Intento: Probar formateo alternativo del código
                [
                    'url' => $this->apiBaseUrl . '/mms/search?q=code:' . urlencode($code) . '&useFlexisearch=true&preferredLanguage=es',
                    'description' => 'búsqueda específica por código'
                ]
            ];
            
            $lastResponse = null;
            $lastUrl = '';
            $errors = [];
            
            // Intentar cada método hasta que uno funcione
            foreach ($searchMethods as $method) {
                error_log("ICD11Ajax - Intentando búsqueda de código '$code' usando " . $method['description']);
                $url = $method['url'];
                
                // Realizar la solicitud HTTP con encabezados mejorados
                $response = $this->httpRequest($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                        'Accept-Language' => 'es,en;q=0.9',
                        'Content-Type' => 'application/json',
                        'API-Version' => 'v2' // Requerido por la API ICD-11
                    ]
                ]);
                
                $lastResponse = $response;
                $lastUrl = $url;
                
                // Si la solicitud es exitosa, detenemos el bucle
                if ($response['status'] == 200) {
                    // Verificar que haya datos en la respuesta
                    $data = json_decode($response['body'], true);
                    if (!empty($data) && isset($data['destinationEntities']) && count($data['destinationEntities']) > 0) {
                        error_log("ICD11Ajax - Éxito al buscar código '$code' usando " . $method['description']);
                        break;
                    } else {
                        error_log("ICD11Ajax - Respuesta 200 pero sin datos para código '$code' usando " . $method['description']);
                    }
                }
                
                // Guardar error para reportar al final si todos los métodos fallan
                $errors[] = "Error con " . $method['description'] . ": HTTP " . $response['status'] . 
                    " - " . substr($response['body'] ?? '', 0, 100);
            }
              // Verificar respuesta final (después de probar todos los métodos)
            if (!isset($lastResponse) || $lastResponse['status'] != 200 || empty($data['destinationEntities'])) {
                // Registrar detalles de error completos para depuración
                error_log("ICD11Ajax - Error en búsqueda por código '$code'. Todos los métodos fallaron.");
                error_log("ICD11Ajax - Intentos realizados: " . implode(" | ", $errors));
                
                // Si tenemos respuesta, usarla para el mensaje de error
                if (isset($lastResponse)) {
                    error_log("ICD11Ajax - Último intento: URL: $lastUrl, Código HTTP: " . $lastResponse['status'] . 
                        ", Respuesta: " . substr($lastResponse['body'] ?? '', 0, 500));
                        
                    // Si es error 404, dar un mensaje más útil
                    if ($lastResponse['status'] == 404) {
                        throw new Exception("El código '$code' no se encontró en la base de datos ICD-11. Verifique que sea un código válido y actual.");
                    } else {
                        throw new Exception('Error al buscar por código. Código HTTP: ' . $lastResponse['status'] . 
                            '. Respuesta: ' . substr($lastResponse['body'] ?? '', 0, 200));
                    }
                } else {
                    throw new Exception('Error al buscar por código. No se pudo conectar a la API ICD-11.');
                }
            }
              // Aquí sabemos que tenemos una respuesta exitosa y que $data ya está decodificado porque lo verificamos antes
            // pero actualizamos para asegurarnos
            $data = json_decode($lastResponse['body'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("ICD11Ajax - Error decodificando JSON para código '$code': " . json_last_error_msg());
                throw new Exception('Error al decodificar respuesta JSON: ' . json_last_error_msg() . 
                    '. Respuesta: ' . substr($lastResponse['body'] ?? '', 0, 100));
            }
            
            // Verificación adicional para asegurarnos de que hay resultados
            if (empty($data['destinationEntities']) || count($data['destinationEntities']) === 0) {
                error_log("ICD11Ajax - No se encontraron entidades para el código '$code'");
                throw new Exception("No se encontraron resultados para el código: $code. Es posible que no sea un código ICD-11 válido o que esté mal formateado.");
            }
            
            // Registrar datos para depuración
            error_log("ICD11Ajax - Respuesta de búsqueda por código '$code': " . substr(print_r($data, true), 0, 500) . "...");
            
            return $data;
        } catch (Exception $e) {
            // Propagamos el error sin usar fallback
            throw $e;
        }
    }
      /**
     * Busca términos por texto
     * 
     * @param string $term Término a buscar
     * @param string $language Código de idioma (es, en)
     * @return array Resultados de la búsqueda
     */    private function searchByTerm($term, $language = 'es') {
        try {
            $token = $this->getAccessToken();
            
            // Depurar el término buscado
            error_log("ICD11Ajax - Buscando término: '$term' (idioma: $language)");
            
            // Construir URL para búsqueda de términos
            $url = $this->apiBaseUrl . '/mms/search?q=' . urlencode($term) . '&useFlexisearch=true';
            if ($language) {
                $url .= '&preferredLanguage=' . urlencode($language);
            }
              // Realizar la solicitud HTTP con encabezados mejorados
            $response = $this->httpRequest($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Accept-Language' => $language . ',en;q=0.9',
                    'Content-Type' => 'application/json',
                    'API-Version' => 'v2' // Requerido por la API ICD-11
                ]
            ]);
            
            // Verificar respuesta
            if ($response['status'] != 200) {
                // Registrar detalles de error completos para depuración
                error_log("ICD11Ajax - Error en búsqueda por término '$term'. Código HTTP: " . $response['status'] . 
                    ", URL: $url, Respuesta: " . substr($response['body'], 0, 500));
                    
                throw new Exception('Error al buscar por término. Código HTTP: ' . $response['status'] . 
                    '. Respuesta: ' . substr($response['body'], 0, 200));
            }
            
            // Decodificar respuesta
            $data = json_decode($response['body'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al decodificar respuesta JSON: ' . json_last_error_msg() . 
                    '. Respuesta: ' . substr($response['body'], 0, 100));
            }
            
            // Si no hay entidades, lanzamos error explícito pero no usamos fallback
            if (empty($data['destinationEntities']) || count($data['destinationEntities']) === 0) {
                throw new Exception('No se encontraron resultados para el término: ' . $term);
            }
            
            // Registrar datos para depuración
            error_log("Respuesta de búsqueda por término '$term': " . substr(print_r($data, true), 0, 500) . "...");
            
            return $data;
        } catch (Exception $e) {
            // Propagamos el error sin usar fallback
            throw $e;
        }
    }
      /**
     * Obtiene detalles completos de una entidad ICD
     * 
     * @param string $uri URI de la entidad
     * @return array Detalles de la entidad
     */    private function getEntityDetails($uri) {
        try {
            $token = $this->getAccessToken();
            
            // Depurar la URI solicitada
            error_log("ICD11Ajax - Obteniendo detalles de entidad: '$uri'");
            
            // Construir URL para obtener detalles de la entidad
            $url = $uri;
            if (strpos($uri, 'http') !== 0) {
                // Si no es una URL completa, agregar la base
                $url = $this->apiBaseUrl . '/' . ltrim($uri, '/');
            }
              // Realizar la solicitud HTTP
            $response = $this->httpRequest($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Accept-Language' => 'es, en',
                    'API-Version' => 'v2' // Requerido por la API ICD-11
                ]
            ]);
            
            // Verificar respuesta
            if ($response['status'] != 200) {
                throw new Exception('Error al obtener detalles de la entidad. Código HTTP: ' . $response['status'] . 
                    '. Respuesta: ' . substr($response['body'], 0, 200));
            }
            
            // Decodificar respuesta
            $data = json_decode($response['body'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al decodificar respuesta JSON: ' . json_last_error_msg() . 
                    '. Respuesta: ' . substr($response['body'], 0, 100));
            }
            
            // Verificar que hay datos válidos
            if (empty($data)) {
                throw new Exception('La API devolvió una respuesta vacía para: ' . $uri);
            }
            
            return $data;
        } catch (Exception $e) {
            // Propagamos el error sin usar fallback
            throw $e;
        }
    }
      // Los métodos getFallbackCodeData y getFallbackTermData han sido eliminados
    // para garantizar que todas las respuestas provengan directamente de la API
      /**
     * Realiza una solicitud HTTP utilizando el método más adecuado disponible
     * 
     * @param string $url URL a solicitar
     * @param array $options Opciones de la solicitud (method, headers, data)
     * @return array Respuesta con cuerpo y código de estado
     * @throws Exception Si no se puede realizar la solicitud
     */    private function httpRequest($url, $options = []) {
        $method = $options['method'] ?? 'GET';
        $headers = $options['headers'] ?? [];
        $data = $options['data'] ?? null;
        
        // Registrar la solicitud para depuración
        error_log("ICD11Ajax - HTTP Request: $method $url");
        
        // Intentar usar cURL primero
        if (function_exists('curl_init')) {
            try {
                // Configurar cURL
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                
                // Configurar método
                if ($method === 'POST') {
                    curl_setopt($ch, CURLOPT_POST, true);
                    if ($data) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
                    }
                }
                
                // Agregar encabezados
                if (!empty($headers)) {
                    $headersList = [];
                    foreach ($headers as $key => $value) {
                        $headersList[] = "$key: $value";
                    }
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headersList);
                }
                  // Configurar opciones avanzadas de depuración
                $verbose = true;
                if ($verbose) {
                    $verboseOutput = fopen('php://temp', 'w+');
                    curl_setopt($ch, CURLOPT_VERBOSE, true);
                    curl_setopt($ch, CURLOPT_STDERR, $verboseOutput);
                }
                
                // Ejecutar la solicitud
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                
                // Capturar información detallada para depuración
                $requestInfo = curl_getinfo($ch);
                
                if ($verbose) {
                    rewind($verboseOutput);
                    $verboseLog = stream_get_contents($verboseOutput);
                    fclose($verboseOutput);
                    
                    // Guardar en archivo log si es un error
                    if ($httpCode >= 400 || $error) {
                        error_log("ICD11Ajax - Detalle completo de solicitud cURL con error:\n" . $verboseLog);
                        error_log("ICD11Ajax - Info de la solicitud: " . print_r($requestInfo, true));
                    }
                }
                
                curl_close($ch);
                
                // Verificar si hubo error
                if ($error) {
                    error_log("ICD11Ajax - Error cURL: $error (URL: $url)");
                    throw new Exception("Error cURL: $error");
                }
                
                // Registrar tiempos para diagnóstico
                error_log("ICD11Ajax - Solicitud procesada: $url - HTTP: $httpCode - Tiempo: {$requestInfo['total_time']}s");
                
                return [
                    'body' => $response,
                    'status' => $httpCode,
                    'method_used' => 'curl',
                    'info' => $requestInfo
                ];
            } catch (Exception $curlError) {
                error_log("Error usando cURL: " . $curlError->getMessage() . ". Intentando con file_get_contents...");
                // Si falla cURL, intentamos con file_get_contents
            }
        }          // Alternativa: usar file_get_contents con stream context
        if (function_exists('file_get_contents') && function_exists('stream_context_create')) {
            try {
                // Registrar intento con file_get_contents
                error_log("ICD11Ajax - Intentando solicitud HTTP con file_get_contents: $method $url");
                
                // Configurar el contexto con opciones mejoradas
                $context = [];
                
                // Configuración común para todas las solicitudes
                $context['http'] = [
                    'method' => $method,
                    'ignore_errors' => true, // Importante: no fallar en códigos de error HTTP
                    'follow_location' => true,
                    'max_redirects' => 10,
                    'timeout' => 30,
                    'user_agent' => 'WHO-ICD11PHP/1.0 (github@example.com)'
                ];
                
                // Configuración específica para POST
                if ($method === 'POST') {
                    $context['http']['header'] = 'Content-Type: application/x-www-form-urlencoded';
                    $context['http']['content'] = is_array($data) ? http_build_query($data) : $data;
                }
                
                // Agregar encabezados personalizados con formato correcto
                if (!empty($headers)) {
                    $headerStr = isset($context['http']['header']) ? $context['http']['header'] . "\r\n" : '';
                    foreach ($headers as $key => $value) {
                        $headerStr .= "$key: $value\r\n";
                    }
                    $context['http']['header'] = rtrim($headerStr); // Quitar espacios en blanco al final
                }
                
                // Mejorar manejo SSL
                $context['ssl'] = [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false
                ];
                  // Crear contexto y realizar la solicitud
                $streamContext = stream_context_create($context);
                
                // Registrar detalles de la solicitud para diagnóstico
                error_log("ICD11Ajax - Realizando solicitud file_get_contents a: $url");
                error_log("ICD11Ajax - Contexto HTTP: " . json_encode($context['http']));
                
                // Usar error suppression pero capturar cualquier error
                $startTime = microtime(true);
                $response = @file_get_contents($url, false, $streamContext);
                $endTime = microtime(true);
                
                // Verificar headers de respuesta
                $httpResponseHeaders = $http_response_header ?? [];
                
                // Registrar headers para depuración
                if (count($httpResponseHeaders) > 0) {
                    error_log("ICD11Ajax - Headers de respuesta: " . implode(" | ", array_slice($httpResponseHeaders, 0, 5)) . 
                        (count($httpResponseHeaders) > 5 ? ' ...' : ''));
                } else {
                    error_log("ICD11Ajax - No se recibieron headers de respuesta");
                }
                
                // Manejar errores
                if ($response === false) {
                    $error = error_get_last();
                    $errorMsg = $error['message'] ?? 'Desconocido';
                    
                    // Registrar error detallado
                    error_log("ICD11Ajax - Error con file_get_contents: $errorMsg");
                    error_log("ICD11Ajax - URL que falló: $url");
                    
                    // Ver si podemos obtener un código de error HTTP
                    $errorStatus = 0;
                    foreach ($httpResponseHeaders as $header) {
                        if (preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#', $header, $matches)) {
                            $errorStatus = intval($matches[1]);
                            break;
                        }
                    }
                    
                    if ($errorStatus > 0) {
                        error_log("ICD11Ajax - Código HTTP detectado en error: $errorStatus");
                        throw new Exception("Error con file_get_contents: $errorMsg", $errorStatus);
                    } else {
                        throw new Exception("Error con file_get_contents: $errorMsg");
                    }
                }
                
                // Obtener el código de estado
                $status = 200; // Default
                foreach ($httpResponseHeaders as $header) {
                    if (preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#', $header, $matches)) {
                        $status = intval($matches[1]);
                        break;
                    }
                }
                
                // Registrar tiempo de respuesta
                $duration = round(($endTime - $startTime) * 1000);
                error_log("ICD11Ajax - Solicitud file_get_contents completada: HTTP $status en {$duration}ms");
                
                return [
                    'body' => $response,
                    'status' => $status,
                    'method_used' => 'file_get_contents',
                    'headers' => $httpResponseHeaders,
                    'time_ms' => $duration
                ];
            } catch (Exception $fileError) {
                error_log("Error usando file_get_contents: " . $fileError->getMessage());
                throw $fileError; // Re-lanzar si ambos métodos fallan
            }
        }
        
        // Si llegamos aquí, ningún método funcionó
        throw new Exception('No se pudo realizar la solicitud HTTP: ni cURL ni file_get_contents están disponibles o funcionan correctamente');
    }

    /**
     * Obtiene y almacena un token de acceso
     * 
     * @return string Token de acceso
     * @throws Exception Si hay un error al obtener el token
     */    private function getAccessToken() {
        // Verificar si ya tenemos un token válido
        if ($this->accessToken && time() < $this->tokenExpiry) {
            error_log("ICD11Ajax - Usando token existente, válido hasta " . date('Y-m-d H:i:s', $this->tokenExpiry));
            return $this->accessToken;
        }
        
        error_log("ICD11Ajax - Solicitando nuevo token de acceso");
        
        try {
            // Datos de autenticación
            $postData = http_build_query([
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'icdapi_access',
                'grant_type' => 'client_credentials'
            ]);
            
            // Registrar intento de autenticación (sin mostrar credenciales completas)
            error_log("ICD11Ajax - Solicitando token con client_id: " . substr($this->clientId, 0, 10) . "...");
            
            // Realizar solicitud HTTP para obtener token, con varios reintentos
            $maxRetries = 2;
            $attempt = 0;
            $success = false;
            $lastError = null;
            
            while (!$success && $attempt <= $maxRetries) {
                $attempt++;
                
                if ($attempt > 1) {
                    error_log("ICD11Ajax - Reintento $attempt de obtener token de acceso");
                    // Esperar un poco antes de reintentar
                    usleep(500000); // 500ms
                }
                
                try {
                    $response = $this->httpRequest($this->tokenUrl, [
                        'method' => 'POST',
                        'headers' => [
                            'Content-Type' => 'application/x-www-form-urlencoded',
                            'Accept' => 'application/json'
                        ],
                        'data' => $postData
                    ]);
                    
                    // Verificar respuesta
                    if ($response['status'] == 200) {
                        $success = true;
                        break;
                    }
                    
                    $lastError = "HTTP Code: {$response['status']}, Respuesta: {$response['body']}";
                    error_log("ICD11Ajax - Error al obtener token (intento $attempt). $lastError");
                    
                } catch (Exception $e) {
                    $lastError = $e->getMessage();
                    error_log("ICD11Ajax - Excepción al obtener token (intento $attempt): " . $lastError);
                }
            }
            
            if (!$success) {
                throw new Exception("Error al obtener token de acceso después de $attempt intentos. $lastError");
            }
            
            // Decodificar respuesta
            $data = json_decode($response['body'], true);
            if (!isset($data['access_token'])) {
                error_log("ICD11Ajax - Respuesta de token inválida: " . substr($response['body'], 0, 100));
                throw new Exception('Respuesta de token no válida: ' . substr($response['body'], 0, 100));
            }
            
            // Almacenar token y tiempo de expiración
            $this->accessToken = $data['access_token'];
            $this->tokenExpiry = time() + ($data['expires_in'] ?? 3600) - 60; // Restar 60 segundos para asegurarnos
            
            error_log("ICD11Ajax - Token obtenido correctamente (longitud: " . strlen($this->accessToken) . "), válido hasta " . date('Y-m-d H:i:s', $this->tokenExpiry));
            return $this->accessToken;
        } catch (Exception $e) {
            error_log("ICD11Ajax - Error fatal al obtener token: " . $e->getMessage());
            throw new Exception('Error al obtener token de acceso: ' . $e->getMessage());
        }
    }
    
    /**
     * Envía una respuesta JSON al cliente
     * 
     * @param array $data Datos a enviar
     * @param int $statusCode Código de estado HTTP
     */
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Instanciar y procesar la solicitud
$icd11Ajax = new ICD11Ajax();
