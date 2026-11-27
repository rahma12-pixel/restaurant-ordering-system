
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'kitchen') {
    header("Location: index.php");
    exit();
}

// Display paid orders only
$sql = "SELECT * FROM orders
        WHERE `payment-status` = 'Paid'
        ORDER BY order_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kitchen Dashboard</title>

    <link rel="stylesheet" href="kitchen-dashboard.css">

    <style>
        .notification-container {
            position: relative;
        }

        .notification-button {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            position: relative;
        }

        .notification-count {
            background: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            position: absolute;
            top: -8px;
            right: -10px;
        }

        .notification-panel {
            display: none;
            position: absolute;
            right: 0;
            top: 35px;
            width: 280px;
            max-height: 350px;
            overflow-y: auto;
            background: white;
            color: #263746;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border-radius: 8px;
            padding: 15px;
            z-index: 1000;
        }

        .notification-panel.show {
            display: block;
        }

        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        .notification-empty {
            padding: 10px;
            text-align: center;
        }
    </style>
</head>

<body>

<header>
    <h1>Kitchen Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
</header>

<nav>
    <ul>

        <li><a href="home.php">Home</a></li>

        <!-- Notification Bell -->
        <li class="notification-container">

            <button type="button"
                    class="notification-button"
                    onclick="toggleNotifications()">

                🔔 Notifications

                <span id="notificationCount"
                      class="notification-count"
                      style="display:none;">0</span>

            </button>

            <div id="notificationPanel"
                 class="notification-panel">

                <h3>Kitchen Notifications</h3>

                <div id="notificationList">
                    <p class="notification-empty">
                        Loading notifications...
                    </p>
                </div>

            </div>

        </li>

        <li><a href="logout.php">Logout</a></li>

    </ul>
</nav>

<main>

    <h2>Customer Orders</h2>

    <?php if ($result && $result->num_rows > 0) { ?>

        <?php while ($order = $result->fetch_assoc()) { ?>

            <div class="card">

                <h3>Order #<?php echo (int)$order['id']; ?></h3>

                <p>
                    <strong>Customer:</strong>
                    <?php echo htmlspecialchars($order['customer_name']); ?>
                </p>

                <p>
                    <strong>Table Number:</strong>
                    <?php echo (int)$order['table_number']; ?>
                </p>

                <p>
                    <strong>Total:</strong>
                    KSh <?php echo number_format($order['total_amount'], 2); ?>
                </p>

                <p>
                    <strong>Status:</strong>
                    <?php echo htmlspecialchars($order['status']); ?>
                </p>

                <h4>Items Ordered</h4>

                <?php
                $order_id = (int)$order['id'];

                $items_sql = "SELECT order_items.quantity,
                                     order_items.price,
                                     menu.food_name
                              FROM order_items
                              JOIN menu ON order_items.menu_id = menu.id
                              WHERE order_items.order_id = $order_id";

                $items_result = $conn->query($items_sql);
                ?>

                <?php if ($items_result && $items_result->num_rows > 0) { ?>

                    <?php while ($item = $items_result->fetch_assoc()) { ?>

                        <p>
                            <?php echo htmlspecialchars($item['food_name']); ?>
                            x <?php echo (int)$item['quantity']; ?>

                            - KSh <?php echo number_format($item['price'], 2); ?>
                        </p>

                    <?php } ?>

                <?php } ?>

                <form action="update-order-status.php" method="POST">

                    <input type="hidden" name="order_id"
                           value="<?php echo (int)$order['id']; ?>">

                    <?php if ($order['status'] === 'Pending') { ?>

                        <button type="submit" name="status" value="Preparing">
                            Start Preparing
                        </button>

                    <?php } ?>

                    <?php if ($order['status'] === 'Preparing') { ?>

                        <button type="submit" name="status" value="Ready">
                            Mark as Ready
                        </button>

                    <?php } ?>

                </form>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>No paid customer orders yet.</p>

    <?php } ?>

</main>

<footer>
    <p>&copy; 2026 Self-Ordering Restaurant System</p>
</footer>

<script>

    function toggleNotifications() {

        document.getElementById("notificationPanel")
            .classList.toggle("show");

    }

    function loadNotifications() {

        fetch("get-notifications.php")
            .then(response => response.json())
            .then(data => {

                const list =
                    document.getElementById("notificationList");

                const count =
                    document.getElementById("notificationCount");

                list.innerHTML = "";

                if (data.error) {
                    list.innerHTML =
                        "<p class='notification-empty'>Unable to load notifications.</p>";
                    return;
                }

                if (data.unread_count > 0) {
                    count.textContent = data.unread_count;
                    count.style.display = "inline";
                } else {
                    count.style.display = "none";
                }

                if (!data.notifications ||
                    data.notifications.length === 0) {

                    list.innerHTML =
                        "<p class='notification-empty'>No notifications yet.</p>";

                    return;
                }

                data.notifications.forEach(notification => {

                    const item = document.createElement("div");

                    item.className = "notification-item";

                    item.textContent =
                        notification.message + " — " +
                        notification.created_at;

                    list.appendChild(item);

                });

            })
            .catch(() => {

                document.getElementById("notificationList")
                    .innerHTML =
                    "<p class='notification-empty'>Notifications unavailable.</p>";

            });

    }

    loadNotifications();

    setInterval(loadNotifications, 5000);

</script>

</body>
</html>
