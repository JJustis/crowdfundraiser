<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrowdFund Project</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress {
            height: 25px;
        }
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
                        <img id="projectImage" class="project-image mb-4" src="/api/placeholder/800/400" alt="Project Image">
                        <h2 class="card-title" id="projectTitle">Select a project</h2>
                        <p class="card-text" id="projectDescription"></p>
                        
                        
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" id="fundingProgress" role="progressbar" style="width: 0%"></div>
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

                        <form id="paymentForm" action="process_payment.php" method="POST">
                            <input type="hidden" name="project_id" id="currentProjectId">
                            <div class="mb-3">
                                <input type="number" class="form-control" name="amount" placeholder="Enter amount" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">Support This Project</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card project-card">
                    <div class="card-body">
                        <h4>Create New Project</h4>
                         <form id="projectForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Project Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Project Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*" required>
                                <img id="imagePreview" class="image-preview" alt="Image preview">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Funding Goal</label>
                                <input type="number" class="form-control" name="goal" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">PayPal Email</label>
                                <input type="email" class="form-control" name="paypal_email" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Project</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
      <script>
        // Project handling
        let currentProject = null;
        let projects = [];

        // Load projects from server
        async function loadProjects() {
            try {
                const response = await fetch('projects.php?action=list');
                projects = await response.json();
                updateProjectsList();
                if (projects.length > 0) {
                    loadProject(projects[0].id);
                }
            } catch (error) {
                console.error('Error loading projects:', error);
            }
        }

        // Update projects list in UI
        function updateProjectsList() {
            const projectsList = document.getElementById('projectsList');
            projectsList.innerHTML = projects.map(project => `
                <button class="nav-link ${currentProject && currentProject.id === project.id ? 'active' : ''}"
                        onclick="loadProject('${project.id}')"
                        type="button">
                    ${project.title}
                </button>
            `).join('');
        }

        // Load specific project
        async function loadProject(projectId) {
            try {
                const response = await fetch(`projects.php?action=get&id=${projectId}`);
                currentProject = await response.json();
                updateProjectDisplay();
                document.getElementById('currentProjectId').value = projectId;
                
                // Update active state in projects list
                const buttons = document.querySelectorAll('#projectsList button');
                buttons.forEach(button => {
                    button.classList.toggle('active', button.textContent.trim() === currentProject.title);
                });
            } catch (error) {
                console.error('Error loading project:', error);
            }
        }

        // Update project display
        function updateProjectDisplay() {
            if (!currentProject) {
                document.getElementById('projectTitle').textContent = 'Select a project';
                document.getElementById('projectDescription').textContent = '';
                document.getElementById('currentAmount').textContent = '$0';
                document.getElementById('goalAmount').textContent = '$0';
                document.getElementById('fundingProgress').style.width = '0%';
                document.getElementById('projectImage').src = '/api/placeholder/800/400';
                return;
            }

            document.getElementById('projectTitle').textContent = currentProject.title;
            document.getElementById('projectDescription').textContent = currentProject.description;
            document.getElementById('currentAmount').textContent = `$${currentProject.current.toLocaleString()}`;
            document.getElementById('goalAmount').textContent = `$${currentProject.goal.toLocaleString()}`;
            document.getElementById('projectImage').src = currentProject.image_url || '/api/placeholder/800/400';
            
            const progress = (currentProject.current / currentProject.goal) * 100;
            document.getElementById('fundingProgress').style.width = `${Math.min(progress, 100)}%`;
            document.getElementById('fundingProgress').setAttribute('aria-valuenow', progress);
        }

        // Add image preview functionality
        document.querySelector('input[name="image"]').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Project form submission
        document.getElementById('projectForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const response = await fetch('projects.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const newProject = await response.json();
                
                // Add new project to projects array
                projects.push(newProject);
                updateProjectsList();
                
                // Switch to the new project
                await loadProject(newProject.id);
                
                // Reset form and image preview
                this.reset();
                document.getElementById('imagePreview').style.display = 'none';
                
                // Show success message
                alert('Project created successfully!');
                
            } catch (error) {
                console.error('Error creating project:', error);
                alert('Error creating project. Please try again.');
            }
        });

        // Payment form submission
        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                if (result.success) {
                    // Reload current project to show updated amount
                    await loadProject(currentProject.id);
                    alert('Payment processed successfully!');
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                alert('Error processing payment. Please try again.');
            }
            
            this.reset();
        });

        // Set up automatic refresh of current project
        setInterval(async () => {
            if (currentProject) {
                await loadProject(currentProject.id);
            }
        }, 30000); // Refresh every 30 seconds

        // Load projects on page load
        loadProjects();
		</script>
</body>
</html>