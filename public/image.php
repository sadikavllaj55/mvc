<?php

// A file to show images/files when they are outside the webserver
$file = __DIR__ . '/../' . $_GET['img'];
if (!file_exists($file) || !is_file($file)) {
    header('Content-Type:image/png');
    readfile('https://dummyimage.com/600x400/cccccc/fff.png&text=No+Image');
}

header('Content-Type:' . mime_content_type($file));
readfile($file);