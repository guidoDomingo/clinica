<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Core\Database;

/**
 * LocationController
 * 
 * Controlador para manejar ubicaciones (departamentos y ciudades)
 */
class LocationController
{
    /**
     * @var Database La instancia de la base de datos
     */
    private $db;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        //$this->db = new Database();
        $this->db = \Api\Core\Database::getConnection();
    }
    
    /**
     * Obtiene todos los departamentos desde la vista v_departments
     * 
     * @return void
     */
    public function getDepartments()
    {
        try {
            $query = "SELECT * FROM v_departments ORDER BY department_id ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $departments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            Response::success($departments);
        } catch (\Exception $e) {
            Response::error(['message' => 'Error al obtener departamentos', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Obtiene las ciudades para un departamento específico desde la vista v_cities
     * 
     * @return void
     */
    public function getCities()
    {
        // Obtener el ID del departamento desde la solicitud
        $departmentId = isset($_GET['department_id']) ? (int) $_GET['department_id'] : null;
        
        if (!$departmentId) {
            Response::error(['message' => 'Se requiere el ID del departamento'], 400);
            return;
        }
        
        try {
            $query = "SELECT * FROM v_cities WHERE department_id = :department_id ORDER BY city_id ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':department_id', $departmentId, \PDO::PARAM_INT);
            $stmt->execute();
            
            $cities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            Response::success($cities);
        } catch (\Exception $e) {
            Response::error(['message' => 'Error al obtener ciudades', 'error' => $e->getMessage()], 500);
        }
    }
}