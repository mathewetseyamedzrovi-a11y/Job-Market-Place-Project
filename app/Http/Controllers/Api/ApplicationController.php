<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    /**
     * Apply to a job (Worker only)
     * 
     * POST /api/jobs/{job}/apply
     */
    public function apply(Request $request, Job $job)
    {
        // Check if user is a worker
        if ($request->user()->role !== 'worker') {
            return response()->json([
                'success' => false,
                'message' => 'Only workers can apply to jobs'
            ], 403);
        }

        // Check if job is still open
        if ($job->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'This job is no longer accepting applications'
            ], 400);
        }

        // Check if user already applied
        $existingApplication = Application::where('job_id', $job->id)
            ->where('worker_id', $request->user()->id)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied to this job'
            ], 400);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'cover_message' => 'nullable|string|max:1000',
            'quoted_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create the application
        $application = Application::create([
            'job_id' => $job->id,
            'worker_id' => $request->user()->id,
            'cover_message' => $request->cover_message,
            'quoted_price' => $request->quoted_price,
            'status' => 'pending'
        ]);

        // Load relationships for response
        $application->load(['job', 'worker']);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'application' => $application
        ], 201);
    }

    /**
     * Get all applications for a specific job (Poster only)
     * 
     * GET /api/jobs/{job}/applications
     */
    public function jobApplications(Request $request, Job $job)
    {
        // Check if user is the poster of this job
        if ($request->user()->id !== $job->poster_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only view applications for your own jobs'
            ], 403);
        }

        // Get all applications with worker details
        $applications = $job->applications()->with('worker')->get();

        return response()->json([
            'success' => true,
            'applications' => $applications
        ]);
    }

    /**
     * Update application status (Poster only)
     * 
     * PATCH /api/applications/{application}/status
     */
    public function updateStatus(Request $request, Application $application)
    {
        $job = $application->job;

        // Check if user is the poster of this job
        if ($request->user()->id !== $job->poster_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update applications for your own jobs'
            ], 403);
        }

        // Validate status
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:shortlisted,hired,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // If hiring, update job status and reject other applications
        if ($request->status === 'hired') {
            $job->update(['status' => 'in_progress']);
            
            // Reject all other applications for this job
            Application::where('job_id', $job->id)
                ->where('id', '!=', $application->id)
                ->update(['status' => 'rejected']);
        }

        // Update this application's status
        $application->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Application status updated successfully',
            'application' => $application->load('worker')
        ]);
    }

    /**
     * Get all applications submitted by the authenticated worker
     * 
     * GET /api/my-applications
     */
    public function myApplications(Request $request)
    {
        $applications = Application::where('worker_id', $request->user()->id)
            ->with(['job', 'job.poster', 'job.category'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'applications' => $applications
        ]);
    }
}