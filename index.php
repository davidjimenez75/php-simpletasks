<?php
declare(strict_types=1);

/**
 * Project and Task Lister
 * Lists all first-level directories as projects and their subdirectories as tasks.
 */

// Conditionally load Parsedown
$parsedownAvailable = false;
$Parsedown = null;
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Parsedown')) {
        $Parsedown = new Parsedown();
        $parsedownAvailable = true;
    }
}

// Version of the script
define('VERSION', '2025.05.18.1500');

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
        h2 {
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        h4 {
            color: #333;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.1em;
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
            /* white-space: pre-line; */ /* Replaced for single-line display */
            white-space: nowrap;      /* Ensures project name and count are on one line */
            overflow: hidden;         /* Hides overflow if content is too long */
            text-overflow: ellipsis;  /* Adds \'...\' for truncated text */
            /* line-height: 4px; */  /* Replaced with normal line height */
            line-height: normal;      /* Allows proper text rendering */
            font-weight: bolder;            
            flex-grow: 1; /* Allows h2 to take available space next to the icon */
            margin-right: 5px; /* Add some space between h2 and the icon */
        }
        .task-count {
            color: #666;
            font-size: 0.7em;
            font-weight: normal;
            margin-left: 6px;
        }
        .project-toggle-icon {
            cursor: pointer;
            margin-right: 8px;
            font-size: 0.9em; /* Adjust as needed */
            transition: transform 0.2s ease-in-out;
            display: inline-block; /* Ensures transform is applied correctly */
        }
        .project-toggle-icon.collapsed {
            transform: rotate(-90deg);
        }
        .tasks {
            margin-left: 15px;
        }
        /* .task -> .gh-item */
        .gh-item {
            display: flex; /* New: for icon and main content layout */
            align-items: flex-start; /* Align icon and content to the top */
            /* background-color: #dadada; */ /* Old: general task background, removed for GitHub style */
            border-left: 3px solid #3498db; /* Default border, will be overridden by status */
            padding: 8px 10px; /* Adjusted padding */
            margin-bottom: 8px; /* Increased margin for separation */
            border-radius: 6px; /* GitHub-like rounded corners */
            font-size: 0.9em;
            background-color: #e5e5e5; /* Changed from #fff to a softer white for light theme */
        }

        .gh-item-icon-col {
            margin-right: 8px;
            padding-top: 2px; /* Align icon nicely with text */
            font-size: 1.2em; /* Icon size */
            flex-shrink: 0; /* Prevent icon column from shrinking */
        }

        .gh-item-main-col {
            flex-grow: 1;
            min-width: 0; /* Prevents overflow issues with flex items */
        }

        .gh-item-header {
            display: flex;
            align-items: center;
            flex-wrap: wrap; /* Allow labels to wrap */
            margin-bottom: 4px;
        }

        .gh-item-title {
            font-weight: 600; /* Bolder title */
            font-size: 1.05em;
            color: #0969da; /* GitHub-like link color for title */
            margin-right: 8px;
            word-break: break-word; /* Break long task names */
        }
        html[data-bs-theme="dark"] .gh-item-title {
            color: #58a6ff;
        }

        .gh-item-id {
            font-size: 0.9em;
            color: #57606a;
            margin-right: 8px;
        }
        html[data-bs-theme="dark"] .gh-item-id {
            color: #8b949e;
        }

        .gh-label {
            padding: 0.15em 0.5em;
            font-size: 0.75em;
            font-weight: 500;
            line-height: 1.5;
            border-radius: 2em; /* Pill-shaped labels */
            margin-right: 4px;
            margin-bottom: 4px; /* For wrapping */
            display: inline-block;
            border: 1px solid transparent;
        }

        /* Base for status labels (will be colored by specific status) */
        .gh-label-status {
            /* Colors will come from specific status classes */
        }

        /* Base for tag labels */
        .gh-label-tag {
            background-color: #f1f8ff; /* Light blue background for tags */
            color: #0969da; /* Dark blue text */
            border-color: #c8e1ff;
        }
        html[data-bs-theme="dark"] .gh-label-tag {
            background-color: #16263a;
            color: #58a6ff;
            border-color: #2f4c77;
        }
        
        .gh-item-meta {
            font-size: 0.8em; 
            color: #57606a; 
            margin-top: 3px; 
        }
        .gh-item-meta span + span {
            margin-left: 8px;
        }
        html[data-bs-theme="dark"] .gh-item-meta { color: #8b949e; }


        /* Remove old task-specific elements or adapt them */
        .task-meta-container { /* No longer used directly, content moved */
            /* margin-top: 5px; */
            /* padding-left: 10px; */
        }
        .task-meta { /* Replaced by .gh-item-meta */
            /* font-size: 0.8em;  */
            /* color: #555;  */
            /* margin-top: 3px;  */
        }
        .task-tags { /* Replaced by .gh-label-tag */
            /* font-size: 0.8em;  */
            /* color: #555;  */
            /* margin-top: 3px;  */
        }
        .task-status-label { /* Replaced by .gh-label-status */
            /* font-size: 0.8em; */
            /* font-weight: normal; */
            /* margin-left: 5px; */
            /* color: #777; */
            /* text-transform: uppercase; */
        }
        .task-name { /* Styling moved to .gh-item-title and .gh-item-id */
        }


        /* Status Specific Styles - Light Theme */
        .gh-item.status-todo { border-left-color: #7f8c8d; }
        .gh-item.status-done { border-left-color: #2ecc71; text-decoration: none; }
        .gh-item.status-finished { border-left-color: #2ecc71; text-decoration: none; }
        .gh-item.status-next { border-left-color: #3498db; }
        .gh-item.status-wip { border-left-color: #f1c40f; }
        .gh-item.status-today { border-left-color: #e74c3c; font-weight: normal; }
        .gh-item.status-someday { border-left-color: #9b59b6; }
        .gh-item.status-waiting { border-left-color: #1abc9c; }
        .gh-item.status-rock { border-left-color: #e67e22; font-weight: normal; }
        .gh-item.status-urgent { border-left-color: #c0392b; font-weight: normal; }
        .gh-item.status-bug { border-left-color: #d35400; }
        .gh-item.status-later { border-left-color: #bdc3c7; }
        .gh-item.status-feature { border-left-color: #27ae60; }
        .gh-item.status-review { border-left-color: #8e44ad; }
        
        /* Status Label Specific Colors - Light Theme */
        .gh-label-status.todo { background-color: #e0e0e0; color: #333; border-color: #ccc; }
        .gh-label-status.done, .gh-label-status.finished { background-color: #d1f7d6; color: #10692c; border-color: #a2e8b3; }
        .gh-label-status.next { background-color: #cfe2f3; color: #2a5296; border-color: #b0cdee; }
        .gh-label-status.wip { background-color: #fff2cc; color: #795500; border-color: #ffe599; }
        .gh-label-status.today { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; font-weight: bold; }
        .gh-label-status.someday { background-color: #e8dff5; color: #5e397a; border-color: #d9c8ee; }
        .gh-label-status.waiting { background-color: #d4f5f0; color: #1b6d5e; border-color: #b0e6dc; }
        .gh-label-status.rock { background-color: #fce2d0; color: #8c4d15; border-color: #f9cba7; font-weight: bold; }
        .gh-label-status.urgent { background-color: #fdd8d4; color: #7f231c; border-color: #fab9b3; font-weight: bold; }
        .gh-label-status.bug { background-color: #fddfc9; color: #853d00; border-color: #fbc79f; }
        .gh-label-status.later { background-color: #e6e9eb; color: #495057; border-color: #d3d9df; }
        .gh-label-status.feature { background-color: #d0f0e0; color: #1a6c43; border-color: #a7e2c3; }
        .gh-label-status.review { background-color: #e9d8f4; color: #592c73; border-color: #d8c0ea; }


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

        html[data-bs-theme="dark"] .project h3 {
            color: #e9ecef;
        }

        html[data-bs-theme="dark"] .project h4 {
            color: #e9ecef;
        }

        html[data-bs-theme="dark"] .gh-item {
            background-color: #2d333b; /* Darker item background */
            box-shadow: none; /* Remove shadow if any from project */
        }

        /* Status Specific Styles - Dark Theme */
        html[data-bs-theme="dark"] .gh-item.status-todo { border-left-color: #95a5a6; }
        html[data-bs-theme="dark"] .gh-item.status-done { border-left-color: #27ae60; text-decoration: none; }
        html[data-bs-theme="dark"] .gh-item.status-finished { border-left-color: #27ae60; text-decoration: none; }        
        html[data-bs-theme="dark"] .gh-item.status-next { border-left-color: #2980b9; }
        html[data-bs-theme="dark"] .gh-item.status-wip { border-left-color: #f39c12; }
        html[data-bs-theme="dark"] .gh-item.status-today { border-left-color: #c0392b; font-weight: normal; }
        html[data-bs-theme="dark"] .gh-item.status-someday { border-left-color: #8e44ad; }
        html[data-bs-theme="dark"] .gh-item.status-waiting { border-left-color: #16a085; }
        html[data-bs-theme="dark"] .gh-item.status-rock { border-left-color: #d35400; font-weight: normal; }
        html[data-bs-theme="dark"] .gh-item.status-urgent { border-left-color: #a93226; font-weight: normal; }
        html[data-bs-theme="dark"] .gh-item.status-bug { border-left-color: #b94900; }
        html[data-bs-theme="dark"] .gh-item.status-later { border-left-color: #a1a7ab; }
        html[data-bs-theme="dark"] .gh-item.status-feature { border-left-color: #229954; }
        html[data-bs-theme="dark"] .gh-item.status-review { border-left-color: #7d3c98; }

        /* Status Label Specific Colors - Dark Theme */
        html[data-bs-theme="dark"] .gh-label-status.todo { background-color: #484f58; color: #c9d1d9; border-color: #5a626c; }
        html[data-bs-theme="dark"] .gh-label-status.done, 
        html[data-bs-theme="dark"] .gh-label-status.finished { background-color: #203d2b; color: #84dab3; border-color: #2f5a40; }
        html[data-bs-theme="dark"] .gh-label-status.next { background-color: #2c3e5c; color: #a8c5f2; border-color: #3b527c; }
        html[data-bs-theme="dark"] .gh-label-status.wip { background-color: #4d3c11; color: #f0d691; border-color: #695218; }
        html[data-bs-theme="dark"] .gh-label-status.today { background-color: #582a2f; color: #f2b8bd; border-color: #74383f; font-weight: bold; }
        html[data-bs-theme="dark"] .gh-label-status.someday { background-color: #442c59; color: #d8c9f0; border-color: #5c3b75; }
        html[data-bs-theme="dark"] .gh-label-status.waiting { background-color: #224c46; color: #93e0d1; border-color: #2d665c; }
        html[data-bs-theme="dark"] .gh-label-status.rock { background-color: #5e3a1e; color: #f5c9a9; border-color: #7a4e26; font-weight: bold; }
        html[data-bs-theme="dark"] .gh-label-status.urgent { background-color: #612925; color: #f7c1bc; border-color: #7d3630; font-weight: bold; }
        html[data-bs-theme="dark"] .gh-label-status.bug { background-color: #5c350d; color: #f7c9a0; border-color: #784611; }
        html[data-bs-theme="dark"] .gh-label-status.later { background-color: #494f55; color: #bdc6cf; border-color: #606870; }
        html[data-bs-theme="dark"] .gh-label-status.feature { background-color: #1e4a35; color: #8be0b5; border-color: #2a664a; }
        html[data-bs-theme="dark"] .gh-label-status.review { background-color: #412754; color: #d3b9ed; border-color: #573470; }

        .task-readme-toggle {
            cursor: pointer;
            font-size: 0.8em;
            margin-left: 8px;
            color: #0d6efd; /* Bootstrap primary blue */
            text-decoration: none; /* Removed underline for FEATURE--004 */
        }
        html[data-bs-theme="dark"] .task-readme-toggle {
            color: #58a6ff; /* Lighter blue for dark mode */
        }
        .task-readme-content {
            margin-top: 8px;
            padding: 10px;
            background-color: #f8f9fa; /* Light background for content */
            border-radius: 4px;
            border: 1px solid #dee2e6;
            font-size: 0.9em;
        }
        html[data-bs-theme="dark"] .task-readme-content {
            background-color: #2c3137; /* Darker background for content */
            border-color: #495057;
            color: #ced4da;
        }
        .task-readme-content pre {
            white-space: pre-wrap; /* Wrap long lines in pre */
            word-break: break-all; /* Break long words if necessary */
            margin-bottom: 0; /* Remove default pre margin */
        }

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
            bottom: 15px; /* Changed from top to bottom */
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

        /* Style for active filter in dropdown */
        .dropdown-item.active-filter {
            font-weight: bold !important; /* Added !important to ensure override */
        }
    </style>
</head>
<body class="container-fluid py-4">
    <!-- Theme toggle button -->
    <button class="theme-toggle" id="themeToggle">
        <i class="bi bi-moon-fill" id="themeIcon"></i>
    </button>

    <!-- New Top-Right Menu -->
    <div class="dropdown position-fixed top-0 end-0 p-3" style="z-index: 1001;">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="topRightMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-list"></i> Menu
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="topRightMenuButton">
            <li><h6 class="dropdown-header">Filter by Status</h6></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="all">All Statuses</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="todo">To Do</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="wip">WIP</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="today">Today</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="next">Next</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="urgent">Urgent</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="rock">Rock</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="someday">Someday</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="waiting">Waiting</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="later">Later</a></li>            
            <li><a class="dropdown-item filter-status-item" href="#" data-status="done">Done</a></li>            
            <!--
            <li><a class="dropdown-item filter-status-item" href="#" data-status="bug">Bug</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="feature">Feature</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="review">Review</a></li>
            <li><a class="dropdown-item filter-status-item" href="#" data-status="finished">Finished</a></li>
            -->
            
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header">Coming Soon</h6></li>
            <li><a class="dropdown-item disabled" href="#">Reports</a></li>
            <li><a class="dropdown-item disabled" href="#">Export Options</a></li>
        </ul>
    </div>
    
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
            // Generate a slug for IDs
            $projectSlug = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower($project));
            $collapseId = 'project-collapse-' . $projectSlug; // Deterministic ID
            ?>
            <div class="project">
                <div class="project-header" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#<?= htmlspecialchars($collapseId) ?>" 
                     aria-expanded="true" 
                     aria-controls="<?= htmlspecialchars($collapseId) ?>"
                     style="cursor: pointer;">
                    <h2>
                        <span class="invisible">##</span> <?= htmlspecialchars($project) ?> 
                        <span class="task-count<?php if(count($tasks) == 0) echo ' invisible';?>"><?php if (count($tasks) > 0) echo '('.count($tasks),')'; ?></span>
                    </h2>
                    <i class="bi bi-chevron-down project-toggle-icon"></i>
                </div>
                
                <div id="<?= htmlspecialchars($collapseId) ?>" class="collapse show project-tasks-container tasks" style="margin-top: 10px;">
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
                                'tags_array' => [],
                                'id' => null // Initialize id
                            ];
                            $rawStatusForLogic = 'TODO'; // For direct comparison if needed

                            // Check for README.md
                            $taskReadmePath = $projectPath . DIRECTORY_SEPARATOR . $task . DIRECTORY_SEPARATOR . 'README.md';
                            $readmeContent = null;
                            $hasReadme = false;
                            if (file_exists($taskReadmePath)) {
                                $hasReadme = true;
                                $readmeContent = file_get_contents($taskReadmePath);
                            }
                            $taskSlug = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower($task));
                            $readmeCollapseId = 'readme-collapse-' . $projectSlug . '-' . $taskSlug;

                            if (file_exists($taskIniPath)) {
                                $iniData = parse_ini_file($taskIniPath);
                                if ($iniData) {
                                    // Process id
                                    if (isset($iniData['id']) && trim($iniData['id']) !== '') {
                                        $taskMeta['id'] = htmlspecialchars(trim($iniData['id']));
                                    }

                                    // Process status
                                    $rawStatus = isset($iniData['status']) && trim($iniData['status']) !== '' ? trim($iniData['status']) : 'TODO';
                                    $rawStatusForLogic = $rawStatus;
                                    $taskMeta['status_display'] = strtoupper(htmlspecialchars($rawStatus));
                                    $taskMeta['status_class'] = 'status-' . strtolower(htmlspecialchars(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $rawStatus)));
                                    
                                    // New: Process direct 'time' (formerly duration_minutes) from folder.ini
                                    if (isset($iniData['time']) && is_numeric($iniData['time'])) {
                                        $taskMeta['duration_minutes'] = (int)$iniData['time'];
                                    }

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
                                    
                                    // Calculate duration ONLY IF NOT ALREADY SET by 'time' (formerly duration_minutes) from INI
                                    if ($taskMeta['duration_minutes'] === null && $startTimeObj && $endTimeObj && $endTimeObj > $startTimeObj) {
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

                            // Determine icon based on status
                            $taskIconHtml = '<i class="bi bi-record-circle"></i>'; // Default: open circle
                            if (in_array(strtoupper($rawStatusForLogic), ['DONE', 'FINISHED'])) {
                                $taskIconHtml = '<i class="bi bi-check-circle-fill text-success"></i>'; // Green check
                            } elseif (in_array(strtoupper($rawStatusForLogic), ['WIP', 'TODAY', 'URGENT', 'ROCK'])) { // Corrected: removed extra 'elseif' and fixed syntax
                                $taskIconHtml = '<i class="bi bi-dot text-warning"></i>'; // Yellow dot for in-progress like
                            }
                            // TODO: Add more specific icons for bug, feature etc. later if desired
                            ?>
                            <div class="gh-item <?= htmlspecialchars($taskMeta['status_class']) ?>">
                                <div class="gh-item-icon-col">
                                    <?= $taskIconHtml ?>
                                </div>
                                <div class="gh-item-main-col">
                                    <div class="gh-item-header">
                                        <span class="gh-item-title"><?= htmlspecialchars($task) ?></span>
                                        <?php if ($taskMeta['id']): ?>
                                            <span class="gh-item-id">#<?= $taskMeta['id'] ?></span>
                                        <?php endif; ?>
                                        
                                        <span class="gh-label gh-label-status <?= strtolower(htmlspecialchars(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $rawStatusForLogic))) ?>">
                                            <?= $taskMeta['status_display'] ?>
                                        </span>

                                        <?php foreach ($taskMeta['tags_array'] as $tag): ?>
                                            <span class="gh-label gh-label-tag"><?= htmlspecialchars($tag) ?></span>
                                        <?php endforeach; ?>

                                        <?php if ($hasReadme): ?>
                                            <a class="task-readme-toggle" data-bs-toggle="collapse" href="#<?= htmlspecialchars($readmeCollapseId) ?>" role="button" aria-expanded="false" aria-controls="<?= htmlspecialchars($readmeCollapseId) ?>">
                                                <i class="bi bi-info-circle"></i> README.md
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($taskMeta['start_display'] || $taskMeta['end_display'] || $taskMeta['duration_minutes'] !== null): ?>
                                    <div class="gh-item-meta">
                                        <?php 
                                        $metaParts = [];
                                        if ($taskMeta['start_display']) {
                                            $metaParts[] = 'Start: ' . $taskMeta['start_display'];
                                        }
                                        if ($taskMeta['end_display']) {
                                            $metaParts[] = 'End: ' . $taskMeta['end_display'];
                                        }
                                        if ($taskMeta['duration_minutes'] !== null) {
                                            $metaParts[] = 'Time: ' . $taskMeta['duration_minutes'] . ' min';
                                        }
                                        echo implode(' <span class="text-muted">&bull;</span> ', $metaParts);
                                        ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($hasReadme): ?>
                                    <div class="collapse task-readme-content" id="<?= htmlspecialchars($readmeCollapseId) ?>">
                                        <?php 
                                            if ($readmeContent !== null) {
                                                if ($parsedownAvailable && $Parsedown) {
                                                    echo $Parsedown->text($readmeContent); 
                                                } else {
                                                    echo '<pre>' . htmlspecialchars($readmeContent) . '</pre>';
                                                }
                                            } else {
                                                echo '<p>No content found in README.md</p>';
                                            }
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-tasks" style="padding-left: 15px; font-style: italic; color: #666;">No tasks in this project.</p>
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

            // Helper function to apply theme and update icon
            function applyTheme(theme) {
                if (!html || !themeIcon) { // Basic safety check
                    // console.error('applyTheme: html or themeIcon element is missing.');
                    return;
                }

                html.setAttribute('data-bs-theme', theme);
                if (theme === 'dark') {
                    themeIcon.classList.remove('bi-moon-fill');
                    themeIcon.classList.add('bi-sun-fill');
                } else { // Default to light theme appearance (icon and attribute)
                    themeIcon.classList.remove('bi-sun-fill');
                    themeIcon.classList.add('bi-moon-fill');
                }
            }

            // Helper function to check if it's night time
            function isNightTime() {
                const hour = new Date().getHours();
                return hour >= 20 || hour < 9; // 8 PM to 8:59 AM
            }

            // Determine and apply initial theme
            let determinedTheme = 'light'; // Default to light theme
            try {
                const storedTheme = localStorage.getItem('theme');
                if (storedTheme === 'dark' || storedTheme === 'light') {
                    determinedTheme = storedTheme;
                } else { // No valid stored theme, or an invalid one
                    if (isNightTime()) {
                        determinedTheme = 'dark';
                    }
                    // If not night and no valid stored theme, it remains 'light' (our default)
                    // This automatically determined theme is not saved to localStorage initially
                }
            } catch (e) {
                console.error("Could not access localStorage for theme: ", e);
                // Fallback to time-based if localStorage fails
                if (isNightTime()) {
                    determinedTheme = 'dark';
                }
            }
            applyTheme(determinedTheme);

            // Toggle theme function
            function toggleThemeOnClick() {
                if (!html) { // Basic safety check
                    // console.error('toggleThemeOnClick: html element is missing.');
                    return;
                }
                const currentTheme = html.getAttribute('data-bs-theme');
                const newTheme = (currentTheme === 'dark') ? 'light' : 'dark';
                applyTheme(newTheme);
                try {
                    localStorage.setItem('theme', newTheme);
                } catch (e) {
                    console.error("Could not save theme to localStorage: ", e);
                }
            }
            
            if (themeToggle) {
                themeToggle.addEventListener('click', toggleThemeOnClick);
            } else {
                // console.error('Theme toggle button (themeToggle) not found.');
            }

            // Collapsible project sections
            const projectCollapseElements = document.querySelectorAll('.project-tasks-container.collapse');

            projectCollapseElements.forEach(function(collapseEl) {
                const collapseId = collapseEl.id; // Deterministic ID from PHP
                const projectHeader = collapseEl.closest('.project').querySelector('.project-header');
                const icon = projectHeader.querySelector('.project-toggle-icon');

                // Ensure Bootstrap Collapse instance is created
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });

                // Event listener for when a section is shown
                collapseEl.addEventListener('show.bs.collapse', function () {
                    try {
                        localStorage.setItem(`projectState_${collapseId}`, 'expanded');
                    } catch (e) {
                        console.error('Could not save project state to localStorage: ', e);
                    }
                    if (icon) {
                        icon.classList.remove('collapsed');
                    }
                });

                // Event listener for when a section is hidden
                collapseEl.addEventListener('hide.bs.collapse', function () {
                    try {
                        localStorage.setItem(`projectState_${collapseId}`, 'collapsed');
                    } catch (e) {
                        console.error('Could not save project state to localStorage: ', e);
                    }
                    if (icon) {
                        icon.classList.add('collapsed');
                    }
                });

                // --- Initialize state based on localStorage ---
                let savedState = null;
                try {
                    savedState = localStorage.getItem(`projectState_${collapseId}`);
                } catch (e) {
                    console.error('Could not access localStorage for project state: ', e);
                }

                if (savedState === 'collapsed') {
                    if (collapseEl.classList.contains('show')) {
                        bsCollapse.hide(); 
                    } else {
                        if (icon) icon.classList.add('collapsed');
                    }
                } else if (savedState === 'expanded') {
                    if (!collapseEl.classList.contains('show')) {
                        bsCollapse.show(); 
                    } else {
                        if (icon) icon.classList.remove('collapsed');
                    }
                } else {
                    // No saved state, rely on default HTML 'show' class
                    if (icon) {
                        if (collapseEl.classList.contains('show')) {
                            icon.classList.remove('collapsed');
                        } else {
                            icon.classList.add('collapsed');
                        }
                    }
                }
            });

            // New Top-Right Menu Logic
            const filterStatusItems = document.querySelectorAll('.filter-status-item');
            const taskItems = document.querySelectorAll('.gh-item'); // Assuming tasks have class 'gh-item'
            const topRightMenuButton = document.getElementById('topRightMenuButton');

            function applyStatusFilter(selectedStatus) {
                // Remove active class from all filter items
                filterStatusItems.forEach(item => {
                    item.classList.remove('active-filter');
                    // item.style.fontWeight = 'normal'; // Alternative: direct style manipulation
                });

                // Add active class to the selected filter item
                const selectedFilterItem = document.querySelector(`.filter-status-item[data-status="${selectedStatus}"]`);
                if (selectedFilterItem) {
                    selectedFilterItem.classList.add('active-filter');
                    // selectedFilterItem.style.fontWeight = 'bold'; // Alternative
                }

                taskItems.forEach(task => {
                    const taskStatusClass = Array.from(task.classList).find(cls => cls.startsWith('status-'));
                    const taskStatus = taskStatusClass ? taskStatusClass.replace('status-', '') : '';

                    if (selectedStatus === 'all' || taskStatus === selectedStatus) {
                        task.style.display = ''; // Show task
                    } else {
                        task.style.display = 'none'; // Hide task
                    }
                });
                // Update menu button text to show active filter
                if (topRightMenuButton) {
                    const selectedStatusText = selectedStatus === 'all' ? 'Menu' : `Filter: ${selectedStatus.toUpperCase()}`;
                    topRightMenuButton.innerHTML = `<i class="bi bi-list"></i> ${selectedStatusText}`;
                }
                // Save filter to localStorage
                try {
                    localStorage.setItem('activeStatusFilter', selectedStatus);
                } catch (e) {
                    console.error('Could not save status filter to localStorage: ', e);
                }
            }

            filterStatusItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const status = this.getAttribute('data-status');
                    applyStatusFilter(status);
                });
            });

            // Load and apply saved filter on page load
            try {
                const savedFilter = localStorage.getItem('activeStatusFilter');
                if (savedFilter) {
                    applyStatusFilter(savedFilter); // This will now also set the bold style
                } else {
                    // If no saved filter, make "All Statuses" bold by default and apply it
                    applyStatusFilter('all'); 
                }
            } catch (e) {
                console.error('Could not load status filter from localStorage: ', e);
                // Fallback if localStorage fails, make "All Statuses" bold
                const allStatusesItem = document.querySelector('.filter-status-item[data-status="all"]');
                if (allStatusesItem) {
                    allStatusesItem.classList.add('active-filter');
                    // allStatusesItem.style.fontWeight = 'bold'; // Alternative
                }
            }

            // --- FEATURE--004: Clickable Task Header for README Toggle ---
            document.querySelectorAll('.gh-item-header').forEach(header => {
                const taskItem = header.closest('.gh-item');
                const readmeToggleLink = header.querySelector('.task-readme-toggle');
                
                if (readmeToggleLink) { // Only make header clickable if a README toggle exists
                    header.style.cursor = 'pointer'; // Indicate it's clickable
                    header.addEventListener('click', function(event) {
                        // Prevent toggle if the click was on an actual interactive element within the header
                        if (event.target.closest('a, button, .gh-label')) {
                            return;
                        }
                        
                        // Find the collapse target ID from the toggle link
                        const targetId = readmeToggleLink.getAttribute('href');
                        if (targetId) {
                            const readmeContentElement = document.querySelector(targetId);
                            if (readmeContentElement) {
                                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(readmeContentElement);
                                bsCollapse.toggle();
                            }
                        }
                    });
                }
            });
            // --- End of FEATURE--004 ---

            // --- FEATURE--002: Keyboard Shortcuts ---
            const allProjectCollapseElements = document.querySelectorAll('.project-tasks-container.collapse');
            const allTaskReadmeCollapseElements = document.querySelectorAll('.task-readme-content.collapse');

            function setProjectCollapseState(expand) {
                allProjectCollapseElements.forEach(el => {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                    if (expand) {
                        bsCollapse.show();
                    } else {
                        bsCollapse.hide();
                    }
                    // Note: The 'show.bs.collapse' and 'hide.bs.collapse' event listeners
                    // already handle localStorage and icon updates.
                });
            }

            function setTaskReadmeCollapseState(expand) {
                allTaskReadmeCollapseElements.forEach(el => {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
                    if (expand) {
                        bsCollapse.show();
                    } else {
                        bsCollapse.hide();
                    }
                    // TODO: Consider adding localStorage persistence for task READMEs if needed in the future.
                });
            }

            document.addEventListener('keydown', function(event) {
                // Ignore if typing in an input, textarea, or contenteditable element
                if (event.target.isContentEditable || 
                    event.target.tagName === 'INPUT' || 
                    event.target.tagName === 'TEXTAREA' ||
                    event.target.tagName === 'SELECT') {
                    return;
                }

                switch (event.key) {
                    case 'c': // Collapse all task READMEs
                        setTaskReadmeCollapseState(false);
                        break;
                    case 'C': // Collapse all projects and task READMEs
                        setProjectCollapseState(false);
                        setTaskReadmeCollapseState(false);
                        break;
                    case 'e': // Expand all projects, collapse task READMEs
                        setProjectCollapseState(true);
                        setTaskReadmeCollapseState(false);
                        break;
                    case 'E': // Expand all projects and task READMEs
                        setProjectCollapseState(true);
                        setTaskReadmeCollapseState(true);
                        break;
                }
            });
            // --- End of FEATURE--002 ---

        });
    </script>
</body>
</html>