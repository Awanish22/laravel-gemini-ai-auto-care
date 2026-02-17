<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleIssueController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes - NO AUTHENTICATION REQUIRED
Route::prefix('vehicle-issues')->group(function () {
    Route::get('/', [VehicleIssueController::class, 'index']);
    Route::get('/{vehicleIssue}', [VehicleIssueController::class, 'show']);
    Route::post('/', [VehicleIssueController::class, 'store']);
    Route::put('/{vehicleIssue}', [VehicleIssueController::class, 'update']);
    Route::delete('/{vehicleIssue}', [VehicleIssueController::class, 'destroy']);
    Route::post('/{id}/analyze', [VehicleIssueController::class, 'requestAnalysis']);
});

// Optional: User route (if you want to keep it)
Route::get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => $request->user()
    ]);
});
Route::get('/test-gemini-final', function() {
    $apiKey = 'YOUR_ACTUAL_GEMINI_API_KEY_HERE'; // Replace with your key
    
    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=' . $apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Explain what a car mechanic does in one sentence.']
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 100,
            ]
        ]);
        
        return response()->json([
            'status' => $response->status(),
            'success' => $response->successful(),
            'data' => $response->json(),
            'body' => $response->body()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});