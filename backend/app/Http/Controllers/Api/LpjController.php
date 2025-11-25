<?php
// app/Http/Controllers/Api/LpjController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lpj;
use App\Models\Tor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LpjStatusChanged;

class LpjController extends Controller
{
    /**
     * Get all LPJs
     */
    public function index(Request $request)
    {
        try {
            $query = Lpj::with(['tor', 'user', 'statusHistories']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by user (my LPJs)
            if ($request->has('my_lpjs') && $request->my_lpjs == true) {
                $query->where('user_id', Auth::guard('api')->id());
            }

            $lpjs = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $lpjs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get LPJs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new LPJ
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tor_id' => 'required|exists:tor,tor_id',
            'activity_result' => 'required|string',
            'activity_evaluation' => 'required|string',
            'actual_date => required|date',
            'budget_used' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if TOR is approved
            $tor = Tor::findOrFail($request->tor_id);

            if ($tor->status !== 'approved_by_head') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only create LPJ for approved TOR'
                ], 400);
            }

            // Check if LPJ already exists for this TOR
            if ($tor->lpj) {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ already exists for this TOR'
                ], 400);
            }

            // Check if user owns the TOR
            if ($tor->user_id !== Auth::guard('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create LPJ for this TOR'
                ], 403);
            }

            $lpj = Lpj::create([
                'tor_id' => $request->tor_id,
                'user_id' => Auth::guard('api')->id(),
                'activity_result' => $request->activity_result,
                'activity_evaluation' => $request->activity_evaluation,
                'budget_used' => $request->budget_used,
                'status' => 'under_review',
                'current_stage' => 'under_review',
            ]);

            $lpj->addStatusHistory('under_review', 'LPJ submitted for review', Auth::guard('api')->id());

            return response()->json([
                'success' => true,
                'message' => 'LPJ created successfully',
                'data' => $lpj->load(['tor', 'user'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create LPJ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific LPJ
     */
    public function show($id)
    {
        try {
            $lpj = Lpj::with(['tor', 'user', 'statusHistories.user', 'approvals.user', 'approvals.role', 'attachments'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $lpj
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'LPJ not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update LPJ
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'activity_result' => 'string',
            'activity_evaluation' => 'string',
            'budget_used' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lpj = Lpj::findOrFail($id);

            // Check if user owns this LPJ
            if ($lpj->user_id !== Auth::guard('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this LPJ'
                ], 403);
            }

            // Only allow update if status is draft or needs_revision
            if (!in_array($lpj->status, ['draft', 'needs_revision'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update LPJ in current status'
                ], 400);
            }

            $lpj->update($request->all());
            $lpj->addStatusHistory('updated', 'LPJ updated', Auth::guard('api')->id());

            return response()->json([
                'success' => true,
                'message' => 'LPJ updated successfully',
                'data' => $lpj->load(['tor', 'user'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update LPJ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete LPJ
     */
    public function destroy($id)
    {
        try {
            $lpj = Lpj::findOrFail($id);

            // Check if user owns this LPJ
            if ($lpj->user_id !== Auth::guard('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this LPJ'
                ], 403);
            }

            // Only allow delete if status is draft
            if ($lpj->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete LPJ that has been submitted'
                ], 400);
            }

            $lpj->delete();

            return response()->json([
                'success' => true,
                'message' => 'LPJ deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete LPJ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit LPJ
     */
    public function submit($id)
    {
        try {
            $lpj = Lpj::findOrFail($id);

            // Check if user owns this LPJ
            if ($lpj->user_id !== Auth::guard('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to submit this LPJ'
                ], 403);
            }

            // Only allow submit if status is draft or needs_revision
            if (!in_array($lpj->status, ['draft', 'needs_revision'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot submit LPJ in current status'
                ], 400);
            }

            $lpj->submit(Auth::guard('api')->id());

            return response()->json([
                'success' => true,
                'message' => 'LPJ submitted successfully',
                'data' => $lpj
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit LPJ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare budget (submitted vs used)
     */
    public function compareBudget($id)
    {
        try {
            $lpj = Lpj::with('tor')->findOrFail($id);
            $comparison = $lpj->compareBudget();

            return response()->json([
                'success' => true,
                'data' => $comparison
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to compare budget',
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
            $lpj = Lpj::findOrFail($id);
            $user = Auth::guard('api')->user();
            $oldStatus = $lpj->status;

            if (!$user->isSekretaris()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only secretary can review LPJ'
                ], 403);
            }

            if ($lpj->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ is not in submitted stage'
                ], 400);
            }

            $action = $request->action;
            $catatan = $request->catatan;

            if ($action === 'approved') {
                $lpj->approveBySecretary($user->user_id, $user->role_id, $catatan);
                $newStatus = 'reviewed_by_secretary';
                $message = 'LPJ reviewed by secretary';

                // Notify admins for verification
                $admins = User::whereHas('role', function ($q) {
                    $q->where('role_def', 'admin jurusan');
                })->get();
                if ($admins->count() > 0) {
                    Notification::send($admins, new LpjStatusChanged(
                        $lpj,
                        $oldStatus,
                        $newStatus,
                        $user->full_name,
                        'LPJ needs admin verification'
                    ));
                }
            } elseif ($action === 'rejected') {
                $lpj->reject($user->user_id, $user->role_id, $catatan);
                $newStatus = 'rejected';
                $message = 'LPJ rejected by secretary';
            } else {
                $lpj->requestRevision($user->user_id, $user->role_id, $catatan);
                $newStatus = 'needs_revision';
                $message = 'Revision requested by secretary';
            }

            // Notify LPJ creator about the status change
            $lpj->user->notify(new LpjStatusChanged(
                $lpj,
                $oldStatus,
                $newStatus,
                $user->full_name,
                $catatan ?: $message
            ));

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $lpj
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to review LPJ',
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lpj = Lpj::findOrFail($id);
            $user = Auth::guard('api')->user();
            $oldStatus = $lpj->status;

            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admin can verify LPJ'
                ], 403);
            }

            if ($lpj->status !== 'reviewed_by_secretary') {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ must be reviewed by secretary first'
                ], 400);
            }

            $action = $request->action;
            $catatan = $request->catatan;

            if ($action === 'approved') {
                $lpj->verifyByAdmin($user->user_id, $user->role_id, $catatan);
                $newStatus = 'verified_by_admin';
                $message = 'LPJ verified by admin';

                // Notify department heads for final approval
                $heads = User::whereHas('role', function ($q) {
                    $q->where('role_def', 'ketua jurusan');
                })->get();
                if ($heads->count() > 0) {
                    Notification::send($heads, new LpjStatusChanged(
                        $lpj,
                        $oldStatus,
                        $newStatus,
                        $user->full_name,
                        'LPJ needs department head approval'
                    ));
                }
            } elseif ($action === 'rejected') {
                $lpj->reject($user->user_id, $user->role_id, $catatan);
                $newStatus = 'rejected';
                $message = 'LPJ rejected by admin';
            } else {
                $lpj->requestRevision($user->user_id, $user->role_id, $catatan);
                $newStatus = 'needs_revision';
                $message = 'Revision requested by admin';
            }

            // Notify LPJ creator about the status change
            $lpj->user->notify(new LpjStatusChanged(
                $lpj,
                $oldStatus,
                $newStatus,
                $user->full_name,
                $catatan ?: $message
            ));

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $lpj
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify LPJ',
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
            $lpj = Lpj::findOrFail($id);
            $user = Auth::guard('api')->user();
            $oldStatus = $lpj->status;

            if (!$user->isKetua()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only department head can approve LPJ'
                ], 403);
            }

            if ($lpj->status !== 'verified_by_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ must be verified by admin first'
                ], 400);
            }

            $action = $request->action;
            $catatan = $request->catatan;

            if ($action === 'approved') {
                $lpj->approveByHead($user->user_id, $user->role_id, $catatan);
                $newStatus = 'approved_by_head';
                $message = 'LPJ approved by department head';
            } elseif ($action === 'rejected') {
                $lpj->reject($user->user_id, $user->role_id, $catatan);
                $newStatus = 'rejected';
                $message = 'LPJ rejected by department head';
            } else {
                $lpj->requestRevision($user->user_id, $user->role_id, $catatan);
                $newStatus = 'needs_revision';
                $message = 'Revision requested by department head';
            }

            // Notify LPJ creator about the status change
            $lpj->user->notify(new LpjStatusChanged(
                $lpj,
                $oldStatus,
                $newStatus,
                $user->full_name,
                $catatan ?: $message
            ));

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $lpj
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve LPJ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get TOR data for pre-filling LPJ form
     *
     * @param int $torId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPreFillData($torId)
    {
        try {
            $tor = Tor::with(['user', 'category', 'annualBudget', 'attachments'])
                ->findOrFail($torId);

            // Check if TOR is approved
            if ($tor->status !== 'approved_by_head') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only create LPJ for approved TOR'
                ], 400);
            }

            // Check if LPJ already exists
            if ($tor->lpj) {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ already exists for this TOR',
                    'existing_lpj_id' => $tor->lpj->lpj_id
                ], 400);
            }

            // Check if user owns the TOR
            if ($tor->user_id !== Auth::guard('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create LPJ for this TOR'
                ], 403);
            }

            // Prepare pre-fill data
            $preFillData = [
                'tor_id' => $tor->tor_id,
                'activity_name' => $tor->activity_name,
                'activity_background' => $tor->activity_background,
                'activity_purpose' => $tor->activity_purpose,
                'participant' => $tor->participant,
                'pic' => $tor->pic,
                'start_date' => $tor->start_date->format('Y-m-d'),
                'end_date' => $tor->end_date->format('Y-m-d'),
                'budget_submitted' => (float) $tor->budget_submitted,
                'category' => $tor->category ? $tor->category->category_def : null,
                'attachments' => $tor->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->attach_id,
                        'file_path' => $attachment->file_path,
                        'file_name' => basename($attachment->file_path),
                    ];
                }),
                // Suggested fields for LPJ
                'suggested_fields' => [
                    'activity_result' => "Workshop/kegiatan {$tor->activity_name} telah dilaksanakan pada tanggal {$tor->start_date->format('d M Y')} sampai {$tor->end_date->format('d M Y')} dengan jumlah peserta {$tor->participant}.",
                    'activity_evaluation' => "Kegiatan berjalan dengan lancar dan mencapai tujuan yang telah ditetapkan.",
                    'budget_used' => (float) $tor->budget_submitted, // Default sama dengan yang diajukan
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'TOR data retrieved for LPJ pre-fill',
                'data' => $preFillData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pre-fill data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create LPJ with pre-filled data from TOR
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createWithPreFill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tor_id' => 'required|exists:tor,tor_id',
            'activity_result' => 'required|string',
            'activity_evaluation' => 'required|string',
            'budget_used' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Validate TOR
            $tor = Tor::findOrFail($request->tor_id);

            if ($tor->status !== 'approved_by_head') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only create LPJ for approved TOR'
                ], 400);
            }

            if ($tor->lpj) {
                return response()->json([
                    'success' => false,
                    'message' => 'LPJ already exists for this TOR'
                ], 400);
            }

            if ($tor->user_id !== Auth::guard('api')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create LPJ for this TOR'
                ], 403);
            }

            // Create LPJ
            $lpj = Lpj::create([
                'tor_id' => $request->tor_id,
                'user_id' => Auth::guard('api')->id(),
                'activity_result' => $request->activity_result,
                'activity_evaluation' => $request->activity_evaluation,
                'budget_used' => $request->budget_used,
                'status' => 'draft',
                'current_stage' => 'draft',
            ]);

            $lpj->addStatusHistory('draft', 'LPJ created as draft', Auth::guard('api')->id());

            // Calculate budget comparison
            $budgetComparison = [
                'budget_submitted' => (float) $tor->budget_submitted,
                'budget_used' => (float) $lpj->budget_used,
                'difference' => (float) ($tor->budget_submitted - $lpj->budget_used),
                'percentage' => $tor->budget_submitted > 0
                    ? round(($lpj->budget_used / $tor->budget_submitted) * 100, 2)
                    : 0,
            ];

            return response()->json([
                'success' => true,
                'message' => 'LPJ created successfully with TOR data',
                'data' => [
                    'lpj' => $lpj->load(['tor', 'user']),
                    'budget_comparison' => $budgetComparison,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create LPJ',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
