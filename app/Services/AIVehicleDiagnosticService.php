<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AIVehicleDiagnosticService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout' => 30,
            'verify' => false, // For development only
        ]);
        
        $this->apiKey = config('services.openai.api_key');
        
        Log::info('OpenAI API Key configured: ' . ($this->apiKey ? 'Yes' : 'No'));
    }

    public function analyzeVehicleIssue(array $issueData): array
    {
        // Log the attempt
        Log::info('Attempting AI analysis', ['issue_data' => $issueData]);
        
        // Check if API key exists
        if (!$this->apiKey) {
            Log::error('OpenAI API key not configured');
            return $this->getFallbackResponse('API key not configured');
        }

        try {
            $prompt = $this->buildPrompt($issueData);
            
            Log::info('Sending request to OpenAI', ['prompt' => $prompt]);
            
            $response = $this->client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an experienced automotive diagnostic specialist. Analyze vehicle issues and provide detailed recommendations. Always respond with valid JSON.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            
            Log::info('OpenAI response received', ['result' => $result]);
            
            if (!isset($result['choices'][0]['message']['content'])) {
                Log::error('Unexpected OpenAI response structure', ['result' => $result]);
                return $this->getFallbackResponse('Unexpected API response');
            }
            
            $content = $result['choices'][0]['message']['content'];
            return $this->parseAIResponse($content);

        } catch (RequestException $e) {
            Log::error('OpenAI API Request Error: ' . $e->getMessage(), [
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            
            if ($e->hasResponse()) {
                $response = json_decode($e->getResponse()->getBody(), true);
                if (isset($response['error']['message'])) {
                    return $this->getFallbackResponse('OpenAI Error: ' . $response['error']['message']);
                }
            }
            
            return $this->getFallbackResponse('API connection error');
            
        } catch (\Exception $e) {
            Log::error('AI Analysis General Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getFallbackResponse($e->getMessage());
        }
    }

    private function buildPrompt(array $data): string
    {
        return sprintf(
            "Analyze this vehicle issue and provide a response in valid JSON format only.
            
            Vehicle Details:
            Make: %s
            Model: %s
            Year: %s
            Odometer: %s
            
            Issue:
            Title: %s
            Description: %s
            
            Respond with JSON in this exact format:
            {
                \"analysis\": \"Detailed analysis text\",
                \"recommendations\": [\"Recommendation 1\", \"Recommendation 2\", \"Recommendation 3\"],
                \"severity\": \"Low/Medium/High/Critical\",
                \"estimated_cost_min\": 100,
                \"estimated_cost_max\": 500,
                \"urgency\": \"Urgency text\"
            }",
            $data['vehicle_make'],
            $data['vehicle_model'],
            $data['vehicle_year'],
            $data['odometer_reading'] ?? 'Not specified',
            $data['title'],
            $data['description']
        );
    }

    private function parseAIResponse(string $response): array
    {
        // Try to extract JSON from the response
        $jsonPattern = '/\{.*\}/s';
        if (preg_match($jsonPattern, $response, $matches)) {
            $jsonStr = $matches[0];
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // If JSON parsing fails, create a structured response from text
        Log::warning('Could not parse JSON from response, using fallback', ['response' => $response]);
        
        return [
            'analysis' => $response,
            'recommendations' => [
                'Schedule professional diagnostic check',
                'Monitor for worsening symptoms',
                'Check vehicle manual for guidance',
                'Consult with certified mechanic'
            ],
            'severity' => 'Medium',
            'estimated_cost_min' => 100,
            'estimated_cost_max' => 500,
            'urgency' => 'Schedule diagnostic within 1-2 weeks'
        ];
    }

    private function getFallbackResponse(string $errorReason = ''): array
    {
        return [
            'analysis' => 'AI analysis temporarily unavailable. ' . $errorReason,
            'recommendations' => [
                'Visit a certified mechanic for inspection',
                'Check vehicle owner\'s manual',
                'Monitor symptoms and note any changes',
                'Contact your regular service center'
            ],
            'severity' => 'Unknown',
            'estimated_cost_min' => 0,
            'estimated_cost_max' => 0,
            'urgency' => 'Schedule professional inspection'
        ];
    }
}