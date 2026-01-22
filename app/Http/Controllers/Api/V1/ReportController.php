<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReportReason;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * Submit a report against a user
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'reason' => ['required', Rule::enum(ReportReason::class)],
            'description' => 'nullable|string|max:1000',
        ], [
            'user_id.required' => 'المستخدم مطلوب',
            'user_id.exists' => 'المستخدم غير موجود',
            'reason.required' => 'سبب البلاغ مطلوب',
            'description.max' => 'الوصف يجب ألا يتجاوز 1000 حرف',
        ]);

        $user = $request->user();
        $reportedUser = User::findOrFail($request->user_id);

        // Cannot report yourself
        if ($user->id === $reportedUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك الإبلاغ عن نفسك',
            ], 422);
        }

        // Check for existing pending report
        $existingReport = Report::where('reporter_id', $user->id)
            ->where('reported_user_id', $reportedUser->id)
            ->where('status', 'pending')
            ->first();

        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => 'يوجد بلاغ معلق مسبقاً ضد هذا المستخدم',
            ], 422);
        }

        // Create report
        $report = Report::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $reportedUser->id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال البلاغ بنجاح وسيتم مراجعته',
            'report' => [
                'id' => $report->id,
                'reason' => $report->reason->value,
                'status' => $report->status,
            ],
        ], 201);
    }
}
