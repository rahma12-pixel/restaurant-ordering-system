
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_menu'])) {

        $food_name = trim($_POST['food_name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category = trim($_POST['category']);
        $image = trim($_POST['image']);

        if ($food_name !== '' && $price > 0) {

            $stmt = $conn->prepare(
                "INSERT INTO menu (food_name, description, price, image, category, availability)
                 VALUES (?, ?, ?, ?, ?, 1)"
            );

            $stmt->bind_param(
                "ssdss",
                $food_name,
                $description,
                $price,
                $image,
                $category
            );

            if ($stmt->execute()) {
                $message = "Menu item added successfully!";
            } else {
                $message = "Could not add menu item.";
            }

        } else {
            $message = "Enter a food name and a valid price.";
        }
    }

    if (isset($_POST['delete_menu'])) {

        $menu_id = (int)$_POST['menu_id'];

        $stmt = $conn->prepare(
            "UPDATE menu SET availability = 0 WHERE id = ?"
        );

        $stmt->bind_param("i", $menu_id);

        if ($stmt->execute()) {
            $message = "Menu item removed from the menu.";
        }
    }
}

$result = $conn->query(
    "SELECT * FROM menu ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu</title>
    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<body>

<div class="main-content">

    <header>
        <h1>Manage Restaurant Menu</h1>
        <a href="admin-dashboard.php">Back to Dashboard</a>
    </header>

    <main>

        <?php if ($message !== '') { ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <section class="management-card">

            <h2>Add New Menu Item</h2>

            <form method="POST">

                <label>Food Name:</label>
                <input type="text" name="food_name" required>

                <label>Description:</label>
                <textarea name="description"></textarea>

                <label>Price (KSh):</label>
                <input type="number" name="price" min="1" step="0.01" required>

                <label>Category:</label>
                <input type="text" name="category" placeholder="Food or Drink">

                <label>Image Path:</label>
                <input type="text" name="image" placeholder="img/burger.jpeg">

                <button type="submit" name="add_menu">
                    Add Menu Item
                </button>

            </form>

        </section>

        <section>

            <h2>Existing Menu Items</h2>

            <?php if ($result && $result->num_rows > 0) { ?>

                <table>
                    <tr>
                        <th>ID</th>
                        <th>Food Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Availability</th>
                        <th>Action</th>
                    </tr>

                    <?php while ($food = $result->fetch_assoc()) { ?>

                        <tr>
                            <td><?php echo (int)$food['id']; ?></td>

                            <td>
                                <?php echo htmlspecialchars($food['food_name']); ?>
                            </td>

                            <td>
                                KSh <?php echo number_format($food['price'], 2); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($food['category']); ?>
                            </td>

                            <td>
                                <?php echo $food['availability'] ? 'Available' : 'Unavailable'; ?>
                            </td>

                            <td>
                                <?php if ($food['availability']) { ?>
                                    <form method="POST">
                                        <input type="hidden" name="menu_id"
                                               value="<?php echo (int)$food['id']; ?>">

                                        <button type="submit" name="delete_menu">
                                            Remove
                                        </button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>

                    <?php } ?>

                </table>

            <?php } else { ?>

                <p>No menu items found.</p>

            <?php } ?>

        </section>

    </main>

</div>

</body>
</html>
