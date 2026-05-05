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
