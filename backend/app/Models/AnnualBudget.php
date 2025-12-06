<?php
// app/Models/AnnualBudget.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualBudget extends Model
{
    use SoftDeletes;

    protected $table = 'annual_budget';
    protected $primaryKey = 'budget_id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'tahun',
        'budget',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
    ];

    public function tors()
    {
        return $this->hasMany(Tor::class, 'budget_id', 'budget_id');
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudget()
    {
        // Calculate used budget from approved LPJs (actual spent money)
        $usedBudget = \App\Models\Lpj::where('status', 'approved_by_head')
            ->whereHas('tor', function ($query) {
                $query->where('budget_id', $this->budget_id);
            })
            ->sum('budget_used');

        return $this->budget - $usedBudget;
    }

    /**
     * Get budget usage percentage
     */
    public function getBudgetUsagePercentage()
    {
        // Calculate used budget from approved LPJs (actual spent money)
        $usedBudget = \App\Models\Lpj::where('status', 'approved_by_head')
            ->whereHas('tor', function ($query) {
                $query->where('budget_id', $this->budget_id);
            })
            ->sum('budget_used');

        if ($this->budget == 0) {
            return 0;
        }

        return ($usedBudget / $this->budget) * 100;
    }
}
