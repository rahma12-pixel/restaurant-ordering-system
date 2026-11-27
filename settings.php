
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$admin_name = $_SESSION['user_name'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<body>

<div class="main-content">

    <header>
        <h1>System Settings</h1>
        <a href="admin-dashboard.php">Back to Dashboard</a>
    </header>

    <main>

        <section class="management">

            <h2>Administrator Information</h2>

            <div class="management-card">
                <h3>Administrator Account</h3>
                <p>
                    Logged in as:
                    <?php echo htmlspecialchars($admin_name); ?>
                </p>
                <p>Role: Administrator</p>
            </div>

            <h2>Restaurant System Information</h2>

            <div class="management-card">
                <h3>System Name</h3>
                <p>Restaurant Self-Ordering System</p>
            </div>

            <div class="management-card">
                <h3>Database</h3>
                <p>MySQL Database</p>
                <p>Connection: Local XAMPP Server</p>
            </div>

            <div class="management-card">
                <h3>Menu Management</h3>
                <p>Manage restaurant food and drink items.</p>
                <a href="manage-menu.php">Open Menu Management</a>
            </div>

            <div class="management-card">
                <h3>Staff Management</h3>
                <p>Manage administrator, kitchen and waiter accounts.</p>
                <a href="manage-staff.php">Open Staff Management</a>
            </div>

            <div class="management-card">
                <h3>Allergen Management</h3>
                <p>Manage food allergen information.</p>
                <a href="manage-allergens.php">Open Allergen Management</a>
            </div>

        </section>

    </main>

</div>

</body>
</html>
