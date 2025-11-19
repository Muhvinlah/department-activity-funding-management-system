<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualBudget extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'annual_budget';
    protected $primaryKey = 'budget_id';
    public $timestamps = true;
    
    protected $fillable = [
        'tahun',
        'budget'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function torSubmissions()
    {
        return $this->hasMany(Tor::class, 'budget_id');
    }
}