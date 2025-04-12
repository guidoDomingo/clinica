<?php
namespace Api\Controllers;

use Api\Core\Logger;
use Api\Core\Response;
use Api\Models\Especialidad;

/**
 * Especialidad Controller
 * 
 * Handles API requests related to medical specialties
 */
class EspecialidadController
{
    /**
     * @var Especialidad The Especialidad model instance
     */
    private $especialidadModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->especialidadModel = new Especialidad();
    }
    
    /**
     * Get all specialties
     * 
     * @return void
     */
    public function index()
    {
        $especialidades = $this->especialidadModel->getActive();
        Response::success($especialidades);
    }
    
    /**
     * Get specialties for a person
     * 
     * @return void
     */
    public function getForPerson()
    {
        // Get the person ID from the request
        $personId = isset($_GET['person_id']) ? $_GET['person_id'] : null;
        
        if (!$personId) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        $especialidades = $this->especialidadModel->getForPerson($personId);
        Response::success($especialidades);
    }
    
    /**
     * Assign specialties to a person
     * 
     * @return void
     */
    public function assignToPerson()
    {
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Validate required fields
        if (!isset($data['person_id']) || empty($data['person_id'])) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        // Especialidades can be empty array (to remove all)
        if (!isset($data['especialidades'])) {
            $data['especialidades'] = [];
        }
        
        try {
            $result = $this->especialidadModel->assignToPerson($data['person_id'], $data['especialidades']);
            
            if ($result) {
                Response::success(['message' => 'Specialties assigned successfully']);
            } else {
                Response::error(['message' => 'Failed to assign specialties'], 500);
            }
        } catch (\Exception $e) {
            Logger::error('Error assigning specialties', ['error' => $e->getMessage()]);
            Response::error(['message' => 'Failed to assign specialties', 'error' => $e->getMessage()], 500);
        }
    }
}