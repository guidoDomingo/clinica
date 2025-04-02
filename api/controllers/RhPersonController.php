<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Models\RhPerson;

/**
 * RhPerson Controller
 * 
 * Handles API requests related to persons
 */
class RhPersonController
{
    /**
     * @var RhPerson The RhPerson model instance
     */
    private $personModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->personModel = new RhPerson();
    }
    
    /**
     * Get all persons
     * 
     * @return void
     */
    public function index()
    {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        
        $persons = $this->personModel->paginate($page, $perPage);
        Response::success($persons);
    }
    
    /**
     * Get a specific person
     * 
     * @return void
     */
    public function show()
    {
        // Get the person ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        $person = $this->personModel->find($id);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        Response::success($person);
    }
    
    /**
     * Create a new person
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
        $requiredFields = ['document_number', 'first_name', 'last_name', 'birth_date'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                Response::error(['message' => "Field '{$field}' is required"], 400);
                return;
            }
        }
        
        // Check if document already exists
        $existingDocument = $this->personModel->getByDocument($data['document_number']);
        if ($existingDocument) {
            Response::error(['message' => 'Document number already registered'], 400);
            return;
        }
        
        // Check if email already exists (if provided)
        if (isset($data['email']) && !empty($data['email'])) {
            $existingEmail = $this->personModel->getByEmail($data['email']);
            if ($existingEmail) {
                Response::error(['message' => 'Email already registered'], 400);
                return;
            }
        }
        
        // Set additional fields
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['is_active'] = true;
        
        try {
            $id = $this->personModel->create($data);
            $person = $this->personModel->find($id);
            Response::success($person, 201);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to create person', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update a person
     * 
     * @return void
     */
    public function update()
    {
        // Get the person ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        // Check if the person exists
        $person = $this->personModel->find($id);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Check if document is being updated and already exists
        if (isset($data['document_number']) && $data['document_number'] !== $person['document_number']) {
            $existingDocument = $this->personModel->getByDocument($data['document_number']);
            if ($existingDocument) {
                Response::error(['message' => 'Document number already registered'], 400);
                return;
            }
        }
        
        // Check if email is being updated and already exists
        if (isset($data['email']) && !empty($data['email']) && $data['email'] !== $person['email']) {
            $existingEmail = $this->personModel->getByEmail($data['email']);
            if ($existingEmail) {
                Response::error(['message' => 'Email already registered'], 400);
                return;
            }
        }
        
        // Set additional fields
        $data['last_modified_at'] = date('Y-m-d H:i:s');
        
        try {
            $this->personModel->update($id, $data);
            $updatedPerson = $this->personModel->find($id);
            Response::success($updatedPerson);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update person', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete a person
     * 
     * @return void
     */
    public function destroy()
    {
        // Get the person ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        // Check if the person exists
        $person = $this->personModel->find($id);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        try {
            $this->personModel->delete($id);
            Response::success(['message' => 'Person deleted successfully']);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to delete person', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Search persons by name, lastname or document
     * 
     * @return void
     */
    public function search()
    {
        // Get the search query from the request
        $query = isset($_GET['q']) ? $_GET['q'] : null;
        
        if (!$query) {
            Response::error(['message' => 'Search query is required'], 400);
            return;
        }
        
        $persons = $this->personModel->search($query);
        Response::success($persons);
    }
    
    /**
     * Get active persons
     * 
     * @return void
     */
    public function getActive()
    {
        $persons = $this->personModel->getActive();
        Response::success($persons);
    }
}