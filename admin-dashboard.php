
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Total orders
$total_orders = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM orders");

if ($result) {
    $total_orders = $result->fetch_assoc()['total'];
}

// Total staff
$total_staff = 0;
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role IN ('kitchen', 'waiter', 'admin')"
);

if ($result) {
    $total_staff = $result->fetch_assoc()['total'];
}

// Total menu items
$total_menu_items = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM menu");

if ($result) {
    $total_menu_items = $result->fetch_assoc()['total'];
}

// Pending paid orders
$pending_orders = 0;
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM orders
     WHERE status = 'Pending'
     AND `payment-status` = 'Paid'"
);

if ($result) {
    $pending_orders = $result->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DEVOURÉ | Admin Dashboard</title>

    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <h2>DEVOURÉ</h2>

        <ul>
            <li class="active">
                <a href="admin-dashboard.php">Dashboard</a>
            </li>

            <li>
                <a href="manage-menu.php">Manage Menu</a>
            </li>

            <li>
                <a href="manage-orders.php">Manage Orders</a>
            </li>

            <li>
                <a href="manage-staff.php">Manage Staff</a>
            </li>

            <li>
                <a href="sales-reports.php">Sales Reports</a>
            </li>

            <li>
                <a href="manage-allergens.php">Allergens</a>
            </li>

            <li>
                <a href="settings.php">Settings</a>
            </li>
        </ul>

        <a href="logout.php" class="logout">Logout</a>

    </aside>

    <main class="main-content">

        <header>

            <div>
                <h1>Admin Dashboard</h1>
                <p>Welcome, Administrator!</p>
            </div>

            <div class="profile">
                Administrator
            </div>

        </header>

        <section class="cards">

            <div class="card">
                <h3>Total Orders</h3>
                <h2><?php echo $total_orders; ?></h2>
            </div>

            <div class="card">
                <h3>Total Staff</h3>
                <h2><?php echo $total_staff; ?></h2>
            </div>

            <div class="card">
                <h3>Menu Items</h3>
                <h2><?php echo $total_menu_items; ?></h2>
            </div>

            <div class="card">
                <h3>Pending Orders</h3>
                <h2><?php echo $pending_orders; ?></h2>
            </div>

        </section>

        <section class="management">

            <h2>Restaurant Management</h2>

            <div class="management-card">

                <h3>Menu Management</h3>

                <p>Add, update and remove food and drink items.</p>

                <a href="manage-menu.php">
                    <button type="button">Manage Menu</button>
                </a>

            </div>

            <div class="management-card">

                <h3>Order Management</h3>

                <p>View and monitor restaurant orders.</p>

                <a href="manage-orders.php">
                    <button type="button">View Orders</button>
                </a>

            </div>

            <div class="management-card">

                <h3>Staff Management</h3>

                <p>Manage kitchen staff, waiters and administrators.</p>

                <a href="manage-staff.php">
                    <button type="button">Manage Staff</button>
                </a>

            </div>

            <div class="management-card">

                <h3>Sales Reports</h3>

                <p>View restaurant sales and performance reports.</p>

                <a href="sales-reports.php">
                    <button type="button">View Reports</button>
                </a>

            </div>

            <div class="management-card">

                <h3>Allergen Management</h3>

                <p>Manage food allergen information.</p>

                <a href="manage-allergens.php">
                    <button type="button">Manage Allergens</button>
                </a>

            </div>

            <div class="management-card">

                <h3>System Settings</h3>

                <p>Manage restaurant system settings.</p>

                <a href="settings.php">
                    <button type="button">Settings</button>
                </a>

            </div>

        </section>

    </main>

</div>

</body>
</html>
