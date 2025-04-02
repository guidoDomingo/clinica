<?php
namespace Api\Models;

use Api\Core\Model;

/**
 * Patient Model
 * 
 * Represents a patient in the system
 */
class Patient extends Model
{
    /**
     * @var string The table associated with the model
     */
    protected $table = 'pacientes';
    
    /**
     * @var string The primary key column
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'documento',
        'tipo_documento',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'telefono',
        'email',
        'grupo_sanguineo',
        'alergias',
        'antecedentes',
        'estado'
    ];
    
    /**
     * Get patients with pagination
     * 
     * @param int $page The page number
     * @param int $perPage The number of records per page
     * @return array
     */
    public function paginate($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $patients = $this->raw($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ])->fetchAll();
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $totalCount = $this->raw($countSql)->fetch()['total'];
        
        return [
            'data' => $patients,
            'pagination' => [
                'total' => (int) $totalCount,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($totalCount / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalCount)
            ]
        ];
    }
    
    /**
     * Search patients by name or document
     * 
     * @param string $query The search query
     * @return array
     */
    public function search($query)
    {
        $sql = "SELECT * FROM {$this->table} WHERE "
             . "nombres ILIKE :query OR "
             . "apellidos ILIKE :query OR "
             . "documento ILIKE :query "
             . "ORDER BY {$this->primaryKey} DESC";
        
        return $this->raw($sql, ['query' => "%{$query}%"])->fetchAll();
    }
    
    /**
     * Get active patients
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->where('estado', 'ACTIVO');
    }
}