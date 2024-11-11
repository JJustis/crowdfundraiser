<?php
declare(strict_types=1);
error_reporting(E_ALL);


require_once 'config.php';
require_once 'ProjectManager.php';
require_once 'gateway-config.php';

final class PayPalPaymentProcessor {
    private const MIN_AMOUNT = 0.01;
    private const MAX_AMOUNT = 10000.00;
    
    private function debugValue($value, $label) {
        echo "<div style='background:#f8f9fa;padding:10px;margin:5px;border:1px solid #ddd;'>";
        echo "<strong>" . htmlspecialchars($label) . ":</strong> ";
        echo "<pre>" . htmlspecialchars(var_export($value, true)) . "</pre>";
        echo "</div>";
    }

    public function processPayment(): void {
        try {
            // Debug POST data
            $this->debugValue($_POST, 'POST Data');
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new InvalidArgumentException('Invalid request method');
            }

            // Get and validate raw amount
            $rawAmount = $_POST['amount'] ?? null;
            $this->debugValue($rawAmount, 'Raw Amount');
            
            // Remove any non-numeric characters except decimal point
            $cleanAmount = preg_replace('/[^0-9.]/', '', (string)$rawAmount);
            $this->debugValue($cleanAmount, 'Cleaned Amount');
            
            // Convert to float and format
            $amount = (float)$cleanAmount;
            $this->debugValue($amount, 'Float Amount');
            
            // Validate amount range
            if ($amount < self::MIN_AMOUNT || $amount > self::MAX_AMOUNT) {
                throw new InvalidArgumentException(
                    "Amount must be between " . self::MIN_AMOUNT . 
                    " and " . self::MAX_AMOUNT
                );
            }
            
            // Format to exactly 2 decimal places
            $formattedAmount = number_format($amount, 2, '.', '');
            $this->debugValue($formattedAmount, 'Formatted Amount');

            // Get and validate project ID
            $projectId = filter_var($_POST['projectIdDisplay'] ?? null, FILTER_SANITIZE_STRING);
            $this->debugValue($projectId, 'Project ID');
            
            if (!$projectId) {
                throw new InvalidArgumentException('Missing project ID');
            }

            // Get project details
            $projectManager = new ProjectManager();
            $project = $projectManager->getProject($projectId);
            
            if (!$project) {
                throw new RuntimeException('Project not found');
            }
            
            $this->debugValue($project, 'Project Data');
            
            // Generate order ID
            $orderId = uniqid('ORD_', true);
            
            // Store session data
            session_start([
                'cookie_secure' => true,
                'cookie_httponly' => true,
                'cookie_samesite' => 'Strict'
            ]);
            
            $_SESSION['pending_payment'] = [
                'order_id' => $orderId,
                'project_id' => $projectId,
                'amount' => $formattedAmount,
                'timestamp' => time()
            ];
            
            $this->debugValue($_SESSION['pending_payment'], 'Session Data');
            
            // Build PayPal URL with debug parameter
            $paypalUrl = 'https://www.paypal.com/cgi-bin/webscr';
            
            // Debug output all PayPal parameters
            $paypalParams = [
                'cmd' => '_xclick',
                'business' => $project['paypal_email'],
                'item_name' => $project['title'],
                'amount' => $formattedAmount,
                'currency_code' => PAYPAL_CURRENCY,
                'return' => PAYPAL_RETURN_URL,
                'cancel_return' => PAYPAL_CANCEL_URL,
                'notify_url' => PAYPAL_NOTIFY_URL,
                'custom' => $orderId,
                'item_number' => $projectId,
                'no_shipping' => '1',
                'no_note' => '1'
            ];
            
            $this->debugValue($paypalParams, 'PayPal Parameters');
            
            // Output form with debug option
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Payment Debug</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .debug { margin: 20px 0; padding: 10px; background: #f8f9fa; }
                    .form-container { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
                </style>
            </head>
            <body>
                <div class="form-container">
                    <h2>PayPal Form Data (Debug Mode)</h2>
                    <form id="paypal_form" action="<?= htmlspecialchars($paypalUrl) ?>" method="post">
                        <?php foreach ($paypalParams as $key => $value): ?>
                            <div>
                                <strong><?= htmlspecialchars($key) ?>:</strong>
                                <input type="text" name="<?= htmlspecialchars($key) ?>" 
                                       value="<?= htmlspecialchars($value) ?>" readonly>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit">Submit to PayPal</button>
                    </form>
                </div>
            </body>
            </html>
            <?php
            
        } catch (Exception $e) {
            error_log('Payment processing error: ' . $e->getMessage());
            echo "<div style='color:red;padding:10px;'>";
            echo "Error: " . htmlspecialchars($e->getMessage());
            echo "</div>";
            exit;
        }
    }
}

// Execute with debug output
$processor = new PayPalPaymentProcessor();
$processor->processPayment();