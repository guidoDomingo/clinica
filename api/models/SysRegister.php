<?php
namespace Api\Models;

use Api\Core\Model;

/**
 * SysRegister Model
 * 
 * Represents a user registration in the system
 */
class SysRegister extends Model
{
    /**
     * @var string The table associated with the model
     */
    protected $table = 'sys_register';
    
    /**
     * @var string The primary key column
     */
    protected $primaryKey = 'reg_id';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected $fillable = [
        'reg_document',
        'reg_name',
        'reg_lastname',
        'reg_email',
        'reg_phone',
        'reg_bdate',
        'reg_activation'
    ];
    
    /**
     * Get registrations with pagination
     * 
     * @param int $page The page number
     * @param int $perPage The number of records per page
     * @return array
     */
    public function paginate($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $registrations = $this->raw($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ])->fetchAll();
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $totalCount = $this->raw($countSql)->fetch()['total'];
        
        return [
            'data' => $registrations,
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
     * Search registrations by name, lastname or document
     * 
     * @param string $query The search query
     * @return array
     */
    public function search($query)
    {
        $sql = "SELECT * FROM {$this->table} WHERE "
             . "reg_name ILIKE :query OR "
             . "reg_lastname ILIKE :query OR "
             . "reg_document ILIKE :query "
             . "ORDER BY {$this->primaryKey} DESC";
        
        return $this->raw($sql, ['query' => "%{$query}%"])->fetchAll();
    }
    
    /**
     * Get user by email
     * 
     * @param string $email The user email
     * @return array|null
     */
    public function getByEmail($email)
    {
        return $this->raw(
            "SELECT * FROM {$this->table} WHERE reg_email = :email",
            ['email' => $email]
        )->fetch();
    }
    
    /**
     * Get user by document
     * 
     * @param string $document The user document
     * @return array|null
     */
    public function getByDocument($document)
    {
        return $this->raw(
            "SELECT * FROM {$this->table} WHERE reg_document = :document",
            ['document' => $document]
        )->fetch();
    }
}