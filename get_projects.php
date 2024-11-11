<?php
require_once '../config.php';
require_once '../ProjectManager.php';

header('Content-Type: application/json');

try {
    $projectManager = new ProjectManager();
    $projects = $projectManager->getProjects();
    
    // Convert associative array to indexed array while preserving all data
    $projectsArray = array_values($projects);
    
    echo json_encode($projectsArray);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}