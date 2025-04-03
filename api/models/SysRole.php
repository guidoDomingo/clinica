<?php
namespace Api\Models;

use Api\Core\Model;
use Api\Core\Database;

/**
 * SysRole Model
 * 
 * Represents a role in the system
 */
class SysRole extends Model
{
    /**
     * @var string The table associated with the model
     */
    protected $table = 'sys_roles';
    
    /**
     * @var string The primary key column
     */
    protected $primaryKey = 'role_id';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected $fillable = [
        'role_name',
        'role_description'
    ];
    
    /**
     * Get all roles with their permissions
     * 
     * @return array
     */
    public function getAllWithPermissions()
    {
        $sql = "SELECT r.*, 
                       array_agg(DISTINCT p.perm_id) as permission_ids,
                       array_agg(DISTINCT p.perm_name) as permission_names
                FROM {$this->table} r
                LEFT JOIN sys_role_permissions rp ON r.role_id = rp.role_id
                LEFT JOIN sys_permissions p ON rp.perm_id = p.perm_id
                GROUP BY r.role_id, r.role_name, r.role_description
                ORDER BY r.role_id";
        
        return $this->raw($sql)->fetchAll();
    }
    
    /**
     * Get role with its permissions
     * 
     * @param int $roleId
     * @return array|null
     */
    public function getWithPermissions($roleId)
    {
        $sql = "SELECT r.*, 
                       array_agg(DISTINCT p.perm_id) as permission_ids,
                       array_agg(DISTINCT p.perm_name) as permission_names
                FROM {$this->table} r
                LEFT JOIN sys_role_permissions rp ON r.role_id = rp.role_id
                LEFT JOIN sys_permissions p ON rp.perm_id = p.perm_id
                WHERE r.role_id = :roleId
                GROUP BY r.role_id, r.role_name, r.role_description";
        
        return $this->raw($sql, ['roleId' => $roleId])->fetch();
    }
    
    /**
     * Assign permissions to a role
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function assignPermissions($roleId, array $permissionIds)
    {
        // Start transaction
        Database::beginTransaction();
        
        try {
            // Remove existing permissions
            $this->raw("DELETE FROM sys_role_permissions WHERE role_id = :roleId", 
                ['roleId' => $roleId]);
            
            // Insert new permissions
            foreach ($permissionIds as $permId) {
                $this->raw("INSERT INTO sys_role_permissions (role_id, perm_id) VALUES (:roleId, :permId)",
                    ['roleId' => $roleId, 'permId' => $permId]);
            }
            
            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}