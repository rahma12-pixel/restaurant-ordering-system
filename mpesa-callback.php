
<?php
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$callback = $data['Body']['stkCallback'] ?? null;

if (!$callback) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback']);
    exit();
}

$checkoutRequestID = $callback['CheckoutRequestID'] ?? '';
$resultCode = (int)($callback['ResultCode'] ?? 1);

if ($checkoutRequestID === '') {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Missing request ID']);
    exit();
}

if ($resultCode === 0) {

    $receipt = '';

    $items = $callback['CallbackMetadata']['Item'] ?? [];

    foreach ($items as $item) {
        if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
            $receipt = (string)($item['Value'] ?? '');
            break;
        }
    }

    if ($receipt === '') {
        echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Receipt missing']);
        exit();
    }

    $stmt = $conn->prepare(
        "UPDATE orders
         SET `payment-status` = 'Paid',
             `mpesa-receipt` = ?,
             status = 'Confirmed'
         WHERE `mpesa-checkout-request-id` = ?
           AND `payment-status` = 'Pending'"
    );

    $stmt->bind_param("ss", $receipt, $checkoutRequestID);
    $stmt->execute();

} else {

    $stmt = $conn->prepare(
        "UPDATE orders
         SET `payment-status` = 'Failed'
         WHERE `mpesa-checkout-request-id` = ?
           AND `payment-status` = 'Pending'"
    );

    $stmt->bind_param("s", $checkoutRequestID);
    $stmt->execute();
}

echo json_encode([
    'ResultCode' => 0,
    'ResultDesc' => 'Callback processed'
]);
?>
