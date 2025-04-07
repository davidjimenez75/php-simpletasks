<?php
declare(strict_types=1);

/**
 * Project and Task Lister
 * Lists all first-level directories as projects and their subdirectories as tasks
 */

// Version of the script
define('VERSION', '2025.04.07.1306');

// Function to get directories excluding hidden ones and vendor
function getDirectories(string $path): array {
    $dirs = [];
    $items = scandir($path);
    
    foreach ($items as $item) {
        $fullPath = $path . DIRECTORY_SEPARATOR . $item;
        if ($item !== '.' && 
            $item !== '..' && 
            !str_starts_with($item, '.') && 
            $item !== 'tests' && 
            $item !== 'vendor' && 
            is_dir($fullPath)) {
            $dirs[] = $item;
        }
    }
    
    return $dirs;
}

$projects = getDirectories(__DIR__);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_SERVER['HTTP_HOST'] ?? 'Projects' ?> (<?= date('Y-m-d') ?>)</title>
    
    <!-- Bootstrap CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Your custom styles -->
    <style>
        /* You can keep your custom styles here or modify them to work with Bootstrap */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.3;
            margin: 0;
            padding: 10px;
            background-color: #f5f5f5;
            font-size: 0.9em;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.4em;
        }
        .project {
            background-color: white;
            border-radius: 3px;
            padding: 8px 10px;
            margin-bottom: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        .project h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.1em;
            white-space: pre-line;
            line-height: 6px;
        }
        .task-count {
            color: #666;
            font-size: 0.7em;
            font-weight: normal;
            margin-left: 6px;
        }
        .tasks {
            margin-left: 15px;
        }
        .task {
            background-color: #f9f9f9;
            border-left: 3px solid #3498db;
            padding: 4px 8px;
            margin-bottom: 4px;
            border-radius: 0 3px 3px 0;
            font-size: 0.9em;
        }
        .no-projects {
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
            padding: 8px;
            border-radius: 0 3px 3px 0;
            font-size: 0.9em;
        }
        .no-tasks {
            color: #6c757d;
            font-style: italic;
            font-size: 0.9em;
        }
        
        /* Dark mode styles */
        html[data-bs-theme="dark"] body {
            background-color: #212529;
            color: #f8f9fa;
        }
        
        html[data-bs-theme="dark"] h1 {
            color: #f8f9fa;
            border-bottom: 1px solid #495057;
        }
        
        html[data-bs-theme="dark"] .project {
            background-color: #343a40;
            box-shadow: 0 1px 2px rgba(255,255,255,0.1);
        }
        
        html[data-bs-theme="dark"] .project h2 {
            color: #e9ecef;
        }
        
        html[data-bs-theme="dark"] .task {
            background-color: #2b3035;
            border-left: 3px solid #0d6efd;
        }
        
        html[data-bs-theme="dark"] .no-projects {
            background-color: #332701;
            border-left: 3px solid #ffc107;
        }
        
        html[data-bs-theme="dark"] .no-tasks {
            color: #adb5bd;
        }
        
        /* Theme toggle button */
        .theme-toggle {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 1000;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--bs-primary);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            border: none;
        }
        
        .theme-toggle i {
            font-size: 1.5rem;
        }
    </style>
</head>
<body class="container-fluid py-4">
    <!-- Theme toggle button -->
    <button class="theme-toggle" id="themeToggle">
        <i class="bi bi-moon-fill" id="themeIcon"></i>
    </button>
    
    <h1># <?= $_SERVER['HTTP_HOST'] ?? 'Projects' ?> (<?= date('Y-m-d') ?>)</h1>
    
    <?php if (empty($projects)): ?>
        <div class="no-projects">
            <p>No projects found. Create directories in the root folder to get started.</p>
        </div>
    <?php else: ?>
        <?php foreach ($projects as $project): ?>
            <?php 
            $projectPath = __DIR__ . DIRECTORY_SEPARATOR . $project;
            $tasks = getDirectories($projectPath);
            ?>
            <div class="project">
                <div class="project-header">
                    <h2>
## <?= htmlspecialchars($project) ?> <span class="task-count">(<?= count($tasks) ?>)</span>

</h2>
                </div>
                
                <div class="tasks" style="margin-top: 10px;">
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $task): ?>
                            <div class="task">
                                - [_] <?= htmlspecialchars($task) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Bootstrap JS from CDN (optional, only if you need JavaScript components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Theme toggle script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const html = document.documentElement;
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');

            // Retrieve stored theme from localStorage
            const storedTheme = localStorage.getItem('theme');
            if (storedTheme) {
                html.setAttribute('data-bs-theme', storedTheme);
                if (storedTheme === 'dark') {
                    themeIcon.classList.remove('bi-moon-fill');
                    themeIcon.classList.add('bi-sun-fill');
                }
            }

            // Toggle theme function with storage
            function toggleTheme() {
                const currentTheme = html.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                if (newTheme === 'dark') {
                    themeIcon.classList.remove('bi-moon-fill');
                    themeIcon.classList.add('bi-sun-fill');
                } else {
                    themeIcon.classList.remove('bi-sun-fill');
                    themeIcon.classList.add('bi-moon-fill');
                }
            }

            // Check if it's night time (between 8 PM and 9 AM)
            function isNightTime() {
                const hour = new Date().getHours();
                return hour >= 20 || hour < 9;
            }
            
            // Set initial theme based on time
            if (isNightTime()) {
                html.setAttribute('data-bs-theme', 'dark');
                themeIcon.classList.remove('bi-moon-fill');
                themeIcon.classList.add('bi-sun-fill');
            }
            
            // Add click event to theme toggle button
            themeToggle.addEventListener('click', toggleTheme);
        });
    </script>
</body>
</html>