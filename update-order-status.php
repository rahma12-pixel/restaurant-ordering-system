
<?php
session_start();
require_once 'config.php';
require_once 'create-notification.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'kitchen') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kitchen-dashboard.php");
    exit();
}

$order_id = (int)($_POST['order_id'] ?? 0);
$new_status = $_POST['status'] ?? '';

if ($order_id <= 0 || !in_array($new_status, ['Preparing', 'Ready'], true)) {
    header("Location: kitchen-dashboard.php");
    exit();
}

// Get the order details
$stmt = $conn->prepare(
    "SELECT customer_user_id, assigned_waiter_id, status, `payment-status`
     FROM orders
     WHERE id = ?"
);

$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order || $order['payment-status'] !== 'Paid') {
    header("Location: kitchen-dashboard.php");
    exit();
}

// Check the correct order-status transition
$previous_status = $new_status === 'Preparing' ? 'Pending' : 'Preparing';

if ($order['status'] !== $previous_status) {
    header("Location: kitchen-dashboard.php");
    exit();
}

// Update the order only if it still has the expected previous status
$update = $conn->prepare(
    "UPDATE orders
     SET status = ?
     WHERE id = ?
     AND status = ?
     AND `payment-status` = 'Paid'"
);

$update->bind_param("sis", $new_status, $order_id, $previous_status);
$update->execute();

$updated = $update->affected_rows === 1;
$update->close();

if ($updated) {

    $customer_id = (int)$order['customer_user_id'];

    if ($new_status === 'Preparing') {

        // Notify the customer
        if ($customer_id > 0) {
            notifyUser(
                $conn,
                $order_id,
                $customer_id,
                'user',
                "Your order #$order_id is now being prepared."
            );
        }

    } elseif ($new_status === 'Ready') {

        // Notify the customer
        if ($customer_id > 0) {
            notifyUser(
                $conn,
                $order_id,
                $customer_id,
                'user',
                "Your order #$order_id is ready!"
            );
        }

        // Notify the assigned waiter, or all waiters if unassigned
        $waiter_id = (int)$order['assigned_waiter_id'];

        if ($waiter_id > 0) {

            notifyUser(
                $conn,
                $order_id,
                $waiter_id,
                'waiter',
                "Order #$order_id is ready for delivery."
            );

        } else {

            notifyRole(
                $conn,
                $order_id,
                'waiter',
                "Order #$order_id is ready and waiting to be claimed."
            );
        }
    }
}

header("Location: kitchen-dashboard.php");
exit();
?>
