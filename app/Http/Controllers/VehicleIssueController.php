<?php

namespace App\Http\Controllers;

use App\Models\VehicleIssue;
use App\Models\User;
use App\Services\GoogleAIVehicleDiagnosticService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VehicleIssueController extends Controller
{
    protected $aiService;

    public function __construct(GoogleAIVehicleDiagnosticService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'vehicle_make' => 'required|string|max:100',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'odometer_reading' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get or create a default user
            $user = User::first();
            
            if (!$user) {
                $user = User::create([
                    'name' => 'Demo User',
                    'email' => 'demo@autocare.com',
                    'password' => bcrypt('password'),
                ]);
            }

            $issue = VehicleIssue::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'vehicle_make' => $request->vehicle_make,
                'vehicle_model' => $request->vehicle_model,
                'vehicle_year' => $request->vehicle_year,
                'odometer_reading' => $request->odometer_reading,
                'status' => 'pending'
            ]);

            // Get AI analysis
            try {
                $aiData = $this->aiService->analyzeVehicleIssue([
                    'title' => $issue->title,
                    'description' => $issue->description,
                    'vehicle_make' => $issue->vehicle_make,
                    'vehicle_model' => $issue->vehicle_model,
                    'vehicle_year' => $issue->vehicle_year,
                    'odometer_reading' => $issue->odometer_reading
                ]);

                $issue->update([
                    'ai_analysis' => $aiData['analysis'] ?? null,
                    'ai_recommendations' => $aiData['recommendations'] ?? null,
                    'severity_level' => $aiData['severity'] ?? null,
                    'estimated_cost' => $aiData['estimated_cost_max'] ?? null,
                    'status' => 'analyzed'
                ]);

            } catch (\Exception $e) {
                Log::error('AI Analysis failed: ' . $e->getMessage());
                // Keep issue as pending or mark as failed
                $issue->update(['status' => 'pending']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Issue reported successfully.',
                'data' => $issue->fresh() // Get fresh data with AI analysis
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating vehicle issue: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create issue: ' . $e->getMessage()
            ], 500);
        }
    }


    public function index()
    {
        $issues = VehicleIssue::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $issues
        ]);
    }

    

    public function show(VehicleIssue $vehicleIssue)
    {
        $vehicleIssue->load('user:id,name,email');
        return response()->json([
            'success' => true,
            'data' => $vehicleIssue
        ]);
    }

    public function update(Request $request, VehicleIssue $vehicleIssue)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'in:pending,analyzed,in_progress,resolved',
            'title' => 'string|max:255',
            'description' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $vehicleIssue->update($request->only(['status', 'title', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'Issue updated successfully',
            'data' => $vehicleIssue
        ]);
    }

    public function destroy(VehicleIssue $vehicleIssue)
    {
        $vehicleIssue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Issue deleted successfully'
        ]);
    }

  

    public function requestAnalysis($id)
    {
        $issue = VehicleIssue::findOrFail($id);
        $analysis = $this->getAIAnalysis($issue);

        return response()->json([
            'success' => true,
            'message' => 'AI analysis completed',
            'analysis' => $analysis
        ]);
    }

    protected function getAIAnalysis(VehicleIssue $vehicleIssue)
    {
        try {
            Log::info('Starting AI analysis for issue: ' . $vehicleIssue->id);
            
            $aiData = $this->aiService->analyzeVehicleIssue([
                'title' => $vehicleIssue->title,
                'description' => $vehicleIssue->description,
                'vehicle_make' => $vehicleIssue->vehicle_make,
                'vehicle_model' => $vehicleIssue->vehicle_model,
                'vehicle_year' => $vehicleIssue->vehicle_year,
                'odometer_reading' => $vehicleIssue->odometer_reading
            ]);

            Log::info('AI analysis completed', ['data' => $aiData]);

            $vehicleIssue->update([
                'ai_analysis' => $aiData['analysis'] ?? null,
                'ai_recommendations' => $aiData['recommendations'] ?? null,
                'severity_level' => $aiData['severity'] ?? null,
                'estimated_cost' => $aiData['estimated_cost_max'] ?? null,
                'status' => 'analyzed'
            ]);

            return $aiData;
        } catch (\Exception $e) {
            Log::error('AI Analysis failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'issue_id' => $vehicleIssue->id
            ]);
            
            // Still update with mock data
            $vehicleIssue->update([
                'status' => 'analyzed'
            ]);
            
            return null;
        }
    }

}