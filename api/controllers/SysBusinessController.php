<?php
namespace Api\Controllers;

use Api\Core\Response;
use Api\Core\Database;
use Api\Core\Logger;

/**
 * SysBusiness Controller
 * 
 * Maneja las solicitudes relacionadas con las empresas del sistema
 */
class SysBusinessController
{
    /**
     * @var PDO Instancia de conexión a la base de datos
     */
    private $db;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    /**
     * Obtiene todas las empresas activas
     * 
     * @return void
     */
    public function index()
    {
        try {
            $query = "SELECT 
                        business_id,
                        business_name,
                        business_ruc,
                        business_email,
                        business_phone,
                        business_address,
                        business_city_id,
                        business_department_id
                      FROM sys_business 
                      WHERE business_is_active = true
                      ORDER BY business_name ASC";
                      
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $businesses = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            Response::success($businesses);
        } catch (\Exception $e) {
            Logger::error('Error al obtener empresas: ' . $e->getMessage(), $e->getTrace());
            Response::error(['message' => 'Error al obtener empresas'], 500);
        }
    }
    
    /**
     * Obtiene una empresa específica
     * 
     * @return void
     */
    public function show()
    {
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$id) {
                Response::error(['message' => 'ID de empresa requerido'], 400);
                return;
            }
            
            $query = "SELECT 
                        business_id,
                        business_name,
                        business_ruc,
                        business_email,
                        business_phone,
                        business_address,
                        business_city_id,
                        business_department_id
                      FROM sys_business 
                      WHERE business_id = :id";
                      
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $business = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$business) {
                Response::error(['message' => 'Empresa no encontrada'], 404);
                return;
            }
            
            Response::success($business);
        } catch (\Exception $e) {
            Logger::error('Error al obtener empresa: ' . $e->getMessage(), $e->getTrace());
            Response::error(['message' => 'Error al obtener empresa'], 500);
        }
    }
    
    /**
     * Obtiene la información de doctor/empresa para una persona específica
     * 
     * @return void
     */
    public function getDoctorInfo()
    {
        try {
            $personId = isset($_GET['person_id']) ? (int)$_GET['person_id'] : null;
            
            if (!$personId) {
                Response::error(['message' => 'ID de persona requerido'], 400);
                return;
            }
            
            $query = "SELECT 
                        doctor_id,
                        person_id,
                        business_id
                      FROM rh_doctors 
                      WHERE person_id = :person_id";
                      
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':person_id', $personId, \PDO::PARAM_INT);
            $stmt->execute();
            
            $doctor = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$doctor) {
                Response::success(null); // No hay información de doctor
                return;
            }
            
            Response::success($doctor);
        } catch (\Exception $e) {
            Logger::error('Error al obtener información de doctor: ' . $e->getMessage(), $e->getTrace());
            Response::error(['message' => 'Error al obtener información de doctor'], 500);
        }
    }

    /**
     * Obtiene la lista de todos los médicos con su información de persona asociada
     * 
     * @return void
     */
    public function getDoctors()
    {
        try {
            $query = "SELECT 
                        d.doctor_id,
                        d.person_id,
                        d.business_id,
                        p.first_name,
                        p.last_name,
                        p.document_number,
                        b.business_name
                      FROM rh_doctors d
                      INNER JOIN rh_person p ON d.person_id = p.person_id
                      LEFT JOIN sys_business b ON d.business_id = b.business_id
                      ORDER BY p.last_name, p.first_name";
                      
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $doctors = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            Response::success($doctors);
        } catch (\Exception $e) {
            Logger::error('Error al obtener lista de médicos: ' . $e->getMessage(), $e->getTrace());
            Response::error(['message' => 'Error al obtener lista de médicos'], 500);
        }
    }
}