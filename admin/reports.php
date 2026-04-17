<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

$current_year = (int) date('Y');
$selected_year = intval($_GET['year'] ?? $current_year);
$selected_month = intval($_GET['month'] ?? (int) date('n'));

if ($selected_month < 1 || $selected_month > 12) {
    $selected_month = (int) date('n');
}

$years_stmt = $db->query("SELECT DISTINCT YEAR(created_at) AS year_num FROM orders ORDER BY year_num DESC");
$available_years = array_map('intval', array_column($years_stmt->fetchAll(), 'year_num'));

if (empty($available_years)) {
    $available_years = [$current_year];
}

if (!in_array($selected_year, $available_years, true)) {
    $selected_year = $available_years[0];
}

$month_names = [
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
];

$summary_stmt = $db->prepare("
    SELECT
        COUNT(*) AS order_count,
        COALESCE(SUM(total), 0) AS revenue,
        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END), 0) AS paid_revenue,
        COALESCE(SUM(CASE WHEN order_status = 'delivered' THEN total ELSE 0 END), 0) AS delivered_revenue
    FROM orders
    WHERE YEAR(created_at) = ?
");
$summary_stmt->execute([$selected_year]);
$year_summary = $summary_stmt->fetch();

$month_summary_stmt = $db->prepare("
    SELECT
        COUNT(*) AS order_count,
        COALESCE(SUM(total), 0) AS revenue,
        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END), 0) AS paid_revenue,
        COALESCE(SUM(CASE WHEN order_status = 'delivered' THEN total ELSE 0 END), 0) AS delivered_revenue
    FROM orders
    WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
");
$month_summary_stmt->execute([$selected_year, $selected_month]);
$month_summary = $month_summary_stmt->fetch();

$monthly_stmt = $db->prepare("
    SELECT
        MONTH(created_at) AS month_num,
        COUNT(*) AS order_count,
        COALESCE(SUM(total), 0) AS revenue
    FROM orders
    WHERE YEAR(created_at) = ?
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
");
$monthly_stmt->execute([$selected_year]);
$monthly_rows = $monthly_stmt->fetchAll();

$monthly_index = [];
foreach ($monthly_rows as $row) {
    $monthly_index[(int) $row['month_num']] = $row;
}

$monthly_chart_labels = [];
$monthly_chart_orders = [];
$monthly_chart_revenue = [];
$monthly_table = [];

for ($month = 1; $month <= 12; $month++) {
    $row = $monthly_index[$month] ?? ['order_count' => 0, 'revenue' => 0];
    $monthly_chart_labels[] = substr($month_names[$month], 0, 3);
    $monthly_chart_orders[] = (int) $row['order_count'];
    $monthly_chart_revenue[] = (float) $row['revenue'];
    $monthly_table[] = [
        'label' => $month_names[$month],
        'order_count' => (int) $row['order_count'],
        'revenue' => (float) $row['revenue']
    ];
}

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year);
$daily_stmt = $db->prepare("
    SELECT
        DAY(created_at) AS day_num,
        COUNT(*) AS order_count,
        COALESCE(SUM(total), 0) AS revenue
    FROM orders
    WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
    GROUP BY DAY(created_at)
    ORDER BY DAY(created_at)
");
$daily_stmt->execute([$selected_year, $selected_month]);
$daily_rows = $daily_stmt->fetchAll();

$daily_index = [];
foreach ($daily_rows as $row) {
    $daily_index[(int) $row['day_num']] = $row;
}

$daily_chart_labels = [];
$daily_chart_orders = [];
$daily_chart_revenue = [];

for ($day = 1; $day <= $days_in_month; $day++) {
    $row = $daily_index[$day] ?? ['order_count' => 0, 'revenue' => 0];
    $daily_chart_labels[] = (string) $day;
    $daily_chart_orders[] = (int) $row['order_count'];
    $daily_chart_revenue[] = (float) $row['revenue'];
}

$yearly_stmt = $db->query("
    SELECT
        YEAR(created_at) AS year_num,
        COUNT(*) AS order_count,
        COALESCE(SUM(total), 0) AS revenue
    FROM orders
    GROUP BY YEAR(created_at)
    ORDER BY YEAR(created_at)
");
$yearly_rows = $yearly_stmt->fetchAll();

$yearly_chart_labels = [];
$yearly_chart_orders = [];
$yearly_chart_revenue = [];

foreach ($yearly_rows as $row) {
    $yearly_chart_labels[] = (string) $row['year_num'];
    $yearly_chart_orders[] = (int) $row['order_count'];
    $yearly_chart_revenue[] = (float) $row['revenue'];
}

$status_stmt = $db->prepare("
    SELECT order_status, COUNT(*) AS total_count
    FROM orders
    WHERE YEAR(created_at) = ?
    GROUP BY order_status
    ORDER BY total_count DESC
");
$status_stmt->execute([$selected_year]);
$status_breakdown = $status_stmt->fetchAll();

$payment_stmt = $db->prepare("
    SELECT payment_method, COUNT(*) AS total_count, COALESCE(SUM(total), 0) AS revenue
    FROM orders
    WHERE YEAR(created_at) = ?
    GROUP BY payment_method
    ORDER BY total_count DESC
");
$payment_stmt->execute([$selected_year]);
$payment_breakdown = $payment_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .admin-sidebar {
            background: var(--color-black);
            color: var(--color-white);
            padding: 2rem 0;
        }

        .admin-sidebar h2 {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
        }

        .admin-menu {
            list-style: none;
        }

        .admin-menu a {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--color-white);
            transition: background var(--transition-fast);
        }

        .admin-menu a:hover,
        .admin-menu a.active {
            background: var(--color-gray);
        }

        .admin-content {
            padding: 2rem;
            background: var(--color-light-gray);
        }

        .admin-header {
            background: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: end;
            margin-bottom: 2rem;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .filter-group select {
            min-width: 180px;
            padding: 0.8rem;
            border: 1px solid var(--color-border);
            background: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-left: 4px solid var(--color-red);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .stat-label {
            color: var(--color-gray);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: var(--font-weight-bold);
            color: var(--color-black);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .card-title {
            margin: 0 0 1rem;
            font-size: 1.1rem;
        }

        .chart-subtitle {
            margin: -0.25rem 0 1rem;
            color: var(--color-gray);
            font-size: 0.9rem;
        }

        .chart-wrap {
            position: relative;
            height: 320px;
        }

        .split-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .metric-list {
            display: grid;
            gap: 0.75rem;
        }

        .metric-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1rem;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
        }

        .data-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 0.9rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--color-border);
        }

        .data-table th {
            background: var(--color-light-gray);
            font-weight: var(--font-weight-semibold);
        }

        .table-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 1100px) {
            .content-grid,
            .split-grid,
            .table-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }

            .filter-group select {
                min-width: 100%;
            }

            .filter-form {
                align-items: stretch;
            }
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1 style="margin: 0;">Reports</h1>
                    <div style="color: var(--color-gray); margin-top: 0.35rem;">Yearly aur monthly business performance ek hi jagah.</div>
                </div>
            </div>

            <form class="filter-form" method="GET">
                <div class="filter-group">
                    <label for="year">Year Report</label>
                    <select id="year" name="year">
                        <?php foreach ($available_years as $year_option): ?>
                            <option value="<?php echo $year_option; ?>" <?php echo $selected_year === $year_option ? 'selected' : ''; ?>>
                                <?php echo $year_option; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="month">Month Report</label>
                    <select id="month" name="month">
                        <?php foreach ($month_names as $month_number => $month_label): ?>
                            <option value="<?php echo $month_number; ?>" <?php echo $selected_month === $month_number ? 'selected' : ''; ?>>
                                <?php echo $month_label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Show Report</button>
                </div>
            </form>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label"><?php echo $selected_year; ?> Total Orders</div>
                    <div class="stat-value"><?php echo (int) $year_summary['order_count']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label"><?php echo $selected_year; ?> Revenue</div>
                    <div class="stat-value"><?php echo formatPrice($year_summary['revenue']); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label"><?php echo $month_names[$selected_month]; ?> Orders</div>
                    <div class="stat-value"><?php echo (int) $month_summary['order_count']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label"><?php echo $month_names[$selected_month]; ?> Revenue</div>
                    <div class="stat-value"><?php echo formatPrice($month_summary['revenue']); ?></div>
                </div>
            </div>

            <div class="content-grid">
                <section class="card">
                    <h2 class="card-title">Monthly Graph - <?php echo $selected_year; ?></h2>
                    <p class="chart-subtitle">Har month ke orders aur revenue ka trend.</p>
                    <div class="chart-wrap">
                        <canvas id="yearChart"></canvas>
                    </div>
                </section>

                <section class="card">
                    <h2 class="card-title">Year Snapshot</h2>
                    <div class="metric-list">
                        <div class="metric-row">
                            <span>Paid Revenue</span>
                            <strong><?php echo formatPrice($year_summary['paid_revenue']); ?></strong>
                        </div>
                        <div class="metric-row">
                            <span>Delivered Revenue</span>
                            <strong><?php echo formatPrice($year_summary['delivered_revenue']); ?></strong>
                        </div>
                        <div class="metric-row">
                            <span>Average Order Value</span>
                            <strong><?php echo formatPrice(($year_summary['order_count'] ?? 0) > 0 ? ($year_summary['revenue'] / $year_summary['order_count']) : 0); ?></strong>
                        </div>
                        <div class="metric-row">
                            <span>Best Month</span>
                            <strong>
                                <?php
                                $best_month_index = 1;
                                $best_month_revenue = 0;
                                foreach ($monthly_table as $index => $month_row) {
                                    if ($month_row['revenue'] >= $best_month_revenue) {
                                        $best_month_revenue = $month_row['revenue'];
                                        $best_month_index = $index + 1;
                                    }
                                }
                                echo htmlspecialchars($month_names[$best_month_index]);
                                ?>
                            </strong>
                        </div>
                    </div>
                </section>
            </div>

            <div class="split-grid">
                <section class="card">
                    <h2 class="card-title">Daily Graph - <?php echo $month_names[$selected_month] . ' ' . $selected_year; ?></h2>
                    <p class="chart-subtitle">Selected month ke daily orders aur revenue.</p>
                    <div class="chart-wrap">
                        <canvas id="monthChart"></canvas>
                    </div>
                </section>

                <section class="card">
                    <h2 class="card-title">Yearly Growth Graph</h2>
                    <p class="chart-subtitle">Saalo ke hisaab se overall growth.</p>
                    <div class="chart-wrap">
                        <canvas id="allYearsChart"></canvas>
                    </div>
                </section>
            </div>

            <div class="table-grid">
                <section class="card">
                    <h2 class="card-title">Monthly Breakdown - <?php echo $selected_year; ?></h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthly_table as $month_row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($month_row['label']); ?></td>
                                    <td><?php echo $month_row['order_count']; ?></td>
                                    <td><?php echo formatPrice($month_row['revenue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <section class="card">
                    <h2 class="card-title">Status Breakdown - <?php echo $selected_year; ?></h2>
                    <div class="metric-list">
                        <?php foreach ($status_breakdown as $status_row): ?>
                            <div class="metric-row">
                                <span><?php echo ucfirst(htmlspecialchars($status_row['order_status'])); ?></span>
                                <strong><?php echo (int) $status_row['total_count']; ?> Orders</strong>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($status_breakdown)): ?>
                            <div class="metric-row">
                                <span>No status data</span>
                                <strong>0</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <div class="table-grid" style="margin-top: 1.5rem;">
                <section class="card">
                    <h2 class="card-title">Payment Method Breakdown - <?php echo $selected_year; ?></h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_breakdown as $payment_row): ?>
                                <tr>
                                    <td><?php echo strtoupper(htmlspecialchars($payment_row['payment_method'])); ?></td>
                                    <td><?php echo (int) $payment_row['total_count']; ?></td>
                                    <td><?php echo formatPrice($payment_row['revenue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($payment_breakdown)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: var(--color-gray);">No payment data found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <section class="card">
                    <h2 class="card-title">Yearly Summary Table</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yearly_rows as $year_row): ?>
                                <tr>
                                    <td><?php echo (int) $year_row['year_num']; ?></td>
                                    <td><?php echo (int) $year_row['order_count']; ?></td>
                                    <td><?php echo formatPrice($year_row['revenue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($yearly_rows)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: var(--color-gray);">No yearly data found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>

    <script>
        function drawComboChart(canvasId, labels, revenueData, ordersData, chartTitle) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            const dpr = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            const width = Math.max(rect.width, 320);
            const height = Math.max(rect.height, 260);
            canvas.width = width * dpr;
            canvas.height = height * dpr;

            const ctx = canvas.getContext('2d');
            ctx.scale(dpr, dpr);
            ctx.clearRect(0, 0, width, height);

            const padding = { top: 20, right: 24, bottom: 50, left: 56 };
            const chartWidth = width - padding.left - padding.right;
            const chartHeight = height - padding.top - padding.bottom;

            const maxRevenue = Math.max(...revenueData, 0);
            const maxOrders = Math.max(...ordersData, 0);
            const revenueScale = maxRevenue > 0 ? maxRevenue : 1;
            const ordersScale = maxOrders > 0 ? maxOrders : 1;
            const stepX = labels.length > 1 ? chartWidth / (labels.length - 1) : chartWidth;

            ctx.strokeStyle = '#d1d5db';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(padding.left, padding.top);
            ctx.lineTo(padding.left, padding.top + chartHeight);
            ctx.lineTo(padding.left + chartWidth, padding.top + chartHeight);
            ctx.stroke();

            ctx.font = '12px Arial';
            ctx.fillStyle = '#6b7280';
            ctx.textAlign = 'center';

            labels.forEach((label, index) => {
                const x = padding.left + (labels.length === 1 ? chartWidth / 2 : stepX * index);
                ctx.fillText(label, x, padding.top + chartHeight + 20);
            });

            const gridLines = 4;
            ctx.textAlign = 'right';
            for (let i = 0; i <= gridLines; i++) {
                const y = padding.top + chartHeight - (chartHeight / gridLines) * i;
                ctx.strokeStyle = '#f1f5f9';
                ctx.beginPath();
                ctx.moveTo(padding.left, y);
                ctx.lineTo(padding.left + chartWidth, y);
                ctx.stroke();

                const revenueValue = Math.round((revenueScale / gridLines) * i);
                ctx.fillStyle = '#6b7280';
                ctx.fillText(revenueValue.toLocaleString(), padding.left - 8, y + 4);
            }

            const barAreaWidth = labels.length > 1 ? stepX * 0.5 : Math.min(chartWidth * 0.4, 80);
            ctx.fillStyle = 'rgba(239, 68, 68, 0.18)';
            revenueData.forEach((value, index) => {
                const xCenter = padding.left + (labels.length === 1 ? chartWidth / 2 : stepX * index);
                const barHeight = (value / revenueScale) * chartHeight;
                const x = xCenter - barAreaWidth / 2;
                const y = padding.top + chartHeight - barHeight;
                ctx.fillRect(x, y, barAreaWidth, barHeight);
                ctx.strokeStyle = 'rgba(239, 68, 68, 0.45)';
                ctx.strokeRect(x, y, barAreaWidth, barHeight);
            });

            ctx.strokeStyle = '#111827';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ordersData.forEach((value, index) => {
                const x = padding.left + (labels.length === 1 ? chartWidth / 2 : stepX * index);
                const y = padding.top + chartHeight - (value / ordersScale) * chartHeight;
                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            ctx.stroke();

            ctx.fillStyle = '#111827';
            ordersData.forEach((value, index) => {
                const x = padding.left + (labels.length === 1 ? chartWidth / 2 : stepX * index);
                const y = padding.top + chartHeight - (value / ordersScale) * chartHeight;
                ctx.beginPath();
                ctx.arc(x, y, 3.5, 0, Math.PI * 2);
                ctx.fill();
            });

            ctx.textAlign = 'left';
            ctx.fillStyle = '#111827';
            ctx.font = 'bold 13px Arial';
            ctx.fillText(chartTitle, padding.left, 12);

            ctx.fillStyle = '#ef4444';
            ctx.fillRect(width - 160, 10, 12, 12);
            ctx.fillStyle = '#111827';
            ctx.font = '12px Arial';
            ctx.fillText('Revenue', width - 142, 20);
            ctx.fillRect(width - 82, 10, 12, 2);
            ctx.fillText('Orders', width - 64, 20);
        }

        const reportData = {
            monthlyLabels: <?php echo json_encode($monthly_chart_labels); ?>,
            monthlyRevenue: <?php echo json_encode($monthly_chart_revenue); ?>,
            monthlyOrders: <?php echo json_encode($monthly_chart_orders); ?>,
            dailyLabels: <?php echo json_encode($daily_chart_labels); ?>,
            dailyRevenue: <?php echo json_encode($daily_chart_revenue); ?>,
            dailyOrders: <?php echo json_encode($daily_chart_orders); ?>,
            yearlyLabels: <?php echo json_encode($yearly_chart_labels); ?>,
            yearlyRevenue: <?php echo json_encode($yearly_chart_revenue); ?>,
            yearlyOrders: <?php echo json_encode($yearly_chart_orders); ?>
        };

        function renderReportCharts() {
            drawComboChart('yearChart', reportData.monthlyLabels, reportData.monthlyRevenue, reportData.monthlyOrders, 'Monthly Performance');
            drawComboChart('monthChart', reportData.dailyLabels, reportData.dailyRevenue, reportData.dailyOrders, 'Daily Performance');
            drawComboChart('allYearsChart', reportData.yearlyLabels, reportData.yearlyRevenue, reportData.yearlyOrders, 'Yearly Growth');
        }

        window.addEventListener('load', renderReportCharts);
        window.addEventListener('resize', renderReportCharts);
    </script>
</body>

</html>
