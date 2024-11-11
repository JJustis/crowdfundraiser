<?php
require_once 'config.php';
require_once 'ProjectManager.php';

class DonationHandler {
    private $projectManager;
    private $logFile = 'donations_log.txt';
    
    public function __construct() {
        $this->projectManager = new ProjectManager();
    }
    
    private function logDonation($message) {
        $timestamp = date('[Y-m-d H:i:s] ');
        file_put_contents($this->logFile, $timestamp . $message . "\n", FILE_APPEND);
    }
    
    public function handleDonation($paypalData) {
        try {
            // Validate PayPal data
            if ($paypalData['payment_status'] !== 'Completed') {
                $this->logDonation("Payment not completed: " . $paypalData['payment_status']);
                return false;
            }
            
            // Get project ID and validate
            $project_id = $paypalData['item_number'];
            $project = $this->projectManager->getProject($project_id);
            
            if (!$project) {
                $this->logDonation("Project not found: " . $project_id);
                return false;
            }
            
            // Record the donation
            $amount = floatval($paypalData['mc_gross']);
            $payer_email = $paypalData['payer_email'];
            $txn_id = $paypalData['txn_id'];
            
            $success = $this->projectManager->recordDonation(
                $project_id,
                $amount,
                $payer_email,
                $txn_id
            );
            
            if ($success) {
                $this->logDonation("Successful donation: $amount to project $project_id");
                return true;
            } else {
                $this->logDonation("Failed to record donation for project $project_id");
                return false;
            }
            
        } catch (Exception $e) {
            $this->logDonation("Error processing donation: " . $e->getMessage());
            return false;
        }
    }
}