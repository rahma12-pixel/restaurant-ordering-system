
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM orders ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<body>

<div class="main-content">

    <header>
        <h1>Manage Customer Orders</h1>
        <a href="admin-dashboard.php">Back to Dashboard</a>
    </header>

    <main>

        <h2>All Orders</h2>

        <?php if ($result && $result->num_rows > 0) { ?>

            <?php while ($order = $result->fetch_assoc()) { ?>

                <div class="management-card">

                    <h3>Order #<?php echo (int)$order['id']; ?></h3>

                    <p>
                        <strong>Customer:</strong>
                        <?php echo htmlspecialchars($order['customer_name']); ?>
                    </p>

                    <p>
                        <strong>Table Number:</strong>
                        <?php echo (int)$order['table_number']; ?>
                    </p>

                    <p>
                        <strong>Total Amount:</strong>
                        KSh <?php echo number_format($order['total_amount'], 2); ?>
                    </p>

                    <p>
                        <strong>Order Date:</strong>
                        <?php echo htmlspecialchars($order['order_date']); ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <?php echo htmlspecialchars($order['status']); ?>
                    </p>

                    <h4>Ordered Items</h4>

                    <?php
                    $order_id = (int)$order['id'];

                    $items_sql = "SELECT order_items.quantity,
                                         order_items.price,
                                         menu.food_name
                                  FROM order_items
                                  JOIN menu ON order_items.menu_id = menu.id
                                  WHERE order_items.order_id = $order_id";

                    $items_result = $conn->query($items_sql);
                    ?>

                    <?php if ($items_result && $items_result->num_rows > 0) { ?>

                        <?php while ($item = $items_result->fetch_assoc()) { ?>

                            <p>
                                <?php echo htmlspecialchars($item['food_name']); ?>
                                x <?php echo (int)$item['quantity']; ?>

                                - KSh <?php echo number_format($item['price'], 2); ?>
                            </p>

                        <?php } ?>

                    <?php } else { ?>

                        <p>No order items found.</p>

                    <?php } ?>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p>No customer orders have been placed yet.</p>

        <?php } ?>

    </main>

</div>

</body>
</html>
