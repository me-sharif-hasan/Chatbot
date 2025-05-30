<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitFront</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
</head>
<body>
<div id="container">
    <header>
        <h1>GitFront</h1>
    </header>

    <nav aria-label="breadcrumb" class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <?php
            // Define SOURCE_DIR early if not already defined for other purposes
            // define('SOURCE_DIR', 'source/'); // Already defined below

            // Get current path from query parameter
            $s_relativePath = isset($_GET['path']) ? $_GET['path'] : ''; // Use a prefixed variable

            // Basic sanitization for breadcrumb generation - realpath validation happens later for content
            $s_tempFullPathForBreadcrumb = SOURCE_DIR . $s_relativePath;
            // Prevent too many ../ by normalizing simply, not full security check here
            $s_normalizedPath = [];
            if (!empty($s_relativePath)) {
                foreach(explode('/', $s_relativePath) as $s_part) {
                    if ($s_part == "..") {
                        array_pop($s_normalizedPath);
                    } elseif ($s_part != "." && $s_part != "") {
                        $s_normalizedPath[] = $s_part;
                    }
                }
            }
            $s_cleanedRelativePathForBreadcrumb = implode('/', $s_normalizedPath);


            if (!empty($s_cleanedRelativePathForBreadcrumb)) {
                $s_pathSegments = explode('/', $s_cleanedRelativePathForBreadcrumb);
                $s_cumulativePath = '';
                foreach ($s_pathSegments as $s_index => $s_segment) {
                    // Build cumulative path for the link
                    if (empty($s_cumulativePath)) {
                        $s_cumulativePath = $s_segment;
                    } else {
                        $s_cumulativePath .= '/' . $s_segment;
                    }

                    // Check if it's the last segment
                    if ($s_index < count($s_pathSegments) - 1) {
                        echo "<li class=\"breadcrumb-item\"><a href=\"index.php?path=" . urlencode($s_cumulativePath) . "\">" . htmlspecialchars($s_segment) . "</a></li>";
                    } else {
                        // Last segment is active, not a link
                        echo "<li class=\"breadcrumb-item active\" aria-current=\"page\">" . htmlspecialchars($s_segment) . "</li>";
                    }
                }
            } elseif (!empty($s_relativePath) && ($s_relativePath === '.' || $s_relativePath === '/')) {
                 // If path is explicitly root, show "Home" as active or nothing extra
            }
            ?>
        </ol>
    </nav>

    <main id="content-area">
    <?php
    define('SOURCE_DIR', 'source/');

    // Get current path from query parameter
    $relativePath = isset($_GET['path']) ? $_GET['path'] : '';

    // Include Parsedown
    require_once 'vendor/Parsedown.php';

    // Construct full path
    $currentPath = realpath(SOURCE_DIR . $relativePath);
    $sourceDirReal = realpath(SOURCE_DIR);

    // Security: Path Sanitization and Validation
    if ($currentPath === false || strpos($currentPath, $sourceDirReal) !== 0) {
        echo "<p style='color: red;'>Error: Access denied. Invalid path.</p>";
        $currentPath = $sourceDirReal;
        $relativePath = '';
    }
    // Note: Breadcrumb path ($s_cleanedRelativePathForBreadcrumb) is for display and basic navigation.
    // Main content serving relies on $currentPath and $relativePath which undergo stricter validation.

    if (is_dir($currentPath)) {
        echo "<div class='directory-listing-header'>";
        echo "<h2>Exploring: /" . htmlspecialchars($relativePath) . "</h2>";
        // Display "Up" link for directories - only if not at the root
        if ($currentPath !== $sourceDirReal && $relativePath !== '' && $relativePath !== DIRECTORY_SEPARATOR) {
            $parentPath = dirname($relativePath);
            if ($parentPath === '.' || $parentPath === DIRECTORY_SEPARATOR) $parentPath = ''; // Ensure root is empty path for link
            echo "<p class='up-link'><a href='index.php?path=" . urlencode($parentPath) . "'>⬆️ Up to parent directory</a></p>";
        }
        echo "</div>";

        echo "<ul class='file-list'>";
        $items = scandir($currentPath);
        if ($items === false) {
            echo "<li class='list-item error-item'><p style='color: red;'>Error: Could not scan directory.</p></li>";
        } else {
            // Separate directories and files for sorting
            $dirs = [];
            $files = [];
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $itemPath = $currentPath . DIRECTORY_SEPARATOR . $item;
                $itemRelativePath = $relativePath ? $relativePath . DIRECTORY_SEPARATOR . $item : $item;
                if (is_dir($itemPath)) {
                    $dirs[] = ['name' => $item, 'path' => $itemRelativePath];
                } else {
                    $files[] = ['name' => $item, 'path' => $itemRelativePath];
                }
            }

            // Output directories first
            foreach ($dirs as $dir) {
                echo "<li class='list-item dir-item'><a href='index.php?path=" . urlencode($dir['path']) . "'>📁 " . htmlspecialchars($dir['name']) . "/</a></li>";
            }
            // Then output files
            foreach ($files as $file) {
                echo "<li class='list-item file-item'><a href='index.php?path=" . urlencode($file['path']) . "'>📄 " . htmlspecialchars($file['name']) . "</a></li>";
            }
            if (empty($dirs) && empty($files)) {
                echo "<li class='list-item empty-dir-item'><p>This directory is empty.</p></li>";
            }
        }
        echo "</ul>";

    } elseif (is_file($currentPath)) {
        $filename = basename($currentPath);
        $parentDirRelativePath = dirname($relativePath);
        if ($parentDirRelativePath === '.' || $parentDirRelativePath === DIRECTORY_SEPARATOR) $parentDirRelativePath = '';

        echo "<div class='file-view-header'>";
        echo "<h2>Viewing File: " . htmlspecialchars($filename) . "</h2>";
         // "Up" link for files - always points to the directory containing the file
        echo "<p class='up-link'><a href='index.php?path=" . urlencode($parentDirRelativePath) . "'>⬆️ Up to /" . htmlspecialchars($parentDirRelativePath) . "</a></p>";
        echo "</div>";

        $content = file_get_contents($currentPath);
        if ($content === false) {
            echo "<p class='error-item' style='color: red;'>Error: Could not read file content.</p>";
        } else {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $textExtensions = ['txt', 'md', 'markdown', 'php', 'js', 'css', 'html', 'xml', 'json', 'java', 'c', 'cpp', 'h', 'py', 'sh', 'log'];

            if (in_array($extension, ['md', 'markdown'])) {
                $Parsedown = new Parsedown();
                echo "<div class='markdown-content'>";
                echo $Parsedown->text($content);
                echo "</div>";
            } elseif (in_array($extension, $textExtensions) || $extension == '') {
                $langClass = '';
                switch ($extension) {
                    case 'java':
                        $langClass = 'language-java';
                        break;
                    case 'xml':
                        $langClass = 'language-xml';
                        break;
                    case 'properties':
                        $langClass = 'language-properties';
                        break;
                    case 'yaml':
                    case 'yml':
                        $langClass = 'language-yaml';
                        break;
                    case 'sh':
                    case 'bash':
                        $langClass = 'language-shell';
                        break;
                    default:
                        // For other text files, let highlight.js auto-detect or use no specific class
                        // $langClass = 'language-plaintext'; // Or omit for auto-detection
                        break;
                }
                echo "<pre><code class=\"" . $langClass . "\">" . htmlspecialchars($content) . "</code></pre>";
            } else {
                echo "<p>Cannot display binary file or unsupported file type: " . htmlspecialchars($filename) . "</p>";
            }
        }
    } else {
        // This case should ideally be caught by the earlier $currentPath validation
        echo "<p style='color: red;'>Error: Path is not a valid file or directory.</p>";
        // Provide a link to the root if path is invalid
        echo "<p><a href='index.php'>Return to root</a></p>";
    }
    ?>
    </main>
    <footer>
        <p>GitFront - A simple file viewer</p>
    </footer>
</div> <!-- end container -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
