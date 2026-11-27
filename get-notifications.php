
<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'error' => 'Not logged in'
    ]);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT id, order_id, message, is_read, created_at
     FROM order_notifications
     WHERE recipient_user_id = ?
     ORDER BY created_at DESC
     LIMIT 20"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$notifications = [];
$unread_count = 0;

while ($row = $result->fetch_assoc()) {

    if ((int)$row['is_read'] === 0) {
        $unread_count++;
    }

    $notifications[] = $row;
}

$stmt->close();

echo json_encode([
    'unread_count' => $unread_count,
    'notifications' => $notifications
]);
?>
