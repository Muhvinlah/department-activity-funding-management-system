<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lpj extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lpj';
    protected $primaryKey = 'lpj_id';
    public $timestamps = true;
    
    protected $fillable = [
        'activity_result',
        'activity_evaluation',
        'budget_used',
        'status',
        'current_stage',
        'tor_id',
        'user_id'
    ];

    protected $casts = [
        'budget_used' => 'decimal:2',
        'sub_date' => 'datetime',
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

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'lpj_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(StatusHist::class, 'lpj_id');
    }

    public function approvals()
    {
        return $this->hasMany(LpjApprov::class, 'lpj_id');
    }
}