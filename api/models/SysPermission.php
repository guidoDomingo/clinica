<?php
namespace Api\Models;

use Api\Core\Model;

/**
 * SysPermission Model
 * 
 * Represents a permission in the system
 */
class SysPermission extends Model
{
    /**
     * @var string The table associated with the model
     */
    protected $table = 'sys_permissions';
    
    /**
     * @var string The primary key column
     */
    protected $primaryKey = 'perm_id';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected $fillable = [
        'perm_name',
        'perm_description'
    ];
    
    /**
     * Get all permissions with the roles that have them
     * 
     * @return array
     */
    public function getAllWithRoles()
    {
        $sql = "SELECT p.*, 
                       array_agg(DISTINCT r.role_id) as role_ids,
                       array_agg(DISTINCT r.role_name) as role_names
                FROM {$this->table} p
                LEFT JOIN sys_role_permissions rp ON p.perm_id = rp.perm_id
                LEFT JOIN sys_roles r ON rp.role_id = r.role_id
                GROUP BY p.perm_id, p.perm_name, p.perm_description
                ORDER BY p.perm_id";
        
        return $this->raw($sql)->fetchAll();
    }
    
    /**
     * Get permission with its roles
     * 
     * @param int $permId
     * @return array|null
     */
    public function getWithRoles($permId)
    {
        $sql = "SELECT p.*, 
                       array_agg(DISTINCT r.role_id) as role_ids,
                       array_agg(DISTINCT r.role_name) as role_names
                FROM {$this->table} p
                LEFT JOIN sys_role_permissions rp ON p.perm_id = rp.perm_id
                LEFT JOIN sys_roles r ON rp.role_id = r.role_id
                WHERE p.perm_id = :permId
                GROUP BY p.perm_id, p.perm_name, p.perm_description";
        
        return $this->raw($sql, ['permId' => $permId])->fetch();
    }
}