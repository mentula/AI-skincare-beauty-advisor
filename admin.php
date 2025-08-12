<?php
// Simple HTTP Basic Auth
$username = 'admin';
$password = 'admin'; // CHANGE THIS TO A SECURE PASSWORD!

if (!isset($_SERVER['PHP_AUTH_USER']) || 
    !isset($_SERVER['PHP_AUTH_PW']) || 
    $_SERVER['PHP_AUTH_USER'] !== $username || 
    $_SERVER['PHP_AUTH_PW'] !== $password) {
    
    header('WWW-Authenticate: Basic realm="Admin Access"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Access denied. Please enter valid credentials.';
    exit;
}

// Load products
$products = [];
$productsFile = 'products.json';

if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true) ?: [];
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newProducts = [];
    
    foreach ($_POST['products'] as $category => $product) {
        $newProducts[$category] = [
            'name' => htmlspecialchars(trim($product['name']), ENT_QUOTES, 'UTF-8'),
            'link' => htmlspecialchars(trim($product['link']), ENT_QUOTES, 'UTF-8')
        ];
    }
    
    // Save to file with locking
    $jsonData = json_encode($newProducts, JSON_PRETTY_PRINT);
    if (file_put_contents($productsFile, $jsonData, LOCK_EX) !== false) {
        $products = $newProducts;
        $message = 'Products updated successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error saving products. Check file permissions.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - AI Skincare Finder</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .admin-header { background: var(--primary-color); color: white; padding: 1rem; text-align: center; }
        .admin-form { max-width: 800px; margin: 2rem auto; }
        .product-row { display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 1rem; align-items: center; margin-bottom: 1rem; padding: 1rem; border: 1px solid #ddd; border-radius: 8px; }
        .product-row label { font-weight: bold; }
        .product-row input { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; width: 100%; }
        .message { padding: 1rem; margin: 1rem 0; border-radius: 4px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn-save { background: var(--primary-color); color: white; padding: 1rem 2rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1.1rem; }
        .btn-save:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>AI Skincare Finder - Admin Panel</h1>
        <p>Manage your affiliate products</p>
    </header>

    <main class="admin-form">
        <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2>Edit Products</h2>
            <form method="POST">
                <?php foreach ($products as $category => $product): ?>
                <div class="product-row">
                    <label><?= ucfirst($category) ?></label>
                    <input type="text" name="products[<?= $category ?>][name]" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Product name" required>
                    <input type="url" name="products[<?= $category ?>][link]" value="<?= htmlspecialchars($product['link']) ?>" placeholder="Affiliate link" required>
                </div>
                <?php endforeach; ?>
                
                <button type="submit" class="btn-save">Save Changes</button>
            </form>
        </div>

        <div class="card">
            <h3>Instructions</h3>
            <ul>
                <li>Replace all affiliate links with your actual Amazon, Sephora, or other affiliate program links</li>
                <li>Update product names to match your affiliate products</li>
                <li>Ensure all links are valid and working</li>
                <li>Test your affiliate links before going live</li>
            </ul>
        </div>
    </main>
</body>
</html>
