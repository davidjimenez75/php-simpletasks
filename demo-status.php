<?php
// filepath: /var/www/html/php-simpletasks/generate_ini_files.php
declare(strict_types=1);

echo "Starting folder.ini generation script.\n";

$baseDir = __DIR__ . '/php-simpletasks-Roadmap';
if (!is_dir($baseDir)) {
    echo "Error: Base directory not found: " . $baseDir . "\n";
    exit(1);
}
echo "Base directory for tasks: " . $baseDir . "\n";

$taskFolders = scandir($baseDir);
if ($taskFolders === false) {
    echo "Error: Could not scan base directory: " . $baseDir . "\n";
    exit(1);
}

$statuses = ["todo", "done", "finished", "next", "wip", "today", "someday", "waiting", "rock", "urgent", "bug", "later", "feature", "review"];
$tags_pool = ["planning", "dev", "test", "ui", "ux", "backend", "frontend", "bugfix", "docs", "research", "urgent", "feature", "refactor", "infra", "security", "performance", "accessibility", "mobile", "api", "database", "config", "core", "build"];

function getRandomElement(array $arr) {
    if (empty($arr)) return null;
    return $arr[array_rand($arr)];
}

function getRandomTags(array $pool, int $count = 2): string {
    if (empty($pool) || $count <= 0) return '';
    $selected_tags = [];
    shuffle($pool);
    for ($i = 0; $i < min($count, count($pool)); $i++) {
        $selected_tags[] = $pool[$i];
    }
    return implode(', ', $selected_tags);
}

$tasksProcessed = 0;
foreach ($taskFolders as $taskFolder) {
    if ($taskFolder === '.' || $taskFolder === '..') continue;
    
    $taskPath = $baseDir . DIRECTORY_SEPARATOR . $taskFolder;
    if (!is_dir($taskPath)) continue;

    $iniFilePath = $taskPath . DIRECTORY_SEPARATOR . 'folder.ini';

    $status = getRandomElement($statuses);
    $tags = getRandomTags($tags_pool, rand(1, 3));
    
    $startDateStr = "";
    $endDateStr = "";

    // Use a fixed date based on context for reproducibility during this generation
    $currentDate = new DateTime('2025-05-18'); 

    if (in_array($status, ['done', 'finished'])) {
        $endDate = clone $currentDate;
        $endDate->modify("-" . rand(1, 365) . " days");
        $endDate->setTime(rand(8,18), rand(0,59), rand(0,59));
        
        $startDate = clone $endDate;
        $startDate->modify("-" . rand(1, 30) . " days");
        $startDate->setTime(rand(8,17), rand(0,59), rand(0,59));

        $startDateStr = $startDate->format('Y-m-d--His');
        $endDateStr = $endDate->format('Y-m-d--His');

    } elseif (in_array($status, ['wip', 'today', 'urgent', 'rock', 'bug', 'feature', 'review'])) {
        $startDate = clone $currentDate;
        $startOffsetDays = rand(0, 60);
        // Make start date mostly in the past, but occasionally today or very near future
        if ($startOffsetDays > 5 && rand(0,10) > 8) { // Small chance to be future for these types if not 'today'
             $startDate->modify("+" . rand(1, 5) . " days");
        } else {
             $startDate->modify("-" . $startOffsetDays . " days");
        }
        $startDate->setTime(rand(8,17), rand(0,59), rand(0,59));
        $startDateStr = $startDate->format('Y-m-d--His');

        if (rand(0, 1) == 1 || in_array($status, ['today', 'urgent', 'rock'])) { 
            $endDate = clone $startDate;
            if ($status == 'today') {
                $endDate = clone $currentDate; // End today
                 // Ensure end time is after start time if on the same day
                if ($endDate->format('Y-m-d') === $startDate->format('Y-m-d') && $endDate < $startDate) {
                    $endDate->setTime(rand(17,20), rand(0,59), rand(0,59)); // Set to later in the day
                    if ($endDate < $startDate) { // If start was already late, push end to next day
                         $endDate->modify('+1 day');
                    }
                }
            } else {
                 $endDate->modify("+" . rand(1, 45) . " days"); 
            }
            // Ensure end is after start
            if ($endDate < $startDate) {
                $endDate = clone $startDate;
                $endDate->modify("+" . rand(1,5) . " days");
            }
            $endDate->setTime(rand(8,18), rand(0,59), rand(0,59));
            $endDateStr = $endDate->format('Y-m-d--His');
        }
    } elseif (in_array($status, ['todo', 'next', 'someday', 'later', 'waiting'])) {
        if (rand(0, 1) == 1) { 
            $startDate = clone $currentDate;
            $startDate->modify("+" . rand(1, 90) . " days"); 
            $startDate->setTime(rand(8,17), rand(0,59), rand(0,59));
            $startDateStr = $startDate->format('Y-m-d--His');

            if (rand(0, 1) == 1 && !in_array($status, ['someday', 'later', 'waiting'])) { 
                $endDate = clone $startDate;
                $endDate->modify("+" . rand(1, 30) . " days"); 
                $endDate->setTime(rand(8,18), rand(0,59), rand(0,59));
                $endDateStr = $endDate->format('Y-m-d--His');
            }
        }
    }

    $iniContent = "status = \"" . $status . "\"\n";
    if (!empty($startDateStr)) {
        $iniContent .= "start = \"" . $startDateStr . "\"\n";
    }
    if (!empty($endDateStr)) {
        $iniContent .= "end = \"" . $endDateStr . "\"\n";
    }
    $iniContent .= "tags = \"" . $tags . "\"\n";

    if (file_put_contents($iniFilePath, $iniContent)) {
        echo "Created/Updated: " . $iniFilePath . " (Status: " . $status . ")\n";
        $tasksProcessed++;
    } else {
        echo "Error writing to: " . $iniFilePath . "\n";
    }
}

echo "Finished generating folder.ini files. Processed " . $tasksProcessed . " task directories.\n";
?>
