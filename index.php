<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrowdFund Project</title>
    <!-- Correctly linked Bootstrap CSS -->
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
        #projectsList {
            max-height: 300px;
            overflow-y: auto;
        }
        .project-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            display: none;
            margin-top: 10px;
            border-radius: 4px;
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
            <a class="navbar-brand" href="#">CrowdFund</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <div class="project-card card mb-4">
                    <div class="card-header bg-transparent">
                        <div class="nav nav-pills" id="projectsList">
                            <!-- Project tabs will be loaded here -->
                        </div>
                    </div> 
                    <div class="card-body p-4">
                        <img id="projectImage" class="project-image mb-4" src="https://i.imgur.com/wwaPkQ4.png" alt="Project Image">
                        <h2 class="card-title" id="projectTitle">Select a project</h2>
                        <p class="card-text" id="projectDescription"></p>
                        
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" id="fundingProgress" role="progressbar" style="width: 0%"></div>
                        </div>
                        
                       <div class="mb-3">
    <p class="text-muted">
	<div id="projectLinkContainer"></div>
    <a href="#" id="projectIdDisplay" class="text-primary text-decoration-underline"></a>
    </p>
</div>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h5 class="mb-0" id="currentAmount">$0</h5>
                                <small class="text-muted">raised</small>
                            </div>
                            <div>
                                <h5 class="mb-0" id="goalAmount">$0</h5>
                                <small class="text-muted">goal</small>
                            </div>
                        </div>

                        <div id="downloadSection" class="download-button">
                            <a href="#" id="downloadButton" class="btn btn-success btn-lg w-100">
                                Download Project Files
                            </a>
                        </div>
                        <form id="paymentForm" action="process_payment.php" method="POST" class="mt-4">
						 <div class="mb-3">
                            <p class="text-muted" id="projectIdDisplay"><input name="projectIdDisplay" id="projectIdDisplay" placeholder="copy and paste the id here"></p>
                        </div>
                            
                            <div class="mb-3">
                                <label for="amount" class="form-label">Contribution Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           name="amount" 
                                           id="amount"
                                           min="1" 
                                           step="0.01" 
                                           required
                                           placeholder="Enter amount">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Support This Project
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card project-card">
                    <div class="card-body">
                        <h4>Create New Project</h4>
                        <form id="projectForm" action="api/create_project.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>Project Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label>Project Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*" required>
                                <img id="imagePreview" class="image-preview" alt="Image preview">
                            </div>
                            <div class="mb-3">
                                <label>Description</label>
                                <textarea class="form-control" name="description" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Funding Goal</label>
                                <input type="number" class="form-control" name="goal" required>
                            </div>
                            <div class="mb-3">
                                <label>PayPal Email</label>
                                <input type="email" class="form-control" name="paypal_email" required>
                            </div>
                            <div class="mb-3">
                                <label>Project Files</label>
                                <input type="file" class="form-control" name="reward_file" accept=".zip,.rar,.7z">
                                <small class="text-muted">Upload files for backers once goal is reached</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Project</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Correctly placed scripts at the end of body -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- Correctly linked main.js -->
    <script src="js/main.js"></script>
  <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the display element
            const displayElement = document.getElementById('projectIdDisplay');
            // Get the hidden input
            const hiddenInput = document.getElementById('projectIdDisplay');
            
            // Extract the ID from the display text
            const displayText = displayElement.textContent; // Gets "ID: XXX"
            const projectId = displayText.replace('ID: ', '').trim(); // Removes "ID: " and trims whitespace
            
            // Set the value in the hidden input if it's valid
            if (projectId && projectId !== 'N/A') {
                hiddenInput.value = projectId;
            }

            // Validate form before submission
            const form = document.getElementById('paymentForm');
            form.addEventListener('submit', function(e) {
                if (!hiddenInput.value || hiddenInput.value === 'N/A') {
                    e.preventDefault();
                    alert('Error: No valid project ID found. Please try again.');
                }
            });
        });
	


</script>

 
</body>
</html>