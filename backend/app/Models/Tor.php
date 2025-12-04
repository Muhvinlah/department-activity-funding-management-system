<?php
// app/Models/Tor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 */
class Tor extends Model
{
    use SoftDeletes;

    protected $table = 'tor';
    protected $primaryKey = 'tor_id';

    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';
    public $timestamps = false;

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
        'budget_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'updated_at' => 'datetime',
        'budget_submitted' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(ActivityCategory::class, 'category_id', 'category_id');
    }

    public function annualBudget()
    {
        return $this->belongsTo(AnnualBudget::class, 'budget_id', 'budget_id');
    }

    public function lpj()
    {
        return $this->hasOne(Lpj::class, 'tor_id', 'tor_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'tor_id', 'tor_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(StatusHist::class, 'tor_id', 'tor_id');
    }

    public function approvals()
    {
        return $this->hasMany(TorApprov::class, 'tor_id', 'tor_id');
    }

    // Submit TOR
    public function submit($userId)
    {
        $this->status = 'submitted';
        $this->current_stage = 'submitted';
        $this->save();
        $this->addStatusHistory('submitted', 'TOR submitted for approval', $userId);
    }

    // Add status history
    public function addStatusHistory($status, $catatan = null, $userId = null)
    {
        StatusHist::create([
            'tor_id' => $this->tor_id,
            'user_id' => $userId,
            'status' => $status,
            'catatan' => $catatan,
        ]);
    }

    // Add approval record
    public function addApproval($userId, $roleId, $action, $catatan = null)
    {
        TorApprov::create([
            'tor_id' => $this->tor_id,
            'user_id' => $userId,
            'role_id' => $roleId,
            'status' => $this->status,
            'action' => $action,
            'catatan' => $catatan,
        ]);
    }

    // Approve by secretary
    public function approveBySecretary($userId, $roleId, $catatan = null)
    {
        $this->status = 'reviewed_by_secretary';
        $this->current_stage = 'reviewed_by_secretary';
        $this->save();

        $this->addStatusHistory('reviewed_by_secretary', $catatan, $userId);
        $this->addApproval($userId, $roleId, 'approved', $catatan);
    }

    // Verify by admin
    public function verifyByAdmin($userId, $roleId, $catatan = null)
    {
        $this->status = 'verified_by_admin';
        $this->current_stage = 'verified_by_admin';
        $this->save();

        $this->addStatusHistory('verified_by_admin', $catatan, $userId);
        $this->addApproval($userId, $roleId, 'approved', $catatan);
    }

    // Approve by head
    public function approveByHead($userId, $roleId, $catatan = null)
    {
        $this->status = 'approved_by_head';
        $this->current_stage = 'approved_by_head';
        $this->save();

        $this->addStatusHistory('approved_by_head', $catatan, $userId);
        $this->addApproval($userId, $roleId, 'approved', $catatan);
    }

    // Reject TOR
    public function reject($userId, $roleId, $catatan)
    {
        $this->status = 'rejected';
        $this->current_stage = 'rejected';
        $this->save();

        $this->addStatusHistory('rejected', $catatan, $userId);
        $this->addApproval($userId, $roleId, 'rejected', $catatan);
    }

    // Request revision
    public function requestRevision($userId, $roleId, $catatan, $currentStatus = null)
    {
        // Determine which stage requested revision based on current status
        $revisionStatus = 'needs_revision'; // Default fallback
        
        if ($currentStatus === 'submitted') {
            $revisionStatus = 'needs_revision_by_secretary';
        } elseif ($currentStatus === 'reviewed_by_secretary') {
            $revisionStatus = 'needs_revision_by_admin';
        } elseif ($currentStatus === 'verified_by_admin') {
            $revisionStatus = 'needs_revision_by_head';
        }
        
        $this->status = $revisionStatus;
        $this->current_stage = $revisionStatus;
        $this->save();

        $this->addStatusHistory($revisionStatus, $catatan, $userId);
        $this->addApproval($userId, $roleId, 'request_revision', $catatan);
    }

    // Resubmit TOR after revision
    public function resubmit($userId)
    {
        // Determine where to send based on current revision status
        $newStatus = 'submitted'; // Default to secretary stage
        
        if ($this->status === 'needs_revision_by_admin') {
            $newStatus = 'reviewed_by_secretary'; // Back to admin stage
        } elseif ($this->status === 'needs_revision_by_head') {
            $newStatus = 'verified_by_admin'; // Back to head stage
        } elseif ($this->status === 'needs_revision_by_secretary') {
            $newStatus = 'submitted'; // Back to secretary stage
        }
        
        $this->status = $newStatus;
        $this->current_stage = $newStatus;
        $this->updated_at = now(); // Update submission date
        $this->save();
        
        $this->addStatusHistory($newStatus, 'TOR resubmitted after revision', $userId);
        
        return $newStatus;
    }
}
