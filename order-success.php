
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['last_order_id'])) {
    header("Location: order.php");
    exit();
}

$order_id = (int)$_SESSION['last_order_id'];

$stmt = $conn->prepare(
    "SELECT status, `payment-status`, `mpesa-receipt`
     FROM orders
     WHERE id = ?"
);

$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: order.php");
    exit();
}

unset($_SESSION['last_order_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DEVOURÉ | Order Status</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>DEVOURÉ</h1>
    <p>A Taste Worth Remembering</p>
</header>

<main>

    <h1>Order Received!</h1>

    <h2>
        Order Number:
        <?php echo $order_id; ?>
    </h2>

    <p>
        Payment Status:
        <strong>
            <?php echo htmlspecialchars($order['payment-status']); ?>
        </strong>
    </p>

    <p>
        Order Status:
        <strong>
            <?php echo htmlspecialchars($order['status']); ?>
        </strong>
    </p>

    <?php if ($order['payment-status'] === 'Paid') { ?>

        <h2>Payment Successful!</h2>

        <p>Your payment has been confirmed.</p>

        <p>
            M-Pesa Receipt:
            <?php echo htmlspecialchars($order['mpesa-receipt']); ?>
        </p>

        <p>Your order has been sent for processing.</p>

    <?php } else { ?>

        <h2>Payment Pending</h2>

        <p>Your payment has not yet been confirmed.</p>

        <p>Please wait for payment verification before your order is processed.</p>

    <?php } ?>

    <a href="home.php">Return to Home</a>

</main>

<footer>
    <p>&copy; 2026 DEVOURÉ Restaurant. All rights reserved.</p>
</footer>

</body>
</html>
