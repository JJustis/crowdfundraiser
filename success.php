// success.php
<?php
require_once 'config.php';
require_once 'ProjectManager.php';

session_start();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-body text-center">
                <h1 class="card-title text-success">Thank You!</h1>
                <p class="card-text">Your contribution has been processed successfully.</p>
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">Return to Projects</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>