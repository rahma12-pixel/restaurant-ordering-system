
<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: order.php");
    exit();
}

$menu_id = (int)($_POST['menu_id'] ?? 0);

if ($menu_id > 0 && isset($_SESSION['cart'][$menu_id])) {
    unset($_SESSION['cart'][$menu_id]);
}

header("Location: order.php");
exit();
?>
