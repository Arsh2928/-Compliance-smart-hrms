<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PerformanceRecord;
use App\Http\Requests\StoreRatingRequest;
use App\Services\RatingService;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * Web form: Submit a rating for an employee
     * POST /admin/employees/{id}/rate  or  /hr/employees/{id}/rate
     */
    public function webRating(Request $request, $id)
    {
        $request->validate([
            'categories.work_quality'    => 'required|numeric|min:1|max:5',
            'categories.punctuality'     => 'required|numeric|min:1|max:5',
            'categories.teamwork'        => 'required|numeric|min:1|max:5',
            'categories.task_completion' => 'required|numeric|min:1|max:5',
            'categories.discipline'      => 'required|numeric|min:1|max:5',
            'feedback'                   => 'nullable|string|max:1000',
        ]);

        $employee = Employee::find($id);
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        try {
            $this->ratingService->submitRating(
                $employee,
                $request->input('categories'),
                auth()->id(),
                $request->input('feedback')
            );

            return redirect()->back()->with('success', "Rating submitted for {$employee->user->name}! They've been notified.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * API: Submit a rating for an employee
     * POST /api/employees/{id}/rate
     */
    public function storeRating(StoreRatingRequest $request, $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        try {
            $rating = $this->ratingService->submitRating(
                $employee,
                $request->categories,
                auth()->id(),
                $request->feedback
            );

            return response()->json(['message' => 'Rating submitted successfully!', 'data' => $rating], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * API: Get current employee performance history
     * GET /api/my-performance
     */
    public function myHistory(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'employee') {
            return response()->json(['error' => 'Only employees have performance records.'], 403);
        }

        $employee = $user->employee;
        $records = PerformanceRecord::where('employee_id', $employee->id)
            ->orderBy('month', 'desc')
            ->get();

        $currentScore = $this->ratingService->calculateFinalScore($employee);

        return response()->json([
            'current_score' => $currentScore,
            'current_rating' => $employee->rating ?? 'N/A',
            'history' => $records
        ]);
    }
}
