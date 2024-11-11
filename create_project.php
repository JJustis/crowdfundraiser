<?php
require_once '../config.php';
require_once '../ProjectManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

try {
    $projectManager = new ProjectManager();
    $projectId = $projectManager->createProject($_POST, $_FILES);
    
    echo json_encode([
        'success' => true,
        'id' => $projectId
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
