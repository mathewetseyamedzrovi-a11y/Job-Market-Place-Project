<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CompletionController extends Controller
{
    /**
     * Poster confirms job completion
     * 
     * POST /api/jobs/{job}/complete
     */
    public function complete(Request $request, Job $job)
    {
        // Check if user is the poster
        if ($request->user()->id !== $job->poster_id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the job poster can mark as complete'
            ], 403);
        }

        // Check if job is in progress
        if ($job->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Job must be in progress to complete'
            ], 400);
        }

        $job->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Job marked as completed successfully'
        ]);
    }

    /**
     * Submit a rating for a completed job
     * 
     * POST /api/jobs/{job}/rate
     */
    public function rate(Request $request, Job $job)
    {
        // Check if job is completed
        if ($job->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Job must be completed to rate'
            ], 400);
        }

        // Check if user is part of this job
        $user = $request->user();
        $isPoster = ($user->id === $job->poster_id);
        $isWorker = $job->applications()
            ->where('worker_id', $user->id)
            ->where('status', 'hired')
            ->exists();

        if (!$isPoster && !$isWorker) {
            return response()->json([
                'success' => false,
                'message' => 'You are not part of this job'
            ], 403);
        }

        // Determine who is being rated
        $rateeId = $isPoster 
            ? $job->applications()->where('status', 'hired')->first()->worker_id 
            : $job->poster_id;

        // Check if already rated
        $existingRating = Rating::where('job_id', $job->id)
            ->where('rater_id', $user->id)
            ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this job'
            ], 400);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create rating
        $rating = Rating::create([
            'job_id' => $job->id,
            'rater_id' => $user->id,
            'ratee_id' => $rateeId,
            'rating' => $request->rating,
            'review' => $request->review
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully',
            'rating' => $rating->load(['rater', 'ratee'])
        ], 201);
    }

    /**
     * Get ratings for a user
     * 
     * GET /api/users/{userId}/ratings
     */
    public function userRatings($userId)
    {
        $ratings = Rating::where('ratee_id', $userId)
            ->with(['rater', 'job'])
            ->latest()
            ->get();

        $average = $ratings->avg('rating');

        return response()->json([
            'success' => true,
            'average_rating' => $average ? round($average, 1) : null,
            'total_ratings' => $ratings->count(),
            'ratings' => $ratings
        ]);
    }
}