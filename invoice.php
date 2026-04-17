<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$order_id = intval($_GET['id'] ?? 0);
$download = isset($_GET['download']) && $_GET['download'] === '1';

if ($order_id <= 0) {
    redirect('/');
}

$is_admin = isAdminLoggedIn();
$user_id = $is_admin ? null : getCurrentUserId();

if (!$is_admin && !$user_id) {
    $_SESSION['redirect_after_login'] = '/invoice.php?id=' . $order_id;
    redirect('/auth/login.php');
}

$order = getOrderDetails($db, $order_id, $user_id);
if (!$order) {
    if ($is_admin) {
        redirect('/admin/orders.php');
    }
    redirect('/pages/account.php');
}

$items = getOrderItems($db, $order_id);

if ($download) {
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="invoice-' . preg_replace('/[^A-Za-z0-9\-]/', '-', $order['order_number']) . '.html"');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo htmlspecialchars($order['order_number']); ?></title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f5;
            color: #111827;
        }

        .invoice-shell {
            max-width: 960px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .invoice-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .action-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 0.85rem 1.2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 700;
            border: 1px solid #d1d5db;
            color: #111827;
            background: #fff;
        }

        .btn-primary {
            background: #111827;
            color: #fff;
            border-color: #111827;
        }

        .invoice-card {
            background: #fff;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            gap: 2rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #111827;
            padding-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .invoice-title {
            margin: 0 0 0.5rem;
            font-size: 2rem;
            letter-spacing: 0.08em;
        }

        .muted {
            color: #6b7280;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .meta-box,
        .address-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 1rem;
        }

        .section-title {
            margin: 0 0 0.75rem;
            font-size: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        th,
        td {
            padding: 0.9rem 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #111827;
            color: #fff;
            font-size: 0.9rem;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            margin-left: auto;
            width: 100%;
            max-width: 340px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .totals-row.total {
            font-size: 1.15rem;
            font-weight: 700;
            border-bottom: 2px solid #111827;
        }

        .invoice-note {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            font-size: 0.95rem;
        }

        @media print {
            body {
                background: #fff;
            }

            .invoice-shell {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .invoice-actions {
                display: none;
            }

            .invoice-card {
                box-shadow: none;
                padding: 0;
            }
        }

        @media (max-width: 640px) {
            .meta-grid {
                grid-template-columns: 1fr;
            }

            .invoice-card {
                padding: 1.25rem;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-shell">
        <?php if (!$download): ?>
            <div class="invoice-actions">
                <div class="action-group">
                    <a href="<?php echo $is_admin ? '/admin/order-details.php?id=' . $order_id : '/pages/order-details.php?id=' . $order_id; ?>"
                        class="btn">Back</a>
                    <a href="/invoice.php?id=<?php echo $order_id; ?>&download=1" class="btn btn-primary">Download Invoice</a>
                </div>
                <div class="action-group">
                    <button onclick="window.print()" class="btn" type="button">Print Invoice</button>
                </div>
            </div>
        <?php endif; ?>

        <div class="invoice-card">
            <div class="invoice-header">
                <div>
                    <p class="muted" style="margin: 0 0 0.5rem;">Issued by</p>
                    <h1 class="invoice-title">INVOICE</h1>
                    <div style="font-size: 1.1rem; font-weight: 700;"><?php echo htmlspecialchars(SITE_NAME); ?></div>
                    <div class="muted"><?php echo htmlspecialchars(ADMIN_EMAIL); ?></div>
                </div>

                <div>
                    <div><strong>Invoice No:</strong> <?php echo htmlspecialchars($order['order_number']); ?></div>
                    <div><strong>Order Date:</strong> <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></div>
                    <div><strong>Payment:</strong> <?php echo strtoupper(htmlspecialchars($order['payment_method'])); ?></div>
                    <div><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?></div>
                </div>
            </div>

            <div class="meta-grid">
                <div class="address-box">
                    <h2 class="section-title">Bill To</h2>
                    <div><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></div>
                    <?php if (!empty($order['customer_email'])): ?>
                        <div><?php echo htmlspecialchars($order['customer_email']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($order['customer_phone'])): ?>
                        <div><?php echo htmlspecialchars($order['customer_phone']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="address-box">
                    <h2 class="section-title">Ship To</h2>
                    <?php if (!empty($order['shipping_name'])): ?>
                        <div><strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong></div>
                        <div><?php echo htmlspecialchars($order['address_line1']); ?></div>
                        <?php if (!empty($order['address_line2'])): ?>
                            <div><?php echo htmlspecialchars($order['address_line2']); ?></div>
                        <?php endif; ?>
                        <div><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['pincode']); ?></div>
                        <div><?php echo htmlspecialchars($order['country']); ?></div>
                        <?php if (!empty($order['shipping_phone'])): ?>
                            <div><?php echo htmlspecialchars($order['shipping_phone']); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="muted">Shipping address unavailable.</div>
                    <?php endif; ?>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Details</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                            </td>
                            <td>
                                Size: <?php echo htmlspecialchars($item['size'] ?: 'N/A'); ?><br>
                                Color: <?php echo htmlspecialchars($item['color'] ?: 'N/A'); ?>
                            </td>
                            <td class="text-right"><?php echo (int) $item['quantity']; ?></td>
                            <td class="text-right"><?php echo formatPrice($item['price']); ?></td>
                            <td class="text-right"><?php echo formatPrice($item['subtotal']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totals">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <strong><?php echo formatPrice($order['subtotal']); ?></strong>
                </div>
                <div class="totals-row">
                    <span>Shipping</span>
                    <strong><?php echo formatPrice($order['shipping_cost']); ?></strong>
                </div>
                <div class="totals-row">
                    <span>Tax</span>
                    <strong><?php echo formatPrice($order['tax']); ?></strong>
                </div>
                <div class="totals-row total">
                    <span>Total</span>
                    <span><?php echo formatPrice($order['total']); ?></span>
                </div>
            </div>

            <div class="invoice-note">
                <strong>Note:</strong> This is a system-generated invoice for order reference and download.
            </div>
        </div>
    </div>
</body>

</html>
