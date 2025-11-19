<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    public $timestamps = false;
    
    protected $fillable = [
        'role_def'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}