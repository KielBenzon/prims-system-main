<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AzureVisionService
{
    private $client;
    private $endpoint;
    private $key;

    public function __construct()
    {
        $this->endpoint = env('AZURE_COMPUTER_VISION_ENDPOINT');
        $this->key = env('AZURE_COMPUTER_VISION_KEY');
        $this->client = new Client(['timeout' => 30]);
    }

    /**
     * Extract text from image using Azure Read API (OCR)
     * Supports: JPG, PNG, BMP, PDF, TIFF
     */
    public function extractTextFromImage($imagePath)
    {
        try {
            Log::info('Azure Vision: Starting OCR for file: ' . basename($imagePath));

            // Step 1: Submit image for OCR processing
            $url = $this->endpoint . 'vision/v3.2/read/analyze';
            
            $response = $this->client->post($url, [
                'headers' => [
                    'Ocp-Apim-Subscription-Key' => $this->key,
                    'Content-Type' => 'application/octet-stream'
                ],
                'body' => fopen($imagePath, 'r')
            ]);

            // Get operation location from response headers
            $operationLocation = $response->getHeader('Operation-Location')[0];
            Log::info('Azure Vision: Operation submitted, polling for results...');

            // Step 2: Poll for results (Azure processes asynchronously)
            $maxAttempts = 10;
            $attempt = 0;
            $result = null;

            while ($attempt < $maxAttempts) {
                sleep(1); // Wait 1 second between polls
                
                $resultResponse = $this->client->get($operationLocation, [
                    'headers' => [
                        'Ocp-Apim-Subscription-Key' => $this->key
                    ]
                ]);

                $result = json_decode($resultResponse->getBody(), true);

                if ($result['status'] === 'succeeded') {
                    Log::info('Azure Vision: OCR completed successfully');
                    break;
                } elseif ($result['status'] === 'failed') {
                    Log::error('Azure Vision: OCR failed');
                    return '';
                }

                $attempt++;
            }

            // Step 3: Extract text from results
            if ($result && $result['status'] === 'succeeded') {
                $extractedText = '';
                
                foreach ($result['analyzeResult']['readResults'] as $page) {
                    foreach ($page['lines'] as $line) {
                        $extractedText .= $line['text'] . "\n";
                    }
                }

                Log::info('Azure Vision: Extracted ' . strlen($extractedText) . ' characters');
                return $extractedText;
            }

            Log::warning('Azure Vision: OCR timed out or failed');
            return '';

        } catch (\Exception $e) {
            Log::error('Azure Vision API error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Classify document type based on extracted text
     * Uses keyword scoring algorithm
     */
    public function classifyDocument($text)
    {
        $text = strtolower(preg_replace('/\s+/', ' ', $text));

        // PRIORITY: Check for explicit title mentions first
        if (preg_match('/certificate of death|death certificate/', $text)) {
            return 'Death Certificate';
        }
        if (preg_match('/certificate of marriage|marriage certificate/', $text)) {
            return 'Marriage Certificate';
        }
        if (preg_match('/baptismal certificate|certificate of baptism/', $text)) {
            return 'Baptismal Certificate';
        }
        if (preg_match('/confirmation certificate/', $text)) {
            return 'Confirmation Certificate';
        }

        // WEIGHTED KEYWORD SCORING
        $scores = [
            'Death Certificate' => 0,
            'Marriage Certificate' => 0,
            'Baptismal Certificate' => 0,
            'Confirmation Certificate' => 0,
        ];

        // Death Certificate keywords
        $deathKeywords = ['death', 'deceased', 'burial', 'cause of death', 'died', 'demise'];
        foreach ($deathKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $scores['Death Certificate'] += 5;
            }
        }

        // Marriage Certificate keywords
        $marriageKeywords = ['marriage', 'married', 'bride', 'groom', 'wedding', 'spouse'];
        foreach ($marriageKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $scores['Marriage Certificate'] += 4;
            }
        }

        // Baptismal Certificate keywords
        $baptismalKeywords = ['baptism', 'baptized', 'godparent', 'godfather', 'godmother', 'christening'];
        foreach ($baptismalKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $scores['Baptismal Certificate'] += 4;
            }
        }

        // Confirmation Certificate keywords
        $confirmationKeywords = ['confirmation', 'confirmed', 'confirmand'];
        foreach ($confirmationKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $scores['Confirmation Certificate'] += 4;
            }
        }

        // Sort by highest score
        arsort($scores);
        $bestMatch = array_key_first($scores);

        // Return best match only if it has a positive score
        // Otherwise return 'Verification Certificate' (unrecognized)
        return $scores[$bestMatch] > 0 ? $bestMatch : 'Verification Certificate';
    }

    /**
     * Check if Azure Computer Vision is configured
     */
    public static function isConfigured()
    {
        return !empty(env('AZURE_COMPUTER_VISION_KEY')) && 
               !empty(env('AZURE_COMPUTER_VISION_ENDPOINT'));
    }
}
