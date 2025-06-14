<?php
// Iniciar sesión para acceder a $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../controller/permisos.controller.php";
require_once "../model/permisos.model.php";
require_once "../model/conexion.php";

class AjaxPermisos {
    
    /*=============================================
    OBTENER TODOS LOS ROLES
    =============================================*/
    public function ajaxGetRoles() {
        $permisos = new PermisosModel();
        $roles = $permisos->getAllRoles();
        
        echo json_encode([
            'status' => 'success',
            'data' => $roles
        ]);
    }
    
    /*=============================================
    OBTENER PERMISOS DEL USUARIO ACTUAL
    =============================================*/    public function ajaxGetPermisosUsuario() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ]);
            return;
        }
        
        $permisos = PermisosController::getPermisosUsuarioActual();
        $roles = PermisosController::getRolesUsuarioActual();
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'permisos' => $permisos,
                'roles' => $roles
            ]
        ]);
    }
    
    /*=============================================
    OBTENER TODOS LOS PERMISOS
    =============================================*/
    public function ajaxGetPermisos() {
        $permisos = new PermisosModel();
        $listaPermisos = $permisos->getAllPermisos();
        
        echo json_encode([
            'status' => 'success',
            'data' => $listaPermisos
        ]);
    }
      /*=============================================
    CREAR NUEVO ROL
    =============================================*/
    public function ajaxCrearRol($datos) {
        try {
            $conn = Conexion::conectar();
            $conn->beginTransaction();
            
            // Crear el rol
            $stmt = $conn->prepare("
                INSERT INTO sys_roles (role_name, role_description) 
                VALUES (:role_name, :description)
            ");
            $stmt->bindParam(":role_name", $datos['role_name'], PDO::PARAM_STR);
            $stmt->bindParam(":description", $datos['role_description'], PDO::PARAM_STR);
            $stmt->execute();
            
            $roleId = $conn->lastInsertId();
            
            // Asignar permisos al rol
            if (isset($datos['permisos']) && is_array($datos['permisos'])) {
                $stmtPermisos = $conn->prepare("
                    INSERT INTO sys_role_permissions (role_id, perm_id) 
                    VALUES (:role_id, :permission_id)
                ");
                
                foreach ($datos['permisos'] as $permisoId) {
                    $stmtPermisos->bindParam(":role_id", $roleId, PDO::PARAM_INT);
                    $stmtPermisos->bindParam(":permission_id", $permisoId, PDO::PARAM_INT);
                    $stmtPermisos->execute();
                }
            }
            
            $conn->commit();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Rol creado exitosamente'
            ]);
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al crear rol: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al crear rol: ' . $e->getMessage()
            ]);
        }
    }
      /*=============================================
    CREAR NUEVO PERMISO
    =============================================*/
    public function ajaxCrearPermiso($datos) {
        try {
            $conn = Conexion::conectar();
            
            $stmt = $conn->prepare("
                INSERT INTO sys_permissions (perm_name, perm_description) 
                VALUES (:perm_name, :description)
            ");
            $stmt->bindParam(":perm_name", $datos['perm_name'], PDO::PARAM_STR);
            $stmt->bindParam(":description", $datos['perm_description'], PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Permiso creado exitosamente'
            ]);
            
        } catch (PDOException $e) {
            error_log("Error al crear permiso: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al crear permiso: ' . $e->getMessage()
            ]);
        }
    }
      /*=============================================
    EDITAR ROL
    =============================================*/
    public function ajaxEditarRol($datos) {
        try {
            $conn = Conexion::conectar();
            $conn->beginTransaction();
            
            // Actualizar el rol
            $stmt = $conn->prepare("
                UPDATE sys_roles SET 
                role_name = :role_name, 
                role_description = :description
                WHERE role_id = :role_id
            ");
            $stmt->bindParam(":role_name", $datos['role_name'], PDO::PARAM_STR);
            $stmt->bindParam(":description", $datos['role_description'], PDO::PARAM_STR);
            $stmt->bindParam(":role_id", $datos['role_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            // Eliminar permisos anteriores
            $stmtDelete = $conn->prepare("
                DELETE FROM sys_role_permissions 
                WHERE role_id = :role_id
            ");
            $stmtDelete->bindParam(":role_id", $datos['role_id'], PDO::PARAM_INT);
            $stmtDelete->execute();
            
            // Asignar nuevos permisos
            if (isset($datos['permisos']) && is_array($datos['permisos'])) {
                $stmtPermisos = $conn->prepare("
                    INSERT INTO sys_role_permissions (role_id, perm_id) 
                    VALUES (:role_id, :permission_id)
                ");
                
                foreach ($datos['permisos'] as $permisoId) {
                    $stmtPermisos->bindParam(":role_id", $datos['role_id'], PDO::PARAM_INT);
                    $stmtPermisos->bindParam(":permission_id", $permisoId, PDO::PARAM_INT);
                    $stmtPermisos->execute();
                }
            }
            
            $conn->commit();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Rol actualizado exitosamente'
            ]);
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al editar rol: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al editar rol: ' . $e->getMessage()
            ]);
        }
    }
      /*=============================================
    EDITAR PERMISO
    =============================================*/
    public function ajaxEditarPermiso($datos) {
        try {
            $conn = Conexion::conectar();
            
            $stmt = $conn->prepare("
                UPDATE sys_permissions SET 
                perm_name = :perm_name, 
                perm_description = :description
                WHERE perm_id = :perm_id
            ");
            $stmt->bindParam(":perm_name", $datos['perm_name'], PDO::PARAM_STR);
            $stmt->bindParam(":description", $datos['perm_description'], PDO::PARAM_STR);
            $stmt->bindParam(":perm_id", $datos['perm_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Permiso actualizado exitosamente'
            ]);
            
        } catch (PDOException $e) {
            error_log("Error al editar permiso: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al editar permiso: ' . $e->getMessage()
            ]);
        }
    }
      /*=============================================
    OBTENER DATOS DE UN ROL
    =============================================*/
    public function ajaxGetRol($id) {
        try {
            $conn = Conexion::conectar();
            
            // Obtener datos del rol
            $stmt = $conn->prepare("
                SELECT role_id as id, role_name, role_description as description 
                FROM sys_roles 
                WHERE role_id = :role_id
            ");
            $stmt->bindParam(":role_id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rol) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Rol no encontrado'
                ]);
                return;
            }
            
            // Obtener permisos del rol
            $stmtPermisos = $conn->prepare("
                SELECT perm_id as permission_id 
                FROM sys_role_permissions 
                WHERE role_id = :role_id
            ");
            $stmtPermisos->bindParam(":role_id", $id, PDO::PARAM_INT);
            $stmtPermisos->execute();
            
            $permisos = $stmtPermisos->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'rol' => $rol,
                    'permisos' => $permisos
                ]
            ]);
            
        } catch (PDOException $e) {
            error_log("Error al obtener rol: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al obtener información del rol'
            ]);
        }
    }
      /*=============================================
    OBTENER USUARIOS CON SUS ROLES
    =============================================*/
    public function ajaxGetUsuariosRoles() {
        try {
            $conn = Conexion::conectar();
            
            $stmt = $conn->prepare("
                SELECT u.user_id as id, u.user_email as email,
                GROUP_CONCAT(r.role_name SEPARATOR ', ') as roles
                FROM sys_users u
                LEFT JOIN sys_user_roles ur ON u.user_id = ur.user_id
                LEFT JOIN sys_roles r ON ur.role_id = r.role_id
                GROUP BY u.user_id
                ORDER BY u.user_email ASC
            ");
            $stmt->execute();
            
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'success',
                'data' => $usuarios
            ]);
            
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios con roles: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al obtener usuarios'
            ]);
        }
    }
      /*=============================================
    OBTENER ROLES DE UN USUARIO
    =============================================*/
    public function ajaxGetRolesUsuario($userId) {
        try {
            $conn = Conexion::conectar();
            
            $stmt = $conn->prepare("
                SELECT role_id 
                FROM sys_user_roles 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                'status' => 'success',
                'data' => $roles
            ]);
            
        } catch (PDOException $e) {
            error_log("Error al obtener roles del usuario: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al obtener roles del usuario'
            ]);
        }
    }
      /*=============================================
    ASIGNAR ROLES A USUARIO
    =============================================*/
    public function ajaxAsignarRolesUsuario($datos) {
        try {
            $conn = Conexion::conectar();
            $conn->beginTransaction();
            
            // Eliminar roles anteriores
            $stmtDelete = $conn->prepare("
                DELETE FROM sys_user_roles 
                WHERE user_id = :user_id
            ");
            $stmtDelete->bindParam(":user_id", $datos['user_id'], PDO::PARAM_INT);
            $stmtDelete->execute();
            
            // Asignar nuevos roles
            if (isset($datos['roles']) && is_array($datos['roles'])) {
                $stmtRoles = $conn->prepare("
                    INSERT INTO sys_user_roles (user_id, role_id) 
                    VALUES (:user_id, :role_id)
                ");
                
                foreach ($datos['roles'] as $roleId) {
                    $stmtRoles->bindParam(":user_id", $datos['user_id'], PDO::PARAM_INT);
                    $stmtRoles->bindParam(":role_id", $roleId, PDO::PARAM_INT);
                    $stmtRoles->execute();
                }
            }
            
            $conn->commit();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Roles asignados exitosamente'
            ]);
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al asignar roles: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al asignar roles al usuario'
            ]);
        }
    }
      /*=============================================
    ELIMINAR ROL
    =============================================*/
    public function ajaxEliminarRol($id) {
        try {
            $conn = Conexion::conectar();
            $conn->beginTransaction();
            
            // Verificar si el rol está en uso
            $stmtCheck = $conn->prepare("
                SELECT COUNT(*) FROM sys_user_roles 
                WHERE role_id = :role_id
            ");
            $stmtCheck->bindParam(":role_id", $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ($stmtCheck->fetchColumn() > 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se puede eliminar este rol porque está asignado a uno o más usuarios'
                ]);
                return;
            }
            
            // Eliminar permisos del rol
            $stmtPermisos = $conn->prepare("
                DELETE FROM sys_role_permissions 
                WHERE role_id = :role_id
            ");
            $stmtPermisos->bindParam(":role_id", $id, PDO::PARAM_INT);
            $stmtPermisos->execute();
            
            // Eliminar el rol
            $stmtRol = $conn->prepare("
                DELETE FROM sys_roles 
                WHERE role_id = :role_id
            ");
            $stmtRol->bindParam(":role_id", $id, PDO::PARAM_INT);
            $stmtRol->execute();
            
            $conn->commit();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Rol eliminado exitosamente'
            ]);
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al eliminar rol: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al eliminar el rol'
            ]);
        }
    }
      /*=============================================
    ELIMINAR PERMISO
    =============================================*/
    public function ajaxEliminarPermiso($id) {
        try {
            $conn = Conexion::conectar();
            $conn->beginTransaction();
            
            // Verificar si el permiso está en uso
            $stmtCheck = $conn->prepare("
                SELECT COUNT(*) FROM sys_role_permissions 
                WHERE perm_id = :permission_id
            ");
            $stmtCheck->bindParam(":permission_id", $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ($stmtCheck->fetchColumn() > 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se puede eliminar este permiso porque está asignado a uno o más roles'
                ]);
                return;
            }
            
            // Eliminar el permiso
            $stmtPermiso = $conn->prepare("
                DELETE FROM sys_permissions 
                WHERE perm_id = :permission_id
            ");
            $stmtPermiso->bindParam(":permission_id", $id, PDO::PARAM_INT);
            $stmtPermiso->execute();
            
            $conn->commit();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Permiso eliminado exitosamente'
            ]);
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al eliminar permiso: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al eliminar el permiso'
            ]);
        }
    }
}

/*=============================================
PROCESAR PETICIÓN AJAX
=============================================*/
if (isset($_POST['operacion'])) {
    $ajax = new AjaxPermisos();
    
    switch ($_POST['operacion']) {
        case 'getRoles':
            $ajax->ajaxGetRoles();
            break;
            
        case 'getPermisosUsuario':
            $ajax->ajaxGetPermisosUsuario();
            break;
            
        case 'getPermisos':
            $ajax->ajaxGetPermisos();
            break;
            
        case 'crearRol':
            $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];
            $datos = [
                'role_name' => $_POST['role_name'],
                'role_description' => $_POST['role_description'],
                'permisos' => $permisos
            ];
            $ajax->ajaxCrearRol($datos);
            break;
            
        case 'crearPermiso':
            $datos = [
                'perm_name' => $_POST['perm_name'],
                'perm_description' => $_POST['perm_description']
            ];
            $ajax->ajaxCrearPermiso($datos);
            break;
            
        case 'getRol':
            $ajax->ajaxGetRol($_POST['id']);
            break;
            
        case 'editarRol':
            $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];
            $datos = [
                'role_id' => $_POST['role_id'],
                'role_name' => $_POST['role_name'],
                'role_description' => $_POST['role_description'],
                'permisos' => $permisos
            ];
            $ajax->ajaxEditarRol($datos);
            break;
            
        case 'editarPermiso':
            $datos = [
                'perm_id' => $_POST['perm_id'],
                'perm_name' => $_POST['perm_name'],
                'perm_description' => $_POST['perm_description']
            ];
            $ajax->ajaxEditarPermiso($datos);
            break;
            
        case 'getUsuariosRoles':
            $ajax->ajaxGetUsuariosRoles();
            break;
            
        case 'getRolesUsuario':
            $ajax->ajaxGetRolesUsuario($_POST['user_id']);
            break;
            
        case 'asignarRoles':
            $roles = isset($_POST['roles']) ? $_POST['roles'] : [];
            $datos = [
                'user_id' => $_POST['user_id'],
                'roles' => $roles
            ];
            $ajax->ajaxAsignarRolesUsuario($datos);
            break;
            
        case 'eliminarRol':
            $ajax->ajaxEliminarRol($_POST['id']);
            break;
            
        case 'eliminarPermiso':
            $ajax->ajaxEliminarPermiso($_POST['id']);
            break;
            
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Operación no válida'
            ]);
    }
}
