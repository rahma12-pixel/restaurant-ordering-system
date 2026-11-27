
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM menu WHERE availability = 1 ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Restaurant Menu</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="page-layout">

    <nav>
        <ul>
           <li><a href="home.php">Home</a></li>
            <li><a href=about.html>About</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="order.php">Order</a></li>
            <li><a href="order.php">🛒 Cart</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

<h1>Welcome to our Restaurant</h1>

    

    <main>

        <h2>Our Menu</h2>

        <p>Explore our delicious meals and refreshing drinks.</p>

        <section>
            <div class="card-container">

                <?php if ($result && $result->num_rows > 0) { ?>

                    <?php while ($food = $result->fetch_assoc()) { ?>

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

                        </article>

                    <?php } ?>

                <?php } else { ?>

                    <p>No food items are currently available.</p>

                <?php } ?>

            </div>
        </section>

    </main>

    <aside>
        <h2>Customer Tips</h2>
        <ul>
            <li>Try our seasonal specials!</li>
            <li>Ask about our daily deals.</li>
            <li>Don't forget to add a drink!</li>
        </ul>
    </aside>

    <footer>
        &copy; 2026 Self-ordering Restaurant System
    </footer>

</body>
</html>
