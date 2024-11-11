<?php
// PayPal Configuration
define('PAYPAL_SANDBOX', true); // Set to false for live environment
define('PAYPAL_CURRENCY', 'USD');
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'payment_errors.log');
?>