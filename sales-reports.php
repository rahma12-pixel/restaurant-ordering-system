
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$total_orders = 0;
$total_sales = 0;
$pending_orders = 0;
$delivered_orders = 0;

$summary = $conn->query(
    "SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(total_amount), 0) AS total_sales,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_orders,
        SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) AS delivered_orders
     FROM orders"
);

if ($summary) {
    $data = $summary->fetch_assoc();

    $total_orders = (int)$data['total_orders'];
    $total_sales = (float)$data['total_sales'];
    $pending_orders = (int)$data['pending_orders'];
    $delivered_orders = (int)$data['delivered_orders'];
}

$orders = $conn->query(
    "SELECT id, customer_name, table_number, total_amount, status, order_date
     FROM orders
     ORDER BY order_date DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports</title>
    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<body>

<div class="main-content">

    <header>
        <h1>Restaurant Sales Reports</h1>
        <a href="admin-dashboard.php">Back to Dashboard</a>
    </header>

    <main>

        <h2>Sales Summary</h2>

        <section class="cards">

            <div class="card">
                <h3>Total Orders</h3>
                <h2><?php echo $total_orders; ?></h2>
            </div>

            <div class="card">
                <h3>Total Sales</h3>
                <h2>KSh <?php echo number_format($total_sales, 2); ?></h2>
            </div>

            <div class="card">
                <h3>Pending Orders</h3>
                <h2><?php echo $pending_orders; ?></h2>
            </div>

            <div class="card">
                <h3>Delivered Orders</h3>
                <h2><?php echo $delivered_orders; ?></h2>
            </div>

        </section>

        <section>

            <h2>Order Sales History</h2>

            <?php if ($orders && $orders->num_rows > 0) { ?>

                <table>

                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Table</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>

                    <?php while ($order = $orders->fetch_assoc()) { ?>

                        <tr>

                            <td><?php echo (int)$order['id']; ?></td>

                            <td>
                                <?php echo htmlspecialchars($order['customer_name']); ?>
                            </td>

                            <td>
                                <?php echo (int)$order['table_number']; ?>
                            </td>

                            <td>
                                KSh <?php echo number_format($order['total_amount'], 2); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($order['status']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($order['order_date']); ?>
                            </td>

                        </tr>

                    <?php } ?>

                </table>

            <?php } else { ?>

                <p>No sales records are available yet.</p>

            <?php } ?>

        </section>

    </main>

</div>

</body>
</html>
