<?php

namespace Api\Controllers;

use Api\Core\Response;
use Api\Core\Database;
use Api\Services\Icd11Service;

class ICD11Controller 
{
    protected $icd11Service;

    /**
     * Constructor del controlador
     */
    public function __construct()
    {
        $this->icd11Service = new Icd11Service();
    }

    /**
     * Obtiene información detallada de una enfermedad por su código ICD-11
     * 
     * @param string|null $code El código ICD-11
     * @return array Respuesta con la información solicitada
     */    public function getDetailedDiseaseByCode($code = null)
    {
        try {
            // Debug para ver qué tipo de dato se está recibiendo
            error_log('ICD11Controller: tipo de $code: ' . gettype($code) . ', valor: ' . (is_array($code) ? json_encode($code) : $code));
            
            // Asegurarse de que el código es una cadena
            if (is_array($code)) {
                // Si es un array pero tiene un valor 'code', usarlo
                if (isset($code['code'])) {
                    $code = $code['code'];
                } 
                // Si es un array con un solo elemento, usar ese elemento
                elseif (count($code) === 1) {
                    $code = reset($code);
                }
                // Si el array tiene datos de ruta, intentar extraer el código de ahí
                elseif (isset($code['params']) && isset($code['params']['code'])) {
                    $code = $code['params']['code'];
                }
            }
            
            // Obtener el código si no se pasó como parámetro
            if (!$code) {
                $code = isset($_GET['code']) ? $_GET['code'] : null;
            }
            
            // Validar que el código no esté vacío
            if (empty($code)) {
                return Response::json([
                    'success' => false,
                    'message' => 'El código ICD-11 es requerido',
                    'data' => null
                ], 400);
            }

            // Obtener los datos detallados de la enfermedad
            $diseaseData = $this->icd11Service->getDetailedDiseaseByCode($code);            // Si tenemos datos, devolver respuesta de éxito
            if ($diseaseData) {
                // Asegurarse de que devolvemos una cadena como código en la respuesta
                $responseCode = is_array($code) ? (string)json_encode($code) : (string)$code;
                
                return Response::json([
                    'success' => true,
                    'code' => $responseCode,
                    'data' => $diseaseData
                ], 200);
            }

            // Si no hay datos, es un error 404
            return Response::json([
                'success' => false,
                'message' => 'No se encontraron datos para el código: ' . $code,
                'data' => null
            ], 404);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
