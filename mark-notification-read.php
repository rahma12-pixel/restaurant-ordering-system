
<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$notification_id = (int) ($_POST['notification_id'] ?? 0);

if ($notification_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification']);
    exit();
}

$stmt = $conn->prepare(
    "UPDATE order_notifications
     SET is_read = 1
     WHERE id = ?
     AND recipient_user_id = ?"
);

$stmt->bind_param("ii", $notification_id, $user_id);
$stmt->execute();

echo json_encode(['success' => true]);

$stmt->close();
?>
