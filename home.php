
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DEVOURÉ | A Taste Worth Remembering</title>

    <link rel="stylesheet" href="style.css">

    <style>
        /* Remove unwanted page spacing and column layout */
        * {
            box-sizing: border-box;
        }

        body.page-layout {
            display: block !important;
            width: 100%;
            margin: 0 !important;
            overflow-x: hidden;
        }

        .home-main {
            display: block !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            float: none !important;
        }

        /* Full-width homepage image */
        .home-hero {
            width: 100%;
            height: 90vh;
            min-height: 500px;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .home-hero img {
            display: block;
            width: 100% !important;
            height: 100% !important;
            max-height: none !important;
            object-fit: cover;
            border-radius: 0 !important;
            margin: 0 !important;
        }

        /* Welcome text and restaurant cards */
        .home-intro,
        .home-explore {
            width: 100%;
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .home-card-container {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            width: 100%;
        }

        /* Notification bell */
        .notification-container {
            position: relative;
        }

        .notification-button {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
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
            cursor: pointer;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item.unread {
            background-color: #EAF3F8;
            font-weight: bold;
        }

        .notification-empty {
            padding: 10px;
            text-align: center;
        }

        @media (max-width: 750px) {
            .home-card-container {
                grid-template-columns: 1fr;
            }

            .home-hero {
                height: 65vh;
                min-height: 350px;
            }
        }
    </style>
</head>

<body class="page-layout">

    <!-- Welcome Section -->
    <section class="welcome">

        <h1>DEVOURÉ</h1>

        <p style="color: white;">
            A Taste Worth Remembering
        </p>

    </section>

    <!-- Navigation Bar -->
    <nav>
        <ul>

            <li><a href="home.php">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="order.php">Order</a></li>
            <li><a href="contact.html">Contact</a></li>

            <li class="notification-container">

                <button
                    type="button"
                    class="notification-button"
                    onclick="toggleNotifications()"
                >
                    🔔 Notifications

                    <span
                        id="notificationCount"
                        class="notification-count"
                        style="display:none;"
                    >0</span>
                </button>

                <div
                    id="notificationPanel"
                    class="notification-panel"
                >

                    <h3>Order Notifications</h3>

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

    <main class="home-main">

        <!-- Full-screen homepage image -->
        <div class="home-hero">

            <img
                src="img/homepage.jpeg"
                alt="DEVOURÉ Restaurant"
            >

        </div>

        <!-- Welcome Message -->
        <section class="home-intro">

            <h2>Welcome to DEVOURÉ</h2>

            <p>
                Enjoy a wonderful dining experience. Browse our menu
                and order your favourite meals directly from your table.
            </p>

        </section>

        <!-- Restaurant Cards -->
        <section class="home-explore">

            <h2>Explore Our Restaurant</h2>

            <div class="home-card-container">

                <article class="card">

                    <h3>Our Menu</h3>

                    <p>
                        Discover our delicious selection of meals
                        and refreshing drinks.
                    </p>

                    <a href="menu.php">View Menu</a>

                </article>

                <article class="card">

                    <h3>Place an Order</h3>

                    <p>
                        Order your favourite meals conveniently
                        using our self-ordering system.
                    </p>

                    <a href="order.php">Order Now</a>

                </article>

                <article class="card">

                    <h3>Our Services</h3>

                    <p>
                        Discover the services we offer to make
                        your dining experience enjoyable.
                    </p>

                    <a href="services.html">Learn More</a>

                </article>

            </div>

        </section>

    </main>

    <!-- Footer -->
    <footer>

        <p style="color: white;">

            <a href="services.html">Services</a>
            <a href="about.html">About</a>
            <a href="faq.html">FAQ</a>

        </p>

        <p style="color: white; margin-top: 12px;">

            &copy; 2026 DEVOURÉ.
            A Taste Worth Remembering.

        </p>

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
                        list.textContent = "Unable to load notifications.";
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

                        list.textContent = "No notifications yet.";
                        return;
                    }

                    data.notifications.forEach(notification => {

                        const item = document.createElement("div");

                        item.className = "notification-item";

                        if (Number(notification.is_read) === 0) {
                            item.classList.add("unread");
                        }

                        item.textContent =
                            notification.message + " — " +
                            notification.created_at;

                        item.addEventListener("click", function() {

                            if (Number(notification.is_read) === 1) {
                                return;
                            }

                            const formData = new URLSearchParams();

                            formData.append(
                                "notification_id",
                                notification.id
                            );

                            fetch("mark-notification-read.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type":
                                        "application/x-www-form-urlencoded"
                                },
                                body: formData.toString()
                            })
                            .then(response => response.json())
                            .then(result => {

                                if (result.success) {
                                    loadNotifications();
                                }

                            });

                        });

                        list.appendChild(item);

                    });

                })
                .catch(() => {

                    document.getElementById("notificationList")
                        .textContent = "Notifications unavailable.";

                });

        }

        loadNotifications();

        setInterval(loadNotifications, 5000);

    </script>

</body>
</html>
