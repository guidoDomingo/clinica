<?php
namespace Api\Controllers;

use Api\Core\Logger;
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
        
        // Ensure boolean fields are properly handled with strict type casting
        // Convert empty strings to null first to avoid PostgreSQL error with empty strings in boolean fields
        $data['is_active'] = isset($data['is_active']) ? (is_string($data['is_active']) && $data['is_active'] === '' ? true : filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN)) : true;
        $data['is_minor'] = isset($data['is_minor']) ? (is_string($data['is_minor']) && $data['is_minor'] === '' ? false : filter_var($data['is_minor'], FILTER_VALIDATE_BOOLEAN)) : false;
        
        // Ensure guardian fields are properly handled
        // If is_minor is false, guardian fields should be null
        if (!$data['is_minor']) {
            $data['guardian_name'] = null;
            $data['guardian_document'] = null;
        } else {
            // If is_minor is true but guardian fields are empty strings, set them to null
            $data['guardian_name'] = (isset($data['guardian_name']) && $data['guardian_name'] !== '') ? $data['guardian_name'] : null;
            $data['guardian_document'] = (isset($data['guardian_document']) && $data['guardian_document'] !== '') ? $data['guardian_document'] : null;
        }
        
        // Ensure guardian fields are properly handled
        // If is_minor is false, guardian fields should be null
        if (!$data['is_minor']) {
            $data['guardian_name'] = null;
            $data['guardian_document'] = null;
        } else {
            // If is_minor is true but guardian fields are empty strings, set them to null
            $data['guardian_name'] = (isset($data['guardian_name']) && $data['guardian_name'] !== '') ? $data['guardian_name'] : null;
            $data['guardian_document'] = (isset($data['guardian_document']) && $data['guardian_document'] !== '') ? $data['guardian_document'] : null;
        }

        Logger::info('Creating person', $data);
        
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
        
        // Ensure boolean fields are properly handled with strict type casting for updates
        if (isset($data['is_active'])) {
            $data['is_active'] = is_string($data['is_active']) && $data['is_active'] === '' ? true : filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }
        
        if (isset($data['is_minor'])) {
            $data['is_minor'] = is_string($data['is_minor']) && $data['is_minor'] === '' ? false : filter_var($data['is_minor'], FILTER_VALIDATE_BOOLEAN);
        }
        
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
     * Upload a profile photo for a person
     * 
     * @return void
     */
    public function uploadProfilePhoto()
    {
        // Get the person ID from the request
        $personId = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$personId) {
            Response::error(['message' => 'Person ID is required'], 400);
            return;
        }
        
        // Check if the person exists
        $person = $this->personModel->find($personId);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            Response::error(['message' => 'No file uploaded or upload error'], 400);
            return;
        }
        
        $file = $_FILES['profile_photo'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            Response::error(['message' => 'Invalid file type. Only JPG, PNG and GIF are allowed'], 400);
            return;
        }
        
        // Validate file size (max 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            Response::error(['message' => 'File too large. Maximum size is 2MB'], 400);
            return;
        }
        
        try {
            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../view/uploads/profile/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'person_' . $personId . '_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Update person profile photo in database
                $this->personModel->update($personId, ['profile_photo' => $filename]);
                
                // Update last modified timestamp
                $this->personModel->update($personId, ['last_modified_at' => date('Y-m-d H:i:s')]);
                
                Response::success([
                    'message' => 'Profile photo uploaded successfully',
                    'data' => [
                        'photo_url' => 'view/uploads/profile/' . $filename
                    ]
                ]);
            } else {
                Response::error(['message' => 'Failed to move uploaded file'], 500);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to upload profile photo', 'error' => $e->getMessage()], 500);
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