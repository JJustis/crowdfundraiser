<?php
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the comment data from the request body
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'];
    $email = $data['email'];
    $comment = $data['comment'];
    $projectId = $data['project_id']; // The project ID must be passed with the comment data

    // Validate and sanitize the input data
    $name = htmlspecialchars(trim($name));
    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    $comment = htmlspecialchars(trim($comment));
    
    // Validate the project ID (make sure it's a valid project)
    if (empty($projectId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Project ID is required']);
        exit;
    }

    // Save the comment to a file or database
    $comments_file = 'data/comments.json';
    $comments = file_exists($comments_file) ? json_decode(file_get_contents($comments_file), true) : [];
    
    // Append the new comment with the project_id
    $comments[] = [
        'name' => $name,
        'email' => $email,
        'comment' => $comment,
        'timestamp' => time(),
        'project_id' => $projectId  // Save the full project ID in the comment
    ];

    // Save the comments back to the file
    file_put_contents($comments_file, json_encode($comments));

    // Return a success response
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Comment saved successfully']);
} else {
    // Return an error response for non-POST requests
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
}
