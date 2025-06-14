<?php

class PermisosModel {
    
    private $connection;
    
    public function __construct() {
        $this->connection = Conexion::conectar();
    }
    
    /**
     * Verifica si un usuario tiene un permiso específico
     * 
     * @param int $userId ID del usuario
     * @param string $permiso Nombre del permiso a verificar
     * @return bool True si tiene el permiso, False si no
     */
    public function tienePermiso($userId, $permiso) {
        try {
            // Primero obtenemos los roles del usuario
            $stmt = $this->connection->prepare("
                SELECT role_id FROM sys_user_roles 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($roles)) {
                return false;
            }
            
            // Luego verificamos si alguno de esos roles tiene el permiso
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) FROM sys_role_permissions rp
                JOIN sys_permissions p ON rp.perm_id = p.perm_id
                WHERE rp.role_id IN ($placeholders)
                AND p.perm_name = ?
            ");
            
            // Bind params para los IDs de roles
            $i = 1;
            foreach ($roles as $roleId) {
                $stmt->bindValue($i++, $roleId, PDO::PARAM_INT);
            }
            
            // Bind param para el nombre del permiso
            $stmt->bindValue($i, $permiso, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
            
        } catch (PDOException $e) {
            error_log("Error al verificar permiso: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todos los permisos de un usuario
     * 
     * @param int $userId ID del usuario
     * @return array Lista de permisos
     */
    public function getPermisosByUsuario($userId) {
        try {
            $stmt = $this->connection->prepare("
                SELECT DISTINCT p.perm_name, p.perm_description AS description 
                FROM sys_user_roles ur
                JOIN sys_role_permissions rp ON ur.role_id = rp.role_id
                JOIN sys_permissions p ON rp.perm_id = p.perm_id
                WHERE ur.user_id = :user_id
            ");
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener permisos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene todos los roles de un usuario
     * 
     * @param int $userId ID del usuario
     * @return array Lista de roles
     */
    public function getRolesByUsuario($userId) {
        try {
            $stmt = $this->connection->prepare("
                SELECT r.role_id, r.role_name, r.role_description AS description 
                FROM sys_user_roles ur
                JOIN sys_roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = :user_id
            ");
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener roles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista todos los permisos disponibles en el sistema
     */
    public function getAllPermisos() {
        try {
            $stmt = $this->connection->prepare("
                SELECT perm_id AS id, perm_name, perm_description AS description
                FROM sys_permissions
                ORDER BY perm_name ASC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al listar permisos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista todos los roles disponibles en el sistema
     */
    public function getAllRoles() {
        try {
            $stmt = $this->connection->prepare("
                SELECT role_id AS id, role_name, role_description AS description
                FROM sys_roles
                ORDER BY role_name ASC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al listar roles: " . $e->getMessage());
            return [];
        }
    }
}
