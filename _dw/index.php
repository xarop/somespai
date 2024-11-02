<?php
// URL of the directory where the files are located
$directory_url = "https://cococure.com/wp-content/plugins/";

// Function to download files
function download_file($url, $save_to)
{
    $ch = curl_init($url);
    $fp = fopen($save_to, 'w+');

    // Check if file handle is valid before proceeding
    if (!$fp) {
        echo "Error opening file for writing: $save_to\n";
        return;
    }

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);

    if (curl_error($ch)) {
        echo "Error downloading file: " . curl_error($ch) . "\n";
    } else {
        echo "Downloaded: $save_to\n";
    }

    curl_close($ch);
    fclose($fp);
}

// Function to recursively download files from directories and subdirectories
function download_from_directory($directory_url, $base_path = '')
{
    $html = file_get_contents($directory_url);

    if ($html === FALSE) {
        die("Error fetching the directory listing from $directory_url.");
    }

    // Regex to extract file and directory links
    preg_match_all('/href="([^"]+)"/i', $html, $matches);

    // Loop through each link found
    foreach ($matches[1] as $link) {
        // Skip parent directory links, sorting parameters, and anchor links
        if ($link == '../' || strpos($link, '#') !== false || strpos($link, '?') !== false) {
            continue;
        }

        // Full URL of the item
        $item_url = $directory_url . $link;

        // Check if it is a subdirectory (usually ends with '/')
        if (substr($link, -1) == '/') {
            // It's a subdirectory; recursively call this function
            $subdirectory_name = rtrim($link, '/'); // remove trailing '/'
            echo "Entering directory: $subdirectory_name\n";

            // Make a new directory locally
            $local_subdirectory = $base_path . $subdirectory_name;
            if (!is_dir($local_subdirectory)) {
                mkdir($local_subdirectory);
            }

            // Recursively download files from the subdirectory
            download_from_directory($item_url, $local_subdirectory . '/');
        } else {
            // It's a file; download it
            $file_name = basename($link);
            $save_path = $base_path . $file_name;

            // Download the file
            download_file($item_url, $save_path);
        }
    }
}

// Start downloading files from the base directory
download_from_directory($directory_url);

echo "All files and subdirectories downloaded!";
