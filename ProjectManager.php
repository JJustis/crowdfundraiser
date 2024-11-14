<?php
// ProjectManager.php
class ProjectManager {
    private $productsFile;
    private $donationsFile;
    
    public function __construct() {
        $this->productsFile = PRODUCTS_FILE;
        $this->donationsFile = DONATIONS_FILE;
        $this->initializeFiles();
    }
    
    private function initializeFiles() {
        if (!file_exists($this->productsFile)) {
            file_put_contents($this->productsFile, json_encode([]));
        }
        if (!file_exists($this->donationsFile)) {
            file_put_contents($this->donationsFile, json_encode([]));
        }
    }
    
    public function getProjects() {
        return json_decode(file_get_contents($this->productsFile), true) ?: [];
    }
    
    public function getProject($id) {
        $projects = $this->getProjects();
        return $projects[$id] ?? null;
    }
    
    public function createProject($data, $files) {
        $projects = $this->getProjects();
        
        // Generate unique ID
        $id = uniqid();
        
        // Handle image upload
        $image_path = '';
        if (isset($files['image'])) {
            $image = $files['image'];
			$image_link = "https://jcmc.serveminecraft.net/crowdfunder\/uploads\/" . $id . '_' . basename($image['name']);;
            $image_path = UPLOAD_DIR . $id . '_' . basename($image['name']);
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            move_uploaded_file($image['tmp_name'], $image_path);
        }
        
        // Handle project files upload
        $reward_file_path = '';
        if (isset($files['reward_file'])) {
            $reward_file = $files['reward_file'];
            $reward_file_path = PROJECT_FILES_DIR . $id . '_' . basename($reward_file['name']);
            if (!is_dir(PROJECT_FILES_DIR)) {
                mkdir(PROJECT_FILES_DIR, 0755, true);
            }
            move_uploaded_file($reward_file['tmp_name'], $reward_file_path);
        }
        
        // Create project data
        $projects[$id] = [
            'id' => $id,
			'link' => "https://jcmc.serveminecraft.net/crowdfunder/project.php?id=".$id,
            'title' => $data['title'],
            'description' => $data['description'],
            'image_path' => $image_link,
            'goal' => floatval($data['goal']),
            'current_amount' => 0,
            'paypal_email' => $data['paypal_email'],
            'reward_file_path' => $reward_file_path,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($this->productsFile, json_encode($projects, JSON_PRETTY_PRINT));
        return $id;
    }
    
    public function recordDonation($projectId, $amount, $payerEmail, $transactionId) {
        $donations = json_decode(file_get_contents($this->donationsFile), true) ?: [];
        $projects = $this->getProjects();
        
        if (!isset($projects[$projectId])) {
            return false;
        }
        
        // Record donation
        $donations[] = [
            'project_id' => $projectId,
            'amount' => $amount,
            'payer_email' => $payerEmail,
            'transaction_id' => $transactionId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Update project amount
        $projects[$projectId]['current_amount'] += $amount;
        
        file_put_contents($this->donationsFile, json_encode($donations, JSON_PRETTY_PRINT));
        file_put_contents($this->productsFile, json_encode($projects, JSON_PRETTY_PRINT));
        return true;
    }
}
?>