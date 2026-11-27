
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: order.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$checkout = $_SESSION['checkout'] ?? [];

$customer_name = trim($checkout['customer_name'] ?? '');
$table_number = (int)($checkout['table_number'] ?? 0);

if (empty($cart) || $customer_name === '' || $table_number <= 0) {
    header("Location: order.php");
    exit();
}

try {

    $conn->begin_transaction();

    $total = 0;
    $items = [];

    $stmt = $conn->prepare(
        "SELECT id, price
         FROM menu
         WHERE id = ? AND availability = 1"
    );

    foreach ($cart as $menu_id => $quantity) {

        $menu_id = (int)$menu_id;
        $quantity = (int)$quantity;

        if ($menu_id <= 0 || $quantity <= 0) {
            throw new Exception("Invalid order item.");
        }

        $stmt->bind_param("i", $menu_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $food = $result->fetch_assoc();

        if (!$food) {
            throw new Exception("A selected food item is unavailable.");
        }

        $total += $food['price'] * $quantity;

        $items[] = [
            'menu_id' => $menu_id,
            'quantity' => $quantity,
            'price' => $food['price']
        ];
    }

    if (empty($items)) {
        throw new Exception("Your order has no available items.");
    }

    $order_stmt = $conn->prepare(
        "INSERT INTO orders
         (customer_name, table_number, total_amount, status)
         VALUES (?, ?, ?, 'Pending')"
    );

    $order_stmt->bind_param(
        "sid",
        $customer_name,
        $table_number,
        $total
    );

    $order_stmt->execute();

    $order_id = $conn->insert_id;

    $item_stmt = $conn->prepare(
        "INSERT INTO order_items
         (order_id, menu_id, quantity, price)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($items as $item) {

        $item_stmt->bind_param(
            "iiid",
            $order_id,
            $item['menu_id'],
            $item['quantity'],
            $item['price']
        );

        $item_stmt->execute();
    }

    $conn->commit();

    unset($_SESSION['cart']);
    unset($_SESSION['checkout']);

    $_SESSION['last_order_id'] = $order_id;

    header("Location: order-success.php");
    exit();

} catch (Throwable $e) {

    $conn->rollback();

    die("Your order could not be completed. Please return to your order page and try again.");
}
?>
