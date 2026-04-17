<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('/admin/login.php');
}

$search = $_GET['search'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
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
        }

        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--color-border);
        }

        .data-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--color-border);
        }

        .data-table th {
            background: var(--color-light-gray);
            font-weight: var(--font-weight-semibold);
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.75rem;
        }

        .badge-verified {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-unverified {
            background: #fff3e0;
            color: #e65100;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1>User Management</h1>
            </div>

            <form class="search-bar" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Search by name, email or phone..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary">Search</button>
            </form>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Verification</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong>
                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                </strong></td>
                            <td>
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?>
                            </td>
                            <td>
                                <div>
                                    <span
                                        class="badge <?php echo $user['email_verified'] ? 'badge-verified' : 'badge-unverified'; ?>">
                                        Email:
                                        <?php echo $user['email_verified'] ? 'Verified' : 'Pending'; ?>
                                    </span>
                                </div>
                                <div style="margin-top: 0.25rem;">
                                    <span
                                        class="badge <?php echo $user['phone_verified'] ? 'badge-verified' : 'badge-unverified'; ?>">
                                        Phone:
                                        <?php echo $user['phone_verified'] ? 'Verified' : 'Pending'; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <a href="/admin/user-details.php?id=<?php echo $user['id']; ?>"
                                    class="btn btn-secondary btn-sm" style="padding: 0.5rem 1rem; font-size: 0.875rem;">View
                                    Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--color-gray);">No users
                                found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>

</html>