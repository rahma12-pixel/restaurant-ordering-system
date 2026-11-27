
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_allergen'])) {

        $menu_id = (int)($_POST['menu_id'] ?? 0);
        $allergen_name = trim($_POST['allergen_name'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($menu_id > 0 && $allergen_name !== '') {

            $stmt = $conn->prepare(
                "INSERT INTO allergens (menu_id, allergen_name, notes)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param("iss", $menu_id, $allergen_name, $notes);

            if ($stmt->execute()) {
                $message = "Allergen added successfully.";
            } else {
                $message = "Could not add allergen.";
            }

        } else {
            $message = "Please select a food item and enter an allergen.";
        }
    }

    if (isset($_POST['delete_allergen'])) {

        $allergen_id = (int)($_POST['allergen_id'] ?? 0);

        if ($allergen_id > 0) {
            $stmt = $conn->prepare(
                "DELETE FROM allergens WHERE id = ?"
            );

            $stmt->bind_param("i", $allergen_id);

            if ($stmt->execute()) {
                $message = "Allergen deleted successfully.";
            } else {
                $message = "Could not delete allergen.";
            }
        }
    }
}

$menu_items = $conn->query(
    "SELECT id, food_name FROM menu ORDER BY food_name ASC"
);

$allergens = $conn->query(
    "SELECT allergens.id, menu.food_name,
            allergens.allergen_name, allergens.notes
     FROM allergens
     INNER JOIN menu ON allergens.menu_id = menu.id
     ORDER BY allergens.id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Allergens</title>
    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<body>

<div class="main-content">

    <header>
        <h1>Manage Food Allergens</h1>
        <a href="admin-dashboard.php">Back to Dashboard</a>
    </header>

    <main>

        <h2>Add Allergen</h2>

        <?php if ($message !== '') { ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <form method="POST">

            <label>Select Food Item</label>

            <select name="menu_id" required>
                <option value="">Choose Food Item</option>

                <?php while ($item = $menu_items->fetch_assoc()) { ?>

                    <option value="<?php echo (int)$item['id']; ?>">
                        <?php echo htmlspecialchars($item['food_name']); ?>
                    </option>

                <?php } ?>

            </select>

            <br><br>

            <label>Allergen Name</label>

            <input
                type="text"
                name="allergen_name"
                placeholder="Example: Milk, Eggs, Peanuts"
                required
            >

            <br><br>

            <label>Notes</label>

            <textarea
                name="notes"
                placeholder="Additional allergen information"
            ></textarea>

            <br><br>

            <button type="submit" name="add_allergen">
                Add Allergen
            </button>

        </form>

        <h2>Saved Allergens</h2>

        <?php if ($allergens && $allergens->num_rows > 0) { ?>

            <table>

                <tr>
                    <th>Food Item</th>
                    <th>Allergen</th>
                    <th>Notes</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = $allergens->fetch_assoc()) { ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($row['food_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['allergen_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['notes'] ?? ''); ?>
                        </td>

                        <td>

                            <form method="POST">
                                <input
                                    type="hidden"
                                    name="allergen_id"
                                    value="<?php echo (int)$row['id']; ?>"
                                >

                                <button
                                    type="submit"
                                    name="delete_allergen"
                                    onclick="return confirm('Delete this allergen?')"
                                >
                                    Delete
                                </button>
                            </form>

                        </td>

                    </tr>

                <?php } ?>

            </table>

        <?php } else { ?>

            <p>No allergens have been added yet.</p>

        <?php } ?>

    </main>

</div>

</body>
</html>
