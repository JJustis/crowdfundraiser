<?php
// PayPal IPN Handler
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();

foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Read the post from PayPal and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

// Post back to PayPal system to validate
$ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSLVERSION, 6);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

$res = curl_exec($ch);

if (!$res) {
    $errno = curl_errno($ch);
    $errstr = curl_error($ch);
    curl_close($ch);
    error_log("cURL error: [$errno] $errstr");
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200) {
    error_log("PayPal responded with http code $http_code");
    exit;
}

// Parse PayPal response
if (strcmp($res, "VERIFIED") == 0) {
    // Check payment details
    $item_name = $_POST['item_name'];
    $item_number = $_POST['item_number']; // This should be the project ID
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];
    
    if ($payment_status == 'Completed') {
        // Load projects
        $projects = loadProjects();
        
        // Update project funding
        foreach ($projects as &$project) {
            if ($project['id'] === $item_number) {
                // Verify receiver email matches project's PayPal email
                if ($project['paypal_email'] === $receiver_email) {
                    $project['current'] += (float)$payment_amount;
                    
                    // Save transaction record
                    $transaction = [
                        'project_id' => $item_number,
                        'amount' => $payment_amount,
                        'currency' => $payment_currency,
                        'txn_id' => $txn_id,
                        'payer_email' => $payer_email,
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    
                    // Save transaction to transactions file
                    $transactionsFile = 'data/transactions.json';
                    $transactions = json_decode(file_get_contents($transactionsFile), true) ?? [];
                    $transactions[] = $transaction;
                    file_put_contents($transactionsFile, json_encode($transactions, JSON_PRETTY_PRINT));
                    
                    // Save updated projects
                    saveProjects($projects);
                    
                    error_log("Payment processed successfully: $payment_amount $payment_currency for project $item_number");
                } else {
                    error_log("PayPal email mismatch for project $item_number");
                }
                break;
            }
        }
    }
} else if (strcmp($res, "INVALID") == 0) {
    error_log("Invalid PayPal payment notification received");
}
?>