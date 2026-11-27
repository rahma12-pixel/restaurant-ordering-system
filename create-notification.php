
<?php

// Send a notification to one specific user
function notifyUser($conn, $order_id, $user_id, $role, $message) {

    $stmt = $conn->prepare(
        "INSERT INTO order_notifications
        (order_id, recipient_user_id, recipient_role, message)
        VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "iiss",
        $order_id,
        $user_id,
        $role,
        $message
    );

    $stmt->execute();
    $stmt->close();
}


// Send a notification to all users with a particular role
function notifyRole($conn, $order_id, $role, $message) {

    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE role = ?"
    );

    $stmt->bind_param("s", $role);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($user = $result->fetch_assoc()) {

        notifyUser(
            $conn,
            $order_id,
            (int)$user['id'],
            $role,
            $message
        );
    }

    $stmt->close();
}

?>
