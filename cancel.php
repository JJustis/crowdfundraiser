// cancel.php
<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Cancelled</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body text-center">
                <h1 class="card-title text-warning">Payment Cancelled</h1>
                <p class="card-text">Your payment has been cancelled. No charges were made.</p>
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">Return to Projects</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>