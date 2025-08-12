<?php
/**
 * AI Helper for Skincare Recommendations
 * Requires OPENAI_API_KEY to be set in environment variables
 * 
 * Rate Limits: OpenAI has rate limits based on your plan
 * Costs: Approximately $0.002 per 1K tokens for GPT-4o-mini
 */

function get_ai_text($prompt) {
    // Check if API key is available
    $apiKey = getenv('OPENAI_API_KEY');
    if (!$apiKey) {
        return false; // No API key, fallback to template
    }
    
    // Prepare the API request
    $data = [
        'model' => 'gpt-4o-mini', // Use mini for cost efficiency
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a friendly, knowledgeable skincare consultant. Provide personalized, encouraging advice. Keep responses under 150 words. Be warm and supportive.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 200,
        'temperature' => 0.7
    ];
    
    // Make API call using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Handle errors
    if ($httpCode !== 200 || !$response) {
        error_log("OpenAI API error: HTTP $httpCode - $response");
        return false;
    }
    
    // Parse response
    $responseData = json_decode($response, true);
    if (!isset($responseData['choices'][0]['message']['content'])) {
        error_log("OpenAI API response format error: " . $response);
        return false;
    }
    
    return trim($responseData['choices'][0]['message']['content']);
}

// Alternative function for testing without API key
function get_mock_ai_text($prompt) {
    $responses = [
        'oily' => "For oily skin, I recommend starting with a gentle foaming cleanser to remove excess oil without stripping your skin. Follow with a lightweight, oil-free serum containing niacinamide to regulate sebum production. Finish with a gel-based moisturizer that hydrates without feeling heavy. Don't forget SPF in the morning!",
        'dry' => "Dry skin needs extra love! Start with a creamy, hydrating cleanser that won't strip your natural oils. Use a rich serum with hyaluronic acid to draw moisture into your skin. Follow with a thick, nourishing moisturizer containing ceramides to strengthen your skin barrier. Your skin will thank you!",
        'combination' => "Combination skin can be tricky, but we've got this! Use a gentle, pH-balanced cleanser that works for both areas. Apply a lightweight serum that addresses your main concern. Use a medium-weight moisturizer that provides hydration without being too heavy for your T-zone.",
        'sensitive' => "Sensitive skin requires extra care. Choose fragrance-free, gentle products with minimal ingredients. Start with a mild, non-foaming cleanser. Use a calming serum with ingredients like aloe vera or centella. Finish with a gentle, hypoallergenic moisturizer. Always patch test new products!",
        'normal' => "Lucky you! Normal skin can handle a variety of products. Focus on maintaining your skin's health with a gentle cleanser, antioxidant-rich serum, and lightweight moisturizer. Don't forget daily SPF to prevent future damage. Keep it simple and consistent!"
    ];
    
    $promptLower = strtolower($prompt);
    foreach ($responses as $skinType => $response) {
        if (strpos($promptLower, $skinType) !== false) {
            return $response;
        }
    }
    
    return "Based on your skin profile, I recommend starting with a gentle cleanser, followed by a targeted serum for your main concern, and finishing with a moisturizer that matches your skin type. Consistency is key to seeing results!";
}
?>
