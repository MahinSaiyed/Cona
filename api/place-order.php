<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Please login first');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request');
}

$user_id = getCurrentUserId();
$address_id = intval($_POST['address_id'] ?? 0);
$payment_method = sanitize($_POST['payment_method'] ?? '');

// Validate inputs
if (!in_array($payment_method, ['card', 'whatsapp', 'cod'])) {
    jsonResponse(false, 'Invalid payment method');
}

// GetCart items
$stmt = $db->prepare("
    SELECT c.*, p.name, p.price, p.stock, p.main_image, p.brand
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Get the address to use
$address = null;
if ($address_id > 0) {
    // Try to get specifically selected address
    $stmt = $db->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    $address = $stmt->fetch();
} else {
    // Try to get default address as fallback
    $stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $stmt->execute([$user_id]);
    $address = $stmt->fetch();

    // If still no default, just get the most recently added one
    if (!$address) {
        $stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $address = $stmt->fetch();
    }
}

if (!$address) {
    jsonResponse(false, 'Please add a delivery address before placing your order');
}
$address_id = $address['id']; // Ensure we have the actual ID

if (empty($cart_items)) {
    jsonResponse(false, 'Cart is empty');
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    // Check stock
    if ($item['stock'] < $item['quantity']) {
        jsonResponse(false, $item['name'] . ' is out of stock');
    }
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = calculateShipping($subtotal);
$tax = calculateTax($subtotal);
$total = $subtotal + $shipping + $tax;

// Create order
$order_number = generateOrderNumber();

try {
    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO orders (user_id, order_number, address_id, subtotal, shipping_cost, tax, total, payment_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $order_number, $address_id, $subtotal, $shipping, $tax, $total, $payment_method]);
    $order_id = $db->lastInsertId();

    // Create order items
    foreach ($cart_items as $item) {
        $stmt = $db->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_image, size, color, quantity, price, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['name'],
            $item['main_image'],
            $item['size'],
            $item['color'],
            $item['quantity'],
            $item['price'],
            $item['price'] * $item['quantity']
        ]);

        // Update product stock
        $stmt = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Clear cart
    $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Create payment record
    $stmt = $db->prepare("INSERT INTO payments (order_id, payment_method, amount) VALUES (?, ?, ?)");
    $stmt->execute([$order_id, $payment_method, $total]);

    $db->commit();

    // Redirect based on payment method
    if ($payment_method === 'card') {
        jsonResponse(true, 'Order placed', [
            'redirect' => '/payments/card-payment.php?order_id=' . $order_id
        ]);
    } else if ($payment_method === 'whatsapp') {
        jsonResponse(true, 'Order placed', [
            'redirect' => '/payments/whatsapp-payment.php?order_id=' . $order_id
        ]);
    } else {
        jsonResponse(true, 'Order placed successfully', [
            'redirect' => '/pages/order-success.php?order_number=' . $order_number
        ]);
    }

} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(false, 'Failed to place order: ' . $e->getMessage());
}
?>