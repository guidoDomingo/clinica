<?php
namespace Api\Models;

use Api\Core\Model;

/**
 * Especialidad Model
 * 
 * Represents a medical specialty in the system
 */
class Especialidad extends Model
{
    /**
     * @var string The table associated with the model
     */
    protected $table = 'especialidades';
    
    /**
     * @var string The primary key column
     */
    protected $primaryKey = 'especialidad_id';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'fecha_creacion'
    ];
    
    /**
     * Get all active specialties
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->where('activo', true);
    }
    
    /**
     * Get specialties for a person
     * 
     * @param int $personId The person ID
     * @return array
     */
    public function getForPerson($personId)
    {
        $sql = "SELECT e.* FROM {$this->table} e "
             . "INNER JOIN persona_especialidad pe ON e.especialidad_id = pe.especialidad_id "
             . "WHERE pe.persona_id = :persona_id AND e.activo = true";
        
        return $this->raw($sql, ['persona_id' => $personId])->fetchAll();
    }
    
    /**
     * Assign specialties to a person
     * 
     * @param int $personId The person ID
     * @param array $especialidadesIds Array of specialty IDs
     * @return bool
     */
    public function assignToPerson($personId, $especialidadesIds)
    {
        try {
            // First remove all existing specialties for this person
            $this->raw(
                "DELETE FROM persona_especialidad WHERE persona_id = :persona_id",
                ['persona_id' => $personId]
            );
            
            // Then add the new ones
            if (!empty($especialidadesIds)) {
                foreach ($especialidadesIds as $especialidadId) {
                    $this->raw(
                        "INSERT INTO persona_especialidad (persona_id, especialidad_id) VALUES (:persona_id, :especialidad_id)",
                        [
                            'persona_id' => $personId,
                            'especialidad_id' => $especialidadId
                        ]
                    );
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}