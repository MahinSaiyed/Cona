<?php
/*
 * One-Time Setup Script
 * Run this file once to set up the database and initial data
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup - Cona Store</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 2rem auto; padding: 2rem; }
        .success { color: green; padding: 0.5rem; background: #e8f5e9; border-left: 4px solid green; margin: 0.5rem 0; }
        .error { color: red; padding: 0.5rem; background: #ffebee; border-left: 4px solid red; margin: 0.5rem 0; }
        .info { color: blue; padding: 0.5rem; background: #e3f2fd; border-left: 4px solid blue; margin: 0.5rem 0; }
        h1 { color: #333; }
        pre { background: #f5f5f5; padding: 1rem; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🚀 Cona Store Setup</h1>";

try {
    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');

    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    echo "<div class='info'><strong>Executing database schema...</strong></div>";

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                if (php_sapi_name() === "cli") echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
                else echo "<div class='success'>✓ Executed statement</div>";
            } catch (PDOException $e) {
                if (php_sapi_name() === "cli") echo "✗ Error: " . $e->getMessage() . "\n";
                else echo "<div class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }

    echo "<div class='success'><strong>✓ Database setup completed!</strong></div>";

    // Insert sample products
    echo "<div class='info'><strong>Adding sample products...</strong></div>";

    $sample_products = [
        [
            'name' => 'Air Jordan 1 High',
            'slug' => 'air-jordan-1-high',
            'brand' => 'Nike',
            'category_id' => 1,
            'description' => 'Classic basketball sneaker with iconic design',
            'price' => 12995,
            'original_price' => 14995,
            'stock' => 20,
            'sku' => 'AJ1-001',
            'sizes' => '["7","8","9","10","11","12"]',
            'colors' => '["Black/Red","White/Black"]',
            'images' => '["https://images.pexels.com/photos/1240892/pexels-photo-1240892.jpeg", "https://images.pexels.com/photos/1598505/pexels-photo-1598505.jpeg", "https://images.pexels.com/photos/1478442/pexels-photo-1478442.jpeg", "https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg"]',
            'main_image' => 'https://images.pexels.com/photos/1240892/pexels-photo-1240892.jpeg',
            'is_featured' => 1,
            'is_new_arrival' => 1,
            'gender' => 'unisex'
        ],
        [
            'name' => 'Adidas Yeezy Boost 350',
            'slug' => 'yeezy-boost-350',
            'brand' => 'Adidas',
            'category_id' => 1,
            'description' => 'Modern sneaker with comfort and style',
            'price' => 19999,
            'stock' => 15,
            'sku' => 'YZ350-001',
            'sizes' => '["7","8","9","10","11"]',
            'colors' => '["Cream White","Black"]',
            'images' => '["yeezy350_front.jpg", "yeezy350_back.jpg", "yeezy350_right.jpg"]',
            'main_image' => 'yeezy350_front.jpg',
            'is_featured' => 1,
            'is_new_arrival' => 1,
            'gender' => 'unisex'
        ],
        [
            'name' => 'Superkicks Graphic Tee',
            'slug' => 'superkicks-graphic-tee',
            'brand' => 'Cona Store',
            'category_id' => 2,
            'description' => 'Premium cotton graphic t-shirt with signature branding.',
            'price' => 2499,
            'stock' => 50,
            'sku' => 'SK-TEE-001',
            'sizes' => '["S","M","L","XL"]',
            'colors' => '["Black","White"]',
            'images' => '["tee_front.jpg", "tee_back.jpg", "tee_right.jpg"]',
            'main_image' => 'tee_front.jpg',
            'is_featured' => 1,
            'is_new_arrival' => 1,
            'gender' => 'unisex'
        ],
        [
            'name' => 'Essential Cargo Pants',
            'slug' => 'essential-cargo-pants',
            'brand' => 'Cona Store',
            'category_id' => 2,
            'description' => 'Functional and stylish cargo pants for everyday wear.',
            'price' => 4999,
            'stock' => 30,
            'sku' => 'SK-CP-001',
            'sizes' => '["30","32","34","36"]',
            'colors' => '["Olive","Black"]',
            'images' => '["cargo_front.jpg", "cargo_back.jpg", "cargo_right.jpg"]',
            'main_image' => 'cargo_front.jpg',
            'is_featured' => 0,
            'is_new_arrival' => 1,
            'gender' => 'men'
        ],
        [
            'name' => 'Classic Signature Cap',
            'slug' => 'classic-signature-cap',
            'brand' => 'Cona Store',
            'category_id' => 3,
            'description' => 'Adjustable signature cap with embroidered logo.',
            'price' => 1499,
            'stock' => 100,
            'sku' => 'SK-CAP-001',
            'sizes' => '["One Size"]',
            'colors' => '["Navy","Black"]',
            'images' => '["cap_front.jpg", "cap_back.jpg", "cap_right.jpg"]',
            'main_image' => 'cap_front.jpg',
            'is_featured' => 1,
            'is_new_arrival' => 0,
            'gender' => 'unisex'
        ],
        [
            'name' => 'Urban Commuter Backpack',
            'slug' => 'urban-commuter-backpack',
            'brand' => 'Cona Store',
            'category_id' => 3,
            'description' => 'Durable and spacious backpack for urban exploration.',
            'price' => 5999,
            'stock' => 25,
            'sku' => 'SK-BP-001',
            'sizes' => '["One Size"]',
            'colors' => '["Grey","Black"]',
            'images' => '["backpack_front.jpg", "backpack_back.jpg", "backpack_right.jpg"]',
            'main_image' => 'backpack_front.jpg',
            'is_featured' => 1,
            'is_new_arrival' => 1,
            'gender' => 'unisex'
        ],
        [
            'name' => 'Over-sized Street Hoodie',
            'slug' => 'oversized-street-hoodie',
            'brand' => 'Cona Store',
            'category_id' => 2,
            'description' => 'Comfortable over-sized hoodie with minimalist design.',
            'price' => 3999,
            'stock' => 40,
            'sku' => 'SK-HD-001',
            'sizes' => '["S","M","L","XL"]',
            'colors' => '["Beige","Black"]',
            'images' => '["hoodie_front.jpg", "hoodie_back.jpg", "hoodie_right.jpg"]',
            'main_image' => 'hoodie_front.jpg',
            'is_featured' => 1,
            'is_new_arrival' => 1,
            'gender' => 'unisex'
        ],
        [
            'name' => 'Tech Utility Jacket',
            'slug' => 'tech-utility-jacket',
            'brand' => 'Cona Store',
            'category_id' => 2,
            'description' => 'Water-resistant utility jacket with multiple pockets.',
            'price' => 7499,
            'stock' => 20,
            'sku' => 'SK-JKT-001',
            'sizes' => '["M","L","XL"]',
            'colors' => '["Grey","Olive"]',
            'images' => '["jacket_front.jpg", "jacket_back.jpg", "jacket_right.jpg"]',
            'main_image' => 'jacket_front.jpg',
            'is_featured' => 1,
            'is_new_arrival' => 0,
            'gender' => 'men'
        ],
        [
            'name' => 'Logo Crew Socks (3-Pack)',
            'slug' => 'logo-crew-socks',
            'brand' => 'Superkicks',
            'category_id' => 3,
            'description' => 'Premium cotton crew socks with embroidered logo.',
            'price' => 999,
            'stock' => 200,
            'sku' => 'SK-SOX-001',
            'sizes' => '["One Size"]',
            'colors' => '["White","Black"]',
            'images' => '["socks_front.jpg", "socks_back.jpg", "socks_right.jpg"]',
            'main_image' => 'socks_front.jpg',
            'is_featured' => 0,
            'is_new_arrival' => 1,
            'gender' => 'unisex'
        ],
        [
            'name' => 'Retro Shield Sunglasses',
            'slug' => 'retro-shield-sunglasses',
            'brand' => 'Superkicks',
            'category_id' => 3,
            'description' => 'Vintage-inspired shield sunglasses with UV protection.',
            'price' => 2999,
            'stock' => 35,
            'sku' => 'SK-SUN-001',
            'sizes' => '["One Size"]',
            'colors' => '["Silver","Black"]',
            'images' => '["sun_front.jpg", "sun_back.jpg", "sun_right.jpg"]',
            'main_image' => 'sun_front.jpg',
            'is_featured' => 1,
            'is_new_arrival' => 1,
            'gender' => 'unisex'
        ]
    ];

    foreach ($sample_products as $product) {
        // Ensure all keys exist for the prepared statement
        $product['original_price'] = $product['original_price'] ?? $product['price'];
        $product['is_featured'] = $product['is_featured'] ?? 0;
        $product['is_new_arrival'] = $product['is_new_arrival'] ?? 0;

        try {
            $stmt = $db->prepare("
                INSERT INTO products (name, slug, brand, category_id, description, price, original_price, stock, sku, sizes, colors, images, main_image, is_featured, is_new_arrival, gender)
                VALUES (:name, :slug, :brand, :category_id, :description, :price, :original_price, :stock, :sku, :sizes, :colors, :images, :main_image, :is_featured, :is_new_arrival, :gender)
            ");
            $stmt->execute($product);
            echo "<div class='success'>✓ Added product: {$product['name']}</div>";
        } catch (PDOException $e) {
            echo "<div class='error'>✗ Failed to add {$product['name']}: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }

    echo "
    <h2>🎉 Setup Complete!</h2>
    
    <div class='info'>
        <h3>Default Admin Credentials:</h3>
        <pre>
Username: admin
Password: admin123
Admin URL: <a href='/admin/login.php'>http://localhost:8000/admin/login.php</a>
        </pre>
    </div>
    
    <div class='info'>
        <h3>Next Steps:</h3>
        <ol>
            <li>Configure Google OAuth credentials in <code>config/config.php</code></li>
            <li>Configure Razorpay credentials in <code>config/config.php</code></li>
            <li>Update WhatsApp number in <code>config/config.php</code></li>
            <li>Add product images to <code>uploads/products/</code> directory</li>
            <li>Change the default admin password from the admin panel</li>
        </ol>
    </div>
    
    <div class='success'>
        <strong>Your store is ready!</strong><br>
        Visit: <a href='/'>http://localhost:8000/</a>
    </div>
    
    <p style='color: red; font-weight: bold;'>⚠️ IMPORTANT: Delete or rename this setup.php file after setup is complete for security!</p>
    ";

} catch (PDOException $e) {
    echo "<div class='error'><strong>Database connection failed:</strong><br>" . htmlspecialchars($e->getMessage()) . "</div>";
    echo "
    <div class='info'>
        <h3>Please check:</h3>
        <ul>
            <li>MySQL is running</li>
            <li>Database name 'conastore_db' exists (or create it)</li>
            <li>Database credentials in <code>config/database.php</code> are correct</li>
        </ul>
        
        <h4>To create the database, run this in MySQL:</h4>
        <pre>CREATE DATABASE conastore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>
    </div>
    ";
}

echo "</body></html>";
?>