<?php
/**
 * ICD-11 Local Service (DESACTIVADO)
 * 
 * Este servicio ha sido desactivado. Todas las solicitudes deben usar la API oficial.
 * Este archivo ahora simplemente rechaza las solicitudes con un mensaje claro.
 */

// Establecer encabezados para JSON
header('Content-Type: application/json');

// Rechazar todas las solicitudes
echo json_encode([
    'success' => false,
    'message' => 'Este servicio local ha sido desactivado. Todas las solicitudes deben usar la API oficial de ICD-11.',
    'api_required' => true
]);

/**
 * Busca datos locales por código ICD-11
 * 
 * @param string $code Código a buscar
 * @return array Datos del código
 */
function searchByCode($code) {
    $fallbackData = getFallbackCodeData($code);
    
    return [
        'fallback' => true,
        'local_service' => true,
        'destinationEntities' => [
            [
                'theCode' => $code,
                'title' => $fallbackData['title'] ?? 'Código ICD: ' . $code,
                'description' => $fallbackData['description'] ?? 'No hay descripción disponible localmente para este código',
                'isFallback' => true
            ]
        ]
    ];
}

/**
 * Busca términos en los datos locales
 * 
 * @param string $term Término a buscar
 * @return array Resultados de la búsqueda
 */
function searchByTerm($term) {
    $term = strtolower($term);
    
    // Términos comunes en español
    $commonTerms = [
        'diabetes' => [
            ['theCode' => '5A11', 'title' => 'Diabetes mellitus tipo 2'],
            ['theCode' => '5A10', 'title' => 'Diabetes mellitus tipo 1']
        ],
        'tos' => [
            ['theCode' => 'MD12', 'title' => 'Tos'],
            ['theCode' => 'CA81', 'title' => 'Tos crónica']
        ],
        'covid' => [
            ['theCode' => 'XN678', 'title' => 'COVID-19'],
            ['theCode' => 'CA401', 'title' => 'Secuelas de COVID-19']
        ],
        'hipertensión' => [
            ['theCode' => 'BA00', 'title' => 'Hipertensión esencial'],
            ['theCode' => 'BA01', 'title' => 'Hipertensión secundaria']
        ],
        'hipertension' => [
            ['theCode' => 'BA00', 'title' => 'Hipertensión esencial'],
            ['theCode' => 'BA01', 'title' => 'Hipertensión secundaria']
        ],
        'enfermedad cardíaca' => [
            ['theCode' => 'BA40', 'title' => 'Insuficiencia cardíaca'],
            ['theCode' => 'BA6Z', 'title' => 'Enfermedad cardíaca isquémica, sin otra especificación']
        ],
        'enfermedad cardiaca' => [
            ['theCode' => 'BA40', 'title' => 'Insuficiencia cardíaca'],
            ['theCode' => 'BA6Z', 'title' => 'Enfermedad cardíaca isquémica, sin otra especificación']
        ],
        'dolor' => [
            ['theCode' => 'MG30', 'title' => 'Dolor abdominal'],
            ['theCode' => 'MG31', 'title' => 'Dolor en el pecho']
        ],
        'fractura' => [
            ['theCode' => 'NC60', 'title' => 'Fractura del fémur'],
            ['theCode' => 'NC31', 'title' => 'Fractura del antebrazo']
        ],
        'dermatitis' => [
            ['theCode' => 'EA81', 'title' => 'Dermatitis atópica'],
            ['theCode' => 'EA85', 'title' => 'Dermatitis de contacto']
        ],
        'infección' => [
            ['theCode' => '1A01', 'title' => 'Infección por salmonela'],
            ['theCode' => '1A20', 'title' => 'Infección por estreptococos']
        ],
        'asma' => [
            ['theCode' => 'CA23', 'title' => 'Asma'],
            ['theCode' => 'CA19', 'title' => 'Bronquitis']
        ]
    ];
    
    // Buscar coincidencias aproximadas
    foreach ($commonTerms as $key => $results) {
        if (strpos($key, $term) !== false || strpos($term, $key) !== false) {
            // Marcar como datos de respaldo
            foreach ($results as &$result) {
                $result['isFallback'] = true;
            }
            return [
                'fallback' => true,
                'local_service' => true,
                'destinationEntities' => $results
            ];
        }
    }
    
    // Si no hay coincidencias específicas, devolver mensaje
    return [
        'fallback' => true,
        'local_service' => true,
        'destinationEntities' => [
            [
                'theCode' => '----',
                'title' => 'No se encontraron coincidencias para: ' . $term,
                'description' => 'Busque otro término o consulte con el soporte técnico para habilitar la extensión cURL',
                'isFallback' => true
            ]
        ]
    ];
}

/**
 * Obtiene detalles de una entidad por su código
 * 
 * @param string $code Código ICD-11
 * @return array Detalles de la entidad
 */
function getEntityDetails($code) {
    $fallback = getFallbackCodeData($code);
    if ($fallback) {
        return array_merge(['fallback' => true, 'code' => $code, 'local_service' => true], $fallback);
    } else {
        return [
            'fallback' => true,
            'local_service' => true,
            'code' => $code,
            'title' => 'Código ICD: ' . $code,
            'description' => 'No hay información detallada disponible localmente para este código'
        ];
    }
}

/**
 * Proporciona datos de respaldo para códigos comunes
 * 
 * @param string $code El código a buscar
 * @return array|null Datos de respaldo o null si no se encuentra
 */
function getFallbackCodeData($code) {
    // Datos de respaldo para códigos comunes
    $fallbackData = [
        'MD12' => [
            'title' => 'Tos',
            'definition' => 'Expulsión súbita y audible de aire desde los pulmones',
            'description' => 'La tos es un mecanismo de defensa natural y un reflejo protector importante que despeja las vías respiratorias'
        ],
        '1A31' => [
            'title' => 'Enfermedad por virus Zika',
            'definition' => 'Enfermedad causada por infección con el virus del Zika',
            'description' => 'Enfermedad viral transmitida por mosquitos caracterizada por erupciones cutáneas, conjuntivitis, fiebre y dolor articular'
        ],
        'BA00' => [
            'title' => 'Hipertensión esencial',
            'definition' => 'Presión arterial alta sin causa secundaria identificable',
            'description' => 'Trastorno hipertensivo caracterizado por presión arterial alta persistente sin causa orgánica identificable'
        ],
        'BA01' => [
            'title' => 'Hipertensión secundaria',
            'definition' => 'Hipertensión como manifestación de otra condición',
            'description' => 'Presión arterial elevada causada por otra condición médica subyacente como enfermedad renal'
        ],
        '5A11' => [
            'title' => 'Diabetes mellitus tipo 2',
            'definition' => 'Trastorno metabólico caracterizado por resistencia a la insulina',
            'description' => 'Trastorno metabólico caracterizado por hiperglucemia crónica con alteraciones en el metabolismo de carbohidratos'
        ],
        '5A10' => [
            'title' => 'Diabetes mellitus tipo 1',
            'definition' => 'Enfermedad autoinmune que causa destrucción de células beta pancreáticas',
            'description' => 'Forma de diabetes que resulta de la destrucción autoinmune de las células beta productoras de insulina'
        ],
        'XN678' => [
            'title' => 'COVID-19',
            'definition' => 'Enfermedad infecciosa causada por el coronavirus SARS-CoV-2',
            'description' => 'Enfermedad causada por el virus SARS-CoV-2, caracterizada por síntomas respiratorios'
        ],
        'CA81' => [
            'title' => 'Tos crónica',
            'definition' => 'Tos persistente que dura 8 semanas o más',
            'description' => 'Tos que persiste durante un período prolongado, a menudo asociada con condiciones subyacentes'
        ],
        'CA401' => [
            'title' => 'Secuelas de COVID-19',
            'definition' => 'Efectos persistentes después de infección por SARS-CoV-2',
            'description' => 'Síntomas o condiciones que persisten o aparecen después de la fase aguda de la infección por COVID-19'
        ],
        'MG30' => [
            'title' => 'Dolor abdominal',
            'definition' => 'Dolor percibido en la región abdominal',
            'description' => 'Sensación dolorosa en el abdomen que puede ser aguda o crónica y estar asociada con diversas causas'
        ],
        'MG31' => [
            'title' => 'Dolor en el pecho',
            'definition' => 'Dolor o molestia percibida en la región torácica',
            'description' => 'Sensación dolorosa en el pecho que puede irradiarse y tener diversas causas, desde musculoesqueléticas hasta cardíacas'
        ]
    ];
    
    return isset($fallbackData[$code]) ? $fallbackData[$code] : null;
}
