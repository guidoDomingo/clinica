<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Models\Patient;

/**
 * Patient Controller
 * 
 * Handles API requests related to patients
 */
class PatientController
{
    /**
     * @var Patient The Patient model instance
     */
    private $patientModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patientModel = new Patient();
    }
    
    /**
     * Get all patients
     * 
     * @return void
     */
    public function index()
    {
        $patients = $this->patientModel->all();
        Response::success($patients);
    }
    
    /**
     * Get a specific patient
     * 
     * @return void
     */
    public function show()
    {
        // Get the patient ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Patient ID is required'], 400);
            return;
        }
        
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            Response::error(['message' => 'Patient not found'], 404);
            return;
        }
        
        Response::success($patient);
    }
    
    /**
     * Create a new patient
     * 
     * @return void
     */
    public function store()
    {
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Validate required fields
        $requiredFields = ['nombres', 'apellidos', 'fecha_nacimiento', 'documento'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                Response::error(['message' => "Field '{$field}' is required"], 400);
                return;
            }
        }
        
        try {
            $id = $this->patientModel->create($data);
            $patient = $this->patientModel->find($id);
            Response::success($patient, 201);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to create patient', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update a patient
     * 
     * @return void
     */
    public function update()
    {
        // Get the patient ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Patient ID is required'], 400);
            return;
        }
        
        // Check if the patient exists
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            Response::error(['message' => 'Patient not found'], 404);
            return;
        }
        
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        try {
            $this->patientModel->update($id, $data);
            $updatedPatient = $this->patientModel->find($id);
            Response::success($updatedPatient);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update patient', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete a patient
     * 
     * @return void
     */
    public function destroy()
    {
        // Get the patient ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Patient ID is required'], 400);
            return;
        }
        
        // Check if the patient exists
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            Response::error(['message' => 'Patient not found'], 404);
            return;
        }
        
        try {
            $this->patientModel->delete($id);
            Response::success(['message' => 'Patient deleted successfully']);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to delete patient', 'error' => $e->getMessage()], 500);
        }
    }
}