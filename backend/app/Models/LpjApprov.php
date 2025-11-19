<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LpjApprov extends Model
{
    use HasFactory;

    protected $table = 'lpj_approv';
    protected $primaryKey = 'approv_id';
    public $timestamps = true;
    
    protected $fillable = [
        'lpj_id',
        'user_id',
        'role_id',
        'status',
        'catatan',
        'action'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public function lpj()
    {
        return $this->belongsTo(Lpj::class, 'lpj_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}