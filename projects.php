<?php
header('Content-Type: application/json');

// Configuration
$projectsFile = 'data/projects.json';
$projectsDir = 'data';
$uploadsDir = 'uploads';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Create necessary directories
foreach ([$projectsDir, $uploadsDir] as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Initialize projects file if it doesn't exist
if (!file_exists($projectsFile)) {
    file_put_contents($projectsFile, json_encode([]));
}

// Load projects
function loadProjects() {
    global $projectsFile;
    $data = file_get_contents($projectsFile);
    return json_decode($data, true) ?? [];
}

// Save projects
function saveProjects($projects) {
    global $projectsFile;
    file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT));
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $projects = loadProjects();

    if ($action === 'list') {
        echo json_encode($projects);
    }
    elseif ($action === 'get' && isset($_GET['id'])) {
        $project = array_values(array_filter($projects, function($p) {
            return $p['id'] === $_GET['id'];
        }))[0] ?? null;
        
        if ($project) {
            echo json_encode($project);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Project not found']);
        }
    }
}
function handleImageUpload($file) {
    global $uploadsDir, $allowedTypes, $maxFileSize;
    
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    if ($file['size'] > $maxFileSize) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new RuntimeException('Invalid file format.');
    }

    $filename = sprintf('%s-%s.%s',
        uniqid(),
        sha1_file($file['tmp_name']),
        pathinfo($file['name'], PATHINFO_EXTENSION)
    );

    $filepath = sprintf('%s/%s', $uploadsDir, $filename);

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $filepath;
}

// Handle POST requests (create new project)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $projects = loadProjects();
        
        // Handle image upload
        $imageUrl = null;
        if (isset($_FILES['image'])) {
            $imageUrl = handleImageUpload($_FILES['image']);
        }
        
        $newProject = [
            'id' => uniqid(),
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'goal' => (float)$_POST['goal'],
            'current' => 0,
            'paypal_email' => $_POST['paypal_email'],
            'image_url' => $imageUrl,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $projects[] = $newProject;
        saveProjects($projects);
        
        echo json_encode($newProject);
    } catch (RuntimeException $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Handle PUT requests (update project)
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $putData);
    $projects = loadProjects();
    
    foreach ($projects as &$project) {
        if ($project['id'] === $putData['id']) {
            $project = array_merge($project, $putData);
            break;
        }
    }
    
    saveProjects($projects);
    echo json_encode(['success' => true]);
}
?>