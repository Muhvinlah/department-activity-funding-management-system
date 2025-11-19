<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tor';
    protected $primaryKey = 'tor_id';
    public $timestamps = true;
    
    protected $fillable = [
        'activity_name',
        'activity_background',
        'activity_purpose',
        'participant',
        'start_date',
        'end_date',
        'budget_submitted',
        'pic',
        'status',
        'current_stage',
        'category_id',
        'user_id',
        'budget_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget_submitted' => 'decimal:2',
        'sub_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(ActivityCategory::class, 'category_id');
    }

    public function annualBudget()
    {
        return $this->belongsTo(AnnualBudget::class, 'budget_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'tor_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(StatusHist::class, 'tor_id');
    }

    public function approvals()
    {
        return $this->hasMany(TorApprov::class, 'tor_id');
    }

    public function lpj()
    {
        return $this->hasOne(Lpj::class, 'tor_id');
    }
}