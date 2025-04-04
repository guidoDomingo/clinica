<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Models\SysRole;
use Api\Models\SysPermission;

/**
 * SysRole Controller
 * 
 * Handles API requests related to system roles
 */
class SysRoleController
{
    /**
     * @var SysRole The SysRole model instance
     */
    private $roleModel;
    
    /**
     * @var SysPermission The SysPermission model instance
     */
    private $permissionModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roleModel = new SysRole();
        $this->permissionModel = new SysPermission();
    }
    
    /**
     * Get all roles with their permissions
     * 
     * @return void
     */
    public function index()
    {
        $roles = $this->roleModel->getAllWithPermissions();
        Response::success($roles);
    }
    
    /**
     * Get a specific role with its permissions
     * 
     * @return void
     */
    public function show($data)
    {
        try {
           
            $id = $data['id'];
            
            if (!$id) {
                Response::error(['message' => 'Role ID is required'], 400);
                return;
            }
            
            $role = $this->roleModel->getWithPermissions($id);
            
            if (!$role) {
                Response::error(['message' => 'Role not found'], 404);
                return;
            }
            
            // Process array fields before sending response
            if (isset($role['permission_ids']) && $role['permission_ids'] === '{NULL}') {
                $role['permission_ids'] = [];
            } else if (isset($role['permission_ids'])) {
                $role['permission_ids'] = array_filter(
                    explode(',', trim($role['permission_ids'], '{}'))
                );
            }
            
            if (isset($role['permission_names']) && $role['permission_names'] === '{NULL}') {
                $role['permission_names'] = [];
            } else if (isset($role['permission_names'])) {
                $role['permission_names'] = array_filter(
                    explode(',', trim($role['permission_names'], '{}'))
                );
            }
            
            Response::success($role);
        } catch (\PDOException $e) {
            // Handle database errors with appropriate HTTP status code
            Response::error(['message' => 'Database error occurred'], 500);
        } catch (\Exception $e) {
            Response::error(['message' => 'Error processing request'], 500);
        }
    }
    
    /**
     * Create a new role
     * 
     * @return void
     */
    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['role_name'])) {
            Response::error(['message' => 'Role name is required'], 400);
            return;
        }
        
        try {
            $roleId = $this->roleModel->create([
                'role_name' => $data['role_name'],
                'role_description' => $data['role_description'] ?? null
            ]);
            
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $this->roleModel->assignPermissions($roleId, $data['permissions']);
            }
            
            $role = $this->roleModel->getWithPermissions($roleId);
            Response::success($role, 201);
        } catch (\Exception $e) {
            Response::error(['message' => 'Error creating role: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Update a role
     * 
     * @return void
     */
    public function update()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        
        if (!$id) {
            Response::error(['message' => 'Role ID is required'], 400);
            return;
        }
        
        try {
            $this->roleModel->update($id, [
                'role_name' => $data['role_name'] ?? null,
                'role_description' => $data['role_description'] ?? null
            ]);
            
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $this->roleModel->assignPermissions($id, $data['permissions']);
            }
            
            $role = $this->roleModel->getWithPermissions($id);
            Response::success($role);
        } catch (\Exception $e) {
            Response::error(['message' => 'Error updating role: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete a role
     * 
     * @return void
     */
    public function delete()
    {
        //$id = isset($_GET['id']) ? $_GET['id'] : null;

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        
        if (!$id) {
            Response::error(['message' => 'Role ID is required'], 400);
            return;
        }
        
        try {
            $this->roleModel->delete($id);
            Response::success(['message' => 'Role deleted successfully']);
        } catch (\Exception $e) {
            Response::error(['message' => 'Error deleting role: ' . $e->getMessage()], 500);
        }
    }
}