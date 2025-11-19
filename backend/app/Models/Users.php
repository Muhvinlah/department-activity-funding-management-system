<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Users extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = true;
    
    protected $fillable = [
        'full_name',
        'email',
        'role_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function torSubmissions()
    {
        return $this->hasMany(Tor::class, 'user_id');
    }

    public function lpjSubmissions()
    {
        return $this->hasMany(Lpj::class, 'user_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(StatusHist::class, 'user_id');
    }

    public function torApprovals()
    {
        return $this->hasMany(TorApprov::class, 'user_id');
    }

    public function lpjApprovals()
    {
        return $this->hasMany(LpjApprov::class, 'user_id');
    }
}