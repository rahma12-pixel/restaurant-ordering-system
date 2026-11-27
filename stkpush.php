
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$checkout = $_SESSION['checkout'] ?? [];

$customer_name = trim($checkout['customer_name'] ?? '');
$table_number = (int)($checkout['table_number'] ?? 0);
$phone = trim($_POST['phone'] ?? '');

if (empty($cart) || $customer_name === '' || $table_number <= 0) {
    die("Your checkout session has expired.");
}

if (!preg_match('/^254[17][0-9]{8}$/', $phone)) {
    die("Enter a valid phone number starting with 254.");
}

// Daraja Sandbox credentials
$shortcode = '174379';
$passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';

$timestamp = date('YmdHis');

$password = base64_encode(
    $shortcode . $passkey . $timestamp
);

// Get access token
$accessToken = require 'mpesa-token.php';

if (!$accessToken) {
    die("Unable to obtain M-Pesa access token.");
}

// Calculate order total
$total = 0;
$items = [];

$stmt = $conn->prepare(
    "SELECT id, price
     FROM menu
     WHERE id = ? AND availability = 1"
);

foreach ($cart as $menu_id => $quantity) {

    $menu_id = (int)$menu_id;
    $quantity = (int)$quantity;

    if ($menu_id <= 0 || $quantity <= 0) {
        die("Invalid order item.");
    }

    $stmt->bind_param("i", $menu_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $food = $result->fetch_assoc();

    if (!$food) {
        die("A selected food item is unavailable.");
    }

    $total += $food['price'] * $quantity;

    $items[] = [
        'menu_id' => $menu_id,
        'quantity' => $quantity,
        'price' => $food['price']
    ];
}

$stmt->close();

$amount = (int)ceil($total);

if ($amount < 1) {
    die("Invalid payment amount.");
}

// Create order as unpaid
try {

    $conn->begin_transaction();

    $customer_user_id = (int)$_SESSION['user_id'];

    $order_stmt = $conn->prepare(
        "INSERT INTO orders
        (customer_name, table_number, total_amount, status,
         `payment-method`, `payment-status`, customer_user_id)
        VALUES (?, ?, ?, 'Pending', 'M-Pesa', 'Pending', ?)"
    );

    $order_stmt->bind_param(
        "sidi",
        $customer_name,
        $table_number,
        $total,
        $customer_user_id
    );

    $order_stmt->execute();

    $order_id = $conn->insert_id;

    $item_stmt = $conn->prepare(
        "INSERT INTO order_items
        (order_id, menu_id, quantity, price)
        VALUES (?, ?, ?, ?)"
    );

    foreach ($items as $item) {

        $item_stmt->bind_param(
            "iiid",
            $order_id,
            $item['menu_id'],
            $item['quantity'],
            $item['price']
        );

        $item_stmt->execute();
    }

    $conn->commit();

} catch (Throwable $e) {

    $conn->rollback();
    die("Could not create your order. Please try again.");
}

// Safaricom STK Push endpoint
$url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

// Payment request
$payload = [
    'BusinessShortCode' => $shortcode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $shortcode,
    'PhoneNumber' => $phone,
    'CallBackURL' => 'YOUR_PUBLIC_HTTPS_CALLBACK_URL',
    'AccountReference' => 'ORDER-' . $order_id,
    'TransactionDesc' => 'DEVOURÉ Order Payment'
];

// Send request
$curl = curl_init($url);

curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($curl);

if ($response === false) {
    $error = curl_error($curl);
    curl_close($curl);

    die("M-Pesa connection error: " . htmlspecialchars($error));
}

$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$result = json_decode($response, true);

if (
    $httpCode !== 200 ||
    empty($result['CheckoutRequestID'])
) {
    die("M-Pesa request failed. Your order is still unpaid. Please contact the restaurant.");
}

// Save M-Pesa request ID against the order
$checkoutRequestID = $result['CheckoutRequestID'];

$update = $conn->prepare(
    "UPDATE orders
     SET `mpesa-checkout-request-id` = ?
     WHERE id = ?"
);

$update->bind_param("si", $checkoutRequestID, $order_id);
$update->execute();

$_SESSION['last_order_id'] = $order_id;
$_SESSION['mpesa_checkout_request_id'] = $checkoutRequestID;

unset($_SESSION['cart']);
unset($_SESSION['checkout']);

echo "<h2>M-Pesa Payment Request Sent</h2>";
echo "<p>Order Number: " . (int)$order_id . "</p>";
echo "<p>Check your phone for the payment prompt.</p>";
echo "<p>Your order will remain pending until payment is verified.</p>";

?>
