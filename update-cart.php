
<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: order.php");
    exit();
}

$menu_id = (int)($_POST['menu_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 0);

if (
    $menu_id > 0 &&
    $quantity > 0 &&
    isset($_SESSION['cart'][$menu_id])
) {
    $_SESSION['cart'][$menu_id] = $quantity;
}

header("Location: order.php");
exit();
?>
