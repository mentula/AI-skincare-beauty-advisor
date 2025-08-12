<?php
// Include helper files
require_once 'ai_helper.php';

// Load products data
$products = [];
if (file_exists('products.json')) {
    $products = json_decode(file_get_contents('products.json'), true) ?: [];
}

// Handle form submission
$results = null;
$userInputs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $skinType = htmlspecialchars($_POST['skinType'] ?? '', ENT_QUOTES, 'UTF-8');
    $mainConcern = htmlspecialchars($_POST['mainConcern'] ?? '', ENT_QUOTES, 'UTF-8');
    $budget = htmlspecialchars($_POST['budget'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8');
    
    $userInputs = [
        'skinType' => $skinType,
        'mainConcern' => $mainConcern,
        'budget' => $budget,
        'email' => $email
    ];
    
    if ($skinType && $mainConcern && $budget) {
        // Build user prompt for AI
        $userPrompt = "Create a personalized skincare routine for someone with {$skinType} skin, main concern: {$mainConcern}, budget: {$budget}. Be friendly and encouraging.";
        
        // Try AI response first, fallback to template
        $aiResponse = get_ai_text($userPrompt);
        
        if ($aiResponse) {
            $results = [
                'type' => 'ai',
                'message' => $aiResponse,
                'products' => $products
            ];
        } else {
            // Fallback template response
            $results = [
                'type' => 'template',
                'message' => generateTemplateResponse($skinType, $mainConcern, $budget),
                'products' => $products
            ];
        }
    }
}

function generateTemplateResponse($skinType, $mainConcern, $budget) {
    $responses = [
        'Oily' => 'For oily skin, focus on gentle cleansing and oil control.',
        'Dry' => 'Dry skin needs extra hydration and gentle, nourishing products.',
        'Combination' => 'Combination skin benefits from balanced products that address multiple concerns.',
        'Sensitive' => 'Sensitive skin requires fragrance-free, gentle formulations.',
        'Normal' => 'Normal skin can handle a variety of products, focus on maintenance.'
    ];
    
    $concernTips = [
        'acne' => 'Look for non-comedogenic products and ingredients like salicylic acid.',
        'aging' => 'Focus on products with retinol, peptides, and antioxidants.',
        'hyperpigmentation' => 'Seek out brightening ingredients like vitamin C and niacinamide.',
        'dryness' => 'Prioritize hydrating ingredients like hyaluronic acid and ceramides.',
        'redness' => 'Choose calming ingredients like aloe vera and centella.'
    ];
    
    $baseResponse = $responses[$skinType] ?? "Based on your {$skinType} skin type, ";
    $concernResponse = $concernTips[$mainConcern] ?? "focus on addressing your {$mainConcern} concern. ";
    $budgetNote = $budget === 'Low' ? "There are great affordable options available!" : 
                  ($budget === 'Medium' ? "You have access to quality mid-range products." : 
                  "You can invest in premium, effective formulations.");
    
    return $baseResponse . $concernResponse . $budgetNote;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Skincare Finder - Your Personalized Routine</title>
    <meta name="description" content="Get your personalized skincare routine in seconds with AI-powered recommendations. Find the perfect products for your skin type and concerns.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-content">
            <h1>AI Skincare Finder</h1>
            <p>Discover your perfect routine in seconds with AI-powered recommendations</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Form Section -->
        <section class="form-section">
            <div class="card">
                <h2>Find Your Perfect Routine</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="skinType">Skin Type *</label>
                        <select name="skinType" id="skinType" required>
                            <option value="">Select your skin type</option>
                            <option value="Oily" <?= ($userInputs['skinType'] ?? '') === 'Oily' ? 'selected' : '' ?>>Oily</option>
                            <option value="Dry" <?= ($userInputs['dryness'] ?? '') === 'Dry' ? 'selected' : '' ?>>Dry</option>
                            <option value="Combination" <?= ($userInputs['skinType'] ?? '') === 'Combination' ? 'selected' : '' ?>>Combination</option>
                            <option value="Sensitive" <?= ($userInputs['skinType'] ?? '') === 'Sensitive' ? 'selected' : '' ?>>Sensitive</option>
                            <option value="Normal" <?= ($userInputs['skinType'] ?? '') === 'Normal' ? 'selected' : '' ?>>Normal</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mainConcern">Main Concern *</label>
                        <select name="mainConcern" id="mainConcern" required>
                            <option value="">Select your main concern</option>
                            <option value="acne" <?= ($userInputs['mainConcern'] ?? '') === 'acne' ? 'selected' : '' ?>>Acne & Breakouts</option>
                            <option value="aging" <?= ($userInputs['mainConcern'] ?? '') === 'aging' ? 'selected' : '' ?>>Aging & Wrinkles</option>
                            <option value="hyperpigmentation" <?= ($userInputs['mainConcern'] ?? '') === 'hyperpigmentation' ? 'selected' : '' ?>>Dark Spots & Hyperpigmentation</option>
                            <option value="dryness" <?= ($userInputs['mainConcern'] ?? '') === 'dryness' ? 'selected' : '' ?>>Dryness & Dehydration</option>
                            <option value="redness" <?= ($userInputs['mainConcern'] ?? '') === 'redness' ? 'selected' : '' ?>>Redness & Irritation</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="budget">Budget *</label>
                        <select name="budget" id="budget" required>
                            <option value="">Select your budget</option>
                            <option value="Low" <?= ($userInputs['budget'] ?? '') === 'Low' ? 'selected' : '' ?>>Low ($10-30)</option>
                            <option value="Medium" <?= ($userInputs['budget'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium ($30-80)</option>
                            <option value="High" <?= ($userInputs['budget'] ?? '') === 'High' ? 'selected' : '' ?>>High ($80+)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email (Optional)</label>
                        <input type="email" name="email" id="email" value="<?= $userInputs['email'] ?? '' ?>" placeholder="Get your routine emailed to you">
                    </div>

                    <button type="submit" class="btn-primary">Get My Routine</button>
                </form>
            </div>
        </section>

        <!-- Results Section -->
        <?php if ($results): ?>
        <section class="results-section">
            <div class="card results">
                <h2>Your Personalized Skincare Routine</h2>
                
                <div class="ai-response">
                    <p><?= $results['message'] ?></p>
                </div>

                <div class="products-grid">
                    <h3>Recommended Products</h3>
                    <div class="product-list">
                        <?php foreach ($results['products'] as $category => $product): ?>
                        <div class="product-item">
                            <div class="product-info">
                                <h4><?= htmlspecialchars($product['name']) ?></h4>
                                <p class="category"><?= ucfirst($category) ?></p>
                            </div>
                            <a href="<?= htmlspecialchars($product['link']) ?>" target="_blank" rel="noopener" class="btn-secondary">
                                View Product
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="routine-tips">
                    <h3>How to Use Your Routine</h3>
                    <ol>
                        <li><strong>Morning:</strong> Cleanser → Serum → Moisturizer → SPF</li>
                        <li><strong>Evening:</strong> Cleanser → Serum → Moisturizer</li>
                        <li><strong>Weekly:</strong> Add foundation and mascara as needed</li>
                    </ol>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 AI Skincare Finder. This site contains affiliate links. We may earn a commission from qualifying purchases at no extra cost to you.</p>
        </div>
    </footer>

    <!-- Admin: /admin.php -->
</body>
</html>
