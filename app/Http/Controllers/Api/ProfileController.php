<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get worker profile by user ID
     * 
     * GET /api/profiles/{userId}
     */
    public function show($userId)
    {
        $user = User::with('workerProfile')->find($userId);
        
        if (!$user || $user->role !== 'worker') {
            return response()->json([
                'success' => false,
                'message' => 'Worker not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'profile' => $user->workerProfile,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone
            ]
        ]);
    }

    /**
     * Update worker profile (authenticated worker only)
     * 
     * PUT /api/profile
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'worker') {
            return response()->json([
                'success' => false,
                'message' => 'Only workers can update profiles'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'skills' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'hourly_rate' => 'nullable|numeric|min:0',
            'availability' => 'nullable|in:available,busy,offline',
            'location' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $profile = $user->workerProfile;
        
        if (!$profile) {
            $profile = new WorkerProfile(['user_id' => $user->id]);
        }

        $profile->fill($request->only([
            'skills', 'experience_years', 'hourly_rate',
            'availability', 'location'
        ]));

        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'profile' => $profile
        ]);
    }

    /**
     * Upload ID photo
     * 
     * POST /api/profile/photo
     */
    public function uploadPhoto(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'worker') {
            return response()->json([
                'success' => false,
                'message' => 'Only workers can upload photos'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('worker-photos', 'public');
            
            $profile = $user->workerProfile;
            
            if (!$profile) {
                $profile = new WorkerProfile(['user_id' => $user->id]);
            }
            
            $profile->id_photo_path = $path;
            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'photo_url' => asset('storage/' . $path)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No photo uploaded'
        ], 400);
    }
}