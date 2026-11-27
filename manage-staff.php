
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update_role'])) {

        $user_id = (int)$_POST['user_id'];
        $role = $_POST['role'];

        $allowed_roles = ['admin', 'kitchen', 'waiter'];

        if ($user_id > 0 && in_array($role, $allowed_roles, true)) {

            if ($user_id === (int)$_SESSION['user_id']) {
                $message = "You cannot change your own role.";
            } else {
                $stmt = $conn->prepare(
                    "UPDATE users SET role = ? WHERE id = ?"
                );

                $stmt->bind_param("si", $role, $user_id);

                if ($stmt->execute()) {
                    $message = "Staff role updated successfully.";
                } else {
                    $message = "Could not update staff role.";
                }
            }
        }
    }

    if (isset($_POST['delete_staff'])) {

        $user_id = (int)$_POST['user_id'];

        if ($user_id === (int)$_SESSION['user_id']) {
            $message = "You cannot remove your own account.";
        } elseif ($user_id > 0) {

            $stmt = $conn->prepare(
                "DELETE FROM users WHERE id = ?"
            );

            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                $message = "Staff account removed successfully.";
            } else {
                $message = "Could not remove staff account.";
            }
        }
    }
}

$result = $conn->query(
    "SELECT id, name, role FROM users ORDER BY id ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff</title>
    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<body>

<div class="main-content">

    <header>
        <h1>Manage Restaurant Staff</h1>
        <a href="admin-dashboard.php">Back to Dashboard</a>
    </header>

    <main>

        <?php if ($message !== '') { ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>

        <h2>Registered Staff Members</h2>

        <?php if ($result && $result->num_rows > 0) { ?>

            <table>

                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Current Role</th>
                    <th>Update Role</th>
                    <th>Action</th>
                </tr>

                <?php while ($staff = $result->fetch_assoc()) { ?>

                    <tr>

                        <td><?php echo (int)$staff['id']; ?></td>

                        <td>
                            <?php echo htmlspecialchars($staff['name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($staff['role']); ?>
                        </td>

                        <td>

                            <?php if ((int)$staff['id'] !== (int)$_SESSION['user_id']) { ?>

                                <form method="POST">

                                    <input type="hidden" name="user_id"
                                           value="<?php echo (int)$staff['id']; ?>">

                                    <select name="role" required>

                                        <option value="admin">Admin</option>
                                        <option value="kitchen">Kitchen</option>
                                        <option value="waiter">Waiter</option>

                                    </select>

                                    <button type="submit" name="update_role">
                                        Update
                                    </button>

                                </form>

                            <?php } else { ?>

                                Your Account

                            <?php } ?>

                        </td>

                        <td>

                            <?php if ((int)$staff['id'] !== (int)$_SESSION['user_id']) { ?>

                                <form method="POST"
                                      onsubmit="return confirm('Are you sure you want to remove this staff account?');">

                                    <input type="hidden" name="user_id"
                                           value="<?php echo (int)$staff['id']; ?>">

                                    <button type="submit" name="delete_staff">
                                        Remove
                                    </button>

                                </form>

                            <?php } ?>

                        </td>

                    </tr>

                <?php } ?>

            </table>

        <?php } else { ?>

            <p>No staff members found.</p>

        <?php } ?>

    </main>

</div>

</body>
</html>
