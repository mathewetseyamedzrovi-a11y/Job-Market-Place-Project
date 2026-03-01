<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
// use Bmatovu\LaravelMtnMomo\Products\Collection;

class PaymentController extends Controller
{
    // protected $collection;

    // public function __construct()
    // {
    //     $this->collection = new Collection();
    // }

    /**
     * Request payment from poster to worker
     * 
     * POST /api/jobs/{job}/pay
     */
    public function requestPayment(Request $request, Job $job)
    {
        try {
            // Check if user is the poster
            if ($request->user()->id !== $job->poster_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the job poster can initiate payment'
                ], 403);
            }

            // Check if job is completed
            if ($job->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Job must be completed to process payment'
                ], 400);
            }

            // Check if payment already exists
            $existingPayment = Payment::where('job_id', $job->id)->first();
            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already processed for this job'
                ], 400);
            }

            // Get the hired worker
            $application = $job->applications()->where('status', 'hired')->first();
            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hired worker found for this job'
                ], 400);
            }

            $worker = $application->worker;

            // Validate request
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'phone' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate transaction ID
            $transactionId = uniqid('txn_');

            // Create payment record
            $payment = Payment::create([
                'job_id' => $job->id,
                'payer_id' => $job->poster_id,
                'payee_id' => $worker->id,
                'amount' => $request->amount,
                'transaction_id' => $transactionId,
                'status' => 'pending'
            ]);

            // In sandbox mode, we'll simulate successful payment
            if (env('MTN_MOMO_ENVIRONMENT') === 'sandbox') {
                $payment->update([
                    'status' => 'completed',
                    'momo_response' => ['simulated' => true]
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully (sandbox mode)',
                    'payment' => $payment->load(['payer', 'payee', 'job'])
                ]);
            }

            // For production, actual MoMo API call would go here
            return response()->json([
                'success' => true,
                'message' => 'Payment request initiated',
                'payment' => $payment
            ]);

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
     * Get payment status
     * 
     * GET /api/payments/{payment}
     */
    public function status(Payment $payment)
    {
        return response()->json([
            'success' => true,
            'payment' => $payment->load(['payer', 'payee', 'job'])
        ]);
    }

    /**
     * Get all payments for authenticated user
     * 
     * GET /api/my-payments
     */
    public function myPayments(Request $request)
    {
        $user = $request->user();

        $payments = Payment::where('payer_id', $user->id)
            ->orWhere('payee_id', $user->id)
            ->with(['job', 'payer', 'payee'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }
}