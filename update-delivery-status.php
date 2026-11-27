
<?php
session_start();
require_once 'config.php';
require_once 'create-notification.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'waiter') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: waiter-dashboard.php");
    exit();
}

$order_id = (int)($_POST['order_id'] ?? 0);
$waiter_id = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    header("Location: waiter-dashboard.php");
    exit();
}

// Get the customer's account ID
$stmt = $conn->prepare(
    "SELECT customer_user_id
     FROM orders
     WHERE id = ?
     AND assigned_waiter_id = ?
     AND status = 'Ready'
     AND `payment-status` = 'Paid'"
);

$stmt->bind_param("ii", $order_id, $waiter_id);
$stmt->execute();

$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: waiter-dashboard.php");
    exit();
}

// Mark the order as delivered
$update = $conn->prepare(
    "UPDATE orders
     SET status = 'Delivered'
     WHERE id = ?
     AND assigned_waiter_id = ?
     AND status = 'Ready'
     AND `payment-status` = 'Paid'"
);

$update->bind_param("ii", $order_id, $waiter_id);
$update->execute();

$updated = $update->affected_rows === 1;
$update->close();

if ($updated) {

    $customer_id = (int)$order['customer_user_id'];

    // Notify the customer
    if ($customer_id > 0) {
        notifyUser(
            $conn,
            $order_id,
            $customer_id,
            'user',
            "Your order #$order_id has been delivered. Enjoy your meal!"
        );
    }

    // Notify all kitchen staff
    notifyRole(
        $conn,
        $order_id,
        'kitchen',
        "Order #$order_id has been delivered to the customer."
    );
}

header("Location: waiter-dashboard.php");
exit();
?>
