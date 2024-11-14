<?php
// Check if the project ID is set
if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];

    // Load the comments from a JSON file or database
    $comments_file = 'data/comments_' . $project_id . '.json';
    $comments = file_exists($comments_file) ? json_decode(file_get_contents($comments_file), true) : [];

    // Process new comment submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $comment = $_POST['comment'];

        // Validate and sanitize the input
        $name = htmlspecialchars(trim($name));
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        $comment = htmlspecialchars(trim($comment));

        if ($name && $email && $comment) {
            $new_comment = ['name' => $name, 'email' => $email, 'comment' => $comment, 'timestamp' => time()];
            $comments[] = $new_comment;
            file_put_contents($comments_file, json_encode($comments));
        }
    }
?>

<div class="comments-section">
    <h3>Project Comments</h3>
    <form method="post" class="comment-form">
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
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <div class="comment-list">
        <?php foreach ($comments as $comment): ?>
        <div class="comment-item">
            <h5><?= $comment['name'] ?></h5>
            <p><?= $comment['comment'] ?></p>
            <small class="text-muted">Posted on <?= date('F j, Y, g:i a', $comment['timestamp']) ?></small>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
} else {
    echo "No project ID provided.";
}
?>
</div>