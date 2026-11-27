<?php

$consumerKey = 'DLO87FkO7q3pR6LmHWoBr8C7pAL2wUlT9IArcBad9NoLh5pG';
$consumerSecret = 'BSWfHQ99esxAQ6GY09xxtEswBqbsyAW81P72GN6jLEkAYNIADGIEOrGWUi3AEHyt';

$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

$credentials = base64_encode($consumerKey . ':' . $consumerSecret);

$curl = curl_init($url);

curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . $credentials
]);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);

if ($response === false) {
    die('Connection error: ' . curl_error($curl));
}

$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

if ($httpCode !== 200) {
    die('Could not obtain M-Pesa access token. HTTP Code: ' . $httpCode);
}

$result = json_decode($response, true);

if (!isset($result['access_token'])) {
    die('Access token was not received.');
}

return $result['access_token'];

?>