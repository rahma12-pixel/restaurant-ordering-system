
<?php
session_start();
require_once 'config.php';

$isAjax = isset($_SERVER['HTTP_ACCEPT']) &&
          strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

function sendResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        sendResponse(['success' => false, 'message' => 'Invalid request.'], 405);
    }

    header("Location: order.php");
    exit();
}

$menu_id = (int)($_POST['menu_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 0);

if ($menu_id <= 0 || $quantity <= 0) {
    if ($isAjax) {
        sendResponse(['success' => false, 'message' => 'Please enter a valid quantity.'], 400);
    }

    header("Location: order.php");
    exit();
}

$stmt = $conn->prepare(
    "SELECT id FROM menu WHERE id = ? AND availability = 1"
);

$stmt->bind_param("i", $menu_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    if ($isAjax) {
        sendResponse(['success' => false, 'message' => 'This food item is not available.'], 404);
    }

    header("Location: order.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][$menu_id] =
    ($_SESSION['cart'][$menu_id] ?? 0) + $quantity;

// Build the latest cart information
$items = [];
$total = 0;
$cart_count = 0;

$cart = $_SESSION['cart'];

$item_stmt = $conn->prepare(
    "SELECT id, food_name, price, image
     FROM menu
     WHERE id = ? AND availability = 1"
);

foreach ($cart as $id => $item_quantity) {

    $id = (int)$id;
    $item_quantity = (int)$item_quantity;

    if ($id <= 0 || $item_quantity <= 0) {
        continue;
    }

    $item_stmt->bind_param("i", $id);
    $item_stmt->execute();

    $item_result = $item_stmt->get_result();

    if ($food = $item_result->fetch_assoc()) {

        $price = (float)$food['price'];
        $subtotal = $price * $item_quantity;

        $items[] = [
            'id' => (int)$food['id'],
            'food_name' => $food['food_name'],
            'price' => $price,
            'quantity' => $item_quantity,
            'subtotal' => $subtotal,
            'image' => $food['image']
        ];

        $total += $subtotal;
        $cart_count += $item_quantity;
    }
}

$item_stmt->close();
$stmt->close();

if ($isAjax) {
    sendResponse([
        'success' => true,
        'message' => 'Food added to your cart.',
        'cart_count' => $cart_count,
        'total' => $total,
        'items' => $items
    ]);
}

header("Location: order.php");
exit();
?>
