<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Models\SysUser;
use Api\Models\SysRegister;
use Api\Models\RhPerson;

/**
 * SysUser Controller
 * 
 * Handles API requests related to system users
 */
class SysUserController
{
    /**
     * @var SysUser The SysUser model instance
     */
    private $userModel;
    
    /**
     * @var SysRegister The SysRegister model instance
     */
    private $registerModel;
    
    /**
     * @var RhPerson The RhPerson model instance
     */
    private $personModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userModel = new SysUser();
        $this->registerModel = new SysRegister();
        $this->personModel = new RhPerson();
    }
    
    /**
     * Get all users
     * 
     * @return void
     */
    public function index()
    {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        
        $users = $this->userModel->paginate($page, $perPage);
        Response::success($users);
    }
    
    /**
     * Get a specific user
     * 
     * @return void
     */
    public function show()
    {
        // Get the user ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'User ID is required'], 400);
            return;
        }
        
        $user = $this->userModel->getUserWithRegistration($id);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        // Get user roles
        $roles = $this->userModel->getUserRoles($id);
        $user['roles'] = $roles;
        
        Response::success($user);
    }
    
    /**
     * Update a user
     * 
     * @return void
     */
    public function update()
    {
        // Get the user ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'User ID is required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($id);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        // Get the request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            Response::error(['message' => 'Invalid request data'], 400);
            return;
        }
        
        // Check if email is being updated and already exists
        if (isset($data['user_email']) && $data['user_email'] !== $user['user_email']) {
            $existingEmail = $this->userModel->getByEmail($data['user_email']);
            if ($existingEmail) {
                Response::error(['message' => 'Email already registered'], 400);
                return;
            }
        }
        
        try {
            $this->userModel->update($id, $data);
            $updatedUser = $this->userModel->getUserWithRegistration($id);
            Response::success($updatedUser);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update user', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Activate or deactivate a user
     * 
     * @return void
     */
    public function toggleActive()
    {
        // Get the user ID from the request
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'User ID is required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($id);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        try {
            $newStatus = !$user['user_is_active'];
            $this->userModel->update($id, ['user_is_active' => $newStatus]);
            $updatedUser = $this->userModel->find($id);
            Response::success([
                'user' => $updatedUser,
                'message' => $newStatus ? 'User activated successfully' : 'User deactivated successfully'
            ]);
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to update user status', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Assign a role to a user
     * 
     * @return void
     */
    public function assignRole()
    {
        // Get the user ID and role ID from the request
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        $roleId = isset($_GET['role_id']) ? $_GET['role_id'] : null;
        
        if (!$userId || !$roleId) {
            Response::error(['message' => 'User ID and Role ID are required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        try {
            $result = $this->userModel->assignRole($userId, $roleId);
            if ($result) {
                $roles = $this->userModel->getUserRoles($userId);
                Response::success([
                    'roles' => $roles,
                    'message' => 'Role assigned successfully'
                ]);
            } else {
                Response::error(['message' => 'Failed to assign role'], 500);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to assign role', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Remove a role from a user
     * 
     * @return void
     */
    public function removeRole()
    {
        // Get the user ID and role ID from the request
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        $roleId = isset($_GET['role_id']) ? $_GET['role_id'] : null;
        
        if (!$userId || !$roleId) {
            Response::error(['message' => 'User ID and Role ID are required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        try {
            $result = $this->userModel->removeRole($userId, $roleId);
            if ($result) {
                $roles = $this->userModel->getUserRoles($userId);
                Response::success([
                    'roles' => $roles,
                    'message' => 'Role removed successfully'
                ]);
            } else {
                Response::error(['message' => 'Failed to remove role'], 500);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to remove role', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Associate a user with a person
     * 
     * @return void
     */
    public function associateWithPerson()
    {
        // Get the user ID and person ID from the request
        $userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        $personId = isset($_GET['person_id']) ? $_GET['person_id'] : null;
        
        if (!$userId || !$personId) {
            Response::error(['message' => 'User ID and Person ID are required'], 400);
            return;
        }
        
        // Check if the user exists
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            Response::error(['message' => 'User not found'], 404);
            return;
        }
        
        // Check if the person exists
        $person = $this->personModel->find($personId);
        
        if (!$person) {
            Response::error(['message' => 'Person not found'], 404);
            return;
        }
        
        try {
            $result = $this->personModel->associateWithUser($personId, $userId);
            if ($result) {
                $updatedPerson = $this->personModel->find($personId);
                Response::success([
                    'person' => $updatedPerson,
                    'message' => 'Person associated with user successfully'
                ]);
            } else {
                Response::error(['message' => 'Failed to associate person with user'], 500);
            }
        } catch (\Exception $e) {
            Response::error(['message' => 'Failed to associate person with user', 'error' => $e->getMessage()], 500);
        }
    }
}