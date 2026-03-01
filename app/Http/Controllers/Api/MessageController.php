<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Get conversation for a specific job
     * 
     * GET /api/jobs/{job}/messages
     */
    public function conversation(Request $request, Job $job)
    {
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

        // Get all messages for this job
        $messages = Message::where('job_id', $job->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark unread messages as read
        Message::where('job_id', $job->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'job_id' => $job->id,
            'messages' => $messages
        ]);
    }

    /**
     * Send a message with error handling
     * 
     * POST /api/jobs/{job}/messages
     */
    public function send(Request $request, Job $job)
    {
        try {
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

            // Determine receiver (the other party)
            $receiverId = $isPoster 
                ? $job->applications()->where('status', 'hired')->first()->worker_id 
                : $job->poster_id;

            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:5000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'job_id' => $job->id,
                'message' => $request->message,
                'is_read' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message->load(['sender', 'receiver'])
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Get all conversations for the authenticated user
     * 
     * GET /api/conversations
     */
    public function conversations(Request $request)
    {
        $user = $request->user();

        // Get all jobs where user has messages
        $jobIds = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->pluck('job_id')
            ->unique();

        $conversations = [];

        foreach ($jobIds as $jobId) {
            $job = Job::with(['poster', 'applications' => function($q) {
                $q->where('status', 'hired');
            }])->find($jobId);

            if (!$job) continue;

            // Get last message
            $lastMessage = Message::where('job_id', $jobId)
                ->latest()
                ->first();

            // Count unread messages
            $unreadCount = Message::where('job_id', $jobId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            // Determine other party
            $otherParty = ($job->poster_id == $user->id) 
                ? $job->applications()->where('status', 'hired')->first()->worker 
                : $job->poster;

            $conversations[] = [
                'job' => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'status' => $job->status
                ],
                'other_party' => $otherParty ? [
                    'id' => $otherParty->id,
                    'name' => $otherParty->name,
                    'role' => $otherParty->role
                ] : null,
                'last_message' => $lastMessage ? [
                    'message' => $lastMessage->message,
                    'sent_at' => $lastMessage->created_at,
                    'is_read' => $lastMessage->is_read,
                    'is_from_me' => $lastMessage->sender_id == $user->id
                ] : null,
                'unread_count' => $unreadCount
            ];
        }

        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    /**
     * Mark a specific message as read
     * 
     * PATCH /api/messages/{message}/read
     */
    public function markAsRead(Request $request, Message $message)
    {
        // Check if user is the receiver
        if ($request->user()->id !== $message->receiver_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only mark your own messages as read'
            ], 403);
        }

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }
}