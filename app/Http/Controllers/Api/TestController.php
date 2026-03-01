<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function testMessage(Request $request, $jobId)
    {
        return response()->json([
            'success' => true,
            'message' => 'Test controller working',
            'user_id' => $request->user()->id ?? 'not authenticated',
            'job_id' => $jobId,
            'received_data' => $request->all()
        ]);
    }
}