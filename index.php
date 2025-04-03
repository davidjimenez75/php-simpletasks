<?php
declare(strict_types=1);

/**
 * Project and Task Lister
 * Lists all first-level directories as projects and their subdirectories as tasks
 */

// Function to get directories excluding hidden ones and vendor
function getDirectories(string $path): array {
    $dirs = [];
    $items = scandir($path);
    
    foreach ($items as $item) {
        $fullPath = $path . DIRECTORY_SEPARATOR . $item;
        if ($item !== '.' && 
            $item !== '..' && 
            !str_starts_with($item, '.') && 
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_SERVER['HTTP_HOST'] ?? 'Projects' ?> (<?= date('Y-m-d') ?>)</title>
    
    <!-- Bootstrap CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
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
    </style>
</head>
<body class="container py-4">
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
</body>
</html>