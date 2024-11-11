// main.js
document.addEventListener('DOMContentLoaded', function() {
    loadProjects();
    setupFormHandlers();
});

async function loadProjects() {
    try {
        const response = await fetch('api/get_projects.php');
        const text = await response.text(); // Get raw response
        console.log('Raw response:', text); // Debug log
        
        const projects = JSON.parse(text);
        console.log('Parsed projects:', projects); // Debug log
        
        const projectsList = document.getElementById('projectsList');
        if (!projectsList) {
            console.error('projectsList element not found');
            return;
        }
        
        projectsList.innerHTML = '';
        
        if (projects.length === 0) {
            console.log('No projects found');
            projectsList.innerHTML = '<p>No projects available.</p>';
            return;
        }
        
        projects.forEach((project, index) => {
            const button = document.createElement('button');
            button.className = `nav-link ${index === 0 ? 'active' : ''}`;
            button.setAttribute('data-project-id', project.id);
            button.textContent = project.title;
            button.onclick = () => displayProject(project);
            projectsList.appendChild(button);
        });
        
        if (projects.length > 0) {
            displayProject(projects[0]);
        }
    } catch (error) {
        console.error('Error loading projects:', error);
        const projectsList = document.getElementById('projectsList');
        if (projectsList) {
            projectsList.innerHTML = '<p>Error loading projects. Check console for details.</p>';
        }
    }
}

function displayProject(project) {
    document.getElementById('projectTitle').textContent = project.title;
    document.getElementById('projectDescription').textContent = project.description;
    document.getElementById('projectImage').src = project.imagepath || 'https://i.imgur.com/wwaPkQ4.png';
    document.getElementById('projectIdDisplay').textContent = `ID: ${project.id}`;
    document.getElementById('projectIdDisplay').value = project.id;
    
    const progress = (project.current_amount / project.goal) * 100;
    document.getElementById('fundingProgress').style.width = `${Math.min(100, progress)}%`;
    
    document.getElementById('currentAmount').textContent = `$${project.current_amount.toLocaleString()}`;
    document.getElementById('goalAmount').textContent = `$${project.goal.toLocaleString()}`;
    
    const downloadSection = document.getElementById('downloadSection');
    if (project.current_amount >= project.goal && project.reward_file_path) {
        downloadSection.classList.add('available');
        document.getElementById('downloadButton').href = project.reward_file_path;
    } else {
        downloadSection.classList.remove('available');
    }
}

function setupFormHandlers() {
    // Project creation form
    const projectForm = document.getElementById('projectForm');
    const imagePreview = document.getElementById('imagePreview');
    
    // Image preview
    const imageInput = projectForm.querySelector('input[name="image"]');
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.style.display = 'block';
                imagePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Form submission
    projectForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            
            const response = await fetch('api/create_project.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Project created successfully!');
                projectForm.reset();
                imagePreview.style.display = 'none';
                loadProjects();
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            alert('Error creating project: ' + error.message);
        }
    });
    
    // Payment form
    const paymentForm = document.getElementById('paymentForm');
    paymentForm.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to support this project?')) {
            e.preventDefault();
        }
    });
}
