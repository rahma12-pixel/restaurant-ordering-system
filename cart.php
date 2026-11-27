
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$cart_count = array_sum(array_map('intval', $cart));

$total = 0;
$items = [];

if (!empty($cart)) {

    $stmt = $conn->prepare(
        "SELECT id, food_name, price
         FROM menu
         WHERE id = ? AND availability = 1"
    );

    foreach ($cart as $id => $quantity) {

        $id = (int)$id;
        $quantity = (int)$quantity;

        if ($id <= 0 || $quantity <= 0) {
            continue;
        }

        $stmt->bind_param("i", $id);
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
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Your Cart | DEVOURÉ</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <h1>Your Shopping Cart</h1>
</header>

<nav>
    <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="order.php">Menu</a></li>

        <li>
            <a href="cart.php">
                🛒 Cart (<?php echo $cart_count; ?>)
            </a>
        </li>

        <li><a href="contact.html">Contact</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<main>

    <?php if (empty($items)) { ?>

        <h2>Your cart is empty.</h2>

        <p>Add your favourite food from our menu.</p>

        <a href="order.php">Continue Shopping</a>

    <?php } else { ?>

        <h2>Your Selected Items</h2>

        <?php foreach ($items as $food) { ?>

            <article class="card">

                <h3>
                    <?php echo htmlspecialchars($food['food_name']); ?>
                </h3>

                <p>
                    Price: KSh
                    <?php echo number_format($food['price'], 2); ?>
                </p>

                <p>
                    Quantity: <?php echo $food['quantity']; ?>
                </p>

                <p>
                    Subtotal: KSh
                    <?php echo number_format($food['subtotal'], 2); ?>
                </p>

                <form action="update-cart.php" method="POST">

                    <input
                        type="hidden"
                        name="menu_id"
                        value="<?php echo (int)$food['id']; ?>"
                    >

                    <label>Update Quantity:</label>

                    <input
                        type="number"
                        name="quantity"
                        value="<?php echo $food['quantity']; ?>"
                        min="1"
                        required
                    >

                    <button type="submit">Update</button>

                </form>

                <form action="remove-from-cart.php" method="POST">

                    <input
                        type="hidden"
                        name="menu_id"
                        value="<?php echo (int)$food['id']; ?>"
                    >

                    <button type="submit">Remove Item</button>

                </form>

            </article>

        <?php } ?>

        <h2>
            Total: KSh <?php echo number_format($total, 2); ?>
        </h2>

        <h2>Proceed to Checkout</h2>

        <form action="checkout.php" method="POST">

            <label for="customer_name">Customer Name:</label>

            <input
                type="text"
                id="customer_name"
                name="customer_name"
                value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>"
                required
            >

            <label for="table_number">Table Number:</label>

            <input
                type="number"
                id="table_number"
                name="table_number"
                min="1"
                required
            >

            <button type="submit">Continue to Checkout</button>

        </form>

        <p>
            <a href="order.php">Continue Shopping</a>
        </p>

    <?php } ?>

</main>

<footer>
    <p>&copy; 2026 DEVOURÉ | A Taste Worth Remembering</p>
</footer>

</body>
</html>
