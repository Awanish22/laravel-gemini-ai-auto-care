<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Google\Cloud\AIPlatform\V1\Client\PredictionServiceClient;
use Google\Cloud\AIPlatform\V1\PredictRequest;
use Google\ApiCore\ApiException;

class GoogleAIVehicleDiagnosticService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.api_key');
    }

    public function analyzeVehicleIssue(array $issueData): array
    {
        // Check if API key is configured
        if (!$this->apiKey || $this->apiKey === 'your-google-ai-studio-key-here') {
            Log::warning('Google API key not configured');
            return $this->getMockAnalysis($issueData);
        }

        try {
            // Using cURL directly for simplicity (you can also use the Google Cloud client)
            $prompt = $this->buildPrompt($issueData);
            
            $response = $this->callGeminiAPI($prompt);
            
            return $this->parseAIResponse($response);

        } catch (\Exception $e) {
            Log::error('Google AI Analysis Error: ' . $e->getMessage());
            return $this->getMockAnalysis($issueData);
        }
    }

    private function callGeminiAPI(string $prompt): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$this->apiKey}";
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
                'topP' => 0.8,
                'topK' => 40
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error('Google API returned error', ['response' => $response, 'code' => $httpCode]);
            throw new \Exception('API returned error code: ' . $httpCode);
        }

        $result = json_decode($response, true);
        
        // Extract text from Gemini response
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return ['text' => $result['candidates'][0]['content']['parts'][0]['text']];
        }

        throw new \Exception('Unexpected API response format');
    }

    private function buildPrompt(array $data): string
    {
        return sprintf(
            "You are an expert automotive diagnostic specialist. Analyze this vehicle issue and provide a detailed JSON response.

Vehicle Details:
- Make: %s
- Model: %s
- Year: %s
- Odometer: %s

Issue:
Title: %s
Description: %s

Provide your analysis in the following JSON format only (no other text):
{
    \"analysis\": \"Detailed analysis of the issue with probable causes\",
    \"recommendations\": [\"List of 3-5 actionable recommendations\"],
    \"severity\": \"Low/Medium/High/Critical\",
    \"estimated_cost_min\": minimum estimated cost as number,
    \"estimated_cost_max\": maximum estimated cost as number,
    \"urgency\": \"Immediate attention required/Address within days/Schedule routine appointment\"
}",
            $data['vehicle_make'],
            $data['vehicle_model'],
            $data['vehicle_year'],
            $data['odometer_reading'] ?? 'Not provided',
            $data['title'],
            $data['description']
        );
    }

    private function parseAIResponse(array $response): array
    {
        $text = $response['text'] ?? '';
        
        // Try to extract JSON from the response
        preg_match('/\{.*\}/s', $text, $matches);
        
        if (!empty($matches)) {
            $jsonStr = $matches[0];
            $decoded = json_decode($jsonStr, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'analysis' => $decoded['analysis'] ?? 'Analysis completed',
                    'recommendations' => $decoded['recommendations'] ?? [
                        'Consult with a certified mechanic',
                        'Monitor the issue',
                        'Check vehicle documentation'
                    ],
                    'severity' => $decoded['severity'] ?? 'Medium',
                    'estimated_cost_min' => $decoded['estimated_cost_min'] ?? 100,
                    'estimated_cost_max' => $decoded['estimated_cost_max'] ?? 500,
                    'urgency' => $decoded['urgency'] ?? 'Schedule appointment'
                ];
            }
        }

        // If JSON parsing fails, return mock analysis
        Log::warning('Failed to parse AI response as JSON', ['response' => $text]);
        return $this->getMockAnalysisFromText($text);
    }

    private function getMockAnalysisFromText(string $text): array
    {
        // Try to extract information from text response
        return [
            'analysis' => $text,
            'recommendations' => [
                'Schedule a professional diagnostic check',
                'Document when the issue occurs',
                'Check for any warning lights',
                'Consult with mechanic for accurate diagnosis'
            ],
            'severity' => $this->extractSeverity($text),
            'estimated_cost_min' => 100,
            'estimated_cost_max' => 500,
            'urgency' => 'Schedule within 1 week'
        ];
    }

    private function extractSeverity(string $text): string
    {
        $text = strtolower($text);
        if (strpos($text, 'critical') !== false || strpos($text, 'immediate') !== false) {
            return 'Critical';
        }
        if (strpos($text, 'high') !== false || strpos($text, 'urgent') !== false) {
            return 'High';
        }
        if (strpos($text, 'medium') !== false || strpos($text, 'moderate') !== false) {
            return 'Medium';
        }
        return 'Low';
    }

    private function getMockAnalysis(array $data): array
    {
        // Fallback mock analysis
        $severity = $this->determineMockSeverity($data);
        $cost = $this->determineMockCost($severity);
        
        return [
            'analysis' => sprintf(
                "Based on your %d %s %s with the issue '%s', here's our preliminary analysis:\n\n" .
                "The symptoms described suggest potential issues that require professional attention. " .
                "This appears to be a %s severity issue. We recommend having a certified mechanic inspect the vehicle.",
                $data['vehicle_year'],
                $data['vehicle_make'],
                $data['vehicle_model'],
                $data['title'],
                $severity
            ),
            'recommendations' => [
                'Schedule an appointment with a certified mechanic',
                'Document any changes in symptoms',
                'Check vehicle owner\'s manual for guidance',
                'Monitor warning lights if any',
                'Keep maintenance records handy'
            ],
            'severity' => $severity,
            'estimated_cost_min' => $cost['min'],
            'estimated_cost_max' => $cost['max'],
            'urgency' => $severity === 'Critical' ? 'Immediate attention required' : 
                        ($severity === 'High' ? 'Address within days' : 'Schedule routine appointment')
        ];
    }

    private function determineMockSeverity(array $data): string
    {
        $title = strtolower($data['title']);
        $desc = strtolower($data['description']);
        
        $criticalKeywords = ['brake', 'smoke', 'fire', 'overheat', 'noise', 'grinding', 'metal', 'warning', 'stop'];
        foreach ($criticalKeywords as $keyword) {
            if (strpos($title, $keyword) !== false || strpos($desc, $keyword) !== false) {
                return 'Critical';
            }
        }
        
        $highKeywords = ['engine', 'check', 'leak', 'vibration', 'rough', 'stall', 'power', 'loss'];
        foreach ($highKeywords as $keyword) {
            if (strpos($title, $keyword) !== false || strpos($desc, $keyword) !== false) {
                return 'High';
            }
        }
        
        $mediumKeywords = ['squeak', 'rattle', 'slow', 'noise', 'smell', 'click', 'flicker'];
        foreach ($mediumKeywords as $keyword) {
            if (strpos($title, $keyword) !== false || strpos($desc, $keyword) !== false) {
                return 'Medium';
            }
        }
        
        return 'Low';
    }

    private function determineMockCost(string $severity): array
    {
        return match($severity) {
            'Critical' => ['min' => 500, 'max' => 2000],
            'High' => ['min' => 300, 'max' => 1000],
            'Medium' => ['min' => 150, 'max' => 500],
            default => ['min' => 50, 'max' => 300]
        };
    }
}