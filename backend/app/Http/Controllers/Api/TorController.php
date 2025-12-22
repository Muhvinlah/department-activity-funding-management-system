<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TorStatusChanged;

class TorController extends Controller
{
    /**
     * Get all TORs
     */
    public function index(Request $request)
    {
        try {
            $query = Tor::with(['user', 'category', 'annualBudget', 'statusHistories']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by user (my TORs)
            if ($request->has('my_tors') && $request->my_tors == true) {
                $query->where('user_id', Auth::guard('api')->id());
            }

            $tors = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $tors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get TORs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new TOR
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activity_name' => 'required|string|max:255',
            'activity_background' => 'required|string',
            'activity_purpose' => 'required|string',
            'participant' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget_submitted' => 'required|numeric|min:0',
            'pic' => 'required|string|max:100',
            'category_id' => 'required|exists:activity_category,category_id',
            // budget_id removed - will be auto-assigned
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Auto-assign budget_id based on activity start_date year
            $activityYear = date('Y', strtotime($request->start_date));
            $annualBudget = \App\Models\AnnualBudget::where('tahun', $activityYear)->first();
            
            if (!$annualBudget) {
                return response()->json([
                    'success' => false,
                    'message' => "No budget found for year {$activityYear}. Please contact admin to create annual budget."
                ], 400);
            }

            $tor = Tor::create([
                'activity_name' => $request->activity_name,
                'activity_background' => $request->activity_background,
                'activity_purpose' => $request->activity_purpose,
                'participant' => $request->participant,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'budget_submitted' => $request->budget_submitted,
                'pic' => $request->pic,
                'category_id' => $request->category_id,
                'budget_id' => $annualBudget->budget_id, // Auto-assigned
                'user_id' => Auth::guard('api')->id(),
                'status' => 'submitted',
                'current_stage' => 'submitted',
            ]);

            $tor->addStatusHistory('submitted', 'TOR submitted', Auth::guard('api')->id());

            return response()->json([
                'success' => true,
                'message' => 'TOR created successfully',
                'data' => $tor->load(['user', 'category', 'annualBudget'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create TOR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific TOR
     */
    public function show($id)
    {
        try {
            $tor = Tor::with(['user', 'category', 'annualBudget', 'statusHistories.user', 'approvals.user', 'approvals.role', 'attachments'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $tor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'TOR not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update TOR
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'activity_name' => 'string|max:255',
            'activity_background' => 'string',
            'activity_purpose' => 'string',
            'participant' => 'string',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'budget_submitted' => 'numeric|min:0',
            'pic' => 'string|max:100',
            'category_id' => 'exists:activity_category,category_id',
            // budget_id removed - will be auto-assigned if start_date changes
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tor = Tor::findOrFail($id);

            // Check if user owns this TOR
            if ($tor->user_id !== Auth::guard('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this TOR'
                ], 403);
            }

            // Only allow update if status is submitted or needs_revision
        if (!in_array($tor->status, ['submitted', 'needs_revision', 'needs_revision_by_secretary', 'needs_revision_by_admin', 'needs_revision_by_head'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update TOR in current status'
                ], 400);
            }

            // If start_date is being updated, auto-reassign budget_id
            $updateData = $request->all();
            if ($request->has('start_date')) {
                $activityYear = date('Y', strtotime($request->start_date));
                $annualBudget = \App\Models\AnnualBudget::where('tahun', $activityYear)->first();
                
                if (!$annualBudget) {
                    return response()->json([
                        'success' => false,
                        'message' => "No budget found for year {$activityYear}. Please contact admin."
                    ], 400);
                }
                
                $updateData['budget_id'] = $annualBudget->budget_id;
            }

            $oldStatus = $tor->status;
            $tor->update($updateData);
            $tor->addStatusHistory('updated', 'TOR updated', Auth::guard('api')->id());

            // If TOR was in revision, automatically resubmit
            if (str_contains($oldStatus, 'needs_revision')) {
                $newStatus = $tor->resubmit(Auth::guard('api')->id());
                
                // Notify TOR creator
                $tor->user->notify(new TorStatusChanged(
                    $tor,
                    $oldStatus,
                    $newStatus,
                    Auth::guard('api')->user()->full_name,
                    'TOR has been resubmitted after revision'
                ));

                // Notify appropriate reviewers
                $reviewers = null;
                $notificationMessage = '';
                
                if ($newStatus === 'submitted') {
                    $reviewers = User::whereHas('role', function ($q) {
                        $q->where('role_def', 'sekretaris jurusan');
                    })->get();
                    $notificationMessage = 'TOR resubmitted after revision';
                } elseif ($newStatus === 'reviewed_by_secretary') {
                    $reviewers = User::whereHas('role', function ($q) {
                        $q->where('role_def', 'admin jurusan');
                    })->get();
                    $notificationMessage = 'TOR resubmitted after revision - needs admin verification';
                } elseif ($newStatus === 'verified_by_admin') {
                    $reviewers = User::whereHas('role', function ($q) {
                        $q->where('role_def', 'ketua jurusan');
                    })->get();
                    $notificationMessage = 'TOR resubmitted after revision - needs final approval';
                }

                if ($reviewers && $reviewers->count() > 0) {
                    Notification::send($reviewers, new TorStatusChanged(
                        $tor,
                        $oldStatus,
                        $newStatus,
                        Auth::guard('api')->user()->full_name,
                        $notificationMessage
                    ));
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'TOR updated successfully',
                'data' => $tor->load(['user', 'category', 'annualBudget'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update TOR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete TOR
     */
    public function destroy($id)
    {
        try {
            $tor = Tor::findOrFail($id);

            // Check if user owns this TOR
            if ($tor->user_id !== Auth::guard('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this TOR'
                ], 403);
            }

        // Only allow delete if status is submitted or needs_revision
        if (!in_array($tor->status, ['submitted', 'needs_revision', 'needs_revision_by_secretary', 'needs_revision_by_admin', 'needs_revision_by_head'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete TOR that is under review or approved'
            ], 400);
        }

            $tor->delete();

            return response()->json([
                'success' => true,
                'message' => 'TOR deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete TOR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit TOR
     */
    public function submit($id)
    {
        try {
            $tor = Tor::findOrFail($id);
            $oldStatus = $tor->status;

            // Check if user owns this TOR
            if ($tor->user_id !== Auth::guard('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to submit this TOR'
                ], 403);
            }

            // Only allow submit if status is submitted or needs_revision (any variant)
        if (!in_array($oldStatus, [
            'submitted', 
            'needs_revision',
            'needs_revision_by_secretary',
            'needs_revision_by_admin',
            'needs_revision_by_head'
        ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot submit TOR in current status'
                ], 400);
            }

            // Determine if this is a resubmission
        $isResubmission = str_contains($oldStatus, 'needs_revision');

        if ($isResubmission) {
            // Resubmit to the appropriate reviewer
            $newStatus = $tor->resubmit(Auth::guard('api')->id());
        } else {
            // New submission
            $tor->submit(Auth::guard('api')->id());
            $newStatus = 'submitted';
        }

        // Notify TOR creator
        $tor->user->notify(new TorStatusChanged(
            $tor,
            $oldStatus,
            $newStatus,
            Auth::guard('api')->user()->name,
            $isResubmission ? 'TOR has been resubmitted for review' : 'TOR has been submitted for review'
        ));

        // Notify appropriate reviewers based on new status
        $reviewers = null;
        $notificationMessage = '';
        
        if ($newStatus === 'submitted') {
            // Notify secretaries
            $reviewers = User::whereHas('role', function ($q) {
                $q->where('role_def', 'sekretaris jurusan');
            })->get();
            $notificationMessage = $isResubmission ? 'TOR resubmitted after revision' : 'New TOR submitted for review';
        } elseif ($newStatus === 'reviewed_by_secretary') {
            // Notify admins
            $reviewers = User::whereHas('role', function ($q) {
                $q->where('role_def', 'admin jurusan');
            })->get();
            $notificationMessage = 'TOR resubmitted after revision - needs admin verification';
        } elseif ($newStatus === 'verified_by_admin') {
            // Notify heads
            $reviewers = User::whereHas('role', function ($q) {
                $q->where('role_def', 'ketua jurusan');
            })->get();
            $notificationMessage = 'TOR resubmitted after revision - needs final approval';
        }

        if ($reviewers && $reviewers->count() > 0) {
            Notification::send($reviewers, new TorStatusChanged(
                $tor,
                $oldStatus,
                $newStatus,
                Auth::guard('api')->user()->full_name,
                $notificationMessage
            ));
        }    

            return response()->json([
                'success' => true,
                'message' => 'TOR submitted successfully',
                'data' => $tor
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit TOR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Review by Secretary
     */
    public function reviewBySecretary(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approved,rejected,request_revision',
            'catatan' => 'required_if:action,rejected,request_revision|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tor = Tor::findOrFail($id);
            $user = Auth::guard('api')->user();
            $oldStatus = $tor->status;

            // Check if user is secretary
            if (!$user->isSekretaris()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only secretary can review TOR'
                ], 403);
            }

            // Check if TOR is in correct stage
            if ($tor->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'TOR is not in submitted stage'
                ], 400);
            }

            $action = $request->action;
            $catatan = $request->catatan;

            if ($action === 'approved') {
                $tor->approveBySecretary($user->user_id, $user->role_id, $catatan);
                $newStatus = 'reviewed_by_secretary';
                $message = 'TOR reviewed by secretary';

                // Notify admins for verification
                $admins = User::whereHas('role', function ($q) {
                    $q->where('role_def', 'admin jurusan');
                })->get();
                if ($admins->count() > 0) {
                    Notification::send($admins, new TorStatusChanged(
                        $tor,
                        $oldStatus,
                        $newStatus,
                        $user->full_name,
                        'TOR needs admin verification'
                    ));
                }
            } elseif ($action === 'rejected') {
                $tor->reject($user->user_id, $user->role_id, $catatan);
                $newStatus = 'rejected';
                $message = 'TOR rejected by secretary';
            } else {
            $tor->requestRevision($user->user_id, $user->role_id, $catatan, $oldStatus);
            $newStatus = $tor->status; // Get the role-specific revision status
            $message = 'Revision requested by secretary';
            }

            // Notify TOR creator about the status change
            $tor->user->notify(new TorStatusChanged(
                $tor,
                $oldStatus,
                $newStatus,
                $user->full_name,
                $catatan ?: $message
            ));

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $tor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to review TOR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify by Admin
     */
    public function verifyByAdmin(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approved,rejected,request_revision',
            'catatan' => 'required_if:action,rejected,request_revision|string',
            // Require nomor_surat if action is approved
            'nomor_surat' => 'required_if:action,approved|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tor = Tor::findOrFail($id);
            $user = Auth::guard('api')->user();
            $oldStatus = $tor->status;

            // Check if user is admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admin can verify TOR'
                ], 403);
            }

            // Check if TOR is in correct stage
            if ($tor->status !== 'reviewed_by_secretary') {
                return response()->json([
                    'success' => false,
                    'message' => 'TOR must be reviewed by secretary first'
                ], 400);
            }

            $action = $request->action;
            $catatan = $request->catatan;

            if ($action === 'approved') {
                // Save reference number
                $tor->reference_number = $request->nomor_surat;
                
                $tor->verifyByAdmin($user->user_id, $user->role_id, $catatan);
                $newStatus = 'verified_by_admin';
                $message = 'TOR verified by admin';

                // Notify department heads for final approval
                $heads = User::whereHas('role', function ($q) {
                    $q->where('role_def', 'ketua jurusan');
                })->get();
                if ($heads->count() > 0) {
                    Notification::send($heads, new TorStatusChanged(
                        $tor,
                        $oldStatus,
                        $newStatus,
                        $user->full_name,
                        'TOR needs final approval'
                    ));
                }
            } elseif ($action === 'rejected') {
                $tor->reject($user->user_id, $user->role_id, $catatan);
                $newStatus = 'rejected';
                $message = 'TOR rejected by admin';
            } else {
                $tor->requestRevision($user->user_id, $user->role_id, $catatan, $oldStatus);
                $newStatus = $tor->status; // Get the role-specific revision status
                $message = 'Revision requested by admin';
            }

            // Notify TOR creator about the status change
            $tor->user->notify(new TorStatusChanged(
                $tor,
                $oldStatus,
                $newStatus,
                $user->full_name,
                $catatan ?: $message
            ));

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $tor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify TOR',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve by Head
     */
    public function approveByHead(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approved,rejected,request_revision',
            'catatan' => 'required_if:action,rejected,request_revision|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tor = Tor::findOrFail($id);
            $user = Auth::guard('api')->user();
            $oldStatus = $tor->status;

            // Check if user is head
            if (!$user->isKetua()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only department head can approve TOR'
                ], 403);
            }

            // Check if TOR is in correct stage
            if ($tor->status !== 'verified_by_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'TOR must be verified by admin first'
                ], 400);
            }

            $action = $request->action;
            $catatan = $request->catatan;

            if ($action === 'approved') {
                $tor->approveByHead($user->user_id, $user->role_id, $catatan);
                $newStatus = 'approved_by_head';
                $message = 'TOR approved by department head';

                // Notify all involved parties about final approval
                $allInvolved = User::whereHas('role', function ($q) {
                    $q->whereIn('role_def', ['admin jurusan', 'sekretaris jurusan']);
                })->orWhere('user_id', $tor->user_id)
                  ->get();

                Notification::send($allInvolved, new TorStatusChanged(
                    $tor,
                    $oldStatus,
                    $newStatus,
                    $user->full_name,
                    'TOR has been fully approved and ready for execution'
                ));
            } elseif ($action === 'rejected') {
                $tor->reject($user->user_id, $user->role_id, $catatan);
                $newStatus = 'rejected';
                $message = 'TOR rejected by department head';
            } else {
            $tor->requestRevision($user->user_id, $user->role_id, $catatan, $oldStatus);
            $newStatus = $tor->status; // Get the role-specific revision status
            $message = 'Revision requested by department head';
            }

            // Always notify TOR creator about the status change
            $tor->user->notify(new TorStatusChanged(
                $tor,
                $oldStatus,
                $newStatus,
                $user->full_name,
                $catatan ?: $message
            ));

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $tor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve TOR',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
