<?php
// Check if the project ID is set in the URL
if (isset($_GET['id'])) {
    $projectId = $_GET['id']; // Retrieve the full project ID from the URL

    // Load the project details from a JSON file or database
    $projects_file = 'products.json';
    $projects = json_decode(file_get_contents($projects_file), true);
    $project = null;

    // Find the project with the matching ID
    foreach ($projects as $p) {
        if ($p['id'] == $projectId) {  // Check if the full project ID matches
            $project = $p;
            break;
        }
    }

    if ($project) {
        // Load the comments from the comments file
        $comments_file = 'data/comments.json';
        $comments = file_exists($comments_file) ? json_decode(file_get_contents($comments_file), true) : [];

        // Filter comments by project_id (only show comments for the current project)
        $filtered_comments = array_filter($comments, function($comment) use ($projectId) {
            return $comment['project_id'] === $projectId; // Only return comments for this project ID
        });

        // Reset the array keys after filtering (for proper display)
        $filtered_comments = array_values($filtered_comments);
    } else {
        // Project not found
        http_response_code(404);
        echo "Project not found.";
        exit;
    }
} else {
    // No project ID provided
    http_response_code(400);
    echo "No project ID provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $project['title'] ?> - CrowdFund</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url("bgimage.webp");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .progress { height: 25px; }
        .project-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .project-card:hover {
            transform: translateY(-5px);
        }
        .nav-pills .nav-link.active {
            background-color: #1e88e5;
        }
        .form-control:focus {
            border-color: #1e88e5;
            box-shadow: 0 0 0 0.2rem rgba(30, 136, 229, 0.25);
        }
        .project-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .download-button {
            display: none;
            margin-top: 20px;
        }
        .download-button.available {
            display: block;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">CrowdFund</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <!-- Project Content Column -->
            <div class="col-md-8">
                <div class="project-card card mb-4">
                    <div class="card-body p-4">
                        <img src="<?= $project['image_path'] ?>" class="project-image mb-4" alt="Project Image">
                        <h2 class="card-title"><?= $project['title'] ?></h2>
                        <p class="card-text"><?= $project['description'] ?></p>

                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= round(($project['current_amount'] / $project['goal']) * 100, 2) ?>%"></div>
                        </div>

                        <div class="mb-3">
                            <p class="text-muted">ID: <?= $project['id'] ?></p>
                        </div>

                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h5 class="mb-0">$<?= number_format($project['current_amount'], 2) ?></h5>
                                <small class="text-muted">raised</small>
                            </div>
                            <div>
                                <h5 class="mb-0">$<?= number_format($project['goal'], 2) ?></h5>
                                <small class="text-muted">goal</small>
                            </div>
                        </div>

                        <?php if (!empty($project['reward_file_path'])): ?>
                        <div id="downloadSection" class="download-button available">
                            <a href="<?= $project['reward_file_path'] ?>" class="btn btn-success btn-lg w-100" download>
                                Download Project Files
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Comments Section Column (Right) -->
            <div class="col-md-4">
                <div id="comments-section" class="card mb-4">
    <div class="card-body p-4">
        <h3>Project Comments</h3>
        <form id="commentForm" method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="comment" class="form-label">Comment</label>
                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
            </div>
            <input type="hidden" id="project_id" value="<?= $project['id'] ?>"> <!-- Add the project ID as hidden input -->
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <div class="comment-list mt-4" id="commentList">
            <!-- Comments will be dynamically loaded here -->
        </div>
    </div>
</div>

            </div>

        </div>
    </div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const projectId = document.getElementById("project_id").value;

    // Fetch and display comments for the current project
    fetchComments(projectId);

    // Submit comment form
    document.getElementById("commentForm").addEventListener("submit", function(event) {
        event.preventDefault();
        
        const name = document.getElementById("name").value;
        const email = document.getElementById("email").value;
        const comment = document.getElementById("comment").value;

        // Send the comment to the server with the project ID
        fetch("save_comment.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ name, email, comment, project_id: projectId }) // Include project ID in the request
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                // Comment saved successfully, fetch and display updated comments
                fetchComments(projectId);
                // Clear form inputs
                document.getElementById("commentForm").reset();
            } else {
                alert(data.error);
            }
        })
        .catch(error => console.error("Error saving comment:", error));
    });

    // Function to fetch and display comments for the current project
    function fetchComments(projectId) {
        fetch('data/comments.json')
            .then(response => response.json())
            .then(data => {
                const commentList = document.getElementById("commentList");
                commentList.innerHTML = ''; // Clear existing comments

                // Filter comments based on project ID
                const projectComments = data.filter(comment => comment.project_id == projectId);

                projectComments.forEach(comment => {
                    const commentElement = document.createElement('div');
                    commentElement.classList.add('comment-item', 'mb-3');
                    commentElement.innerHTML = `
                        <h5>${comment.name}</h5>
                        <p>${comment.comment}</p>
                        <small class="text-muted">Posted on ${new Date(comment.timestamp * 1000).toLocaleString()}</small>
                    `;
                    commentList.appendChild(commentElement);
                });

                // If no comments, show a placeholder message
                if (projectComments.length === 0) {
                    commentList.innerHTML = "<p>No comments yet. Be the first to comment!</p>";
                }
            })
            .catch(error => console.error('Error loading comments:', error));
    }
});
</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
