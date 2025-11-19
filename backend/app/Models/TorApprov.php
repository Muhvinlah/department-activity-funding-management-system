<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorApprov extends Model
{
    use HasFactory;

    protected $table = 'tor_approv';
    protected $primaryKey = 'approv_id';
    public $timestamps = true;
    
    protected $fillable = [
        'tor_id',
        'user_id',
        'role_id',
        'status',
        'catatan',
        'action'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public function tor()
    {
        return $this->belongsTo(Tor::class, 'tor_id');
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