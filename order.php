
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$menu = $conn->query(
    "SELECT id, food_name, description, price, image
     FROM menu
     WHERE availability = 1
     ORDER BY id ASC"
);

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$cart_total = 0;
$cart_count = 0;

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

            $cart_items[] = $food;
            $cart_total += $food['subtotal'];
            $cart_count += $quantity;
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

    <title>Place Your Order | DEVOURÉ</title>

    <link rel="stylesheet" href="style.css">

    <style>
        /* Floating cart panel */
        .floating-cart {
            position: fixed;
            top: 85px;
            right: 20px;
            width: 320px;
            max-width: calc(100vw - 30px);
            max-height: 75vh;
            overflow-y: auto;
            background: white;
            color: #222;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            z-index: 9999;
            display: none;
        }

        .floating-cart.open {
            display: block;
        }

        .floating-cart h2 {
            margin-top: 0;
        }

        .floating-cart-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .floating-cart-item h3 {
            margin: 0 0 6px;
            font-size: 16px;
        }

        .floating-cart-item p {
            margin: 5px 0;
        }

        .floating-cart-total {
            font-weight: bold;
            font-size: 18px;
            margin-top: 15px;
        }

        .floating-cart-link {
            display: block;
            text-align: center;
            background: #246b35;
            color: white;
            text-decoration: none;
            padding: 12px;
            margin-top: 15px;
            border-radius: 6px;
        }

        .floating-cart-link:hover {
            background: #194d27;
        }

        .cart-toggle {
            border: none;
            background: transparent;
            color: inherit;
            font: inherit;
            cursor: pointer;
            padding: 8px;
        }

        .cart-message {
            color: green;
            font-weight: bold;
        }

        .cart-close {
            float: right;
            border: none;
            background: transparent;
            font-size: 22px;
            cursor: pointer;
        }

        @media (max-width: 480px) {
            .floating-cart {
                right: 10px;
                top: 75px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Place Your Order</h1>
</header>

<nav>
    <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="menu.php">Menu</a></li>
        <li><a href="order.php">Order</a></li>
        <li><a href="contact.html">Contact</a></li>
        <li><a href="logout.php">Logout</a></li>

        <li>
            <button type="button" class="cart-toggle" id="cart-toggle">
                🛒 Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)
            </button>
        </li>
    </ul>
</nav>

<!-- ORIGINAL MENU LAYOUT -->

<main>

    <h2>Select Your Food</h2>

    <div class="card-container">

        <?php if ($menu && $menu->num_rows > 0) { ?>

            <?php while ($food = $menu->fetch_assoc()) { ?>

                <article class="card">

                    <?php if (!empty($food['image'])) { ?>

                        <img
                            src="<?php echo htmlspecialchars($food['image']); ?>"
                            alt="<?php echo htmlspecialchars($food['food_name']); ?>"
                        >

                    <?php } ?>

                    <h3>
                        <?php echo htmlspecialchars($food['food_name']); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($food['description']); ?>
                    </p>

                    <p>
                        <strong>
                            Price: KSh
                            <?php echo number_format($food['price'], 2); ?>
                        </strong>
                    </p>

                    <form method="POST"
                          action="add-to-cart.php"
                          class="add-to-cart-form">

                        <input
                            type="hidden"
                            name="menu_id"
                            value="<?php echo (int)$food['id']; ?>"
                        >

                        <label>Quantity:</label>

                        <input
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            required
                        >

                        <button type="submit">
                            Add to Order
                        </button>

                    </form>

                </article>

            <?php } ?>

        <?php } else { ?>

            <p>No food items are currently available.</p>

        <?php } ?>

    </div>

</main>

<!-- FLOATING CART PANEL -->

<aside class="floating-cart" id="floating-cart">

    <button type="button" class="cart-close" id="cart-close">
        &times;
    </button>

    <h2>
        🛒 Your Cart
        (<span id="panel-cart-count"><?php echo $cart_count; ?></span>)
    </h2>

    <p id="cart-message" class="cart-message"
       role="status" aria-live="polite"></p>

    <div id="cart-empty"
         <?php echo !empty($cart_items) ? 'hidden' : ''; ?>>

        <p>Your cart is empty.</p>

    </div>

    <div id="cart-content"
         <?php echo empty($cart_items) ? 'hidden' : ''; ?>>

        <div id="cart-items">

            <?php foreach ($cart_items as $food) { ?>

                <div class="floating-cart-item">

                    <h3>
                        <?php echo htmlspecialchars($food['food_name']); ?>
                    </h3>

                    <p>Quantity: <?php echo $food['quantity']; ?></p>

                    <p>
                        Subtotal: KSh
                        <?php echo number_format($food['subtotal'], 2); ?>
                    </p>

                </div>

            <?php } ?>

        </div>

        <div class="floating-cart-total">
            Total: KSh
            <span id="cart-total">
                <?php echo number_format($cart_total, 2); ?>
            </span>
        </div>

        <a href="cart.php" class="floating-cart-link">
            View Cart and Checkout
        </a>

    </div>

</aside>

<footer>
    <p>&copy; 2026 DEVOURÉ | A Taste Worth Remembering</p>
</footer>

<script>

const floatingCart = document.getElementById('floating-cart');
const cartToggle = document.getElementById('cart-toggle');

function openFloatingCart() {
    floatingCart.classList.add('open');
}

function closeFloatingCart() {
    floatingCart.classList.remove('open');
}

cartToggle.addEventListener('click', function() {
    floatingCart.classList.toggle('open');
});

document.getElementById('cart-close').addEventListener(
    'click',
    closeFloatingCart
);

function updateCartDisplay(data) {

    document.getElementById('cart-count').textContent =
        data.cart_count;

    document.getElementById('panel-cart-count').textContent =
        data.cart_count;

    const cartItems = document.getElementById('cart-items');
    const cartEmpty = document.getElementById('cart-empty');
    const cartContent = document.getElementById('cart-content');

    cartItems.replaceChildren();

    if (data.items.length === 0) {
        cartEmpty.hidden = false;
        cartContent.hidden = true;
        return;
    }

    cartEmpty.hidden = true;
    cartContent.hidden = false;

    data.items.forEach(function(item) {

        const itemBox = document.createElement('div');
        itemBox.className = 'floating-cart-item';

        const name = document.createElement('h3');
        name.textContent = item.food_name;

        const quantity = document.createElement('p');
        quantity.textContent = 'Quantity: ' + item.quantity;

        const subtotal = document.createElement('p');
        subtotal.textContent =
            'Subtotal: KSh ' + Number(item.subtotal).toFixed(2);

        itemBox.appendChild(name);
        itemBox.appendChild(quantity);
        itemBox.appendChild(subtotal);

        cartItems.appendChild(itemBox);

    });

    document.getElementById('cart-total').textContent =
        Number(data.total).toFixed(2);

}

document.querySelectorAll('.add-to-cart-form').forEach(function(form) {

    form.addEventListener('submit', async function(event) {

        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const message = document.getElementById('cart-message');
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = 'Adding...';
        message.textContent = '';

        try {

            const response = await fetch('add-to-cart.php', {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Unable to add this food.'
                );
            }

            updateCartDisplay(data);

            message.textContent = 'Food added successfully!';

            form.querySelector('[name="quantity"]').value = 1;

            // Show the floating cart immediately
            openFloatingCart();

        } catch (error) {

            message.textContent = error.message;

        } finally {

            button.disabled = false;
            button.textContent = originalText;

        }

    });

});

</script>

</body>
</html>
