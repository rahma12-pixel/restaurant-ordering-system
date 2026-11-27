
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

if (empty($cart)) {
    header("Location: order.php");
    exit();
}

$customer_name = trim($_POST['customer_name'] ?? '');
$table_number = (int)($_POST['table_number'] ?? 0);

if ($customer_name === '' || $table_number <= 0) {
    die("Please enter a valid customer name and table number.");
}

$total = 0;
$items = [];

$stmt = $conn->prepare(
    "SELECT id, food_name, price
     FROM menu
     WHERE id = ? AND availability = 1"
);

foreach ($cart as $menu_id => $quantity) {

    $menu_id = (int)$menu_id;
    $quantity = (int)$quantity;

    if ($menu_id <= 0 || $quantity <= 0) {
        continue;
    }

    $stmt->bind_param("i", $menu_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($food = $result->fetch_assoc()) {

        $food['quantity'] = $quantity;
        $food['subtotal'] = $food['price'] * $quantity;

        $total += $food['subtotal'];
        $items[] = $food;
    }
}

$stmt->close();

if (empty($items)) {
    die("Your order contains no available items.");
}

$_SESSION['checkout'] = [
    'customer_name' => $customer_name,
    'table_number' => $table_number
];

$_SESSION['checkout_total'] = $total;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DEVOURÉ | Checkout</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>DEVOURÉ</h1>
    <p>Checkout - A Taste Worth Remembering</p>
</header>

<nav>
    <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="menu.php">Menu</a></li>
        <li><a href="order.php">Order</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<main>

    <h2>Review Your Order</h2>

    <p>
        <strong>Customer Name:</strong>
        <?php echo htmlspecialchars($customer_name); ?>
    </p>

    <p>
        <strong>Table Number:</strong>
        <?php echo $table_number; ?>
    </p>

    <h3>Order Details</h3>

    <?php foreach ($items as $food) { ?>

        <div class="card">

            <h3>
                <?php echo htmlspecialchars($food['food_name']); ?>
            </h3>

            <p>
                Quantity:
                <?php echo $food['quantity']; ?>
            </p>

            <p>
                Price: KSh
                <?php echo number_format($food['price'], 2); ?>
            </p>

            <p>
                Subtotal: KSh
                <?php echo number_format($food['subtotal'], 2); ?>
            </p>

        </div>

    <?php } ?>

    <h2>
        Total Amount: KSh
        <?php echo number_format($total, 2); ?>
    </h2>

    <hr>

    <h2>M-Pesa Payment</h2>

    <p>
        Enter your M-Pesa phone number to receive a payment request.
    </p>

    <form action="stkpush.php" method="POST">

        <label for="phone">
            M-Pesa Phone Number:
        </label>

        <input
            type="text"
            id="phone"
            name="phone"
            placeholder="2547XXXXXXXX"
            pattern="254[17][0-9]{8}"
            maxlength="12"
            required
        >

        <p>
            <button type="submit">
                Pay with M-Pesa
            </button>
        </p>

    </form>

    <p>
        <a href="order.php">Go Back and Edit Order</a>
    </p>

</main>

<footer>
    <p>&copy; 2026 DEVOURÉ Restaurant. All rights reserved.</p>
</footer>

</body>
</html>
