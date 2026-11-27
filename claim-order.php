
<?php
session_start();
require_once 'config.php';

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

// Assign the order only if another waiter has not claimed it
$stmt = $conn->prepare(
    "UPDATE orders
     SET assigned_waiter_id = ?
     WHERE id = ?
     AND assigned_waiter_id IS NULL
     AND status = 'Ready'
     AND `payment-status` = 'Paid'"
);

$stmt->bind_param("ii", $waiter_id, $order_id);
$stmt->execute();

$claimed = $stmt->affected_rows === 1;
$stmt->close();

if ($claimed) {
    $_SESSION['claim_message'] = "You have successfully claimed order #$order_id.";
} else {
    $_SESSION['claim_message'] = "This order has already been claimed or is no longer available.";
}

header("Location: waiter-dashboard.php");
exit();
?>
