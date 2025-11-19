<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusHist extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'status_hist';
    protected $primaryKey = 'hist_id';
    public $timestamps = true;
    
    protected $fillable = [
        'status',
        'catatan',
        'user_id',
        'tor_id',
        'lpj_id'
    ];

    protected $casts = [
        'timestamp_aksi' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tor()
    {
        return $this->belongsTo(Tor::class, 'tor_id');
    }

    public function lpj()
    {
        return $this->belongsTo(Lpj::class, 'lpj_id');
    }
}