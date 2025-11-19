<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attachment';
    protected $primaryKey = 'attach_id';
    public $timestamps = true;
    
    protected $fillable = [
        'file_path',
        'tor_id',
        'lpj_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function tor()
    {
        return $this->belongsTo(Tor::class, 'tor_id');
    }

    public function lpj()
    {
        return $this->belongsTo(Lpj::class, 'lpj_id');
    }
}