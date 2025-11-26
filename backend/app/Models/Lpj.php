<?php
// app/Models/Lpj.php

namespace App\Models;

use App\Events\LpjApproved;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lpj extends Model
{
    use SoftDeletes;

    protected $table = 'lpj';
    protected $primaryKey = 'lpj_id';

    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';
    public $timestamps = false;

    protected $fillable = [
        'tor_id',
        'user_id',
        'activity_result',
        'activity_evaluation',
        'budget_used',
        'status',
        'current_stage',
    ];

    protected $casts = [
        'sub_date' => 'datetime',
        'budget_used' => 'decimal:2',
    ];

    // Relationships
    public function tor()
    {
        return $this->belongsTo(Tor::class, 'tor_id', 'tor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'lpj_id', 'lpj_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(StatusHist::class, 'lpj_id', 'lpj_id');
    }

    public function approvals()
    {
        return $this->hasMany(LpjApprov::class, 'lpj_id', 'lpj_id');
    }

    /**
     * Submit LPJ
     */
    public function submit($userId)
    {
        $this->status = 'under_review';
        $this->current_stage = 'under_review';
        $this->save();

        $this->addStatusHistory('under_review', 'LPJ submitted for approval', $userId);
    }

    /**
     * Add status history
     */
    public function addStatusHistory($status, $catatan = null, $userId = null)
    {
        StatusHist::create([
            'lpj_id' => $this->lpj_id,
            'tor_id' => $this->tor_id,
            'user_id' => $userId,
            'status' => $status,
            'catatan' => $catatan,
        ]);
    }

    /**
     * Add approval record
     */
    public function addApproval($userId, $roleId, $action, $catatan = null)
    {
        LpjApprov::create([
            'lpj_id' => $this->lpj_id,
            'user_id' => $userId,
            'role_id' => $roleId,
            'status' => $this->status,
            'action' => $action,
            'catatan' => $catatan,
        ]);
    }

    /**
     * Compare budget
     */
    public function compareBudget()
    {
        $tor = $this->tor;
        $difference = $tor->budget_submitted - $this->budget_used;
        $percentage = ($this->budget_used / $tor->budget_submitted) * 100;

        return [
            'budget_submitted' => $tor->budget_submitted,
            'budget_used' => $this->budget_used,
            'difference' => $difference,
            'percentage' => round($percentage, 2),
        ];
    }

    /**
     * Approve by secretary
     */
    public function approveBySecretary($userId, $roleId, $catatan = null)
    {
        $this->status = 'reviewed_by_secretary';
        $this->current_stage = 'reviewed_by_secretary';
        $this->save();

        $this->addStatusHistory('reviewed_by_secretary', $catatan, $userId);
        $this->addApproval($userId, $roleId, 'approved', $catatan);
    }

    /**
     * Verify by admin
     */
    public function verifyByAdmin($userId, $roleId, $catatan = null)
    {
        $this->status = 'verified_by_admin';
        $this->current_stage = 'verified_by_admin';
        $this->save();

        $this->addStatusHistory('verified_by_admin', $catatan, $userId);
        $this->addApproval($userId, $roleId, 'approved', $catatan);
    }

    /**
     * Approve by head
     */
    public function approveByHead($userId, $roleId, $catatan = null)
    {
        $this->status = 'approved_by_head';
        $this->current_stage = 'approved_by_head';
        $this->save();

        $this->addStatusHistory('approved_by_head', $catatan, $userId);
        $this->addApproval($userId, $roleId, 'approved', $catatan);

        // Trigger event to update dashboard
        event(new LpjApproved($this));
    }

    /**
     * Reject LPJ
     */
    public function reject($userId, $roleId, $catatan)
    {
        $this->status = 'rejected';
        $this->current_stage = 'rejected';
        $this->save();

        $this->addStatusHistory('rejected', $catatan, $userId);
        $this->addApproval($userId, $roleId, 'rejected', $catatan);
    }

    /**
     * Request revision
     */
    public function requestRevision($userId, $roleId, $catatan, $currentStatus = null)
    {
        // Determine which stage requested revision based on current status
        $revisionStatus = 'needs_revision'; // Default fallback
        
        if ($currentStatus === 'under_review') {
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

    /**
     * Resubmit LPJ after revision
     */
    public function resubmit($userId)
    {
        // Determine where to send based on current revision status
        $newStatus = 'under_review'; // Default to secretary stage
        
        if ($this->status === 'needs_revision_by_admin') {
            $newStatus = 'reviewed_by_secretary'; // Back to admin stage
        } elseif ($this->status === 'needs_revision_by_head') {
            $newStatus = 'verified_by_admin'; // Back to head stage
        } elseif ($this->status === 'needs_revision_by_secretary') {
            $newStatus = 'under_review'; // Back to secretary stage
        }
        
        $this->status = $newStatus;
        $this->current_stage = $newStatus;
        $this->sub_date = now(); // Update submission date
        $this->save();
        
        $this->addStatusHistory($newStatus, 'LPJ resubmitted after revision', $userId);
        
        return $newStatus;
    }
}
