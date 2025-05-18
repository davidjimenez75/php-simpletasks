<?php
declare(strict_types=1);

/**
 * Project and Task Lister
 * Lists all first-level directories as projects and their subdirectories as tasks in a markdown format.
 * Copy and paste the website content into a markdown file.
 * 
 * Compatible with PHP 7.4 and above.
 */

// Version of the script
define('VERSION', '2025.05.18.1000');

// Function to get directories excluding hidden ones and vendor (PHP7 compatible)
/**
 * Get directories in a given path.
 *
 * @param string $path The path to scan for directories.
 * @return array An array of directory names.
 */
function getDirectories(string $path): array {
    $dirs = [];
    $items = scandir($path);
    
    foreach ($items as $item) {
        $fullPath = $path . DIRECTORY_SEPARATOR . $item;
        if ($item !== '.' && 
            $item !== '..' && 
            !(substr($item, 0, 1) === '.') && 
            $item !== 'tests' && 
            $item !== 'vendor' && 
            $item !== '$RECYCLE.BIN' && 
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
            background-color: #aaaaaa;
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
            background-color: #cacaca;
            border-radius: 3px;
            padding: 8px 4px;
            margin-bottom: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            text-transform: uppercase;

        }
        .project h2 {
            margin: 0;
            font-size: 1em;
            white-space: pre-line;
            line-height: 4px;
            font-weight: bolder;            
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
            background-color: #dadada;
            border-left: 3px solid #3498db; /* Default border, will be overridden by status */
            padding: 4px 8px;
            margin-bottom: 4px;
            border-radius: 0 3px 3px 0;
            font-size: 0.9em;
        }
        .task-meta-container {
            margin-top: 5px;
            padding-left: 10px;
        }
        .task-meta { 
            font-size: 0.8em; 
            color: #555; 
            margin-top: 3px; 
        }
        .task-meta span + span {
            margin-left: 8px;
        }
        .task-tags { 
            font-size: 0.8em; 
            color: #555; 
            margin-top: 3px; 
        }
        .task-status-label {
            font-size: 0.8em;
            font-weight: normal; /* Overrides bold if task itself is bold */
            margin-left: 5px;
            color: #777;
            text-transform: uppercase;
        }

        /* Status Specific Styles - Light Theme */
        .task.status-todo { border-left-color: #7f8c8d; background-color: #ecf0f1; }
        .task.status-done { border-left-color: #2ecc71; background-color: #e8f8f5; text-decoration: line-through; }
        .task.status-finished { border-left-color: #2ecc71; background-color: #e8f8f5; text-decoration: line-through; }
        .task.status-next { border-left-color: #3498db; background-color: #ebf5fb; }
        .task.status-wip { border-left-color: #f1c40f; background-color: #fef9e7; }
        .task.status-today { border-left-color: #e74c3c; background-color: #fdedec; font-weight: bold; }
        .task.status-someday { border-left-color: #9b59b6; background-color: #f5eef8; }
        .task.status-waiting { border-left-color: #1abc9c; background-color: #e8f6f3; }
        .task.status-rock { border-left-color: #e67e22; background-color: #fdf2e9; font-weight: bold; }
        .task.status-urgent { border-left-color: #c0392b; background-color: #f9ebea; font-weight: bold; }
        .task.status-bug { border-left-color: #d35400; background-color: #fbeee6; }
        .task.status-later { border-left-color: #bdc3c7; background-color: #f8f9f9; }
        .task.status-feature { border-left-color: #27ae60; background-color: #e9f7ef; }
        .task.status-review { border-left-color: #8e44ad; background-color: #f4ecf7; }
        
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
            background-color: #2b3035; /* Default dark bg */
            border-left: 3px solid #0d6efd; /* Default dark border, will be overridden */
        }

        html[data-bs-theme="dark"] .task-meta { color: #bbb; }
        html[data-bs-theme="dark"] .task-tags { color: #bbb; }
        html[data-bs-theme="dark"] .task-status-label { color: #aaa; }

        /* Status Specific Styles - Dark Theme */
        html[data-bs-theme="dark"] .task.status-todo { border-left-color: #95a5a6; background-color: #34495e; }
        html[data-bs-theme="dark"] .task.status-done { border-left-color: #27ae60; background-color: #1e4620; text-decoration: line-through; }
        html[data-bs-theme="dark"] .task.status-finished { border-left-color: #27ae60; background-color: #1e4620; text-decoration: line-through; }        
        html[data-bs-theme="dark"] .task.status-next { border-left-color: #2980b9; background-color: #1f3a93; }
        html[data-bs-theme="dark"] .task.status-wip { border-left-color: #f39c12; background-color: #5a4807; }
        html[data-bs-theme="dark"] .task.status-today { border-left-color: #c0392b; background-color: #78281f; font-weight: bold; }
        html[data-bs-theme="dark"] .task.status-someday { border-left-color: #8e44ad; background-color: #512e5f; }
        html[data-bs-theme="dark"] .task.status-waiting { border-left-color: #16a085; background-color: #0e6251; }
        html[data-bs-theme="dark"] .task.status-rock { border-left-color: #d35400; background-color: #7e2f0c; font-weight: bold; }
        html[data-bs-theme="dark"] .task.status-urgent { border-left-color: #a93226; background-color: #641e16; font-weight: bold; }
        html[data-bs-theme="dark"] .task.status-bug { border-left-color: #b94900; background-color: #6e2c00; }
        html[data-bs-theme="dark"] .task.status-later { border-left-color: #a1a7ab; background-color: #3e4444; }
        html[data-bs-theme="dark"] .task.status-feature { border-left-color: #229954; background-color: #196f3d; }
        html[data-bs-theme="dark"] .task.status-review { border-left-color: #7d3c98; background-color: #4a235a; }

        html[data-bs-theme="dark"] .no-projects {
            background-color: #332701;
            border-left: 3px solid #ffc107;
        }
        
        html[data-bs-theme="dark"] .no-tasks {
            color: #adb5bd;
        }

        .invisible {
            font-size:0px;
            color: transparent;
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
<span class="invisible">##</span> <?= htmlspecialchars($project) ?> <span class="task-count<?php if(count($tasks) == 0) echo 'invisible';?>"><?php if (count($tasks) > 0) echo '('.count($tasks),')'; ?></span>

</h2>
                </div>
                
                <div class="tasks" style="margin-top: 10px;">
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $task): ?>
                            <?php
                            $taskIniPath = $projectPath . DIRECTORY_SEPARATOR . $task . DIRECTORY_SEPARATOR . 'folder.ini';
                            $taskMeta = [
                                'status_display' => 'TODO',
                                'status_class' => 'status-todo',
                                'start_display' => null,
                                'end_display' => null,
                                'duration_minutes' => null,
                                'tags_array' => []
                            ];
                            $rawStatusForLogic = 'TODO'; // For direct comparison if needed

                            if (file_exists($taskIniPath)) {
                                $iniData = parse_ini_file($taskIniPath);
                                if ($iniData) {
                                    // Process status
                                    $rawStatus = isset($iniData['status']) && trim($iniData['status']) !== '' ? trim($iniData['status']) : 'TODO';
                                    $rawStatusForLogic = $rawStatus;
                                    $taskMeta['status_display'] = strtoupper(htmlspecialchars($rawStatus));
                                    $taskMeta['status_class'] = 'status-' . strtolower(htmlspecialchars(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $rawStatus)));
                                    
                                    // Process start time
                                    $startTimeObj = null;
                                    if (!empty($iniData['start'])) {
                                        $startTimeObj = DateTime::createFromFormat('Y-m-d--His', $iniData['start']);
                                        if ($startTimeObj) {
                                            $taskMeta['start_display'] = $startTimeObj->format('Y-m-d H:i');
                                        }
                                    }

                                    // Process end time
                                    $endTimeObj = null;
                                    if (!empty($iniData['end'])) {
                                        $endTimeObj = DateTime::createFromFormat('Y-m-d--His', $iniData['end']);
                                        if ($endTimeObj) {
                                            $taskMeta['end_display'] = $endTimeObj->format('Y-m-d H:i');
                                        }
                                    }
                                    
                                    // Calculate duration
                                    if ($startTimeObj && $endTimeObj && $endTimeObj > $startTimeObj) {
                                        $interval = $startTimeObj->diff($endTimeObj);
                                        $taskMeta['duration_minutes'] = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                                    }

                                    // Process tags
                                    if (!empty($iniData['tags'])) {
                                        $tags = explode(',', $iniData['tags']);
                                        foreach ($tags as $tagItem) {
                                            $trimmedTag = trim($tagItem);
                                            if (!empty($trimmedTag)) {
                                                $taskMeta['tags_array'][] = htmlspecialchars($trimmedTag);
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                            <div class="task <?= htmlspecialchars($taskMeta['status_class']) ?>">
                                - [_] <?= htmlspecialchars($task) ?> <span class="task-status-label">[<?= $taskMeta['status_display'] ?>]</span>
                                
                                <?php if ($taskMeta['start_display'] || $taskMeta['end_display'] || $taskMeta['duration_minutes'] !== null || !empty($taskMeta['tags_array'])): ?>
                                <div class="task-meta-container">
                                    <?php if ($taskMeta['start_display'] || $taskMeta['end_display'] || $taskMeta['duration_minutes'] !== null): ?>
                                    <div class="task-meta">
                                        <?php if ($taskMeta['start_display']): ?>
                                            <span>Start: <?= $taskMeta['start_display'] ?></span>
                                        <?php endif; ?>
                                        <?php if ($taskMeta['end_display']): ?>
                                            <span>End: <?= $taskMeta['end_display'] ?></span>
                                        <?php endif; ?>
                                        <?php if ($taskMeta['duration_minutes'] !== null): ?>
                                            <span>Time: <?= $taskMeta['duration_minutes'] ?> min</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($taskMeta['tags_array'])): ?>
                                    <div class="task-tags">
                                        Tags: <?= implode(', ', $taskMeta['tags_array']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
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