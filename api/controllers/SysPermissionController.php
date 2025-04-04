<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Models\SysPermission;

/**
 * SysPermission Controller
 * 
 * Handles API requests related to system permissions
 */
class SysPermissionController
{
    /**
     * @var SysPermission The SysPermission model instance
     */
    private $permissionModel;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->permissionModel = new SysPermission();
    }
    
    /**
     * Get all permissions with their roles
     * 
     * @return void
     */
    public function index()
    {
        $permissions = $this->permissionModel->getAllWithRoles();
        Response::success($permissions);
    }
    
    /**
     * Get a specific permission with its roles
     * 
     * @return void
     */
    public function show()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Permission ID is required'], 400);
            return;
        }
        
        $permission = $this->permissionModel->getWithRoles($id);
        
        if (!$permission) {
            Response::error(['message' => 'Permission not found'], 404);
            return;
        }
        
        Response::success($permission);
    }
    
    /**
     * Create a new permission
     * 
     * @return void
     */
    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['perm_name'])) {
            Response::error(['message' => 'Permission name is required'], 400);
            return;
        }
        
        try {
            $permId = $this->permissionModel->create([
                'perm_name' => $data['perm_name'],
                'perm_description' => $data['perm_description'] ?? null
            ]);
            
            $permission = $this->permissionModel->getWithRoles($permId);
            Response::success($permission, 201);
        } catch (\Exception $e) {
            Response::error(['message' => 'Error creating permission: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Update a permission
     * 
     * @return void
     */
    public function update()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Permission ID is required'], 400);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $this->permissionModel->update($id, [
                'perm_name' => $data['perm_name'] ?? null,
                'perm_description' => $data['perm_description'] ?? null
            ]);
            
            $permission = $this->permissionModel->getWithRoles($id);
            Response::success($permission);
        } catch (\Exception $e) {
            Response::error(['message' => 'Error updating permission: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete a permission
     * 
     * @return void
     */
    public function delete()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            Response::error(['message' => 'Permission ID is required'], 400);
            return;
        }
        
        try {
            $this->permissionModel->delete($id);
            Response::success(['message' => 'Permission deleted successfully']);
        } catch (\Exception $e) {
            Response::error(['message' => 'Error deleting permission: ' . $e->getMessage()], 500);
        }
    }
}