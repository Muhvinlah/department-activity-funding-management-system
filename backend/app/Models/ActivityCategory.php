<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'activity_category';
    protected $primaryKey = 'category_id';
    public $timestamps = true;
    
    protected $fillable = [
        'category_def'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function torSubmissions()
    {
        return $this->hasMany(Tor::class, 'category_id');
    }
}