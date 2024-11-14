<?php
// Check if the project ID is set
if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];

    // Load the updates from a JSON file or database
    $updates_file = 'data/updates_' . $project_id . '.json';
    $updates = file_exists($updates_file) ? json_decode(file_get_contents($updates_file), true) : [];

    // Process new update submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $content = $_POST['content'];

        // Validate and sanitize the input
        $title = htmlspecialchars(trim($title));
        $content = htmlspecialchars(trim($content));

        if ($title && $content) {
            $new_update = ['title' => $title, 'content' => $content, 'timestamp' => time()];
            $updates[] = $new_update;
            file_put_contents($updates_file, json_encode($updates));
        }
    }
?>

<div class="updates-section">
    <h3>Project Updates</h3>
    <?php if (current_user_can_update_project($project_id)): ?>
    <form method="post" class="update-form">
        <div class="mb-3">
            <label for="title" class="form-label">Update Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Update Content</label>
            <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Post Update</button>
    </form>
    <?php endif; ?>

    <div class="update-list">
        <?php foreach ($updates as $update): ?>
        <div class="update-item">
            <h5><?= $update['title'] ?></h5>
            <p><?= $update['content'] ?></p>
            <small class="text-muted">Posted on <?= date('F j, Y, g:i a', $update['timestamp']) ?></small>
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