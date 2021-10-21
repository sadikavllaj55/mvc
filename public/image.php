<?php

// A file to show images/files when they are outside the webserver
$file = __DIR__ . '/../' . $_GET['img'];
header('Content-Type:' . mime_content_type($file));
readfile($file);