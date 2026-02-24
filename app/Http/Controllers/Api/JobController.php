<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Job;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    /**
     * Display a listing of all jobs.
     */
    public function index()
    {
        $jobs = Job::with(['poster', 'category'])->latest()->get();
        
        return response()->json([
            'success' => true,
            'jobs' => $jobs
        ]);
    }

    /**
     * Store a newly created job.
     */
    public function store(Request $request)
    {
        // Check if user is a poster
        if ($request->user()->role !== 'poster') {
            return response()->json([
                'success' => false,
                'message' => 'Only posters can create jobs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'budget' => 'required|numeric|min:0',
            'location' => 'required|string',
            'urgency' => 'required|string',
            'duration' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $job = Job::create([
            'poster_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'budget' => $request->budget,
            'location' => $request->location,
            'urgency' => $request->urgency,
            'duration' => $request->duration,
            'status' => 'open'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job created successfully',
            'job' => $job->load(['poster', 'category'])
        ], 201);
    }

    /**
     * Display the specified job.
     */
    public function show(string $id)
    {
        $job = Job::with(['poster', 'category'])->find($id);
        
        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'job' => $job
        ]);
    }

    /**
     * Update the specified job.
     */
    public function update(Request $request, string $id)
    {
        $job = Job::find($id);
        
        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        // Check if user is the poster who created this job
        if ($request->user()->id !== $job->poster_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update your own jobs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'budget' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string',
            'urgency' => 'sometimes|string',
            'duration' => 'nullable|string',
            'status' => 'sometimes|in:open,in_progress,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $job->update($request->only([
            'title', 'description', 'category_id', 'budget',
            'location', 'urgency', 'duration', 'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'job' => $job->load(['poster', 'category'])
        ]);
    }

    /**
     * Remove the specified job.
     */
    public function destroy(Request $request, string $id)
    {
        $job = Job::find($id);
        
        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        }

        // Check if user is the poster who created this job
        if ($request->user()->id !== $job->poster_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own jobs'
            ], 403);
        }

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully'
        ]);
    }

    /**
     * Get all job categories
     */
    public function categories()
    {
        $categories = Category::all();
        
        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }
}