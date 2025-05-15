<?php

namespace Api\Services;

/**
 * Simplified ICD-11 Service
 * Works without Laravel dependencies, using native PHP
 */
class Icd11Service
{
    /**
     * Obtiene información detallada de una enfermedad directamente por su código ICD-11
     *
     * @param string $code Código ICD-11 (por ejemplo, MD12)
     * @return array Información detallada de la enfermedad
     * @throws \Exception si hay un error al obtener los datos
     */    public function getDetailedDiseaseByCode($code)
    {
        try {
            // Verificar que el código no esté vacío
            if (empty($code)) {
                throw new \Exception('El código ICD-11 es requerido');
            }
            
            // Si el código es un array, extraer el valor apropiado
            if (is_array($code)) {
                // Si hay información de depuración
                error_log('WARNING: getDetailedDiseaseByCode recibió un código como array: ' . print_r($code, true));
                
                if (isset($code['code'])) {
                    $code = $code['code']; // Si existe una clave 'code', usar ese valor
                } else {
                    // Buscar la primera cadena no vacía en el array
                    foreach ($code as $value) {
                        if (is_string($value) && !empty($value)) {
                            $code = $value;
                            break;
                        }
                    }
                }
                
                // Verificar que ahora el código sea un string
                if (!is_string($code) || empty($code)) {
                    throw new \Exception('No se pudo determinar un código ICD-11 válido');
                }
            }

            // Inicializar el resultado con información básica
            $result = [
                'code' => $code,
                'title' => '',
            ];
            
            // Intentar obtener datos desde la API (deshabilitado por problemas de conexión)
            $dataFromApi = false;
            
            // Si no podemos obtener datos de la API, usar datos de respaldo
            if (!$dataFromApi) {
                $fallbackData = $this->getFallbackData($code);
                if ($fallbackData) {
                    $result = array_merge($result, $fallbackData);
                    $result['source'] = 'fallback';
                    return $result;
                } else {                    // Si no hay datos de respaldo para este código específico, usar datos genéricos
                    return [
                        'code' => $code,
                        'title' => 'Código ICD-11: ' . $code,
                        'definition' => 'No se encontró información detallada para este código.',
                        'description' => 'No se encontró una descripción detallada para este código en la base de datos local. Consulte la documentación oficial de la CIE-11 para más información.',
                        'source' => 'fallback_generic'
                    ];
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            // En caso de error, devolver un array con la información del error
            throw new \Exception('Error al obtener detalles de la enfermedad: ' . $e->getMessage());
        }
    }
      /**
     * Obtiene datos de respaldo para códigos comunes
     * 
     * @param string|array $code El código ICD-11
     * @return array|null Datos del código o null si no se encuentra en la lista de respaldo
     */
    private function getFallbackData($code)
    {
        // Si el código es un array, extraer el primer valor como string o retornar null
        if (is_array($code)) {
            // Si hay información de depuración
            error_log('WARNING: ICD11Service recibió un código como array: ' . print_r($code, true));
            
            // Intentar obtener el primer valor si existe
            if (count($code) > 0) {
                if (isset($code['code'])) {
                    $code = $code['code']; // Si existe una clave 'code', usar ese valor
                } else {
                    $code = reset($code); // Usar el primer elemento del array
                }
                
                // Verificar que ahora el código sea un string
                if (!is_string($code)) {
                    return null;
                }
            } else {
                return null;
            }
        }
          // Datos de respaldo para algunos códigos comunes
        $fallbackData = [
            'MD12' => [
                'title' => 'Tos',
                'definition' => 'Expulsión súbita y audible de aire desde los pulmones',
                'description' => 'La tos es un mecanismo de defensa natural y un reflejo protector importante que despeja las vías respiratorias altas y bajas por eliminación de las secreciones excesivas, como el moco y las partículas inhaladas. La tos es un síntoma frecuente de la mayoría de los trastornos respiratorios y puede apuntar a la presencia de una afección de las vías respiratorias o del pulmón que puede ser o insignificante o sumamente grave.',
                'inclusion_terms' => ['Tos seca', 'Tos húmeda', 'Tos persistente'],
                'exclusion_terms' => ['Tos con sangre (hemoptisis)'],
                'source' => 'fallback'
            ],            'BA00' => [
                'title' => 'Hipertensión esencial',
                'definition' => 'Presión arterial alta sin causa secundaria identificable',
                'description' => 'Trastorno hipertensivo caracterizado por presión arterial alta persistente sin causa orgánica identificable. La hipertensión esencial es un factor de riesgo importante para enfermedades cerebrovasculares, cardíacas y renales. La presión arterial suele estar por encima de 140/90 mmHg, aunque los criterios diagnósticos específicos pueden variar según las guías clínicas vigentes.',
                'inclusion_terms' => ['Hipertensión arterial', 'Presión arterial alta'],
                'exclusion_terms' => ['Hipertensión secundaria'],
                'source' => 'fallback'
            ],
            '5A11' => [
                'title' => 'Diabetes mellitus tipo 2',
                'definition' => 'Trastorno metabólico caracterizado por resistencia a la insulina',
                'description' => 'La diabetes mellitus tipo 2 es un trastorno metabólico caracterizado por hiperglucemia crónica con alteraciones en el metabolismo de carbohidratos, grasas y proteínas. Es causada principalmente por una combinación de resistencia a la acción de la insulina y una respuesta secretora inadecuada de insulina compensatoria. Suele asociarse con obesidad, especialmente abdominal, y estilo de vida sedentario. Puede permanecer sin diagnosticar durante muchos años hasta que se manifiestan complicaciones.',
                'inclusion_terms' => ['Diabetes del adulto', 'Diabetes no insulinodependiente'],
                'exclusion_terms' => ['Diabetes mellitus tipo 1'],
                'source' => 'fallback'
            ],
            'XN678' => [
                'title' => 'COVID-19',
                'definition' => 'Enfermedad infecciosa causada por el coronavirus SARS-CoV-2',
                'description' => 'Enfermedad causada por el virus SARS-CoV-2, caracterizada por síntomas respiratorios que varían desde leves (similares a un resfriado común) hasta neumonía severa y síndrome de dificultad respiratoria aguda. También puede presentarse con síntomas gastrointestinales, neurológicos y cardiovasculares. La enfermedad puede ser asintomática en algunos casos, mientras que en otros puede progresar rápidamente a fallo multiorgánico y muerte. Los factores de riesgo para enfermedad grave incluyen edad avanzada y comorbilidades como hipertensión, diabetes y enfermedades cardiovasculares.',
                'inclusion_terms' => ['Infección por SARS-CoV-2', 'Enfermedad por coronavirus 2019'],
                'exclusion_terms' => ['Síndrome respiratorio agudo severo', 'Resfriado común'],
                'source' => 'fallback'
            ]
        ];
        
        return isset($fallbackData[$code]) ? $fallbackData[$code] : null;
    }
}
